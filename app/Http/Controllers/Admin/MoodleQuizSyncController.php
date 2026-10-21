<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\MasterMoodle;
use App\Models\MoodleQuizAttempt;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class MoodleQuizSyncController extends Controller
{
    private $moodleUrl;
    private $moodleToken;
    private $quizId = 2;

    public function __construct()
    {
        $this->moodleUrl = config('services.moodle.url', 'https://elearning2.ubpkarawang.ac.id/webservice/rest/server.php');
        $this->moodleToken = config('services.moodle.token', '47d4b340520e06fbb1d21127d683ae60');

        // Log untuk debugging
        Log::info("MoodleQuizSyncController initialized", [
            'url' => $this->moodleUrl,
            'token_length' => strlen($this->moodleToken),
            'quiz_id' => $this->quizId
        ]);
    }

    /**
     * Sync all quiz attempts for all users (hanya data baru)
     */
    public function syncAllQuizAttempts(Request $request): JsonResponse
    {
        Log::info("🔄 Sync quiz attempts (hanya data baru) dimulai");

        DB::beginTransaction();

        try {
            // Validasi koneksi Moodle
            Log::info("🔍 Validasi koneksi Moodle");
            $connectionValid = $this->validateMoodleConnection();

            if (!$connectionValid) {
                DB::rollBack();
                Log::error("❌ Validasi koneksi Moodle gagal");
                return response()->json([
                    'success' => false,
                    'message' => 'Koneksi ke Moodle gagal. Token mungkin tidak valid atau server tidak dapat diakses.',
                    'quiz_id' => $this->quizId
                ], 500);
            }

            Log::info("✅ Validasi koneksi Moodle berhasil");

            // Ambil users dengan error handling
            try {
                $moodleUsers = MasterMoodle::whereNotNull('moodle_user_id')->get();
                Log::info("📊 Jumlah users ditemukan: " . $moodleUsers->count());
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error("❌ Error mengambil data users: " . $e->getMessage());
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal mengambil data users: ' . $e->getMessage(),
                    'quiz_id' => $this->quizId
                ], 500);
            }

            $results = [
                'success' => 0,
                'failed' => 0,
                'attempts_synced' => 0,
                'users_with_attempts' => 0,
                'skipped_existing' => 0,
                'errors' => []
            ];

            Log::info("🔄 Memulai sync quiz attempts (Quiz ID: {$this->quizId}) untuk {$moodleUsers->count()} users - MODE: HANYA DATA BARU");

            foreach ($moodleUsers as $index => $moodleUser) {
                Log::info("👤 Processing user {$index}/{$moodleUsers->count()}: {$moodleUser->username} (ID: {$moodleUser->moodle_user_id})");

                try {
                    // Gunakan forceUpdate = false untuk hanya sync data baru
                    $userResult = $this->syncUserQuizAttempts($moodleUser, false);

                    if ($userResult['success']) {
                        $results['success']++;
                        $results['attempts_synced'] += $userResult['attempts_synced'];
                        $results['skipped_existing'] += isset($userResult['skipped_existing']) ? $userResult['skipped_existing'] : 0;

                        if ($userResult['attempts_synced'] > 0) {
                            $results['users_with_attempts']++;
                        }

                        $skippedCount = isset($userResult['skipped_existing']) ? $userResult['skipped_existing'] : 0;
                        Log::info("✅ User {$moodleUser->username} berhasil: {$userResult['attempts_synced']} attempts baru, {$skippedCount} attempts existing di-skip");
                    } else {
                        $results['failed']++;
                        $errorMsg = isset($userResult['error']) ? $userResult['error'] : 'Unknown error';
                        $results['errors'][] = [
                            'user' => $moodleUser->username,
                            'moodle_user_id' => $moodleUser->moodle_user_id,
                            'error' => $errorMsg
                        ];
                        Log::warning("⚠️ User {$moodleUser->username} gagal: " . $errorMsg);
                    }
                } catch (\Exception $e) {
                    $results['failed']++;
                    $errorMsg = $e->getMessage();
                    $results['errors'][] = [
                        'user' => $moodleUser->username,
                        'moodle_user_id' => $moodleUser->moodle_user_id,
                        'error' => $errorMsg
                    ];
                    Log::error("❌ Exception processing user {$moodleUser->username}: " . $errorMsg);
                }

                // Beri jeda untuk tidak membebani server
                if ($index % 10 === 0) {
                    sleep(1);
                }
            }

            DB::commit();

            Log::info("✅ Sync selesai. {$results['success']} users berhasil, {$results['failed']} gagal, {$results['attempts_synced']} attempts baru, {$results['skipped_existing']} attempts existing di-skip");

            // Dalam method syncAllQuizAttempts dan forceSyncQuizAttempts
            return response()->json([
                'success' => true,
                'message' => 'Sync quiz attempts berhasil (hanya data baru)',
                'quiz_id' => $this->quizId,
                'mode' => 'new_data_only',
                'results' => $results,
                'summary' => [
                    'total_users' => $moodleUsers->count(),
                    'users_success' => $results['success'],
                    'users_failed' => $results['failed'],
                    'users_with_attempts' => $results['users_with_attempts'],
                    'users_without_attempts' => $moodleUsers->count() - $results['users_with_attempts'],
                    'total_attempts_synced' => $results['attempts_synced'],
                    'existing_attempts_skipped' => $results['skipped_existing'],
                    'existing_attempts_updated' => $results['existing_updated'],
                    'attempts_no_changes' => $results['no_changes'] ?? 0, // Tambahkan ini
                    'total_errors' => count($results['errors'])
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            $errorMessage = '💥 Error sync quiz attempts: ' . $e->getMessage();
            Log::error($errorMessage, [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal sync quiz attempts: ' . $e->getMessage(),
                'quiz_id' => $this->quizId,
                'debug' => [
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ]
            ], 500);
        }
    }
    /**
     * Manual update untuk attempt tertentu (hanya jika benar-benar diperlukan)
     */
    public function manualUpdateAttempt(Request $request, $attemptId): JsonResponse
    {
        try {
            $attempt = MoodleQuizAttempt::where('attempt_id', $attemptId)->first();

            if (!$attempt) {
                return response()->json([
                    'success' => false,
                    'message' => 'Attempt tidak ditemukan'
                ], 404);
            }

            $validated = $request->validate([
                'sumgrades' => 'sometimes|numeric',
                'state' => 'sometimes|string'
            ]);

            $attempt->update($validated);

            Log::info("🔧 Manual update attempt: ID {$attemptId}, Changes: " . json_encode($validated));

            return response()->json([
                'success' => true,
                'message' => 'Attempt berhasil diupdate manual',
                'data' => $attempt
            ]);
        } catch (\Exception $e) {
            Log::error("❌ Error manual update attempt {$attemptId}: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal update manual: ' . $e->getMessage()
            ], 500);
        }
    }
    public function cleanDuplicateAttempts(): JsonResponse
    {
        try {
            $duplicates = DB::table('moodle_quiz_attempts')
                ->select('attempt_id', 'moodle_user_id', 'quiz_id', DB::raw('COUNT(*) as count'))
                ->groupBy('attempt_id', 'moodle_user_id', 'quiz_id')
                ->having('count', '>', 1)
                ->get();

            $deletedCount = 0;

            foreach ($duplicates as $duplicate) {
                $records = MoodleQuizAttempt::where('attempt_id', (string)$duplicate->attempt_id)
                    ->where('moodle_user_id', (string)$duplicate->moodle_user_id)
                    ->where('quiz_id', (string)$duplicate->quiz_id)
                    ->orderBy('created_at', 'desc')
                    ->get();

                // Keep the most recent one, delete others
                if ($records->count() > 1) {
                    $keep = $records->first();
                    $deleteCount = $records->slice(1)->count();

                    MoodleQuizAttempt::where('attempt_id', (string)$duplicate->attempt_id)
                        ->where('moodle_user_id', (string)$duplicate->moodle_user_id)
                        ->where('quiz_id', (string)$duplicate->quiz_id)
                        ->where('id', '!=', $keep->id)
                        ->delete();

                    $deletedCount += $deleteCount;
                    Log::info("🧹 Deleted {$deleteCount} duplicate attempts for attempt_id: {$duplicate->attempt_id}");
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Duplicate cleanup completed',
                'duplicates_found' => $duplicates->count(),
                'records_deleted' => $deletedCount
            ]);
        } catch (\Exception $e) {
            Log::error("❌ Error cleaning duplicates: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal membersihkan duplikasi: ' . $e->getMessage()
            ], 500);
        }
    }
 /**
 * Force sync - HANYA untuk menambahkan data baru saja
 */
public function forceSyncQuizAttempts(Request $request): JsonResponse
{
    Log::info("🔄 Force sync quiz attempts dimulai - MODE: HANYA DATA BARU");
    
    DB::beginTransaction();
    
    try {
        // Validasi koneksi Moodle
        Log::info("🔍 Validasi koneksi Moodle");
        $connectionValid = $this->validateMoodleConnection();
        
        if (!$connectionValid) {
            DB::rollBack();
            Log::error("❌ Validasi koneksi Moodle gagal");
            return response()->json([
                'success' => false,
                'message' => 'Koneksi ke Moodle gagal. Token mungkin tidak valid atau server tidak dapat diakses.',
                'quiz_id' => $this->quizId
            ], 500);
        }

        Log::info("✅ Validasi koneksi Moodle berhasil");
        
        // Ambil users dengan error handling
        try {
            $moodleUsers = MasterMoodle::whereNotNull('moodle_user_id')->get();
            Log::info("📊 Jumlah users ditemukan: " . $moodleUsers->count());
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("❌ Error mengambil data users: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data users: ' . $e->getMessage(),
                'quiz_id' => $this->quizId
            ], 500);
        }

        $results = [
            'success' => 0,
            'failed' => 0,
            'attempts_synced' => 0,
            'users_with_attempts' => 0,
            'skipped_existing' => 0,
            'errors' => []
        ];

        Log::info("🔄 Memulai force sync quiz attempts (Quiz ID: {$this->quizId}) untuk {$moodleUsers->count()} users - MODE: HANYA DATA BARU");

        foreach ($moodleUsers as $index => $moodleUser) {
            Log::info("👤 Processing user {$index}/{$moodleUsers->count()}: {$moodleUser->username} (ID: {$moodleUser->moodle_user_id})");
            
            try {
                // SELALU gunakan forceUpdate = false untuk melindungi data existing
                $userResult = $this->syncUserQuizAttempts($moodleUser, false);
                
                if ($userResult['success']) {
                    $results['success']++;
                    $results['attempts_synced'] += $userResult['attempts_synced'];
                    $results['skipped_existing'] += $userResult['skipped_existing'];
                    
                    if ($userResult['attempts_synced'] > 0) {
                        $results['users_with_attempts']++;
                    }
                    
                    Log::info("✅ User {$moodleUser->username} berhasil: {$userResult['attempts_synced']} attempts BARU, {$userResult['skipped_existing']} attempts existing di-SKIP");
                } else {
                    $results['failed']++;
                    $errorMsg = isset($userResult['error']) ? $userResult['error'] : 'Unknown error';
                    $results['errors'][] = [
                        'user' => $moodleUser->username,
                        'moodle_user_id' => $moodleUser->moodle_user_id,
                        'error' => $errorMsg
                    ];
                    Log::warning("⚠️ User {$moodleUser->username} gagal: " . $errorMsg);
                }

            } catch (\Exception $e) {
                $results['failed']++;
                $errorMsg = $e->getMessage();
                $results['errors'][] = [
                    'user' => $moodleUser->username,
                    'moodle_user_id' => $moodleUser->moodle_user_id,
                    'error' => $errorMsg
                ];
                Log::error("❌ Exception processing user {$moodleUser->username}: " . $errorMsg);
            }
            
            // Beri jeda untuk tidak membebani server
            if ($index % 10 === 0) {
                sleep(1);
            }
        }

        DB::commit();

        Log::info("✅ Force sync selesai. {$results['success']} users berhasil, {$results['failed']} gagal, {$results['attempts_synced']} attempts BARU, {$results['skipped_existing']} attempts existing di-SKIP");

        return response()->json([
            'success' => true,
            'message' => 'Force sync quiz attempts berhasil - HANYA DATA BARU',
            'quiz_id' => $this->quizId,
            'mode' => 'new_data_only_protected',
            'results' => $results,
            'summary' => [
                'total_users' => $moodleUsers->count(),
                'users_success' => $results['success'],
                'users_failed' => $results['failed'],
                'users_with_attempts' => $results['users_with_attempts'],
                'users_without_attempts' => $moodleUsers->count() - $results['users_with_attempts'],
                'total_attempts_synced' => $results['attempts_synced'],
                'existing_attempts_protected' => $results['skipped_existing'],
                'total_errors' => count($results['errors'])
            ]
        ]);

    } catch (\Exception $e) {
        DB::rollBack();
        $errorMessage = '💥 Error force sync quiz attempts: ' . $e->getMessage();
        Log::error($errorMessage);
        
        return response()->json([
            'success' => false,
            'message' => 'Gagal force sync quiz attempts: ' . $e->getMessage(),
            'quiz_id' => $this->quizId
        ], 500);
    }
}

    /**
     * Sync quiz attempts for specific user
     */
    public function syncUserQuizAttemptsData(Request $request, $moodleUserId): JsonResponse
    {
        try {
            // Validasi koneksi Moodle
            if (!$this->validateMoodleConnection()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Koneksi ke Moodle gagal. Token mungkin tidak valid.',
                    'quiz_id' => $this->quizId
                ], 500);
            }

            $moodleUser = MasterMoodle::where('moodle_user_id', $moodleUserId)->first();

            if (!$moodleUser) {
                return response()->json([
                    'success' => false,
                    'message' => 'User tidak ditemukan dengan moodle_user_id: ' . $moodleUserId
                ], 404);
            }

            $forceUpdate = $request->get('force', false);
            $result = $this->syncUserQuizAttempts($moodleUser, $forceUpdate);

            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'message' => 'Sync quiz attempts berhasil untuk user ' . $moodleUser->username,
                    'quiz_id' => $this->quizId,
                    'mode' => $forceUpdate ? 'force_update' : 'new_data_only',
                    'data' => $result
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal sync quiz attempts: ' . $result['error'],
                    'quiz_id' => $this->quizId,
                    'data' => $result
                ], 400);
            }
        } catch (\Exception $e) {
            Log::error("💥 Error sync user quiz attempts {$moodleUserId}: " . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal sync quiz attempts: ' . $e->getMessage(),
                'quiz_id' => $this->quizId
            ], 500);
        }
    }
    /**
     * Sync quiz attempts for a single user dengan improved duplicate prevention
     */
    private function syncUserQuizAttempts(MasterMoodle $moodleUser, bool $forceUpdate = false): array
{
    try {
        Log::info("🎯 Sync quiz attempts untuk user: {$moodleUser->moodle_user_id} ({$moodleUser->username}) - Mode: HANYA DATA BARU");

        $attemptsSynced = 0;
        $skippedExisting = 0;
        $errors = [];

        // Validasi moodle_user_id
        if (empty($moodleUser->moodle_user_id)) {
            $errorMsg = "Moodle user ID kosong untuk user {$moodleUser->username}";
            Log::warning("⚠️ " . $errorMsg);
            return [
                'success' => false,
                'user' => $moodleUser->username,
                'moodle_user_id' => $moodleUser->moodle_user_id,
                'quiz_id' => $this->quizId,
                'error' => $errorMsg,
                'attempts_synced' => 0,
                'skipped_existing' => 0
            ];
        }

        try {
            $quizAttempts = $this->getUserQuizAttempts($moodleUser->moodle_user_id);
            
            if ($quizAttempts && is_array($quizAttempts) && count($quizAttempts) > 0) {
                Log::info("📥 Memproses " . count($quizAttempts) . " attempts untuk user {$moodleUser->username}");
                
                foreach ($quizAttempts as $attemptIndex => $attemptData) {
                    try {
                        // Validasi bahwa ini adalah attempt data yang valid
                        if (!is_array($attemptData) || empty($attemptData)) {
                            Log::warning("⚠️ Attempt data tidak valid pada index {$attemptIndex}", [
                                'user' => $moodleUser->username
                            ]);
                            continue;
                        }

                        // SELALU gunakan forceUpdate = false untuk melindungi data existing
                        $saveResult = $this->saveQuizAttempt($moodleUser->moodle_user_id, $attemptData, false);
                        
                        if ($saveResult['saved']) {
                            $attemptsSynced++;
                            Log::info("✅ Berhasil menyimpan attempt BARU {$attemptIndex} untuk user {$moodleUser->username}");
                        } else {
                            if ($saveResult['skipped_existing']) {
                                $skippedExisting++;
                                Log::debug("⏭️  Skipped attempt existing {$attemptIndex} untuk user {$moodleUser->username}");
                            }
                        }
                    } catch (\Exception $e) {
                        $errorMsg = "Attempt {$attemptIndex}: " . $e->getMessage();
                        $errors[] = $errorMsg;
                        Log::error("❌ Error save attempt {$attemptIndex}: " . $e->getMessage());
                    }
                }
                
                Log::info("📊 Summary untuk user {$moodleUser->username}: {$attemptsSynced} attempts BARU, {$skippedExisting} attempts existing di-SKIP (dilindungi)");
            } else {
                Log::info("ℹ️  Tidak ada attempts untuk user {$moodleUser->username} di quiz {$this->quizId}");
            }

        } catch (\Exception $e) {
            $errorMsg = "Quiz {$this->quizId}: " . $e->getMessage();
            $errors[] = $errorMsg;
            Log::error("❌ Error process quiz {$this->quizId} untuk user {$moodleUser->username}: " . $e->getMessage());
        }

        // Update master_moodle record hanya jika ada data baru
        try {
            if ($attemptsSynced > 0) {
                $moodleUser->update([
                    'last_sync_at' => now(),
                    'sync_notes' => "Quiz attempts sync: {$attemptsSynced} attempts BARU pada quiz {$this->quizId} - " . now()->format('Y-m-d H:i:s'),
                    'data_uploaded' => true,
                    'last_upload_at' => now()
                ]);
                Log::info("✅ Updated master_moodle record untuk user {$moodleUser->username}");
            }
        } catch (\Exception $e) {
            Log::error("❌ Error update master_moodle untuk user {$moodleUser->username}: " . $e->getMessage());
        }

        return [
            'success' => true,
            'user' => $moodleUser->username,
            'moodle_user_id' => $moodleUser->moodle_user_id,
            'quiz_id' => $this->quizId,
            'attempts_synced' => $attemptsSynced,
            'skipped_existing' => $skippedExisting,
            'errors' => $errors,
            'action' => 'created_only'
        ];

    } catch (\Exception $e) {
        $errorMsg = "❌ Error sync user quiz attempts {$moodleUser->moodle_user_id}: " . $e->getMessage();
        Log::error($errorMsg);
        
        return [
            'success' => false,
            'user' => $moodleUser->username,
            'moodle_user_id' => $moodleUser->moodle_user_id,
            'quiz_id' => $this->quizId,
            'error' => $e->getMessage(),
            'attempts_synced' => 0,
            'skipped_existing' => 0
        ];
    }
}

    /**
     * Get user quiz attempts from Moodle
     */
    private function getUserQuizAttempts($moodleUserId): ?array
    {
        try {
            Log::debug("🔍 Getting quiz attempts for user: {$moodleUserId}, quiz: {$this->quizId}");

            $response = $this->callMoodleApi('mod_quiz_get_user_attempts', [
                'quizid' => $this->quizId,
                'userid' => $moodleUserId
            ]);

            if (isset($response['exception'])) {
                if (isset($response['errorcode']) && $response['errorcode'] === 'activityisscheduledfordeletion') {
                    Log::warning("⏸️  Quiz {$this->quizId} sedang dihapus");
                    return null;
                }
                $errorMessage = isset($response['message']) ? $response['message'] : 'Unknown error';
                Log::warning("⚠️  Error get attempts quiz {$this->quizId} untuk user {$moodleUserId}: {$errorMessage}");
                return null;
            }

            // Handle response structure yang benar
            // Response dari Moodle: {"attempts":[{...}, {...}]}
            if (isset($response['attempts']) && is_array($response['attempts'])) {
                Log::info("📥 Ditemukan " . count($response['attempts']) . " attempts untuk user {$moodleUserId}");
                return $response['attempts'];
            }

            // Jika response langsung array (fallback)
            if (is_array($response) && !isset($response['exception'])) {
                Log::info("📥 Ditemukan " . count($response) . " attempts untuk user {$moodleUserId} (direct array)");
                return $response;
            }

            Log::warning("⚠️  Format response tidak dikenali untuk user {$moodleUserId}", ['response_keys' => array_keys($response)]);
            return null;
        } catch (\Exception $e) {
            Log::error("❌ Error get user quiz attempts untuk user {$moodleUserId}: " . $e->getMessage());
            return null;
        }
    }
private function saveQuizAttempt($moodleUserId, array $attemptData, bool $forceUpdate = false): array
{
    try {
        // Validasi data dengan lebih ketat
        $attemptId = isset($attemptData['id']) ? $attemptData['id'] : null;
        $quizId = isset($attemptData['quiz']) ? $attemptData['quiz'] : $this->quizId;
        $userId = isset($attemptData['userid']) ? $attemptData['userid'] : $moodleUserId;

        if (!$attemptId || !$quizId || !$userId) {
            Log::warning("⚠️ Data attempt tidak valid", [
                'attempt_id' => $attemptId,
                'quiz_id' => $quizId,
                'user_id' => $userId,
                'moodle_user_id' => $moodleUserId
            ]);
            return [
                'saved' => false,
                'skipped_existing' => false,
                'existing_updated' => false,
                'reason' => 'invalid_data'
            ];
        }

        // Cek apakah attempt sudah ada
        $existingAttempt = MoodleQuizAttempt::where([
            'attempt_id' => $attemptId,
            'moodle_user_id' => $userId,
            'quiz_id' => $quizId
        ])->first();

        $isExisting = $existingAttempt !== null;

        // JIKA DATA SUDAH ADA: Skip update untuk menjaga modifikasi manual
        if ($isExisting) {
            Log::debug("⏭️  Attempt sudah ada: ID {$attemptId} untuk user {$userId} - SKIPPED (protect manual changes)");
            return [
                'saved' => false,
                'skipped_existing' => true,
                'existing_updated' => false,
                'reason' => 'existing_protected'
            ];
        }

        // HANYA SIMPAN DATA BARU YANG BELUM ADA
        // Hitung durasi
        $duration = null;
        $timestart = isset($attemptData['timestart']) ? $attemptData['timestart'] : 0;
        $timefinish = isset($attemptData['timefinish']) ? $attemptData['timefinish'] : 0;
        
        if ($timestart > 0 && $timefinish > 0) {
            $duration = $timefinish - $timestart;
        }

        // Konversi timestamp ke datetime
        $timestartDate = $timestart > 0 ? Carbon::createFromTimestamp($timestart) : null;
        $timefinishDate = $timefinish > 0 ? Carbon::createFromTimestamp($timefinish) : null;

        // Prepare data untuk disimpan
        $saveData = [
            'state' => isset($attemptData['state']) ? $attemptData['state'] : 'unknown',
            'timestart' => $timestartDate,
            'timefinish' => $timefinishDate,
            'attempted_at' => now(),
            'sumgrades' => isset($attemptData['sumgrades']) ? $attemptData['sumgrades'] : 0,
            'attempt_data' => json_encode($attemptData),
            'duration' => $duration,
            'created_at' => now(),
            'updated_at' => now()
        ];

        Log::debug("💾 Menyimpan attempt data BARU", [
            'attempt_id' => $attemptId,
            'user_id' => $userId,
            'quiz_id' => $quizId,
            'state' => $saveData['state'],
            'sumgrades' => $saveData['sumgrades']
        ]);

        // HANYA CREATE DATA BARU
        MoodleQuizAttempt::create(array_merge([
            'attempt_id' => $attemptId,
            'moodle_user_id' => $userId,
            'quiz_id' => $quizId
        ], $saveData));

        $grade = isset($attemptData['sumgrades']) ? $attemptData['sumgrades'] : 0;
        $state = isset($attemptData['state']) ? $attemptData['state'] : 'unknown';
        
        Log::info("✅ Attempt CREATED: ID {$attemptId}, User {$userId}, Quiz {$quizId}, State: {$state}, Nilai: {$grade}");
        
        return [
            'saved' => true,
            'skipped_existing' => false,
            'existing_updated' => false,
            'action' => 'created'
        ];

    } catch (\Exception $e) {
        $attemptId = isset($attemptData['id']) ? $attemptData['id'] : 'unknown';
        Log::error("❌ Error save quiz attempt: " . $e->getMessage(), [
            'attempt_id' => $attemptId,
            'user_id' => $moodleUserId
        ]);
        return [
            'saved' => false,
            'skipped_existing' => false,
            'existing_updated' => false,
            'reason' => 'error'
        ];
    }
}
    /**
     * Check if attempt data has actually changed
     */
    private function hasAttemptDataChanged(MoodleQuizAttempt $existingAttempt, array $newAttemptData): bool
    {
        // Compare critical fields
        $newState = isset($newAttemptData['state']) ? $newAttemptData['state'] : 'unknown';
        $newSumgrades = isset($newAttemptData['sumgrades']) ? $newAttemptData['sumgrades'] : 0;

        $timestart = isset($newAttemptData['timestart']) ? $newAttemptData['timestart'] : 0;
        $timefinish = isset($newAttemptData['timefinish']) ? $newAttemptData['timefinish'] : 0;

        $newTimestart = $timestart > 0 ? Carbon::createFromTimestamp($timestart) : null;
        $newTimefinish = $timefinish > 0 ? Carbon::createFromTimestamp($timefinish) : null;

        // Check if any critical field has changed
        if ($existingAttempt->state !== $newState) {
            return true;
        }

        if (floatval($existingAttempt->sumgrades) !== floatval($newSumgrades)) {
            return true;
        }

        if ($existingAttempt->timestart != $newTimestart) {
            return true;
        }

        if ($existingAttempt->timefinish != $newTimefinish) {
            return true;
        }

        return false;
    }

    /**
     * Validasi koneksi ke Moodle
     */
    private function validateMoodleConnection(): bool
    {
        try {
            Log::info("🔗 Testing Moodle connection...");

            $response = $this->callMoodleApi('core_webservice_get_site_info', []);

            if (isset($response['exception'])) {
                Log::error("❌ Moodle connection validation failed: " . $response['message']);
                return false;
            }

            if (isset($response['sitename'])) {
                Log::info("✅ Moodle connection successful. Site: " . $response['sitename']);
                return true;
            }

            Log::warning("⚠️ Moodle connection response unexpected", ['response' => $response]);
            return false;
        } catch (\Exception $e) {
            Log::error("❌ Moodle connection validation error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Call Moodle API
     */
    private function callMoodleApi(string $function, array $params): array
    {
        try {
            $payload = [
                'wstoken' => $this->moodleToken,
                'wsfunction' => $function,
                'moodlewsrestformat' => 'json'
            ];

            $payload = array_merge($payload, $params);

            Log::debug("📡 Calling Moodle API: {$function}", [
                'params' => $params,
                'url' => $this->moodleUrl
            ]);

            $response = Http::timeout(30)
                ->retry(2, 1000)
                ->asForm()
                ->post($this->moodleUrl, $payload);

            Log::debug("📡 Moodle API Response Status: " . $response->status());

            if ($response->failed()) {
                $errorBody = $response->body();
                Log::error("❌ Moodle API HTTP Error", [
                    'status' => $response->status(),
                    'body' => substr($errorBody, 0, 1000),
                    'function' => $function
                ]);
                return [
                    'exception' => true,
                    'message' => 'HTTP Error: ' . $response->status(),
                    'errorcode' => 'http_error',
                    'details' => 'Status: ' . $response->status()
                ];
            }

            $content = $response->body();
            Log::debug("📡 Moodle API Response Body Preview: " . substr($content, 0, 200));

            // Cek jika response adalah HTML (bukan JSON)
            if (str_starts_with(trim($content), '<!DOCTYPE') || str_starts_with(trim($content), '<html')) {
                Log::error("❌ Moodle API returned HTML instead of JSON", [
                    'content_preview' => substr($content, 0, 500),
                    'function' => $function
                ]);
                return [
                    'exception' => true,
                    'message' => 'Invalid response format: HTML received instead of JSON',
                    'errorcode' => 'invalid_format',
                    'details' => 'Server returned HTML page'
                ];
            }

            $result = $response->json();

            // Jika json decoding gagal
            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::error("❌ JSON decode error", [
                    'error' => json_last_error_msg(),
                    'content_preview' => substr($content, 0, 500),
                    'function' => $function
                ]);
                return [
                    'exception' => true,
                    'message' => 'JSON decode error: ' . json_last_error_msg(),
                    'errorcode' => 'json_error',
                    'details' => 'JSON parsing failed'
                ];
            }

            if (isset($result['exception'])) {
                Log::warning("⚠️ Moodle API Exception", [
                    'function' => $function,
                    'exception' => $result['exception'],
                    'message' => isset($result['message']) ? $result['message'] : 'No message',
                    'errorcode' => isset($result['errorcode']) ? $result['errorcode'] : 'unknown'
                ]);
            }

            return $result;
        } catch (\Exception $e) {
            Log::error("❌ Moodle API Call Failed", [
                'function' => $function,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            return [
                'exception' => true,
                'message' => $e->getMessage(),
                'errorcode' => 'connection_error',
                'details' => 'Exception during API call'
            ];
        }
    }

    /**
     * Get sync statistics
     */
    public function getSyncStatistics(): JsonResponse
    {
        try {
            $totalUsers = MasterMoodle::whereNotNull('moodle_user_id')->count();
            $usersWithAttempts = MoodleQuizAttempt::where('quiz_id', $this->quizId)
                ->distinct('moodle_user_id')
                ->count();
            $totalAttempts = MoodleQuizAttempt::where('quiz_id', $this->quizId)->count();

            $recentSync = MasterMoodle::whereNotNull('last_sync_at')
                ->orderBy('last_sync_at', 'desc')
                ->first();

            $gradeStats = MoodleQuizAttempt::where('quiz_id', $this->quizId)
                ->selectRaw('COUNT(*) as total_attempts')
                ->selectRaw('AVG(sumgrades) as average_grade')
                ->selectRaw('MAX(sumgrades) as highest_grade')
                ->selectRaw('MIN(sumgrades) as lowest_grade')
                ->first();

            $stateStats = MoodleQuizAttempt::where('quiz_id', $this->quizId)
                ->select('state')
                ->selectRaw('COUNT(*) as count')
                ->groupBy('state')
                ->get()
                ->pluck('count', 'state');

            return response()->json([
                'success' => true,
                'data' => [
                    'quiz_id' => $this->quizId,
                    'total_users' => $totalUsers,
                    'users_with_attempts' => $usersWithAttempts,
                    'users_without_attempts' => $totalUsers - $usersWithAttempts,
                    'total_attempts' => $totalAttempts,
                    'last_sync' => $recentSync ? $recentSync->last_sync_at : null,
                    'completion_rate' => $totalUsers > 0 ? round(($usersWithAttempts / $totalUsers) * 100, 2) : 0,
                    'grade_statistics' => [
                        'average_grade' => $gradeStats->average_grade ? round($gradeStats->average_grade, 2) : 0,
                        'highest_grade' => $gradeStats->highest_grade ? $gradeStats->highest_grade : 0,
                        'lowest_grade' => $gradeStats->lowest_grade ? $gradeStats->lowest_grade : 0,
                        'total_attempts' => $gradeStats->total_attempts ? $gradeStats->total_attempts : 0
                    ],
                    'state_statistics' => $stateStats
                ],
                'message' => 'Statistik sync berhasil diambil'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil statistik: ' . $e->getMessage()
            ], 500);
        }
    }
}
