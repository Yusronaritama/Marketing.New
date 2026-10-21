<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class Admin_MoodleTestController extends Controller
{
    private $moodleUrl;
    private $moodleToken;

    public function __construct()
    {
        $this->moodleUrl = 'https://elearning2.ubpkarawang.ac.id/webservice/rest/server.php';
        $this->moodleToken = '9d812708c38116dbbe03864ccb860b52';
    }

    /**
     * Test connection to Moodle API
     */
    public function testConnection(): JsonResponse
    {
        try {
            $response = $this->callMoodleApi('core_webservice_get_site_info', []);

            return response()->json([
                'success' => true,
                'data' => $response,
                'message' => 'Koneksi Moodle API berhasil'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal terhubung ke Moodle API: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all courses available
     */
    public function getAllCourses(): JsonResponse
    {
        try {
            $response = $this->callMoodleApi('core_course_get_courses', []);

            $courses = [];
            if (is_array($response)) {
                $courses = $response;
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'total_courses' => count($courses),
                    'courses' => $courses
                ],
                'message' => 'Data courses berhasil diambil'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil courses: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all quizzes from all courses
     */
    public function getAllQuizzes(): JsonResponse
    {
        try {
            // First get all courses
            $courses = $this->callMoodleApi('core_course_get_courses', []);
            
            if (isset($courses['exception'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal mengambil courses: ' . ($courses['message'] ?? 'Unknown error')
                ], 500);
            }

            $allQuizzes = [];
            $courseQuizzes = [];

            foreach ($courses as $course) {
                $courseId = $course['id'] ?? null;
                $courseName = $course['fullname'] ?? 'Unknown Course';
                
                if ($courseId) {
                    $quizzes = $this->getQuizzesByCourse($courseId);
                    
                    if (!empty($quizzes)) {
                        $courseQuizzes[] = [
                            'course_id' => $courseId,
                            'course_name' => $courseName,
                            'quizzes_count' => count($quizzes),
                            'quizzes' => $quizzes
                        ];
                        
                        $allQuizzes = array_merge($allQuizzes, $quizzes);
                    }
                }
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'total_courses_scanned' => count($courses),
                    'total_quizzes_found' => count($allQuizzes),
                    'courses_with_quizzes' => count($courseQuizzes),
                    'course_quizzes' => $courseQuizzes,
                    'all_quizzes' => $allQuizzes
                ],
                'message' => 'Data quizzes berhasil diambil'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil quizzes: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get quizzes by specific course ID
     */
    public function getQuizzesByCourseId($courseId): JsonResponse
    {
        try {
            Log::info('Getting quizzes for course', ['course_id' => $courseId]);

            $quizzes = $this->getQuizzesByCourse($courseId);
            
            // Also get course info
            $courseInfo = $this->getCourseInfo($courseId);

            return response()->json([
                'success' => true,
                'data' => [
                    'course_info' => $courseInfo,
                    'total_quizzes' => count($quizzes),
                    'quizzes' => $quizzes
                ],
                'message' => count($quizzes) > 0 ? 
                    'Found ' . count($quizzes) . ' quizzes in course' : 
                    'No quizzes found in course'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil quizzes: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Search quizzes by name or course
     */
    public function searchQuizzes(Request $request): JsonResponse
    {
        try {
            $searchTerm = $request->input('search', '');
            $courseId = $request->input('course_id');

            if ($courseId) {
                // Search in specific course
                $quizzes = $this->getQuizzesByCourse($courseId);
                $filteredQuizzes = $this->filterQuizzes($quizzes, $searchTerm);
                
                $courseInfo = $this->getCourseInfo($courseId);
                
                return response()->json([
                    'success' => true,
                    'data' => [
                        'course_info' => $courseInfo,
                        'total_found' => count($filteredQuizzes),
                        'quizzes' => $filteredQuizzes
                    ],
                    'message' => 'Search results for quizzes'
                ]);
            } else {
                // Search in all courses
                $allQuizzes = [];
                $courses = $this->callMoodleApi('core_course_get_courses', []);
                
                foreach ($courses as $course) {
                    $courseQuizzes = $this->getQuizzesByCourse($course['id'] ?? null);
                    $allQuizzes = array_merge($allQuizzes, $courseQuizzes);
                }
                
                $filteredQuizzes = $this->filterQuizzes($allQuizzes, $searchTerm);
                
                return response()->json([
                    'success' => true,
                    'data' => [
                        'total_scanned' => count($allQuizzes),
                        'total_found' => count($filteredQuizzes),
                        'quizzes' => $filteredQuizzes
                    ],
                    'message' => 'Search results for quizzes in all courses'
                ]);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mencari quizzes: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get specific quiz details
     */
    public function getQuizDetails($quizId): JsonResponse
    {
        try {
            Log::info('Getting quiz details', ['quiz_id' => $quizId]);

            // Try to get quiz by accessing through courses
            $courses = $this->callMoodleApi('core_course_get_courses', []);
            
            $quizDetails = null;
            $foundInCourse = null;

            foreach ($courses as $course) {
                $courseId = $course['id'] ?? null;
                if ($courseId) {
                    $quizzes = $this->getQuizzesByCourse($courseId);
                    
                    foreach ($quizzes as $quiz) {
                        if (($quiz['id'] ?? null) == $quizId) {
                            $quizDetails = $quiz;
                            $foundInCourse = $course;
                            break 2;
                        }
                    }
                }
            }

            if ($quizDetails) {
                // Get additional quiz information
                $quizAccessInfo = $this->getQuizAccessInformation($quizId);
                $quizFeedback = $this->getQuizFeedback($quizId);

                return response()->json([
                    'success' => true,
                    'data' => [
                        'quiz' => $quizDetails,
                        'course' => $foundInCourse,
                        'access_info' => $quizAccessInfo,
                        'feedback' => $quizFeedback,
                        'raw_data' => $this->safeJsonEncode($quizDetails)
                    ],
                    'message' => 'Quiz details retrieved successfully'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Quiz not found with ID: ' . $quizId
                ], 404);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil detail quiz: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Test quiz attempts for specific user
     */
    public function testQuizAttempts($moodleUserId, $quizId = null): JsonResponse
    {
        try {
            Log::info('Testing quiz attempts', [
                'moodle_user_id' => $moodleUserId,
                'quiz_id' => $quizId
            ]);

            $testResults = [];

            if ($quizId) {
                // Test specific quiz
                $testResults['specific_quiz'] = $this->testSingleQuizAttempt($moodleUserId, $quizId);
            } else {
                // Test all quizzes in user's courses
                $testResults['all_quizzes'] = $this->testAllQuizAttempts($moodleUserId);
            }

            // Also test alternative methods
            $testResults['alternative_methods'] = $this->testAlternativeMethods($moodleUserId);

            return response()->json([
                'success' => true,
                'data' => $testResults,
                'message' => 'Quiz attempts test completed'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal testing quiz attempts: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get user's enrolled courses and their quizzes
     */
    public function getUserCoursesWithQuizzes($moodleUserId): JsonResponse
    {
        try {
            Log::info('Getting user courses with quizzes', ['user_id' => $moodleUserId]);

            // Get user courses
            $userCourses = $this->callMoodleApi('core_enrol_get_users_courses', [
                'userid' => (int)$moodleUserId
            ]);

            if (isset($userCourses['exception'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to get user courses: ' . ($userCourses['message'] ?? 'Unknown error')
                ], 500);
            }

            $coursesWithQuizzes = [];

            foreach ($userCourses as $course) {
                $courseId = $course['id'] ?? null;
                $courseName = $course['fullname'] ?? 'Unknown Course';
                
                if ($courseId) {
                    $quizzes = $this->getQuizzesByCourse($courseId);
                    
                    $coursesWithQuizzes[] = [
                        'course_id' => $courseId,
                        'course_name' => $courseName,
                        'quizzes_count' => count($quizzes),
                        'quizzes' => $quizzes,
                        'course_details' => $course
                    ];
                }
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'user_id' => $moodleUserId,
                    'total_courses' => count($userCourses),
                    'courses_with_quizzes' => $coursesWithQuizzes
                ],
                'message' => 'User courses with quizzes retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil courses user: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get quiz attempts statistics
     */
    public function getQuizAttemptsStats($quizId): JsonResponse
    {
        try {
            Log::info('Getting quiz attempts stats', ['quiz_id' => $quizId]);

            // Get quiz attempts (limited to 50 for testing)
            $response = $this->callMoodleApi('mod_quiz_get_attempt_review', [
                'attemptid' => 1, // We need an attempt ID, this might not work
                'page' => 0
            ]);

            // Try alternative: get user attempts
            $testAttempts = [];
            $testUserIds = [39955, 39958]; // Test dengan user IDs dari data Anda
            
            foreach ($testUserIds as $userId) {
                $attempts = $this->callMoodleApi('mod_quiz_get_user_attempts', [
                    'quizid' => (int)$quizId,
                    'userid' => $userId
                ]);
                
                if (isset($attempts['attempts'])) {
                    $testAttempts[$userId] = $attempts['attempts'];
                }
            }

            // Get quiz structure
            $quizStructure = $this->callMoodleApi('mod_quiz_get_quiz_access_information', [
                'quizid' => (int)$quizId
            ]);

            return response()->json([
                'success' => true,
                'data' => [
                    'quiz_id' => $quizId,
                    'test_attempts' => $testAttempts,
                    'quiz_structure' => $quizStructure,
                    'response_samples' => [
                        'attempt_review_response' => $response,
                        'test_user_ids' => $testUserIds
                    ]
                ],
                'message' => 'Quiz attempts statistics retrieved'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil statistik attempts: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Test Moodle API functions related to quizzes
     */
    public function testMoodleQuizFunctions(): JsonResponse
    {
        try {
            $functions = [
                'mod_quiz_get_quizzes_by_courses',
                'mod_quiz_get_user_attempts',
                'mod_quiz_get_attempt_review',
                'mod_quiz_get_attempt_summary',
                'mod_quiz_get_quiz_access_information',
                'mod_quiz_get_quiz_feedback_for_grade',
                'mod_quiz_get_quiz_required_qtypes',
                'core_grades_get_grades'
            ];

            $results = [];

            foreach ($functions as $function) {
                try {
                    $params = [];
                    
                    // Set appropriate parameters for each function
                    switch ($function) {
                        case 'mod_quiz_get_quizzes_by_courses':
                            $params = ['courseids' => [384]]; // Course ID Ujian
                            break;
                        case 'mod_quiz_get_user_attempts':
                            $params = [
                                'quizid' => 1197,
                                'userid' => 39955 // User ID dari data Anda
                            ];
                            break;
                        case 'mod_quiz_get_attempt_review':
                        case 'mod_quiz_get_attempt_summary':
                            $params = ['attemptid' => 1]; // Test attempt ID
                            break;
                        case 'mod_quiz_get_quiz_access_information':
                            $params = ['quizid' => 1197];
                            break;
                        case 'core_grades_get_grades':
                            $params = [
                                'courseid' => 384,
                                'userids' => [39955]
                            ];
                            break;
                    }

                    $response = $this->callMoodleApi($function, $params);
                    
                    $results[$function] = [
                        'success' => !isset($response['exception']),
                        'has_exception' => isset($response['exception']),
                        'exception_message' => $response['message'] ?? null,
                        'response_keys' => array_keys($response),
                        'sample_data' => $this->getSampleData($response)
                    ];

                } catch (\Exception $e) {
                    $results[$function] = [
                        'success' => false,
                        'error' => $e->getMessage()
                    ];
                }
            }

            return response()->json([
                'success' => true,
                'data' => $results,
                'message' => 'Moodle quiz functions test completed'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal testing Moodle functions: ' . $e->getMessage()
            ], 500);
        }
    }

    // ========== HELPER METHODS ==========

    /**
     * Get quizzes by course
     */
    private function getQuizzesByCourse($courseId): array
    {
        try {
            $response = $this->callMoodleApi('mod_quiz_get_quizzes_by_courses', [
                'courseids' => [(int)$courseId]
            ]);

            if (isset($response['exception'])) {
                Log::warning('Error getting quizzes by course', [
                    'course_id' => $courseId,
                    'response' => $response
                ]);
                return [];
            }

            return $response['quizzes'] ?? [];

        } catch (\Exception $e) {
            Log::error('Error in getQuizzesByCourse', [
                'course_id' => $courseId,
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    /**
     * Get course info
     */
    private function getCourseInfo($courseId): array
    {
        try {
            $response = $this->callMoodleApi('core_course_get_courses', [
                'options' => [
                    'ids' => [(int)$courseId]
                ]
            ]);

            if (isset($response['exception'])) {
                return ['error' => $response['message'] ?? 'Unknown error'];
            }

            return $response[0] ?? ['id' => $courseId, 'name' => 'Unknown Course'];

        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Filter quizzes by search term
     */
    private function filterQuizzes(array $quizzes, string $searchTerm): array
    {
        if (empty($searchTerm)) {
            return $quizzes;
        }

        $filtered = [];
        $searchLower = strtolower($searchTerm);

        foreach ($quizzes as $quiz) {
            $name = strtolower($quiz['name'] ?? '');
            $intro = strtolower($quiz['intro'] ?? '');
            
            if (strpos($name, $searchLower) !== false || 
                strpos($intro, $searchLower) !== false) {
                $filtered[] = $quiz;
            }
        }

        return $filtered;
    }

    /**
     * Get quiz access information
     */
    private function getQuizAccessInformation($quizId): array
    {
        try {
            $response = $this->callMoodleApi('mod_quiz_get_quiz_access_information', [
                'quizid' => (int)$quizId
            ]);

            return $response;

        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Get quiz feedback
     */
    private function getQuizFeedback($quizId): array
    {
        try {
            $response = $this->callMoodleApi('mod_quiz_get_quiz_feedback_for_grade', [
                'quizid' => (int)$quizId,
                'grade' => 50 // Test grade
            ]);

            return $response;

        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Test single quiz attempt
     */
    private function testSingleQuizAttempt($moodleUserId, $quizId): array
    {
        $response = $this->callMoodleApi('mod_quiz_get_user_attempts', [
            'quizid' => (int)$quizId,
            'userid' => (int)$moodleUserId
        ]);

        return [
            'quiz_id' => $quizId,
            'user_id' => $moodleUserId,
            'has_attempts' => isset($response['attempts']),
            'attempts_count' => isset($response['attempts']) ? count($response['attempts']) : 0,
            'has_exception' => isset($response['exception']),
            'exception_message' => $response['message'] ?? null,
            'response_structure' => array_keys($response)
        ];
    }

    /**
     * Test all quiz attempts for user
     */
    private function testAllQuizAttempts($moodleUserId): array
    {
        $results = [];
        
        // Get user courses
        $userCourses = $this->callMoodleApi('core_enrol_get_users_courses', [
            'userid' => (int)$moodleUserId
        ]);

        if (isset($userCourses['exception'])) {
            return ['error' => 'Failed to get user courses'];
        }

        foreach ($userCourses as $course) {
            $courseId = $course['id'] ?? null;
            if ($courseId) {
                $quizzes = $this->getQuizzesByCourse($courseId);
                
                foreach ($quizzes as $quiz) {
                    $quizId = $quiz['id'] ?? null;
                    if ($quizId) {
                        $results[] = $this->testSingleQuizAttempt($moodleUserId, $quizId);
                    }
                }
            }
        }

        return $results;
    }

    /**
     * Test alternative methods for getting quiz data
     */
    private function testAlternativeMethods($moodleUserId): array
    {
        $methods = [];

        // Method 1: core_grades_get_grades
        $methods['core_grades_get_grades'] = $this->callMoodleApi('core_grades_get_grades', [
            'courseid' => 384,
            'userids' => [(int)$moodleUserId]
        ]);

        // Method 2: gradereport_user_get_grade_items
        $methods['gradereport_user_get_grade_items'] = $this->callMoodleApi('gradereport_user_get_grade_items', [
            'courseid' => 384,
            'userid' => (int)$moodleUserId
        ]);

        // Method 3: gradereport_user_get_grades_table
        $methods['gradereport_user_get_grades_table'] = $this->callMoodleApi('gradereport_user_get_grades_table', [
            'courseid' => 384,
            'userid' => (int)$moodleUserId
        ]);

        return $methods;
    }

    /**
     * Get sample data from response
     */
    private function getSampleData($response): array
    {
        if (!is_array($response)) {
            return ['type' => gettype($response)];
        }

        $sample = [];
        $count = 0;
        
        foreach ($response as $key => $value) {
            if ($count >= 3) break; // Limit to 3 items
            
            if (is_array($value) && count($value) > 0) {
                $sample[$key] = ['count' => count($value), 'first_item' => $value[0] ?? null];
            } else {
                $sample[$key] = $value;
            }
            
            $count++;
        }

        return $sample;
    }

    /**
     * Safe JSON encode
     */
    private function safeJsonEncode($data): string
    {
        try {
            return json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        } catch (\Exception $e) {
            return 'Error encoding JSON: ' . $e->getMessage();
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

            Log::debug('Calling Moodle API', [
                'function' => $function,
                'params' => $params
            ]);

            $response = Http::timeout(60)
                ->retry(3, 1000)
                ->asForm()
                ->post($this->moodleUrl, $payload);

            if ($response->failed()) {
                return [
                    'exception' => true,
                    'message' => 'HTTP Error: ' . $response->status(),
                    'errorcode' => 'http_error',
                    'body' => $response->body()
                ];
            }

            $result = $response->json();

            if (isset($result['exception'])) {
                Log::warning('Moodle API Exception', [
                    'function' => $function,
                    'exception' => $result['exception'],
                    'message' => $result['message'] ?? '',
                    'errorcode' => $result['errorcode'] ?? ''
                ]);
            }

            return $result;

        } catch (\Exception $e) {
            Log::error('Moodle API Call Failed', [
                'function' => $function,
                'error' => $e->getMessage()
            ]);
            
            return [
                'exception' => true,
                'message' => $e->getMessage(),
                'errorcode' => 'connection_error'
            ];
        }
    }
}