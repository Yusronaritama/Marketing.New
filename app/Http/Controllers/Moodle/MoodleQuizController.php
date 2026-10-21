<?php

namespace App\Http\Controllers\Moodle;

use App\Http\Controllers\Controller;
use App\Models\MasterUser;
use App\Models\MasterMoodle;
use App\Models\MasterEreg;
use App\Models\Nilai;
use App\Models\Kelulusan;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Libraries\MoodleRest;

class MoodleQuizController extends Controller
{
    protected $moodleRest;

    public function __construct()
    {
        $this->moodleRest = new MoodleRest(
            config('services.moodle.url'),
            config('services.moodle.token')
        );
    }

    /**
     * Display the main quiz management page
     */
    public function index()
    {
        return view('admin.moodle-quiz.index');
    }

    /**
     * Display quiz dashboard
     */
    public function dashboard()
    {
        return view('admin.moodle-quiz.dashboard');
    }

    /**
     * Get all Moodle users via REST API
     */
    public function getMoodleUsers(Request $request)
    {
        try {
            $search = $request->get('search');
            $perPage = $request->get('per_page', 50);

            // Get Moodle users via REST API
            $moodleUsers = $this->getMoodleUsersFromAPI();

            if (!$moodleUsers['success']) {
                return response()->json([
                    'success' => false,
                    'message' => $moodleUsers['message']
                ], 400);
            }

            $users = collect($moodleUsers['data']);

            // Apply search filter
            if ($search) {
                $users = $users->filter(function($user) use ($search) {
                    return stripos($user['username'], $search) !== false ||
                           stripos($user['email'], $search) !== false ||
                           stripos($user['firstname'] . ' ' . $user['lastname'], $search) !== false;
                });
            }

            // Paginate results
            $currentPage = $request->get('page', 1);
            $paginatedUsers = $users->slice(($currentPage - 1) * $perPage, $perPage)->values();

            return response()->json([
                'success' => true,
                'data' => [
                    'users' => $paginatedUsers,
                    'pagination' => [
                        'total' => $users->count(),
                        'per_page' => $perPage,
                        'current_page' => (int)$currentPage,
                        'last_page' => ceil($users->count() / $perPage),
                    ]
                ]
            ]);

        } catch (\Throwable $th) {
            Log::error("💥 Error getting Moodle users: " . $th->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem: ' . $th->getMessage()
            ], 500);
        }
    }

    /**
     * Get user quiz scores with pagination and filters
     */
    public function getUserQuizScores(Request $request)
    {
        try {
            $perPage = $request->get('per_page', 20);
            $search = $request->get('search');
            $status = $request->get('status');
            $syncWithMoodle = $request->get('sync', false);

            // Query untuk mendapatkan users dengan akun Moodle
            $query = DB::table('master_users as mu')
                ->join('master_moodle as mm', 'mu.id', '=', 'mm.master_user_id')
                ->join('master_ereg as me', 'mu.user_id', '=', 'me.user_id')
                ->leftJoin('users as u', 'mu.user_id', '=', 'u.id')
                ->leftJoin('nilai as n', 'me.id', '=', 'n.id_pendaftaran')
                ->where('mm.status', 'success')
                ->select(
                    'mu.id as master_user_id',
                    'mu.va_number',
                    'u.id as user_id',
                    'u.name as user_name',
                    'u.email as user_email',
                    'mm.moodle_user_id',
                    'mm.status as moodle_status',
                    'mm.enrol_status',
                    'me.id as ereg_id',
                    'me.jurusan1',
                    'me.jurusan2',
                    'n.nilai',
                    'n.attempt_id',
                    'n.attempt_number',
                    'n.time_start',
                    'n.time_finish',
                    'n.updated_at as nilai_updated_at'
                );

            // Filter pencarian
            if ($search) {
                $query->where(function($q) use ($search) {
                    $q->where('mu.va_number', 'like', "%{$search}%")
                      ->orWhere('u.name', 'like', "%{$search}%")
                      ->orWhere('u.email', 'like', "%{$search}%")
                      ->orWhere('mm.moodle_user_id', 'like', "%{$search}%");
                });
            }

            // Filter status
            if ($status) {
                if ($status === 'sudah_ujian') {
                    $query->whereNotNull('n.nilai');
                } elseif ($status === 'belum_ujian') {
                    $query->whereNull('n.nilai');
                } elseif ($status === 'enrolled') {
                    $query->where('mm.enrol_status', 'enrolled');
                }
            }

            $users = $query->orderBy('mu.id', 'desc')->paginate($perPage);

            // Sync dengan Moodle jika diminta
            if ($syncWithMoodle) {
                $this->syncQuizScoresWithMoodle($users->items());
            }

            $formattedUsers = collect($users->items())->map(function($user) {
                return [
                    'master_user_id' => $user->master_user_id,
                    'va_number' => $user->va_number,
                    'user' => [
                        'id' => $user->user_id,
                        'name' => $user->user_name,
                        'email' => $user->user_email,
                    ],
                    'moodle_user' => [
                        'moodle_user_id' => $user->moodle_user_id,
                        'status' => $user->moodle_status,
                        'enrol_status' => $user->enrol_status,
                    ],
                    'ereg' => [
                        'id' => $user->ereg_id,
                        'jurusan1' => $user->jurusan1,
                        'jurusan2' => $user->jurusan2,
                    ],
                    'quiz_data' => $user->nilai ? [
                        'nilai' => (float) $user->nilai,
                        'attempt_id' => $user->attempt_id,
                        'attempt_number' => $user->attempt_number,
                        'time_start' => $user->time_start,
                        'time_finish' => $user->time_finish,
                        'last_updated' => $user->nilai_updated_at,
                    ] : null,
                    'status_ujian' => $user->nilai ? 'Sudah Ujian' : 'Belum Ujian'
                ];
            });

            return response()->json([
                'success' => true,
                'data' => [
                    'users' => $formattedUsers,
                    'pagination' => [
                        'total' => $users->total(),
                        'per_page' => $users->perPage(),
                        'current_page' => $users->currentPage(),
                        'last_page' => $users->lastPage(),
                        'from' => $users->firstItem(),
                        'to' => $users->lastItem(),
                    ],
                    'summary' => [
                        'total_users' => $users->total(),
                        'sudah_ujian' => $users->where('nilai', '!=', null)->count(),
                        'belum_ujian' => $users->where('nilai', null)->count(),
                    ]
                ]
            ]);

        } catch (\Throwable $th) {
            Log::error("💥 Error getting user quiz scores: " . $th->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem: ' . $th->getMessage()
            ], 500);
        }
    }

    /**
     * Get detailed quiz information for specific user with Moodle REST API
     */
    public function getUserQuizDetail($master_user_id)
    {
        try {
            // Get user data with relations
            $masterUser = MasterUser::with(['user', 'moodleUser', 'ereg'])->find($master_user_id);

            if (!$masterUser) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data user tidak ditemukan'
                ], 404);
            }

            if (!$masterUser->moodleUser || $masterUser->moodleUser->status !== 'success') {
                return response()->json([
                    'success' => false,
                    'message' => 'User tidak memiliki akun Moodle yang aktif'
                ], 400);
            }

            if (!$masterUser->ereg) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data pendaftaran tidak ditemukan'
                ], 400);
            }

            $moodleUserId = $masterUser->moodleUser->moodle_user_id;
            $vaNumber = $masterUser->va_number;
            $eregId = $masterUser->ereg->id;

            // Get detailed user info from Moodle REST API
            $moodleUserInfo = $this->getMoodleUserInfo($moodleUserId);
            
            // Get quiz attempts from Moodle REST API
            $quizResult = $this->getQuizAttemptsFromMoodle($moodleUserId, $vaNumber);

            if (!$quizResult['success']) {
                return response()->json([
                    'success' => false,
                    'message' => $quizResult['message']
                ], 400);
            }

            $attempt = $quizResult['attempt'];
            $score = $attempt['sumgrades'] ?? 0;

            // Save to database
            $this->saveQuizScore($eregId, $score, $attempt);

            // Get saved data
            $nilai = Nilai::where('id_pendaftaran', $eregId)->first();
            $kelulusan = Kelulusan::where('id_pendaftaran', $eregId)->first();

            return response()->json([
                'success' => true,
                'data' => [
                    'user_info' => [
                        'master_user_id' => $masterUser->id,
                        'va_number' => $vaNumber,
                        'name' => $masterUser->user->name ?? 'N/A',
                        'email' => $masterUser->user->email ?? 'N/A',
                    ],
                    'moodle_info' => [
                        'moodle_user_id' => $moodleUserId,
                        'username' => $moodleUserInfo['username'] ?? 'N/A',
                        'firstname' => $moodleUserInfo['firstname'] ?? 'N/A',
                        'lastname' => $moodleUserInfo['lastname'] ?? 'N/A',
                        'email' => $moodleUserInfo['email'] ?? 'N/A',
                        'status' => $masterUser->moodleUser->status,
                        'enrol_status' => $masterUser->moodleUser->enrol_status,
                    ],
                    'ereg_info' => [
                        'id' => $eregId,
                        'jurusan1' => $masterUser->ereg->jurusan1,
                        'jurusan2' => $masterUser->ereg->jurusan2,
                    ],
                    'quiz_attempt' => [
                        'score' => $score,
                        'attempt_id' => $attempt['id'] ?? null,
                        'attempt_number' => $attempt['attempt'] ?? null,
                        'state' => $attempt['state'] ?? null,
                        'time_start' => $attempt['timestart'] ? date('Y-m-d H:i:s', $attempt['timestart']) : null,
                        'time_finish' => $attempt['timefinish'] ? date('Y-m-d H:i:s', $attempt['timefinish']) : null,
                        'questions' => $this->getQuizAttemptReview($attempt['id'] ?? null),
                    ],
                    'saved_data' => [
                        'nilai' => $nilai ? [
                            'nilai' => (float) $nilai->nilai,
                            'attempt_id' => $nilai->attempt_id,
                            'attempt_number' => $nilai->attempt_number,
                            'time_start' => $nilai->time_start,
                            'time_finish' => $nilai->time_finish,
                            'last_updated' => $nilai->updated_at,
                        ] : null,
                        'kelulusan' => $kelulusan ? [
                            'status_lulus' => $kelulusan->status_lulus,
                            'keterangan' => $kelulusan->keterangan,
                            'nilai_akhir' => $kelulusan->nilai_akhir,
                            'prodi' => $kelulusan->prodi,
                        ] : null,
                    ]
                ]
            ]);

        } catch (\Throwable $th) {
            Log::error("💥 Error getting user quiz detail for {$master_user_id}: " . $th->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem: ' . $th->getMessage()
            ], 500);
        }
    }

    /**
     * Get all courses from Moodle via REST API
     */
    public function getMoodleCourses(Request $request)
    {
        try {
            $response = $this->moodleRest->request('core_course_get_courses', [
                'options' => [
                    'ids' => []
                ]
            ]);

            if (isset($response['exception'])) {
                return response()->json([
                    'success' => false,
                    'message' => $response['message'] ?? 'Error fetching courses from Moodle'
                ], 400);
            }

            return response()->json([
                'success' => true,
                'data' => $response
            ]);

        } catch (\Throwable $th) {
            Log::error("💥 Error getting Moodle courses: " . $th->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem: ' . $th->getMessage()
            ], 500);
        }
    }

    /**
     * Get quiz information from Moodle
     */
    public function getMoodleQuizzes(Request $request)
    {
        try {
            $courseId = $request->get('course_id');

            $parameters = [];
            if ($courseId) {
                $parameters['courseids'] = [$courseId];
            }

            $response = $this->moodleRest->request('mod_quiz_get_quizzes_by_courses', $parameters);

            if (isset($response['exception'])) {
                return response()->json([
                    'success' => false,
                    'message' => $response['message'] ?? 'Error fetching quizzes from Moodle'
                ], 400);
            }

            return response()->json([
                'success' => true,
                'data' => $response['quizzes'] ?? []
            ]);

        } catch (\Throwable $th) {
            Log::error("💥 Error getting Moodle quizzes: " . $th->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem: ' . $th->getMessage()
            ], 500);
        }
    }

    /**
     * Get Moodle users from REST API
     */
    private function getMoodleUsersFromAPI()
    {
        try {
            $response = $this->moodleRest->request('core_user_get_users', [
                'criteria' => [
                    [
                        'key' => 'all',
                        'value' => true
                    ]
                ]
            ]);

            if (isset($response['exception'])) {
                return [
                    'success' => false,
                    'message' => $response['message'] ?? 'Error fetching users from Moodle'
                ];
            }

            return [
                'success' => true,
                'data' => $response['users'] ?? []
            ];

        } catch (\Throwable $th) {
            Log::error("💥 Error getting Moodle users from API: " . $th->getMessage());
            return [
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil data user dari Moodle'
            ];
        }
    }

    /**
     * Get specific Moodle user info
     */
    private function getMoodleUserInfo($moodleUserId)
    {
        try {
            $response = $this->moodleRest->request('core_user_get_users', [
                'criteria' => [
                    [
                        'key' => 'id',
                        'value' => $moodleUserId
                    ]
                ]
            ]);

            if (isset($response['exception']) || empty($response['users'])) {
                return [];
            }

            return $response['users'][0] ?? [];

        } catch (\Throwable $th) {
            Log::error("💥 Error getting Moodle user info for {$moodleUserId}: " . $th->getMessage());
            return [];
        }
    }

    /**
     * Get quiz attempts from Moodle REST API
     */
    private function getQuizAttemptsFromMoodle($moodleUserId, $vaNumber)
    {
        try {
            $requestData = [
                'quizid' => 1197, // Quiz ID - bisa dijadikan dinamis
                'userid' => $moodleUserId
            ];

            Log::info("📤 Fetching quiz attempts from Moodle for user {$vaNumber}", $requestData);

            $response = $this->moodleRest->request('mod_quiz_get_user_attempts', $requestData);

            if (isset($response['exception'])) {
                $errorMessage = $response['message'] ?? 'Unknown Moodle API error';
                Log::error("❌ Moodle API Error for user {$vaNumber}: {$errorMessage}");
                
                return [
                    'success' => false,
                    'message' => $errorMessage
                ];
            }

            if (isset($response['attempts']) && count($response['attempts']) > 0) {
                $attempt = $response['attempts'][0];
                return [
                    'success' => true,
                    'attempt' => $attempt
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Tidak ada attempt ujian ditemukan'
                ];
            }

        } catch (\Throwable $th) {
            Log::error("💥 Error getting quiz attempts for user {$vaNumber}: " . $th->getMessage());
            return [
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil data quiz'
            ];
        }
    }

    /**
     * Get detailed quiz attempt review
     */
    private function getQuizAttemptReview($attemptId)
    {
        if (!$attemptId) {
            return null;
        }

        try {
            $response = $this->moodleRest->request('mod_quiz_get_attempt_review', [
                'attemptid' => $attemptId
            ]);

            if (isset($response['exception'])) {
                return null;
            }

            return $response;

        } catch (\Throwable $th) {
            Log::error("💥 Error getting quiz attempt review for attempt {$attemptId}: " . $th->getMessage());
            return null;
        }
    }

    /**
     * Sync quiz scores with Moodle for multiple users
     */
    private function syncQuizScoresWithMoodle($users)
    {
        try {
            $results = [];
            
            foreach ($users as $user) {
                if (!$user->nilai && $user->moodle_user_id) {
                    $quizResult = $this->getQuizAttemptsFromMoodle($user->moodle_user_id, $user->va_number);
                    
                    if ($quizResult['success']) {
                        $attempt = $quizResult['attempt'];
                        $score = $attempt['sumgrades'] ?? 0;
                        
                        if ($score > 0) {
                            $this->saveQuizScore($user->ereg_id, $score, $attempt);
                            $results[] = [
                                'va_number' => $user->va_number,
                                'status' => 'synced',
                                'score' => $score
                            ];
                        }
                    }
                }
            }

            Log::info("✅ Sync completed for " . count($results) . " users");
            return $results;

        } catch (\Throwable $th) {
            Log::error("💥 Error syncing quiz scores: " . $th->getMessage());
            return [];
        }
    }

    /**
     * Save quiz score to database
     */
    private function saveQuizScore($id_pendaftaran, $score, $attemptData)
    {
        try {
            $scoreData = [
                'id_pendaftaran' => $id_pendaftaran,
                'nilai' => $score,
                'ket' => 'elearning.ubpkarawang.ac.id',
                'nama_petugas' => 'TIM BOC',
                'attempt_id' => $attemptData['id'] ?? null,
                'attempt_number' => $attemptData['attempt'] ?? null,
                'time_start' => isset($attemptData['timestart']) ? date('Y-m-d H:i:s', $attemptData['timestart']) : null,
                'time_finish' => isset($attemptData['timefinish']) ? date('Y-m-d H:i:s', $attemptData['timefinish']) : null,
                'attempt_data' => json_encode($attemptData),
                'updated_at' => now()
            ];

            Nilai::updateOrCreate(
                ['id_pendaftaran' => $id_pendaftaran],
                $scoreData
            );

            Log::info("✅ Score saved for pendaftaran {$id_pendaftaran}: {$score}");

        } catch (\Throwable $th) {
            Log::error("❌ Error saving quiz score for pendaftaran {$id_pendaftaran}: " . $th->getMessage());
        }
    }

    // ... (method-method lainnya seperti exportQuizScores, getQuizStatistics, dll tetap sama)

    /**
     * Get enrolled users in a specific course
     */
    public function getEnrolledUsers(Request $request)
    {
        try {
            $courseId = $request->get('course_id', 2); // Default course ID

            $response = $this->moodleRest->request('core_enrol_get_enrolled_users', [
                'courseid' => $courseId
            ]);

            if (isset($response['exception'])) {
                return response()->json([
                    'success' => false,
                    'message' => $response['message'] ?? 'Error fetching enrolled users'
                ], 400);
            }

            return response()->json([
                'success' => true,
                'data' => $response
            ]);

        } catch (\Throwable $th) {
            Log::error("💥 Error getting enrolled users: " . $th->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem: ' . $th->getMessage()
            ], 500);
        }
    }

    /**
     * Get site info from Moodle
     */
    public function getMoodleSiteInfo()
    {
        try {
            $response = $this->moodleRest->request('core_webservice_get_site_info');

            if (isset($response['exception'])) {
                return response()->json([
                    'success' => false,
                    'message' => $response['message'] ?? 'Error fetching site info'
                ], 400);
            }

            return response()->json([
                'success' => true,
                'data' => $response
            ]);

        } catch (\Throwable $th) {
            Log::error("💥 Error getting Moodle site info: " . $th->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem: ' . $th->getMessage()
            ], 500);
        }
    }
}