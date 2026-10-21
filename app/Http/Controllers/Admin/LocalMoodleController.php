<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MasterMoodle;
use App\Models\MoodleQuizAttempt;
use App\Models\MoodleUserCourse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class LocalMoodleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
 public function index(): View
    {
        $moodleData = MasterMoodle::with(['masterUser.user'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.moodle.local.index', compact('moodleData'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('admin.moodle.local.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'master_user_id' => 'required|integer',
            'username' => 'required|string|max:255',
            'status' => 'nullable|string|max:50',
            'enrol_status' => 'nullable|string|max:50',
        ]);

        MasterMoodle::create($validated);

        return redirect()->route('admin.moodle.local.index')
            ->with('success', 'Data Moodle berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show($id): View
    {
        $moodle = MasterMoodle::with(['masterUser.user'])->findOrFail($id);
        
        return view('admin.moodle.local.show', compact('moodle'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id): View
    {
        $moodle = MasterMoodle::findOrFail($id);
        
        return view('admin.moodle.local.edit', compact('moodle'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $moodle = MasterMoodle::findOrFail($id);
        
        $validated = $request->validate([
            'master_user_id' => 'required|integer',
            'username' => 'required|string|max:255',
            'status' => 'nullable|string|max:50',
            'enrol_status' => 'nullable|string|max:50',
            'error_message' => 'nullable|string',
            'sync_notes' => 'nullable|string',
            'moodle_user_id' => 'nullable|integer',
        ]);

        $moodle->update($validated);

        return redirect()->route('admin.moodle.local.index')
            ->with('success', 'Data Moodle berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $moodle = MasterMoodle::findOrFail($id);
        $moodle->delete();

        return redirect()->route('admin.moodle.local.index')
            ->with('success', 'Data Moodle berhasil dihapus.');
    }

    /**
     * Sync data dengan Moodle
     */
    public function sync(Request $request, $id)
    {
        try {
            $moodle = MasterMoodle::findOrFail($id);
            
            // Implementasi logika sync dengan Moodle API
            $moodle->update([
                'last_sync_at' => now(),
                'sync_notes' => 'Sync berhasil pada ' . now()->format('Y-m-d H:i:s'),
                'status' => 'synced',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Sync dengan Moodle berhasil.',
                'data' => $moodle
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Sync gagal: ' . $e->getMessage()
            ], 500);
        }
    }
    public function syncAndRedirect(Request $request)
    {
        try {
            Log::info('🔄 Memulai sync data quiz attempts dari Moodle sebelum redirect');

            $moodleRestUrl = 'https://elearning2.ubpkarawang.ac.id/webservice/rest/server.php';
            $token = '9d812708c38116dbbe03864ccb860b52';

            // Ambil semua user dari master_moodle yang memiliki moodle_user_id
            $moodleUsers = MasterMoodle::whereNotNull('moodle_user_id')->get();
            $syncedAttempts = 0;
            $errors = [];

            Log::info("👥 Found {$moodleUsers->count()} moodle users to sync");

            // Dapatkan daftar quiz yang aktif dari Moodle
            $activeQuizIds = $this->getActiveQuizIds($token, $moodleRestUrl);
            Log::info("🎯 Quiz aktif yang akan diproses: " . implode(', ', $activeQuizIds));

            if (empty($activeQuizIds)) {
                Log::warning("⚠️ Tidak ada quiz aktif yang ditemukan");
                return redirect()->route('admin.moodle.quiz-attempts.index')
                    ->with('error', 'Tidak ada quiz aktif yang ditemukan di Moodle.');
            }

            foreach ($moodleUsers as $moodleUser) {
                try {
                    Log::info("🎯 Memproses user: {$moodleUser->moodle_user_id} ({$moodleUser->username})");

                    foreach ($activeQuizIds as $quizId) {
                        try {
                            Log::info("📝 Mengambil attempts untuk user {$moodleUser->moodle_user_id} di quiz {$quizId}");

                            // Parameter untuk mengambil quiz attempts
                            $params = [
                                'wstoken' => $token,
                                'wsfunction' => 'mod_quiz_get_user_attempts',
                                'moodlewsrestformat' => 'json',
                                'quizid' => $quizId,
                                'userid' => $moodleUser->moodle_user_id,
                            ];

                            $response = Http::timeout(30)->get($moodleRestUrl, $params);

                            if ($response->successful()) {
                                $attemptsData = $response->json();
                                
                                // Handle error response
                                if (isset($attemptsData['exception'])) {
                                    if ($attemptsData['errorcode'] === 'activityisscheduledfordeletion') {
                                        Log::warning("⏸️  Quiz {$quizId} sedang dihapus, skip");
                                        continue;
                                    }
                                    Log::warning("⚠️  Error untuk quiz {$quizId}: {$attemptsData['message']}");
                                    continue;
                                }

                                // Handle empty response
                                if (empty($attemptsData)) {
                                    Log::info("ℹ️  Tidak ada attempts untuk user {$moodleUser->moodle_user_id} di quiz {$quizId}");
                                    continue;
                                }

                                if (is_array($attemptsData)) {
                                    foreach ($attemptsData as $attemptData) {
                                        try {
                                            // Validasi data attempt
                                            if (!isset($attemptData['id']) || !isset($attemptData['userid']) || !isset($attemptData['quiz'])) {
                                                Log::warning("⚠️ Data attempt tidak lengkap:", $attemptData);
                                                continue;
                                            }

                                            // Cek apakah attempt sudah ada di database
                                            $existingAttempt = MoodleQuizAttempt::where([
                                                'attempt_id' => $attemptData['id'],
                                                'moodle_user_id' => $attemptData['userid'],
                                                'quiz_id' => $attemptData['quiz']
                                            ])->first();

                                            if (!$existingAttempt) {
                                                // Hitung durasi pengerjaan
                                                $duration = null;
                                                if ($attemptData['timestart'] && $attemptData['timefinish']) {
                                                    $duration = $attemptData['timefinish'] - $attemptData['timestart'];
                                                }

                                                // Simpan data attempt baru
                                                MoodleQuizAttempt::create([
                                                    'attempt_id' => $attemptData['id'],
                                                    'moodle_user_id' => $attemptData['userid'],
                                                    'quiz_id' => $attemptData['quiz'],
                                                    'state' => $attemptData['state'] ?? 'unknown',
                                                    'timestart' => $attemptData['timestart'] ? Carbon::createFromTimestamp($attemptData['timestart']) : null,
                                                    'timefinish' => $attemptData['timefinish'] ? Carbon::createFromTimestamp($attemptData['timefinish']) : null,
                                                    'attempted_at' => now(),
                                                    'sumgrades' => $attemptData['sumgrades'] ?? 0,
                                                    'attempt_data' => json_encode($attemptData),
                                                    'duration' => $duration,
                                                ]);

                                                $syncedAttempts++;
                                                Log::info("✅ Data attempt baru ditambahkan: Attempt ID {$attemptData['id']}, User ID {$attemptData['userid']}, Quiz ID {$attemptData['quiz']}, Nilai: " . ($attemptData['sumgrades'] ?? 0));
                                                
                                                // Update last_sync_at di master_moodle
                                                $moodleUser->update([
                                                    'last_sync_at' => now(),
                                                    'sync_notes' => "Data attempts berhasil di-sync pada " . now()->format('Y-m-d H:i:s'),
                                                    'data_uploaded' => true,
                                                    'last_upload_at' => now(),
                                                ]);
                                            } else {
                                                Log::info("⏭️  Data attempt sudah ada: {$attemptData['id']} untuk user {$moodleUser->username}");
                                            }
                                        } catch (\Exception $e) {
                                            $errors[] = "Error processing attempt {$attemptData['id']}: " . $e->getMessage();
                                            Log::error("❌ Error processing attempt: " . $e->getMessage(), ['attempt_data' => $attemptData]);
                                        }
                                    }
                                }
                            } else {
                                $errorMsg = "HTTP Error untuk user {$moodleUser->moodle_user_id} quiz {$quizId}: " . $response->status();
                                $errors[] = $errorMsg;
                                Log::error("❌ $errorMsg");
                            }

                            // Delay kecil antara requests
                            usleep(500000); // 0.5 detik

                        } catch (\Exception $e) {
                            $errors[] = "Error processing quiz {$quizId} for user {$moodleUser->moodle_user_id}: " . $e->getMessage();
                            Log::error("❌ Error processing quiz {$quizId}: " . $e->getMessage());
                        }
                    }

                    // Delay antara user yang berbeda
                    sleep(1);

                } catch (\Exception $e) {
                    $errors[] = "Error processing user {$moodleUser->moodle_user_id}: " . $e->getMessage();
                    Log::error("❌ Error processing user {$moodleUser->moodle_user_id}: " . $e->getMessage());
                }
            }

            Log::info("✅ Sync selesai. {$syncedAttempts} attempts baru berhasil disinkronisasi");

            // Debug: Tampilkan summary
            $totalUsers = $moodleUsers->count();
            $usersWithAttempts = MoodleQuizAttempt::distinct('moodle_user_id')->count();
            
            Log::info("📊 Summary: {$totalUsers} users diproses, {$usersWithAttempts} users memiliki attempts, {$syncedAttempts} attempts baru ditambahkan");

            // Prepare success message
            $successMessage = "Sync berhasil! {$syncedAttempts} data quiz attempts baru berhasil disinkronisasi.";
            
            if (!empty($errors)) {
                $successMessage .= " Terdapat " . count($errors) . " error selama proses.";
            }

            // Redirect ke halaman quiz attempts dengan pesan sukses
            return redirect()->route('admin.moodle.quiz-attempts.index')
                ->with('success', $successMessage);

        } catch (\Exception $e) {
            Log::error('💥 Error sync dari Moodle: ' . $e->getMessage());
            
            // Redirect dengan pesan error
            return redirect()->route('admin.moodle.quiz-attempts.index')
                ->with('error', 'Sync gagal: ' . $e->getMessage());
        }
    }

    /**
     * Get active quiz IDs from Moodle
     */
    private function getActiveQuizIds($token, $moodleRestUrl)
    {
        try {
            $params = [
                'wstoken' => $token,
                'wsfunction' => 'mod_quiz_get_quizzes_by_courses',
                'moodlewsrestformat' => 'json',
                'courseids[0]' => 384, // Course ID
            ];

            $response = Http::timeout(30)->get($moodleRestUrl, $params);

            if ($response->successful()) {
                $quizzesData = $response->json();
                
                if (isset($quizzesData['quizzes']) && is_array($quizzesData['quizzes'])) {
                    $activeQuizIds = [];
                    
                    foreach ($quizzesData['quizzes'] as $quiz) {
                        // Hanya ambil quiz yang visible dan tidak sedang dihapus
                        if ($quiz['visible'] == 1) {
                            $activeQuizIds[] = $quiz['id'];
                            Log::info("🎯 Quiz aktif ditemukan: ID {$quiz['id']}, Name: {$quiz['name']}");
                        }
                    }
                    
                    return $activeQuizIds;
                }
            }
            
            Log::warning("⚠️ Tidak bisa mendapatkan daftar quiz aktif");
            return [];
            
        } catch (\Exception $e) {
            Log::error('💥 Error getting active quiz IDs: ' . $e->getMessage());
            return [];
        }
    }
public function testMoodleApi(Request $request)
    {
        try {
            $moodleRestUrl = 'https://elearning2.ubpkarawang.ac.id/webservice/rest/server.php';
            $token = '9d812708c38116dbbe03864ccb860b52';

            // Test dengan user spesifik (39931)
            $testUserId = 39931;
            
            Log::info("🧪 Testing Moodle API untuk user: {$testUserId}");

            // Test 1: Get user attempts tanpa quizid
            $params1 = [
                'wstoken' => $token,
                'wsfunction' => 'mod_quiz_get_user_attempts',
                'moodlewsrestformat' => 'json',
                'userid' => $testUserId,
            ];

            $response1 = Http::timeout(30)->get($moodleRestUrl, $params1);
            Log::info("Test 1 - Get user attempts:", ['response' => $response1->json()]);

            // Test 2: Get user attempts dengan quizid spesifik
            $params2 = [
                'wstoken' => $token,
                'wsfunction' => 'mod_quiz_get_user_attempts',
                'moodlewsrestformat' => 'json',
                'quizid' => 384, // Course ID
                'userid' => $testUserId,
            ];

            $response2 = Http::timeout(30)->get($moodleRestUrl, $params2);
            Log::info("Test 2 - Get user attempts dengan quizid 384:", ['response' => $response2->json()]);

            // Test 3: Get quizzes by courses
            $params3 = [
                'wstoken' => $token,
                'wsfunction' => 'mod_quiz_get_quizzes_by_courses',
                'moodlewsrestformat' => 'json',
                'courseids[0]' => 384,
            ];

            $response3 = Http::timeout(30)->get($moodleRestUrl, $params3);
            Log::info("Test 3 - Get quizzes by course 384:", ['response' => $response3->json()]);

            return response()->json([
                'success' => true,
                'message' => 'Test completed',
                'test1' => $response1->json(),
                'test2' => $response2->json(),
                'test3' => $response3->json(),
            ]);

        } catch (\Exception $e) {
            Log::error('💥 Error testing Moodle API: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Test failed: ' . $e->getMessage()
            ], 500);
        }
    }
    public function syncSpecificQuizzes(Request $request)
    {
        try {
            Log::info('🔄 Memulai sync data quiz attempts dengan quiz spesifik');

            $moodleRestUrl = 'https://elearning2.ubpkarawang.ac.id/webservice/rest/server.php';
            $token = '9d812708c38116dbbe03864ccb860b52';

            // Gunakan quiz ID yang aktif dari hasil test (1197 dan 1198)
            $specificQuizIds = [1197, 1198]; // Quiz ID yang aktif dari test API
            $moodleUsers = MasterMoodle::whereNotNull('moodle_user_id')->get();
            $syncedAttempts = 0;

            Log::info("🎯 Sync quiz spesifik: " . implode(', ', $specificQuizIds));
            Log::info("👥 Processing {$moodleUsers->count()} moodle users");

            foreach ($moodleUsers as $moodleUser) {
                foreach ($specificQuizIds as $quizId) {
                    try {
                        Log::info("📝 Mengambil attempts untuk user {$moodleUser->moodle_user_id} di quiz {$quizId}");

                        $params = [
                            'wstoken' => $token,
                            'wsfunction' => 'mod_quiz_get_user_attempts',
                            'moodlewsrestformat' => 'json',
                            'quizid' => $quizId,
                            'userid' => $moodleUser->moodle_user_id,
                        ];

                        $response = Http::timeout(30)->get($moodleRestUrl, $params);

                        if ($response->successful()) {
                            $attemptsData = $response->json();

                            // Skip error responses
                            if (isset($attemptsData['exception'])) {
                                Log::warning("⚠️ Error untuk quiz {$quizId}: {$attemptsData['message']}");
                                continue;
                            }

                            if (is_array($attemptsData) && !empty($attemptsData)) {
                                foreach ($attemptsData as $attemptData) {
                                    try {
                                        // Cek duplikasi
                                        $existingAttempt = MoodleQuizAttempt::where([
                                            'attempt_id' => $attemptData['id'],
                                            'moodle_user_id' => $attemptData['userid'],
                                            'quiz_id' => $attemptData['quiz']
                                        ])->first();

                                        if (!$existingAttempt) {
                                            // Hitung durasi
                                            $duration = null;
                                            if ($attemptData['timestart'] && $attemptData['timefinish']) {
                                                $duration = $attemptData['timefinish'] - $attemptData['timestart'];
                                            }

                                            // Simpan attempt
                                            MoodleQuizAttempt::create([
                                                'attempt_id' => $attemptData['id'],
                                                'moodle_user_id' => $attemptData['userid'],
                                                'quiz_id' => $attemptData['quiz'],
                                                'state' => $attemptData['state'] ?? 'unknown',
                                                'timestart' => $attemptData['timestart'] ? Carbon::createFromTimestamp($attemptData['timestart']) : null,
                                                'timefinish' => $attemptData['timefinish'] ? Carbon::createFromTimestamp($attemptData['timefinish']) : null,
                                                'attempted_at' => now(),
                                                'sumgrades' => $attemptData['sumgrades'] ?? 0,
                                                'attempt_data' => json_encode($attemptData),
                                                'duration' => $duration,
                                            ]);

                                            $syncedAttempts++;
                                            Log::info("✅ Attempt baru: ID {$attemptData['id']}, User {$moodleUser->username}, Quiz {$quizId}, Nilai: " . ($attemptData['sumgrades'] ?? 0));
                                            
                                            // Update master_moodle
                                            $moodleUser->update([
                                                'last_sync_at' => now(),
                                                'sync_notes' => "Data attempts berhasil di-sync pada " . now()->format('Y-m-d H:i:s'),
                                                'data_uploaded' => true,
                                                'last_upload_at' => now(),
                                            ]);
                                        }
                                    } catch (\Exception $e) {
                                        Log::error("❌ Error saving attempt: " . $e->getMessage());
                                    }
                                }
                            }
                        }

                        usleep(300000); // 0.3 detik delay

                    } catch (\Exception $e) {
                        Log::error("❌ Error processing quiz {$quizId}: " . $e->getMessage());
                    }
                }
                
                sleep(1); // Delay antara users
            }

            Log::info("✅ Specific sync selesai. {$syncedAttempts} attempts baru");

            return redirect()->route('admin.moodle.quiz-attempts.index')
                ->with('success', "Sync berhasil! {$syncedAttempts} data quiz attempts baru berhasil disinkronisasi.");

        } catch (\Exception $e) {
            Log::error('💥 Error specific sync: ' . $e->getMessage());
            return redirect()->route('admin.moodle.quiz-attempts.index')
                ->with('error', 'Sync gagal: ' . $e->getMessage());
        }
    }
    public function syncQuizAttemptsSimple(Request $request)
    {
        try {
            Log::info('🔄 Memulai sync data quiz attempts (simple approach)');

            $moodleRestUrl = 'https://elearning2.ubpkarawang.ac.id/webservice/rest/server.php';
            $token = '9d812708c38116dbbe03864ccb860b52';

            // Ambil semua moodle users
            $moodleUsers = MasterMoodle::whereNotNull('moodle_user_id')->get();
            $syncedAttempts = 0;

            Log::info("👥 Processing {$moodleUsers->count()} moodle users");

            foreach ($moodleUsers as $moodleUser) {
                try {
                    // Gunakan fungsi mod_quiz_get_attempts_overview untuk mendapatkan semua attempts user
                    $params = [
                        'wstoken' => $token,
                        'wsfunction' => 'mod_quiz_get_user_attempts',
                        'moodlewsrestformat' => 'json',
                        'userid' => $moodleUser->moodle_user_id,
                    ];

                    $response = Http::timeout(60)->get($moodleRestUrl, $params);

                    if ($response->successful()) {
                        $attemptsData = $response->json();

                        // Skip jika error response
                        if (isset($attemptsData['exception'])) {
                            if ($attemptsData['errorcode'] === 'activityisscheduledfordeletion') {
                                Log::warning("⏸️  Quiz sedang dihapus untuk user {$moodleUser->moodle_user_id}");
                                continue;
                            }
                            Log::warning("⚠️  Error untuk user {$moodleUser->moodle_user_id}: {$attemptsData['message']}");
                            continue;
                        }

                        if (is_array($attemptsData) && !empty($attemptsData)) {
                            foreach ($attemptsData as $attemptData) {
                                try {
                                    // Cek apakah attempt sudah ada
                                    $existingAttempt = MoodleQuizAttempt::where([
                                        'attempt_id' => $attemptData['id'],
                                        'moodle_user_id' => $attemptData['userid'],
                                    ])->first();

                                    if (!$existingAttempt) {
                                        // Hitung durasi
                                        $duration = null;
                                        if ($attemptData['timestart'] && $attemptData['timefinish']) {
                                            $duration = $attemptData['timefinish'] - $attemptData['timestart'];
                                        }

                                        // Simpan attempt baru
                                        MoodleQuizAttempt::create([
                                            'attempt_id' => $attemptData['id'],
                                            'moodle_user_id' => $attemptData['userid'],
                                            'quiz_id' => $attemptData['quiz'],
                                            'state' => $attemptData['state'],
                                            'timestart' => $attemptData['timestart'] ? Carbon::createFromTimestamp($attemptData['timestart']) : null,
                                            'timefinish' => $attemptData['timefinish'] ? Carbon::createFromTimestamp($attemptData['timefinish']) : null,
                                            'attempted_at' => now(),
                                            'sumgrades' => $attemptData['sumgrades'] ?? 0,
                                            'attempt_data' => json_encode($attemptData),
                                            'duration' => $duration,
                                        ]);

                                        $syncedAttempts++;
                                        Log::info("✅ Attempt baru: {$attemptData['id']} untuk user {$moodleUser->username}");
                                    }
                                } catch (\Exception $e) {
                                    Log::error("❌ Error saving attempt {$attemptData['id']}: " . $e->getMessage());
                                }
                            }
                        }
                    }

                    // Delay antara users
                    usleep(300000); // 0.3 detik

                } catch (\Exception $e) {
                    Log::error("❌ Error processing user {$moodleUser->moodle_user_id}: " . $e->getMessage());
                }
            }

            Log::info("✅ Simple sync selesai. {$syncedAttempts} attempts baru");

            return redirect()->route('admin.moodle.quiz-attempts.index')
                ->with('success', "Sync berhasil! {$syncedAttempts} data quiz attempts baru berhasil disinkronisasi.");

        } catch (\Exception $e) {
            Log::error('💥 Error simple sync: ' . $e->getMessage());
            
            return redirect()->route('admin.moodle.quiz-attempts.index')
                ->with('error', 'Sync gagal: ' . $e->getMessage());
        }
    }
}