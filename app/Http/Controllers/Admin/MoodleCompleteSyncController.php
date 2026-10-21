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

class MoodleCompleteSyncController extends Controller
{
    private $moodleUrl;
    private $moodleToken;
    
    // COURSE ID (Target Course)
    private $courseId = 2; 

    public function __construct()
    {
        $this->moodleUrl = config('services.moodle.url', 'https://elearning2.ubpkarawang.ac.id/webservice/rest/server.php');
        $this->moodleToken = config('services.moodle.token', '47d4b340520e06fbb1d21127d683ae60');

        Log::info("MoodleCompleteSyncController initialized (Course Mode)", [
            'url' => $this->moodleUrl,
            'course_id' => $this->courseId
        ]);
    }

    /**
     * Get sync statistics
     */
    public function getSyncStatistics(): JsonResponse
    {
        try {
            $totalUsers = MasterMoodle::whereNotNull('moodle_user_id')->count();
            
            $usersWithGrades = MoodleQuizAttempt::where('quiz_id', $this->courseId)
                ->distinct('moodle_user_id')
                ->count();
            
            $recentSync = MasterMoodle::whereNotNull('last_sync_at')
                ->orderBy('last_sync_at', 'desc')
                ->first();

            // Gunakan 'score' untuk statistik karena itu yang menyimpan nilai skala 100
            $gradeStats = MoodleQuizAttempt::where('quiz_id', $this->courseId)
                ->selectRaw('COUNT(*) as total_attempts')
                ->selectRaw('AVG(score) as average_grade')
                ->selectRaw('MAX(score) as highest_grade')
                ->selectRaw('MIN(score) as lowest_grade')
                ->first();

            // Statistik grade letter berdasarkan percentage/score
            $gradeLetterStats = MoodleQuizAttempt::where('quiz_id', $this->courseId)
                ->selectRaw("
                    CASE 
                        WHEN score >= 85 THEN 'A'
                        WHEN score >= 75 THEN 'B'
                        WHEN score >= 65 THEN 'C'
                        WHEN score >= 55 THEN 'D'
                        ELSE 'E'
                    END as grade_letter,
                    COUNT(*) as count
                ")
                ->whereNotNull('score')
                ->groupBy('grade_letter')
                ->orderBy('grade_letter')
                ->get()
                ->pluck('count', 'grade_letter');

            return response()->json([
                'success' => true,
                'data' => [
                    'course_id' => $this->courseId,
                    'total_users' => $totalUsers,
                    'users_with_grades' => $usersWithGrades, 
                    'users_without_grades' => $totalUsers - $usersWithGrades,
                    'last_sync' => $recentSync ? $recentSync->last_sync_at : null,
                    'completion_rate' => $totalUsers > 0 ? round(($usersWithGrades / $totalUsers) * 100, 2) : 0,
                    'grade_statistics' => [
                        'average_grade' => $gradeStats && $gradeStats->average_grade ? round($gradeStats->average_grade, 2) : 0,
                        'highest_grade' => $gradeStats && $gradeStats->highest_grade ? $gradeStats->highest_grade : 0,
                        'lowest_grade' => $gradeStats && $gradeStats->lowest_grade ? $gradeStats->lowest_grade : 0,
                    ],
                    'grade_letter_distribution' => $gradeLetterStats,
                    'passed_count' => MoodleQuizAttempt::where('quiz_id', $this->courseId)
                        ->where('is_passed', true)
                        ->count(),
                    'failed_count' => MoodleQuizAttempt::where('quiz_id', $this->courseId)
                        ->where('is_passed', false)
                        ->count()
                ],
                'message' => 'Statistik sync course berhasil diambil'
            ]);
        } catch (\Exception $e) {
            Log::error("❌ Error get sync statistics: " . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil statistik: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get PAD grade report (Laporan Nilai)
     */
    public function getPadGradeReport(): JsonResponse
    {
        try {
            $attempts = MoodleQuizAttempt::with(['masterMoodle.user'])
                ->where('quiz_id', $this->courseId)
                ->whereNotNull('score') 
                ->orderBy('score', 'desc')
                ->get()
                ->map(function ($attempt) {
                    return [
                        'user_id' => $attempt->moodle_user_id,
                        'username' => $attempt->masterMoodle->username ?? 'N/A',
                        'full_name' => optional($attempt->masterMoodle->user)->name ?? 'N/A',
                        'grade' => $attempt->score, // Tampilkan Score (Skala 100)
                        'percentage' => $attempt->percentage, 
                        'grade_letter' => $this->calculateGradeLetter($attempt->score),
                        'is_passed' => $attempt->is_passed,
                        'attempted_at' => $attempt->attempted_at,
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => [
                    'total_records' => $attempts->count(),
                    'course_id' => $this->courseId,
                    'grades' => $attempts
                ],
                'message' => 'Grade report berhasil diambil'
            ]);
        } catch (\Exception $e) {
            Log::error("❌ Error get grade report: " . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil grade report: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Sync all COURSE GRADES for all users (Mass Sync)
     */
    public function syncAllQuizAttempts(Request $request): JsonResponse
    {
        Log::info("🔄 Sync COURSE GRADES dimulai");

        try {
            if (!$this->validateMoodleConnection()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Koneksi ke Moodle gagal.',
                ], 500);
            }

            $moodleUsers = MasterMoodle::whereNotNull('moodle_user_id')->get();
            
            $results = [
                'success' => 0,
                'failed' => 0,
                'grades_synced' => 0,
                'skipped_no_grade' => 0,
                'errors' => []
            ];

            Log::info("🔄 Memulai sync COURSE ID: {$this->courseId}");

            foreach ($moodleUsers as $index => $moodleUser) {
                try {
                    $userResult = $this->syncUserCourseGrade($moodleUser);

                    if ($userResult['success']) {
                        if ($userResult['grade_synced']) {
                            $results['success']++;
                            $results['grades_synced']++;
                        } else {
                            $results['skipped_no_grade']++;
                        }
                    } else {
                        $results['failed']++;
                        $results['errors'][] = "{$moodleUser->username}: " . ($userResult['error'] ?? 'Unknown');
                    }
                } catch (\Exception $e) {
                    $results['failed']++;
                    $results['errors'][] = "{$moodleUser->username}: " . $e->getMessage();
                    Log::error("Error syncing user {$moodleUser->username}: " . $e->getMessage());
                }

                // Rate limiting
                if ($index % 10 === 0 && $index > 0) {
                    sleep(1);
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Sync course grades selesai',
                'course_id' => $this->courseId,
                'results' => $results
            ]);
        } catch (\Exception $e) {
            Log::error('💥 Error sync course grades: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Gagal sync: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Force sync implementation (Batching)
     */
    public function forceSyncQuizAttempts(Request $request): JsonResponse
    {
        // Paksa PHP agar tidak timeout
        set_time_limit(300); // 5 menit
        ini_set('memory_limit', '512M');

        $limit = $request->input('limit', 20);
        $offset = $request->input('offset', 0);
        
        Log::info("🚀 Memulai Batch Sync: Offset $offset, Limit $limit");

        try {
            if ($offset == 0 && !$this->validateMoodleConnection()) {
                return response()->json([
                    'success' => false, 
                    'message' => 'Koneksi ke Moodle gagal'
                ], 500);
            }

            $totalUsers = MasterMoodle::whereNotNull('moodle_user_id')->count();
            $moodleUsers = MasterMoodle::whereNotNull('moodle_user_id')
                                    ->orderBy('id', 'asc')
                                    ->skip($offset)
                                    ->take($limit)
                                    ->get();

            $results = [
                'success' => 0, 
                'failed' => 0, 
                'grades_synced' => 0, 
                'skipped' => 0, 
                'errors' => []
            ];

            foreach ($moodleUsers as $moodleUser) {
                Log::debug("Processing User: {$moodleUser->username} (ID: {$moodleUser->moodle_user_id})");

                try {
                    $userResult = $this->syncUserCourseGrade($moodleUser);

                    if ($userResult['success']) {
                        if ($userResult['grade_synced']) {
                            $results['success']++;
                            $results['grades_synced']++;
                        } else {
                            $results['skipped']++;
                        }
                    } else {
                        $results['failed']++;
                        $results['errors'][] = "{$moodleUser->username}: " . ($userResult['error'] ?? 'API Error');
                    }
                } catch (\Exception $e) {
                    $results['failed']++;
                    $results['errors'][] = "{$moodleUser->username}: " . $e->getMessage();
                    Log::error("Error processing user {$moodleUser->username}: " . $e->getMessage());
                }
            }

            $processedSoFar = $offset + $moodleUsers->count();
            $isComplete = $processedSoFar >= $totalUsers;
            $progressPercentage = $totalUsers > 0 ? round(($processedSoFar / $totalUsers) * 100) : 100;

            Log::info("✅ Batch Offset $offset Selesai. Progress: $progressPercentage%");

            return response()->json([
                'success' => true,
                'completed' => $isComplete,
                'progress' => $progressPercentage,
                'total_users' => $totalUsers,
                'processed_in_batch' => $moodleUsers->count(),
                'results' => $results,
                'message' => "Batch offset {$offset} selesai."
            ]);

        } catch (\Exception $e) {
            Log::error("💥 ERROR FATAL di forceSyncQuizAttempts: " . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false, 
                'message' => 'System Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Core Logic: Sync Course Grade Per User
     * FIX: Removed nested transaction to prevent deadlock
     */
    private function syncUserCourseGrade(MasterMoodle $moodleUser): array
    {
        try {
            if (empty($moodleUser->moodle_user_id)) {
                return ['success' => false, 'error' => 'No Moodle ID'];
            }

            $courseGradeData = $this->getUserCourseGradeFromMoodle($moodleUser->moodle_user_id);

            if (!$courseGradeData || !isset($courseGradeData['graderaw']) || is_null($courseGradeData['graderaw'])) {
                return ['success' => true, 'grade_synced' => false];
            }

            $saveResult = $this->saveCourseGrade($moodleUser->moodle_user_id, $courseGradeData);

            if ($saveResult['saved']) {
                $moodleUser->update([
                    'last_sync_at' => now(),
                    'sync_notes' => "Synced: Score " . ($saveResult['grade_info']['score'] ?? 0),
                    'data_uploaded' => true
                ]);
            }

            return ['success' => true, 'grade_synced' => $saveResult['saved']];
        } catch (\Exception $e) {
            Log::error("Error in syncUserCourseGrade for user {$moodleUser->moodle_user_id}: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Calculate grade letter based on score
     */
    private function calculateGradeLetter($score): string
    {
        $val = floatval($score);
        if ($val >= 85) return 'A';
        if ($val >= 75) return 'B';
        if ($val >= 65) return 'C';
        if ($val >= 55) return 'D';
        return 'E';
    }

    /**
     * Mengambil Nilai Kursus dari API Moodle
     */
    private function getUserCourseGradeFromMoodle($moodleUserId): ?array
    {
        try {
            $response = $this->callMoodleApi('gradereport_user_get_grade_items', [
                'courseid' => $this->courseId,
                'userid' => $moodleUserId
            ]);

            if (isset($response['exception'])) {
                Log::warning("⚠️ Error getting grade items for user {$moodleUserId}: " . ($response['message'] ?? 'Unknown'));
                return null;
            }

            if (isset($response['usergrades'][0]['gradeitems'])) {
                $gradeItems = $response['usergrades'][0]['gradeitems'];
                
                foreach ($gradeItems as $item) {
                    if (isset($item['itemtype']) && $item['itemtype'] === 'course') {
                        return $item;
                    }
                }
            }

            return null;
        } catch (\Exception $e) {
            Log::error("❌ Error API getUserCourseGrade for user {$moodleUserId}: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Menyimpan Nilai Kursus dengan ID Integer & Rumus Persentase (Skala 100)
     */
    private function saveCourseGrade($moodleUserId, array $gradeData): array
    {
        try {
            // FIX ERROR SQL: ID Integer
            $fakeAttemptId = ($this->courseId * 100000000) + $moodleUserId;
            
            // Ambil data nilai
            $gradeRaw = isset($gradeData['graderaw']) ? floatval($gradeData['graderaw']) : 0;
            $gradeMax = isset($gradeData['grademax']) ? floatval($gradeData['grademax']) : 100;
            
            // Perhitungan Skala 100
            $finalScore = 0;
            if ($gradeMax > 0) {
                $finalScore = ($gradeRaw / $gradeMax) * 100;
            }
            $finalScore = round($finalScore, 2);

            $isPassed = $finalScore >= 60; 

            // Cek apakah data sudah ada
            $existingEntry = MoodleQuizAttempt::where([
                'moodle_user_id' => $moodleUserId,
                'quiz_id' => $this->courseId 
            ])->first();

            // Proteksi Data Manual Edit
            if ($existingEntry && isset($existingEntry->is_manual_edit) && $existingEntry->is_manual_edit) {
                Log::info("🛡️ Skip update user {$moodleUserId}: Data has been manually edited.");
                return [
                    'saved' => false,
                    'action' => 'skipped_protected',
                    'grade_info' => [
                        'grade' => $existingEntry->grade,
                        'score' => $existingEntry->score,
                        'percentage' => $existingEntry->percentage
                    ]
                ];
            }

            $dataToSave = [
                'attempt_id' => $fakeAttemptId,
                'moodle_user_id' => $moodleUserId,
                'quiz_id' => $this->courseId,
                'state' => 'finished',
                'grade' => $gradeRaw,       
                'sumgrades' => $gradeRaw,
                'max_grade' => $gradeMax,   
                'percentage' => $finalScore, 
                'is_passed' => $isPassed,
                'score' => $finalScore,     
                'attempted_at' => now(),
                'attempt_data' => json_encode($gradeData), 
                'updated_at' => now(),
                'is_manual_edit' => false
            ];

            if ($existingEntry) {
                $existingEntry->update($dataToSave);
                $action = 'updated';
            } else {
                $dataToSave['created_at'] = now();
                MoodleQuizAttempt::create($dataToSave);
                $action = 'created';
            }

            return [
                'saved' => true,
                'action' => $action,
                'grade_info' => [
                    'grade' => $gradeRaw,
                    'score' => $finalScore,
                    'percentage' => $finalScore
                ]
            ];

        } catch (\Exception $e) {
            Log::error("❌ Error saveCourseGrade for user {$moodleUserId}: " . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return ['saved' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Validate Moodle API Connection
     */
    private function validateMoodleConnection(): bool
    {
        try {
            $response = $this->callMoodleApi('core_webservice_get_site_info', []);
            return isset($response['sitename']) && !isset($response['exception']);
        } catch (\Exception $e) {
            Log::error("❌ Moodle connection validation failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Call Moodle Web Service API
     */
    private function callMoodleApi(string $function, array $params): array
    {
        try {
            $payload = array_merge([
                'wstoken' => $this->moodleToken,
                'wsfunction' => $function,
                'moodlewsrestformat' => 'json'
            ], $params);

            $response = Http::timeout(30)->asForm()->post($this->moodleUrl, $payload);
            
            if ($response->failed()) {
                Log::error("HTTP request failed for function {$function}", [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                return ['exception' => true, 'message' => 'HTTP Error: ' . $response->status()];
            }

            $result = $response->json();
            
            if (isset($result['exception'])) {
                Log::warning("Moodle API exception for {$function}: " . ($result['message'] ?? 'Unknown'));
            }
            
            return $result ?? ['exception' => true, 'message' => 'Empty response'];
            
        } catch (\Exception $e) {
            Log::error("Exception calling Moodle API {$function}: " . $e->getMessage());
            return ['exception' => true, 'message' => $e->getMessage()];
        }
    }

    /**
     * Show edit form for quiz attempt
     */
    public function edit($id)
    {
        try {
            $attempt = MoodleQuizAttempt::with(['masterMoodle.user'])->findOrFail($id);
            return view('admin.moodle.quiz-attempts.edit', compact('attempt'));
        } catch (\Exception $e) {
            Log::error("Error loading edit form for attempt {$id}: " . $e->getMessage());
            return redirect()
                ->route('admin.moodle.quiz-attempts.index')
                ->with('error', 'Data tidak ditemukan.');
        }
    }

    /**
     * Update the specified resource in storage
     */
    public function update(Request $request, $id)
    {
        try {
            $request->validate([
                'score' => 'required|numeric|min:0|max:100',
            ]);

            $attempt = MoodleQuizAttempt::findOrFail($id);

            // Ambil nilai baru dari input
            $newScore = floatval($request->score);
            
            // Ambil max grade (default 100 jika null)
            $maxGrade = $attempt->max_grade > 0 ? floatval($attempt->max_grade) : 100;

            // Hitung ulang Raw Grade (Nilai asli Moodle)
            // Rumus balik: (Score / 100) * MaxGrade
            $newRawGrade = ($newScore / 100) * $maxGrade;

            // Hitung status kelulusan baru (Threshold 60)
            $isPassed = $newScore >= 60;

            // Update dengan flag manual edit
            $attempt->update([
                'score' => $newScore,
                'percentage' => $newScore,
                'grade' => round($newRawGrade, 2),
                'sumgrades' => round($newRawGrade, 2),
                'is_passed' => $isPassed,
                'is_manual_edit' => true, // PENTING: Set flag manual edit
                'sync_notes' => ($attempt->sync_notes ?? '') . " | Manual Edit: {$newScore} by Admin (" . now()->format('Y-m-d H:i:s') . ")"
            ]);

            return redirect()
                ->route('admin.moodle.quiz-attempts.index')
                ->with('success', 'Nilai berhasil diperbarui.');
                
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()
                ->back()
                ->withErrors($e->validator)
                ->withInput();
        } catch (\Exception $e) {
            Log::error("Error updating quiz attempt {$id}: " . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()
                ->back()
                ->with('error', 'Gagal memperbarui nilai: ' . $e->getMessage())
                ->withInput();
        }
    }
}