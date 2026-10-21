<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\MasterMoodle;
use App\Models\MasterUser;
use Illuminate\Support\Facades\Http;
use App\Models\MoodleUserData; 
use App\Models\MoodleUserCourse; 
use App\Models\MoodleQuizAttempt; 
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class Admin_MoodleController extends Controller
{
    private $moodleUrl;
    private $moodleToken;

    public function __construct()
    {
        $this->moodleUrl = 'https://elearning2.ubpkarawang.ac.id/webservice/rest/server.php';
        $this->moodleToken = '9d812708c38116dbbe03864ccb860b52';
    }

    public function uploadToDatabase(Request $request): JsonResponse
    {
        try {
            $users = $request->input('users', []);
            $results = [
                'success' => 0,
                'failed' => 0,
                'skipped' => 0,
                'errors' => []
            ];

            DB::beginTransaction();

            foreach ($users as $userData) {
                try {
                    $masterData = $userData['master_moodle_data'] ?? [];
                    $moodleUserId = $masterData['moodle_user_id'] ?? null;

                    if (!$moodleUserId) {
                        $results['failed']++;
                        $results['errors'][] = [
                            'user' => $masterData['username'] ?? 'Unknown',
                            'error' => 'Moodle User ID tidak tersedia'
                        ];
                        continue;
                    }

                    // Cek apakah user sudah ada di database dan sudah diupload
                    $existingUser = MasterMoodle::where('moodle_user_id', $moodleUserId)
                        ->where('data_uploaded', true)
                        ->first();

                    if ($existingUser) {
                        $results['skipped']++;
                        continue;
                    }

                    // User belum ada atau belum diupload, proses upload
                    $this->saveUserData($userData);
                    $results['success']++;

                } catch (\Exception $e) {
                    $results['failed']++;
                    $results['errors'][] = [
                        'user' => $userData['master_moodle_data']['username'] ?? 'Unknown',
                        'error' => $e->getMessage()
                    ];
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Data berhasil diproses',
                'results' => $results,
                'summary' => [
                    'total_processed' => count($users),
                    'new_uploaded' => $results['success'],
                    'already_exists' => $results['skipped'],
                    'failed' => $results['failed']
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal memproses data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Upload single user data to database
     */
    public function uploadUserToDatabase(Request $request): JsonResponse
    {
        try {
            $userData = $request->input('user');

            if (!$userData) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data user tidak ditemukan'
                ], 400);
            }

            $masterData = $userData['master_moodle_data'] ?? [];
            $moodleUserId = $masterData['moodle_user_id'] ?? null;

            if (!$moodleUserId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Moodle User ID tidak tersedia'
                ], 400);
            }

            // Cek apakah user sudah ada di database dan sudah diupload
            $existingUser = MasterMoodle::where('moodle_user_id', $moodleUserId)
                ->where('data_uploaded', true)
                ->first();

            if ($existingUser) {
                return response()->json([
                    'success' => true,
                    'message' => 'Data user sudah ada di database',
                    'data' => [
                        'user' => $existingUser,
                        'status' => 'already_exists',
                        'last_upload_at' => $existingUser->last_upload_at
                    ]
                ]);
            }

            DB::beginTransaction();

            $result = $this->saveUserData($userData);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Data user berhasil dimasukkan ke database',
                'data' => $result
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal memasukkan data user: ' . $e->getMessage()
            ], 500);
        }
    }

    public function forceUploadUserToDatabase(Request $request): JsonResponse
    {
        try {
            $userData = $request->input('user');

            if (!$userData) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data user tidak ditemukan'
                ], 400);
            }

            DB::beginTransaction();

            $result = $this->saveUserData($userData, true);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Data user berhasil diupdate di database',
                'data' => $result
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengupdate data user: ' . $e->getMessage()
            ], 500);
        }
    }
   

    private function saveUserData(array $userData, bool $forceUpdate = false): array
    {
        $masterData = $userData['master_moodle_data'] ?? [];
        $moodleData = $userData['moodle_user_data'] ?? [];
        $userCourses = $userData['user_courses'] ?? [];
        $quizAttempts = $userData['quiz_attempts'] ?? [];
        $userGrades = $userData['user_grades'] ?? [];

        $moodleUserId = $masterData['moodle_user_id'] ?? null;

        if (!$moodleUserId) {
            throw new \Exception('Moodle User ID tidak tersedia');
        }

        // 1. Save Moodle User Data
        $savedUser = $this->saveMoodleUserData($moodleUserId, $moodleData, $forceUpdate);

        // 2. Save User Courses (only if force update or new user)
        $savedCourses = $this->saveUserCourses($moodleUserId, $userCourses, $forceUpdate);

        // 3. Save Quiz Attempts (only if force update or new user)
        $savedAttempts = $this->saveQuizAttempts($moodleUserId, $quizAttempts, $forceUpdate);

        // 4. Save User Grades
        $savedGrades = $this->saveUserGrades($moodleUserId, $userGrades);

        // 5. Update Master Moodle record
        $this->updateMasterMoodle($masterData['id'], [
            'last_sync_at' => now(),
            'data_uploaded' => true,
            'last_upload_at' => now()
        ]);

        return [
            'user' => $savedUser,
            'courses' => $savedCourses,
            'quiz_attempts' => $savedAttempts,
            'grades' => $savedGrades,
            'action' => $forceUpdate ? 'updated' : 'created'
        ];
    }

    /**
     * Save Moodle user data
     */
    private function saveMoodleUserData($moodleUserId, array $moodleData, bool $forceUpdate = false)
    {
        // Convert Unix timestamp to proper datetime format
        $firstaccess = isset($moodleData['firstaccess']) && $moodleData['firstaccess'] > 0 
            ? date('Y-m-d H:i:s', $moodleData['firstaccess']) 
            : null;
            
        $lastaccess = isset($moodleData['lastaccess']) && $moodleData['lastaccess'] > 0 
            ? date('Y-m-d H:i:s', $moodleData['lastaccess']) 
            : null;

        // Handle description field
        $description = $moodleData['description'] ?? null;
        if ($description && strlen($description) > 65535) {
            $description = substr($description, 0, 65535);
        }

        return MasterMoodle::updateOrCreate(
            ['moodle_user_id' => $moodleUserId],
            [
                'username' => $moodleData['username'] ?? null,
                'email' => $moodleData['email'] ?? null,
                'firstname' => $moodleData['firstname'] ?? null,
                'lastname' => $moodleData['lastname'] ?? null,
                'fullname' => $moodleData['fullname'] ?? null,
                'city' => $moodleData['city'] ?? null,
                'country' => $moodleData['country'] ?? null,
                'timezone' => $moodleData['timezone'] ?? null,
                'description' => $description,
                'firstaccess' => $firstaccess,
                'lastaccess' => $lastaccess,
                'suspended' => $moodleData['suspended'] ?? false,
                'confirmed' => $moodleData['confirmed'] ?? false,
                'lang' => $moodleData['lang'] ?? null,
                'profile_image' => $moodleData['profileimageurl'] ?? null,
                'custom_fields' => json_encode($moodleData['customfields'] ?? []),
                'moodle_data' => json_encode($moodleData),
                'last_sync_at' => now(),
                'data_uploaded' => true,
                'last_upload_at' => now()
            ]
        );
    }

    /**
     * Save user courses
     */
    private function saveUserCourses($moodleUserId, array $courses, bool $forceUpdate = false): array
    {
        $savedCourses = [];

        // Jika bukan force update dan user sudah ada courses, skip
        if (!$forceUpdate) {
            $existingCourses = MoodleUserCourse::where('moodle_user_id', $moodleUserId)->count();
            if ($existingCourses > 0) {
                return []; // Skip jika sudah ada courses
            }
        }

        foreach ($courses as $course) {
            // Convert Unix timestamp to proper datetime format
            $startdate = isset($course['startdate']) && $course['startdate'] > 0 
                ? date('Y-m-d H:i:s', $course['startdate']) 
                : null;
                
            $enddate = isset($course['enddate']) && $course['enddate'] > 0 
                ? date('Y-m-d H:i:s', $course['enddate']) 
                : null;

            // Handle summary field to prevent SQL errors
            $summary = $course['summary'] ?? null;
            if ($summary && strlen($summary) > 65535) {
                $summary = substr($summary, 0, 65535);
            }

            $savedCourse = MoodleUserCourse::updateOrCreate(
                [
                    'moodle_user_id' => $moodleUserId,
                    'course_id' => $course['id'] ?? null
                ],
                [
                    'shortname' => $course['shortname'] ?? null,
                    'fullname' => $course['fullname'] ?? null,
                    'displayname' => $course['displayname'] ?? null,
                    'summary' => $summary,
                    'startdate' => $startdate,
                    'enddate' => $enddate,
                    'visible' => $course['visible'] ?? true,
                    'progress' => $course['progress'] ?? 0,
                    'completed' => $course['completed'] ?? false,
                    'course_data' => json_encode($course),
                    'last_accessed' => now()
                ]
            );

            $savedCourses[] = $savedCourse;
        }

        return $savedCourses;
    }

    /**
     * Save quiz attempts
     */
    private function saveQuizAttempts($moodleUserId, array $quizData, bool $forceUpdate = false): array
    {
        $savedAttempts = [];
        $attempts = $quizData['attempts'] ?? [];

        // Jika bukan force update dan user sudah ada attempts, skip
        if (!$forceUpdate) {
            $existingAttempts = MoodleQuizAttempt::where('moodle_user_id', $moodleUserId)->count();
            if ($existingAttempts > 0) {
                return []; // Skip jika sudah ada attempts
            }
        }

        foreach ($attempts as $attempt) {
            // Convert Unix timestamp to proper datetime format
            $timestart = isset($attempt['timestart']) && $attempt['timestart'] > 0 
                ? date('Y-m-d H:i:s', $attempt['timestart']) 
                : null;
                
            $timefinish = isset($attempt['timefinish']) && $attempt['timefinish'] > 0 
                ? date('Y-m-d H:i:s', $attempt['timefinish']) 
                : null;

            $savedAttempt = MoodleQuizAttempt::updateOrCreate(
                [
                    'moodle_user_id' => $moodleUserId,
                    'quiz_id' => $attempt['quiz'] ?? null,
                    'attempt_id' => $attempt['id'] ?? null
                ],
                [
                    'state' => $attempt['state'] ?? null,
                    'timestart' => $timestart,
                    'timefinish' => $timefinish,
                    'sumgrades' => $attempt['sumgrades'] ?? null,
                    'grade' => $attempt['grade'] ?? null,
                    'attempt_data' => json_encode($attempt),
                    'attempted_at' => now()
                ]
            );

            $savedAttempts[] = $savedAttempt;
        }

        return $savedAttempts;
    }

    /**
     * Save user grades
     */
    private function saveUserGrades($moodleUserId, array $grades)
    {
        if (empty($grades)) {
            return null;
        }

        return [
            'moodle_user_id' => $moodleUserId,
            'grade_data' => json_encode($grades),
            'processed_at' => now()
        ];
    }

    private function updateMasterMoodle($id, array $data)
    {
        return MasterMoodle::where('id', $id)->update($data);
    }

    /**
     * Get all users from master_moodle table ONLY (not from Moodle directly)
     */
    public function getAllMoodleUsers(): JsonResponse
    {
        try {
            $moodleUsers = MasterMoodle::with('masterUser')
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $moodleUsers,
                'total' => $moodleUsers->count(),
                'message' => 'Data users dari master_moodle berhasil diambil'
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting master_moodle users', [
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil users dari master_moodle: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all users from master_moodle table with complete data from Moodle
     */
    public function getCompleteMoodleUsers(): JsonResponse
    {
        try {
            $moodleUsers = MasterMoodle::with('masterUser')
                ->whereNotNull('moodle_user_id')
                ->orderBy('created_at', 'desc')
                ->get();

            $completeUsers = [];
            $errors = [];

            foreach ($moodleUsers as $moodleUser) {
                try {
                    // Get complete user data from Moodle using moodle_user_id
                    $moodleUserData = $this->getMoodleUserById($moodleUser->moodle_user_id);
                    
                    // Get user courses from Moodle
                    $userCourses = $this->getUserCourses($moodleUser->moodle_user_id);
                    
                    // Get user quiz attempts for specific quiz (ID 1205)
                    $quizAttempts = $this->getUserQuizAttemptsForSpecificQuiz($moodleUser->moodle_user_id, 1205);

                    $completeUsers[] = [
                        'master_moodle_data' => $moodleUser,
                        'moodle_user_data' => $moodleUserData,
                        'user_courses' => $userCourses,
                        'quiz_attempts' => $quizAttempts,
                        'enrollment_status' => $this->getEnrollmentStatus($moodleUser->moodle_user_id),
                        'user_grades' => $this->getUserGrades($moodleUser->moodle_user_id)
                    ];
                } catch (\Exception $e) {
                    $errors[] = [
                        'moodle_user_id' => $moodleUser->moodle_user_id,
                        'username' => $moodleUser->username,
                        'error' => $e->getMessage()
                    ];
                }
            }

            return response()->json([
                'success' => true,
                'data' => $completeUsers,
                'errors' => $errors,
                'total_success' => count($completeUsers),
                'total_errors' => count($errors),
                'message' => 'Data lengkap users dari Moodle berhasil diambil'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data lengkap: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get specific user complete data from Moodle using moodle_user_id
     */
    public function getCompleteUserData($moodleUserId): JsonResponse
    {
        try {
            $moodleUser = MasterMoodle::with('masterUser')
                ->where('moodle_user_id', $moodleUserId)
                ->first();

            if (!$moodleUser) {
                return response()->json([
                    'success' => false,
                    'message' => 'User tidak ditemukan di master_moodle dengan moodle_user_id: ' . $moodleUserId
                ], 404);
            }

            // Get complete user data from Moodle
            $moodleUserData = $this->getMoodleUserById($moodleUser->moodle_user_id);
            
            // Get user courses from Moodle
            $userCourses = $this->getUserCourses($moodleUser->moodle_user_id);
            
            // Get user quiz attempts for specific quiz (ID 1205)
            $quizAttempts = $this->getUserQuizAttemptsForSpecificQuiz($moodleUser->moodle_user_id, 1205);
            
            // Get user grades
            $userGrades = $this->getUserGrades($moodleUser->moodle_user_id);

            return response()->json([
                'success' => true,
                'data' => [
                    'master_moodle_data' => $moodleUser,
                    'moodle_user_data' => $moodleUserData,
                    'user_courses' => $userCourses,
                    'quiz_attempts' => $quizAttempts,
                    'user_grades' => $userGrades,
                    'enrollment_status' => $this->getEnrollmentStatus($moodleUser->moodle_user_id)
                ],
                'message' => 'Data lengkap user berhasil diambil'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data lengkap user: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get users statistics from master_moodle
     */
    public function getMoodleUsersStats(): JsonResponse
    {
        try {
            $totalUsers = MasterMoodle::count();
            $usersWithMoodleId = MasterMoodle::whereNotNull('moodle_user_id')->count();
            $usersWithoutMoodleId = MasterMoodle::whereNull('moodle_user_id')->count();
            
            $statusStats = MasterMoodle::select('status')
                ->selectRaw('COUNT(*) as count')
                ->groupBy('status')
                ->get()
                ->pluck('count', 'status');
            
            $enrolStats = MasterMoodle::select('enrol_status')
                ->selectRaw('COUNT(*) as count')
                ->groupBy('enrol_status')
                ->get()
                ->pluck('count', 'enrol_status');

            return response()->json([
                'success' => true,
                'data' => [
                    'total_users' => $totalUsers,
                    'users_with_moodle_id' => $usersWithMoodleId,
                    'users_without_moodle_id' => $usersWithoutMoodleId,
                    'status_statistics' => $statusStats,
                    'enrollment_statistics' => $enrolStats,
                    'completion_rate' => $totalUsers > 0 ? round(($usersWithMoodleId / $totalUsers) * 100, 2) : 0
                ],
                'message' => 'Statistik users dari master_moodle berhasil diambil'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil statistik users: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get user quiz attempts for specific quiz - IMPROVED VERSION
     */
    private function getUserQuizAttemptsForSpecificQuiz($moodleUserId, $quizId): array
    {
        try {
            $response = $this->callMoodleApi('mod_quiz_get_user_attempts', [
                'quizid' => (int)$quizId,
                'userid' => (int)$moodleUserId
            ]);

            if (isset($response['exception'])) {
                // Check if error is because no attempts exist
                if (strpos($response['message'] ?? '', 'no attempts') !== false || 
                    strpos($response['errorcode'] ?? '', 'noattemptsfound') !== false) {
                    return [
                        'attempts' => [],
                        'total' => 0,
                        'message' => 'Tidak ada attempt untuk quiz ini',
                        'quiz_id' => $quizId
                    ];
                }
                
                Log::error('Moodle API Error - Quiz Attempts', [
                    'quiz_id' => $quizId,
                    'user_id' => $moodleUserId,
                    'response' => $response
                ]);
                
                return [
                    'attempts' => [],
                    'error' => $response['message'] ?? 'Unknown error',
                    'error_code' => $response['errorcode'] ?? 'unknown',
                    'quiz_id' => $quizId
                ];
            }

            // Parse the attempts data
            $attempts = $response['attempts'] ?? [];
            
            // Get additional details for each attempt
            foreach ($attempts as &$attempt) {
                // Get attempt summary
                $attemptSummary = $this->getAttemptSummary($attempt['id'] ?? null);
                if ($attemptSummary) {
                    $attempt['summary'] = $attemptSummary;
                }
                
                // Get attempt review
                $attemptReview = $this->getAttemptReview($attempt['id'] ?? null);
                if ($attemptReview) {
                    $attempt['review'] = $attemptReview;
                }
                
                // Format timestamps to readable date
                if (isset($attempt['timestart']) && $attempt['timestart'] > 0) {
                    $attempt['timestart_formatted'] = date('Y-m-d H:i:s', $attempt['timestart']);
                }
                if (isset($attempt['timefinish']) && $attempt['timefinish'] > 0) {
                    $attempt['timefinish_formatted'] = date('Y-m-d H:i:s', $attempt['timefinish']);
                }
                
                // Calculate time taken
                if (isset($attempt['timestart']) && isset($attempt['timefinish']) && 
                    $attempt['timestart'] > 0 && $attempt['timefinish'] > 0) {
                    $timeTaken = $attempt['timefinish'] - $attempt['timestart'];
                    $attempt['time_taken_seconds'] = $timeTaken;
                    $attempt['time_taken_formatted'] = $this->formatTime($timeTaken);
                }
                
                // Calculate grade percentage if sumgrades and grade available
                if (isset($attempt['sumgrades']) && isset($attempt['grade'])) {
                    if ($attempt['sumgrades'] > 0) {
                        $attempt['grade_percentage'] = round(($attempt['grade'] / $attempt['sumgrades']) * 100, 2);
                    }
                }
                
                // Add quiz info
                $attempt['quiz_id'] = $quizId;
            }

            return [
                'attempts' => $attempts,
                'total' => count($attempts),
                'quiz_id' => $quizId,
                'message' => count($attempts) > 0 ? 'Berhasil mengambil ' . count($attempts) . ' attempt' : 'Tidak ada attempt ditemukan'
            ];

        } catch (\Exception $e) {
            Log::error('Error getting quiz attempts for quiz', [
                'quiz_id' => $quizId,
                'user_id' => $moodleUserId,
                'error' => $e->getMessage()
            ]);
            
            return [
                'attempts' => [],
                'error' => $e->getMessage(),
                'quiz_id' => $quizId
            ];
        }
    }

    /**
     * Get attempt summary
     */
    private function getAttemptSummary($attemptId): ?array
    {
        if (!$attemptId) return null;
        
        try {
            $response = $this->callMoodleApi('mod_quiz_get_attempt_summary', [
                'attemptid' => (int)$attemptId
            ]);

            if (isset($response['exception'])) {
                return null;
            }

            return $response;

        } catch (\Exception $e) {
            Log::error('Error getting attempt summary', [
                'attempt_id' => $attemptId,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Get attempt review
     */
    private function getAttemptReview($attemptId): ?array
    {
        if (!$attemptId) return null;
        
        try {
            $response = $this->callMoodleApi('mod_quiz_get_attempt_review', [
                'attemptid' => (int)$attemptId
            ]);

            if (isset($response['exception'])) {
                return null;
            }

            return $response;

        } catch (\Exception $e) {
            Log::error('Error getting attempt review', [
                'attempt_id' => $attemptId,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Format time in seconds to readable format
     */
    private function formatTime($seconds): string
    {
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $secs = $seconds % 60;
        
        if ($hours > 0) {
            return sprintf('%d jam %d menit %d detik', $hours, $minutes, $secs);
        } elseif ($minutes > 0) {
            return sprintf('%d menit %d detik', $minutes, $secs);
        } else {
            return sprintf('%d detik', $secs);
        }
    }

    /**
     * Get ALL quiz attempts for a user across ALL courses
     */
    public function getAllUserQuizAttempts($moodleUserId): JsonResponse
    {
        try {
            $allAttempts = [];
            
            // First, get user's courses
            $userCourses = $this->getUserCourses($moodleUserId);
            
            if (empty($userCourses)) {
                return response()->json([
                    'success' => true,
                    'data' => [
                        'attempts' => [],
                        'total_attempts' => 0,
                        'message' => 'User tidak memiliki kursus'
                    ]
                ]);
            }

            // For each course, get quiz attempts
            foreach ($userCourses as $course) {
                $courseId = $course['id'] ?? null;
                $courseName = $course['fullname'] ?? 'Unknown Course';
                
                if ($courseId) {
                    // Get all quizzes in the course
                    $quizzes = $this->getCourseQuizzes($courseId);
                    
                    foreach ($quizzes as $quiz) {
                        $quizId = $quiz['id'] ?? null;
                        $quizName = $quiz['name'] ?? 'Unknown Quiz';
                        
                        if ($quizId) {
                            $quizAttempts = $this->getUserQuizAttemptsForSpecificQuiz($moodleUserId, $quizId);
                            
                            if (!empty($quizAttempts['attempts'])) {
                                foreach ($quizAttempts['attempts'] as $attempt) {
                                    $attempt['course_id'] = $courseId;
                                    $attempt['course_name'] = $courseName;
                                    $attempt['quiz_name'] = $quizName;
                                    $allAttempts[] = $attempt;
                                }
                            }
                        }
                    }
                }
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'attempts' => $allAttempts,
                    'total_attempts' => count($allAttempts),
                    'message' => count($allAttempts) > 0 ? 'Berhasil mengambil ' . count($allAttempts) . ' attempt' : 'Tidak ada attempt ditemukan'
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting all quiz attempts', [
                'moodle_user_id' => $moodleUserId,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil attempt quiz: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all quizzes in a course
     */
    private function getCourseQuizzes($courseId): array
    {
        try {
            $response = $this->callMoodleApi('mod_quiz_get_quizzes_by_courses', [
                'courseids' => [(int)$courseId]
            ]);

            if (isset($response['exception'])) {
                Log::error('Error getting course quizzes', [
                    'course_id' => $courseId,
                    'response' => $response
                ]);
                return [];
            }

            return $response['quizzes'] ?? [];

        } catch (\Exception $e) {
            Log::error('Error in getCourseQuizzes', [
                'course_id' => $courseId,
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    /**
     * Get user quiz attempts data (backward compatibility)
     */
    private function getUserQuizAttemptsData($moodleUserId): array
    {
        // Use the specific quiz ID 1205 for backward compatibility
        return $this->getUserQuizAttemptsForSpecificQuiz($moodleUserId, 1205);
    }

    /**
     * Call Moodle API - IMPROVED VERSION with better error handling
     */
    private function callMoodleApi(string $function, array $params): array
    {
        try {
            $payload = [
                'wstoken' => $this->moodleToken,
                'wsfunction' => $function,
                'moodlewsrestformat' => 'json'
            ];

            // Merge params dengan payload
            $payload = array_merge($payload, $params);

            Log::info('Calling Moodle API', [
                'function' => $function,
                'params' => $params
            ]);

            $response = Http::timeout(60)
                ->retry(3, 1000)
                ->asForm()
                ->post($this->moodleUrl, $payload);

            if ($response->failed()) {
                Log::error('Moodle API HTTP Error', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                
                return [
                    'exception' => true,
                    'message' => 'HTTP Error: ' . $response->status(),
                    'errorcode' => 'http_error'
                ];
            }

            $result = $response->json();

            // Log warning jika ada exception
            if (isset($result['exception'])) {
                Log::warning('Moodle API Exception', [
                    'function' => $function,
                    'exception' => $result['exception'] ?? '',
                    'message' => $result['message'] ?? '',
                    'errorcode' => $result['errorcode'] ?? ''
                ]);
            }

            return $result;

        } catch (\Exception $e) {
            Log::error('Moodle API Call Failed', [
                'function' => $function,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return [
                'exception' => true,
                'message' => $e->getMessage(),
                'errorcode' => 'connection_error'
            ];
        }
    }

    /**
     * Get Moodle user data by ID - FIXED VERSION
     */
    private function getMoodleUserById($moodleUserId): ?array
    {
        try {
            $response = $this->callMoodleApi('core_user_get_users', [
                'criteria' => [
                    ['key' => 'id', 'value' => (string)$moodleUserId]
                ]
            ]);

            if (isset($response['exception'])) {
                Log::error('Moodle API Error', [
                    'function' => 'core_user_get_users',
                    'moodle_user_id' => $moodleUserId,
                    'response' => $response
                ]);
                return null;
            }

            if (isset($response['users']) && count($response['users']) > 0) {
                return $response['users'][0];
            }

            return null;

        } catch (\Exception $e) {
            Log::error('Error getting Moodle user', [
                'moodle_user_id' => $moodleUserId,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Get user courses from Moodle - FIXED VERSION
     */
    private function getUserCourses($moodleUserId): ?array
    {
        try {
            $response = $this->callMoodleApi('core_enrol_get_users_courses', [
                'userid' => (int)$moodleUserId
            ]);

            if (isset($response['exception'])) {
                Log::error('Moodle API Error - User Courses', [
                    'function' => 'core_enrol_get_users_courses',
                    'moodle_user_id' => $moodleUserId,
                    'response' => $response
                ]);
                return null;
            }

            return $response ?? null;

        } catch (\Exception $e) {
            Log::error('Error getting user courses', [
                'moodle_user_id' => $moodleUserId,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Get user grades from Moodle - FIXED VERSION
     */
    private function getUserGrades($moodleUserId): ?array
    {
        try {
            $response = $this->callMoodleApi('gradereport_user_get_grade_items', [
                'userid' => (int)$moodleUserId,
                'courseid' => 384 // Course ID tertentu
            ]);

            if (isset($response['exception'])) {
                Log::error('Moodle API Error - User Grades', [
                    'function' => 'gradereport_user_get_grade_items',
                    'moodle_user_id' => $moodleUserId,
                    'response' => $response
                ]);
                return null;
            }

            return $response ?? null;

        } catch (\Exception $e) {
            Log::error('Error getting user grades', [
                'moodle_user_id' => $moodleUserId,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Get enrollment status for user
     */
    private function getEnrollmentStatus($moodleUserId): array
    {
        try {
            $courses = $this->getUserCourses($moodleUserId);
            
            if (!$courses || isset($courses['exception'])) {
                return ['status' => 'unknown', 'enrolled_courses' => 0];
            }

            $enrolledCourses = is_array($courses) ? count($courses) : 0;
            
            return [
                'status' => $enrolledCourses > 0 ? 'enrolled' : 'not_enrolled',
                'enrolled_courses' => $enrolledCourses
            ];

        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    /**
     * Get quiz attempts by course
     */
    public function getQuizAttemptsByCourse($moodleUserId, $courseId): JsonResponse
    {
        try {
            $moodleUser = MasterMoodle::where('moodle_user_id', $moodleUserId)->first();
            
            if (!$moodleUser) {
                return response()->json([
                    'success' => false,
                    'message' => 'User tidak ditemukan'
                ], 404);
            }

            $courseAttempts = [];
            
            // Get all quizzes in the course
            $quizzes = $this->getCourseQuizzes($courseId);
            
            foreach ($quizzes as $quiz) {
                $quizId = $quiz['id'] ?? null;
                $quizName = $quiz['name'] ?? 'Unknown Quiz';
                
                if ($quizId) {
                    $quizAttempts = $this->getUserQuizAttemptsForSpecificQuiz($moodleUserId, $quizId);
                    
                    if (!empty($quizAttempts['attempts'])) {
                        foreach ($quizAttempts['attempts'] as $attempt) {
                            $attempt['quiz_name'] = $quizName;
                            $attempt['course_id'] = $courseId;
                            $courseAttempts[] = $attempt;
                        }
                    }
                }
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'attempts' => $courseAttempts,
                    'total' => count($courseAttempts),
                    'course_id' => $courseId,
                    'message' => count($courseAttempts) > 0 ? 'Berhasil mengambil ' . count($courseAttempts) . ' attempt' : 'Tidak ada attempt ditemukan'
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil attempt quiz: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get detailed attempt information
     */
    public function getAttemptDetails($attemptId): JsonResponse
    {
        try {
            $attemptSummary = $this->getAttemptSummary($attemptId);
            $attemptReview = $this->getAttemptReview($attemptId);

            return response()->json([
                'success' => true,
                'data' => [
                    'summary' => $attemptSummary,
                    'review' => $attemptReview
                ],
                'message' => 'Detail attempt berhasil diambil'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil detail attempt: ' . $e->getMessage()
            ], 500);
        }
    }
}