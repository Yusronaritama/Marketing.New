<?php

namespace App\Http\Controllers\Moodle;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MasterMoodle;
use App\Models\MasterUser;
use App\Libraries\MoodleRest;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MoodleSyncController extends Controller
{
    private $moodleToken;
    private $moodleUrl;
    private $courseId = 2;
    private $studentRoleId = 5;

    public function __construct()
    {
        $this->moodleUrl   = 'https://elearning2.ubpkarawang.ac.id/webservice/rest/server.php';
        $this->moodleToken = '6c7943bb4103dc5748e22780b4fe9467';
    }

    private function generateMoodleUsername($masterUser)
    {
        $va = $masterUser->va_number;
        $pureId = substr(preg_replace('/[^0-9]/', '', $va), -4);
        return '2026' . $pureId;
    }

    private function generateMoodlePassword($masterUser)
    {
        return 'Ubp2026*';
    }

    private function generateMoodleEmail($masterUser)
    {
        $username = $this->generateMoodleUsername($masterUser);
        return 'pmb' . $username . '@ubpkarawang.ac.id';
    }

    private function enrolToCourse($moodleUserId, $courseId = null, $roleId = null)
{
    try {
        $courseId = $courseId ?? $this->courseId;
        $roleId = $roleId ?? $this->studentRoleId;
        $moodle = new MoodleRest($this->moodleUrl, $this->moodleToken);

        // Parameter dalam format array of objects (Standard Moodle)
        $params = [
            'enrolments' => [[
                'roleid'   => (int)$roleId,
                'userid'   => (int)$moodleUserId,
                'courseid' => (int)$courseId
            ]]
        ];

        $result = $moodle->request('enrol_manual_enrol_users', $params);

        // Jika Moodle melempar exception di dalam response body
        if (isset($result['exception'])) {
            Log::error("🔐 Moodle API Exception: " . $result['message']);
            // OTOMATIS COBA METHOD ALTERNATIF
            return $this->enrolUsingAlternativeMethod($moodleUserId, $courseId, $roleId);
        }

        return true;
    } catch (\Exception $e) {
        Log::error("❌ Exception Enrol: " . $e->getMessage());
        return $this->enrolUsingAlternativeMethod($moodleUserId, $courseId, $roleId);
    }
}
    
    /**
     * Method alternatif untuk enrol jika manual enrol tidak berhasil
     */
    private function enrolUsingAlternativeMethod($moodleUserId, $courseId, $roleId)
    {
        try {
            Log::info("🔄 Mencoba method alternatif untuk enrol user {$moodleUserId}");
            
            $moodle = new MoodleRest($this->moodleUrl, $this->moodleToken);
            
            // Coba method lain: core_enrol_enrol_user
            $result = $moodle->request('core_enrol_enrol_user', [
                'courseid' => (int)$courseId,
                'userid' => (int)$moodleUserId,
                'roleid' => (int)$roleId,
                'timestart' => time(),
                'timeend' => 0,
                'suspend' => 0
            ]);
            
            Log::info("📥 Alternatif Enrol Response: " . json_encode($result));
            
            if (empty($result) || $result === [] || (is_array($result) && count($result) === 0)) {
                Log::info("✅ Berhasil enrol user {$moodleUserId} menggunakan method alternatif");
                return true;
            }
            
            return false;
            
        } catch (\Exception $e) {
            Log::error("❌ Alternatif enrol juga gagal: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Verifikasi enrol di Moodle
     */
    private function verifyMoodleEnrollment($moodleUserId, $courseId = null)
    {
        try {
            $courseId = $courseId ?? $this->courseId;
            
            $moodle = new MoodleRest($this->moodleUrl, $this->moodleToken);
            
            // Dapatkan kursus user
            $userCourses = $moodle->request('core_enrol_get_users_courses', [
                'userid' => $moodleUserId
            ], MoodleRest::METHOD_GET);
            
            foreach ($userCourses as $course) {
                if ($course['id'] == $courseId) {
                    Log::info("✅ Verifikasi: User {$moodleUserId} terdaftar di course {$courseId}");
                    return true;
                }
            }
            
            Log::warning("⚠️ Verifikasi: User {$moodleUserId} TIDAK terdaftar di course {$courseId}");
            return false;
            
        } catch (\Exception $e) {
            Log::error("❌ Error verifyMoodleEnrollment: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Dapatkan nama tabel moodle yang benar
     */
    private function getMoodleTable()
    {
        try {
            if (Schema::hasTable('master_moodles')) {
                return 'master_moodles';
            } elseif (Schema::hasTable('master_moodle')) {
                return 'master_moodle';
            } else {
                Log::error("❌ Tabel master_moodle/master_moodles tidak ditemukan!");
                return 'master_moodle';
            }
        } catch (\Exception $e) {
            return 'master_moodle';
        }
    }
    
    /**
     * Sinkronisasi massal users yang failed atau baru
     */
    public function syncFailedAndNewUsers()
    {
        try {
            Log::info("🚀 Memulai Sinkronisasi Massal: User Failed & New");

            $tableName = $this->getMoodleTable();
            
            // Ambil data dari tabel yang benar
            $pendingData = DB::table($tableName)
                ->where(function ($query) {
                    $query->where('status', 'failed')
                          ->orWhereNull('status')
                          ->orWhere('enrol_status', 'enrol_failed');
                })
                ->get();

            $summary = [
                'total' => $pendingData->count(),
                'success' => 0,
                'failed' => 0
            ];

            foreach ($pendingData as $moodleEntry) {
                // Cari data master_users berdasarkan master_user_id
                $masterUser = DB::table('master_users')
                    ->where('id', $moodleEntry->master_user_id)
                    ->first();
                
                if ($masterUser) {
                    $result = $this->registerSingleUserToMoodle($masterUser);
                    
                    if ($result['success']) {
                        $summary['success']++;
                    } else {
                        $summary['failed']++;
                    }
                    
                    // Tunggu sedikit antara proses untuk menghindari rate limit
                    usleep(500000); // 0.5 detik
                } else {
                    Log::warning("⚠️ Master user tidak ditemukan untuk moodle entry: " . $moodleEntry->id);
                }
            }

            return response()->json([
                'status' => 'completed',
                'message' => 'Sinkronisasi selesai',
                'summary' => $summary
            ]);

        } catch (\Exception $e) {
            Log::error("💥 Error syncFailedAndNewUsers: " . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Debug enrollment status
     */
    public function debugEnrollment(Request $request)
    {
        try {
            $validated = $request->validate([
                'moodle_user_id' => 'required|integer',
                'course_id' => 'nullable|integer'
            ]);
            
            $moodleUserId = $validated['moodle_user_id'];
            $courseId = $validated['course_id'] ?? $this->courseId;
            
            // 1. Cek di database local
            $tableName = $this->getMoodleTable();
            $localData = DB::table($tableName)
                ->where('moodle_user_id', $moodleUserId)
                ->first();
            
            // 2. Cek di moodle_user_courses
            $courseData = DB::table('moodle_user_courses')
                ->where('moodle_user_id', $moodleUserId)
                ->where('course_id', $courseId)
                ->first();
            
            // 3. Cek langsung ke Moodle
            $moodleCheck = $this->verifyMoodleEnrollment($moodleUserId, $courseId);
            
            // 4. Cek course di Moodle
            $moodle = new MoodleRest($this->moodleUrl, $this->moodleToken);
            $courseInfo = $moodle->request('core_course_get_courses_by_field', [
                'field' => 'id',
                'value' => $courseId
            ], MoodleRest::METHOD_GET);
            
            // 5. Cek user di Moodle
            $userInfo = $moodle->request('core_user_get_users_by_field', [
                'field' => 'id',
                'values' => [$moodleUserId]
            ], MoodleRest::METHOD_GET);
            
            // 6. Cek webservice capabilities
            $siteInfo = $moodle->request('core_webservice_get_site_info', [], MoodleRest::METHOD_GET);
            
            return response()->json([
                'success' => true,
                'debug_info' => [
                    'local_moodle_status' => $localData ? [
                        'status' => $localData->status,
                        'enrol_status' => $localData->enrol_status,
                        'username' => $localData->username,
                        'va_number' => $localData->va_number
                    ] : null,
                    'local_course_status' => $courseData ? [
                        'enrolled' => true,
                        'course_name' => $courseData->fullname,
                        'enrolled_at' => $courseData->created_at
                    ] : null,
                    'moodle_verification' => $moodleCheck,
                    'course_exists' => !empty($courseInfo['courses']),
                    'course_info' => $courseInfo['courses'][0] ?? null,
                    'user_exists_in_moodle' => !empty($userInfo),
                    'user_info' => $userInfo[0] ?? null,
                    'webservice_info' => [
                        'site_name' => $siteInfo['sitename'] ?? 'N/A',
                        'username' => $siteInfo['username'] ?? 'N/A',
                        'userid' => $siteInfo['userid'] ?? 'N/A',
                        'functions' => count($siteInfo['functions'] ?? [])
                    ]
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Debug error: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Test Moodle connection and permissions
     */
    public function testMoodleConnection()
    {
        try {
            $moodle = new MoodleRest($this->moodleUrl, $this->moodleToken);
            
            // 1. Test connection
            $siteInfo = $moodle->request('core_webservice_get_site_info', [], MoodleRest::METHOD_GET);
            
            // 2. Test course access
            $courseInfo = $moodle->request('core_course_get_courses_by_field', [
                'field' => 'id',
                'value' => $this->courseId
            ], MoodleRest::METHOD_GET);
            
            // 3. Test enrol capabilities
            $functions = $moodle->request('core_webservice_get_site_info', [], MoodleRest::METHOD_GET);
            $availableFunctions = $functions['functions'] ?? [];
            
            $enrolFunctions = array_filter($availableFunctions, function($func) {
                return strpos($func['name'], 'enrol') !== false;
            });
            
            // 4. Test token permissions
            $tokenInfo = [
                'has_enrol_manual_enrol_users' => in_array('enrol_manual_enrol_users', array_column($availableFunctions, 'name')),
                'has_core_enrol_enrol_user' => in_array('core_enrol_enrol_user', array_column($availableFunctions, 'name')),
                'total_enrol_functions' => count($enrolFunctions)
            ];
            
            return response()->json([
                'success' => true,
                'connection_test' => [
                    'site_name' => $siteInfo['sitename'] ?? 'N/A',
                    'connected' => !empty($siteInfo),
                    'course_exists' => !empty($courseInfo['courses']),
                    'course_name' => $courseInfo['courses'][0]['fullname'] ?? 'N/A',
                    'token_permissions' => $tokenInfo,
                    'available_enrol_functions' => array_column($enrolFunctions, 'name')
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Connection test failed: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get all failed or unregistered users
     */
    public function getFailedOrUnregisteredUsers(Request $request)
    {
        try {
            $status = $request->get('status', 'all');
            
            $query = MasterMoodle::query();
            
            switch ($status) {
                case 'failed':
                    $query->where('status', 'failed');
                    break;
                case 'pending':
                    $query->where(function($q) {
                        $q->whereNull('status')
                          ->orWhere('status', 'pending');
                    });
                    break;
                case 'no_account':
                    $query->where(function($q) {
                        $q->whereNull('moodle_user_id')
                          ->orWhere('moodle_user_id', '=', 0)
                          ->orWhere('moodle_user_id', '=', '');
                    });
                    break;
                case 'enrol_failed':
                    $query->where('enrol_status', 'enrol_failed')
                          ->where('status', 'success');
                    break;
                default:
                    $query->where(function($q) {
                        $q->where('status', 'failed')
                          ->orWhereNull('status')
                          ->orWhereNull('moodle_user_id')
                          ->orWhere('moodle_user_id', '=', 0)
                          ->orWhere('moodle_user_id', '=', '')
                          ->orWhere('enrol_status', 'enrol_failed');
                    });
            }
            
            if ($request->has('search')) {
                $search = $request->get('search');
                $query->where(function($q) use ($search) {
                    $q->where('va_number', 'LIKE', "%{$search}%")
                      ->orWhere('username', 'LIKE', "%{$search}%")
                      ->orWhere('error_message', 'LIKE', "%{$search}%");
                });
            }
            
            $orderBy = $request->get('order_by', 'last_sync_at');
            $orderDir = $request->get('order_dir', 'desc');
            $query->orderBy($orderBy, $orderDir);
            
            $perPage = $request->get('per_page', 50);
            $users = $query->paginate($perPage);
            
            $stats = [
                'total_failed' => MasterMoodle::where('status', 'failed')->count(),
                'total_pending' => MasterMoodle::whereNull('status')->count(),
                'total_no_account' => MasterMoodle::where(function($q) {
                    $q->whereNull('moodle_user_id')
                      ->orWhere('moodle_user_id', '=', 0)
                      ->orWhere('moodle_user_id', '=', '');
                })->count(),
                'total_enrol_failed' => MasterMoodle::where('enrol_status', 'enrol_failed')->count(),
                'total_success' => MasterMoodle::where('status', 'success')->where('enrol_status', 'enrolled')->count(),
            ];
            
            return response()->json([
                'success' => true,
                'status' => $status,
                'statistics' => $stats,
                'users' => $users
            ]);
            
        } catch (\Exception $e) {
            Log::error("❌ Error getFailedOrUnregisteredUsers: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * MAIN METHOD: Register single user to Moodle
     */
    public function registerSingleUserToMoodle($masterUser)
    {
        $username = null;
        try {
            $moodle = new MoodleRest($this->moodleUrl, $this->moodleToken);
            
            $username = $this->generateMoodleUsername($masterUser);
            $password = $this->generateMoodlePassword($masterUser);
            $email = $this->generateMoodleEmail($masterUser);

            $userName = 'Peserta';
            $userPhone = '089797979899';

            $userDataDb = DB::table('users')->where('id', $masterUser->user_id)->first();
            
            if ($userDataDb) {
                $userName = $userDataDb->name;
                $userPhone = $userDataDb->phone ?? $userPhone;
                
                $biodata = DB::table('biodatas')->where('user_id', $userDataDb->id)->first();
                if ($biodata && !empty($biodata->nama_lengkap)) {
                    $userName = strtoupper(trim($biodata->nama_lengkap));
                }
            } else {
                Log::warning("⚠️ Data User ID {$masterUser->user_id} tidak ditemukan di tabel users.");
            }

            $userData = [
                'users' => [[
                    'username' => $username,
                    'password' => $password,
                    'firstname' => $masterUser->va_number,
                    'lastname' => $userName,
                    'email' => $email,
                    'customfields' => [
                        ['type' => 'nip', 'value' => $masterUser->va_number],
                        ['type' => 'klas', 'value' => 'usm2025'],
                        ['type' => 'nomortelpon', 'value' => $userPhone],
                    ],
                ]]
            ];

            Log::info("📤 Mengirim data ke Moodle untuk {$masterUser->va_number}");

            $response = $moodle->request('core_user_create_users', $userData);

            if (is_array($response) && isset($response[0]['id'])) {
                $moodleUserId = $response[0]['id'];
                
                // PERBAIKAN: Tambahkan delay sebelum enrol
                sleep(1);
                
                $enrolResult = $this->enrolToCourse($moodleUserId);

                MasterMoodle::updateOrCreate(
                    ['master_user_id' => $masterUser->id],
                    [
                        'moodle_user_id' => $moodleUserId,
                        'username' => $username,
                        'status' => 'success',
                        'enrol_status' => $enrolResult ? 'enrolled' : 'enrol_failed',
                        'last_sync_at' => now(),
                        'sync_notes' => 'Registration success via Bulk Sync'
                    ]
                );

                // Verifikasi enrol
                if ($enrolResult) {
                    sleep(2);
                    $verified = $this->verifyMoodleEnrollment($moodleUserId);
                    if (!$verified) {
                        Log::warning("⚠️ User {$moodleUserId} dibuat tapi enrol tidak terverifikasi");
                    }
                }

                return ['success' => true, 'message' => 'Berhasil', 'username' => $username, 'moodle_user_id' => $moodleUserId];
            }
            
            throw new \Exception("Moodle API Error: " . json_encode($response));

        } catch (\Throwable $th) {
            Log::error("💥 Exception Moodle register user {$masterUser->va_number}", [
                'error' => $th->getMessage(),
                'line' => $th->getLine()
            ]);

            MasterMoodle::updateOrCreate(
                ['master_user_id' => $masterUser->id],
                [
                    'status' => 'failed', 
                    'error_message' => substr($th->getMessage(), 0, 255), 
                    'last_sync_at' => now()
                ]
            );
            return ['success' => false, 'message' => $th->getMessage()];
        }
    }
    
    /**
     * Enrol user to course
     */
    public function enrolUserToCourse(Request $request)
    {
        try {
            $validated = $request->validate([
                'moodle_user_id' => 'required|integer',
                'course_id' => 'nullable|integer',
                'role_id' => 'nullable|integer'
            ]);

            $moodleUserId = $validated['moodle_user_id'];
            $courseId = $validated['course_id'] ?? $this->courseId;
            $roleId = $validated['role_id'] ?? $this->studentRoleId;

            Log::info("🎯 Mendaftarkan user {$moodleUserId} ke course {$courseId} dengan role {$roleId}");

            // Cek apakah user sudah terdaftar di course ini
            $existing = DB::table('moodle_user_courses')
                ->where('moodle_user_id', $moodleUserId)
                ->where('course_id', $courseId)
                ->first();

            if ($existing) {
                return response()->json([
                    'success' => true,
                    'message' => 'User sudah terdaftar di course ini',
                    'already_enrolled' => true
                ]);
            }

            $moodle = new MoodleRest($this->moodleUrl, $this->moodleToken);
            
            // 1. Dapatkan data course
            $courseResponse = $moodle->request('core_course_get_courses_by_field', [
                'field' => 'id',
                'value' => $courseId
            ], MoodleRest::METHOD_GET);

            if (empty($courseResponse['courses'])) {
                throw new \Exception("Course dengan ID {$courseId} tidak ditemukan di Moodle");
            }

            $courseData = $courseResponse['courses'][0];

            // 2. Enrol user ke course
            $enrolResult = $this->enrolToCourse($moodleUserId, $courseId, $roleId);
            
            if (!$enrolResult) {
                throw new \Exception("Gagal mendaftarkan user ke course");
            }
            
            // 3. Verifikasi enrol berhasil
            $verified = false;
            $attempts = 0;
            
            do {
                sleep(2); // Tunggu 2 detik untuk sinkronisasi Moodle
                $verified = $this->verifyMoodleEnrollment($moodleUserId, $courseId);
                $attempts++;
            } while (!$verified && $attempts < 3);
            
            if (!$verified) {
                Log::warning("⚠️ Enrol berhasil tapi verifikasi gagal untuk user {$moodleUserId}");
            }
            
            // 4. Simpan ke database local
            DB::table('moodle_user_courses')->insert([
                'moodle_user_id' => $moodleUserId,
                'course_id' => $courseId,
                'shortname' => $courseData['shortname'] ?? '',
                'fullname' => $courseData['fullname'] ?? '',
                'displayname' => $courseData['displayname'] ?? $courseData['fullname'] ?? '',
                'summary' => $courseData['summary'] ?? '',
                'startdate' => isset($courseData['startdate']) ? date('Y-m-d H:i:s', $courseData['startdate']) : null,
                'enddate' => isset($courseData['enddate']) ? date('Y-m-d H:i:s', $courseData['enddate']) : null,
                'visible' => $courseData['visible'] ?? 1,
                'course_data' => json_encode($courseData),
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // Update status enrol di master_moodle
            $tableName = $this->getMoodleTable();
            DB::table($tableName)
                ->where('moodle_user_id', $moodleUserId)
                ->update([
                    'enrol_status' => 'enrolled',
                    'last_sync_at' => now(),
                    'sync_notes' => 'Berhasil enrolled ke course ' . $courseId
                ]);

            Log::info("✅ Berhasil mendaftarkan user {$moodleUserId} ke course {$courseId}");

            return response()->json([
                'success' => true,
                'message' => 'User berhasil didaftarkan ke course',
                'course_id' => $courseId,
                'course_name' => $courseData['fullname'] ?? 'N/A',
                'enrolled_at' => now()->toDateTimeString(),
                'verified' => $verified
            ]);

        } catch (\Exception $e) {
            Log::error("❌ Error enrolUserToCourse: " . $e->getMessage());

            // Update status failed
            if (isset($moodleUserId)) {
                $tableName = $this->getMoodleTable();
                DB::table($tableName)
                    ->where('moodle_user_id', $moodleUserId)
                    ->update([
                        'enrol_status' => 'enrol_failed',
                        'error_message' => substr($e->getMessage(), 0, 255),
                        'last_sync_at' => now()
                    ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Gagal mendaftarkan user: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Bulk enrol users (METHOD YANG HILANG)
     */
    public function bulkEnrolUsers(Request $request)
    {
        try {
            // Validasi dengan konversi boolean yang benar
            $validated = $request->validate([
                'moodle_user_ids' => 'required|array',
                'moodle_user_ids.*' => 'integer',
                'course_id' => 'nullable|integer',
                'role_id' => 'nullable|integer',
                'skip_existing' => 'nullable'
            ]);

            $moodleUserIds = $validated['moodle_user_ids'];
            $courseId = $validated['course_id'] ?? $this->courseId;
            $roleId = $validated['role_id'] ?? $this->studentRoleId;
            
            // Konversi skip_existing ke boolean dengan benar
            $skipExisting = isset($validated['skip_existing']) 
                ? filter_var($validated['skip_existing'], FILTER_VALIDATE_BOOLEAN)
                : true;

            Log::info("🚀 Memulai bulk enrol untuk " . count($moodleUserIds) . " user ke course {$courseId}, skip_existing: " . ($skipExisting ? 'true' : 'false'));

            $results = [];
            $successCount = 0;
            $failedCount = 0;
            $skippedCount = 0;

            foreach ($moodleUserIds as $moodleUserId) {
                try {
                    // Cek apakah sudah terdaftar
                    if ($skipExisting) {
                        $existing = DB::table('moodle_user_courses')
                            ->where('moodle_user_id', $moodleUserId)
                            ->where('course_id', $courseId)
                            ->exists();

                        if ($existing) {
                            $skippedCount++;
                            $results[] = [
                                'moodle_user_id' => $moodleUserId,
                                'success' => true,
                                'message' => 'Sudah terdaftar, dilewati',
                                'skipped' => true
                            ];
                            continue;
                        }
                    }

                    // Panggil method enrol
                    $response = $this->enrolUserToCourse(new Request([
                        'moodle_user_id' => $moodleUserId,
                        'course_id' => $courseId,
                        'role_id' => $roleId
                    ]));

                    $data = json_decode($response->getContent(), true);

                    if ($data['success']) {
                        $successCount++;
                        $results[] = [
                            'moodle_user_id' => $moodleUserId,
                            'success' => true,
                            'message' => $data['message'],
                            'course_name' => $data['course_name'] ?? 'N/A'
                        ];
                    } else {
                        $failedCount++;
                        $results[] = [
                            'moodle_user_id' => $moodleUserId,
                            'success' => false,
                            'message' => $data['message']
                        ];
                    }

                } catch (\Exception $e) {
                    $failedCount++;
                    $results[] = [
                        'moodle_user_id' => $moodleUserId,
                        'success' => false,
                        'message' => 'Exception: ' . $e->getMessage()
                    ];
                    Log::error("❌ Error enrol user {$moodleUserId}: " . $e->getMessage());
                }
                
                // Delay antar request untuk menghindari rate limit
                usleep(200000); // 0.2 detik
            }

            return response()->json([
                'success' => true,
                'message' => 'Bulk enrol process completed',
                'summary' => [
                    'total' => count($moodleUserIds),
                    'success' => $successCount,
                    'failed' => $failedCount,
                    'skipped' => $skippedCount
                ],
                'course_id' => $courseId,
                'skip_existing' => $skipExisting,
                'results' => $results
            ]);

        } catch (\Exception $e) {
            Log::error("💥 Error bulkEnrolUsers: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Enrol all successful users to course
     */
    public function enrolAllSuccessfulUsers(Request $request)
    {
        try {
            $validated = $request->validate([
                'course_id' => 'nullable|integer',
                'role_id' => 'nullable|integer',
                'limit' => 'nullable|integer|min:1|max:1000',
                'skip_existing' => 'nullable',
                'test_mode' => 'nullable|boolean'
            ]);

            $courseId = $validated['course_id'] ?? $this->courseId;
            $roleId = $validated['role_id'] ?? $this->studentRoleId;
            $limit = $validated['limit'] ?? 100;
            $testMode = $validated['test_mode'] ?? false;
            
            $skipExisting = isset($validated['skip_existing']) 
                ? filter_var($validated['skip_existing'], FILTER_VALIDATE_BOOLEAN)
                : true;

            Log::info("🎯 Memulai enrol semua user sukses ke course {$courseId}", [
                'skip_existing' => $skipExisting,
                'test_mode' => $testMode
            ]);

            $tableName = $this->getMoodleTable();
            
            // Ambil user yang statusnya success tapi belum enrolled ke course ini
            $users = DB::table($tableName)
                ->where('status', 'success')
                ->whereNotNull('moodle_user_id')
                ->where(function ($query) use ($courseId, $skipExisting) {
                    if ($skipExisting) {
                        $query->where(function($q) use ($courseId) {
                            $q->whereNull('enrol_status')
                              ->orWhere('enrol_status', '!=', 'enrolled')
                              ->orWhereRaw("NOT EXISTS (
                                  SELECT 1 FROM moodle_user_courses 
                                  WHERE moodle_user_courses.moodle_user_id = {$tableName}.moodle_user_id
                                  AND moodle_user_courses.course_id = ?
                              )", [$courseId]);
                        });
                    } else {
                        $query->where('enrol_status', '!=', 'enrolled');
                    }
                })
                ->limit($limit)
                ->get(['id', 'moodle_user_id', 'va_number', 'username']);

            Log::info("📊 Found {$users->count()} users to enrol");

            if ($users->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Semua user sudah terdaftar di course ini',
                    'total_users' => 0
                ]);
            }

            if ($testMode) {
                return response()->json([
                    'success' => true,
                    'message' => 'Test mode - hanya menampilkan data',
                    'test_mode' => true,
                    'total_users' => $users->count(),
                    'users' => $users->map(function($user) {
                        return [
                            'id' => $user->id,
                            'moodle_user_id' => $user->moodle_user_id,
                            'va_number' => $user->va_number,
                            'username' => $user->username
                        ];
                    })
                ]);
            }

            $moodleUserIds = $users->pluck('moodle_user_id')->toArray();

            // Gunakan bulk enrol method
            return $this->bulkEnrolUsers(new Request([
                'moodle_user_ids' => $moodleUserIds,
                'course_id' => $courseId,
                'role_id' => $roleId,
                'skip_existing' => $skipExisting
            ]));

        } catch (\Exception $e) {
            Log::error("💥 Error enrolAllSuccessfulUsers: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Fix Moodle token permissions issue
     */
    public function fixTokenPermissions()
    {
        try {
            Log::info("🔧 Memperbaiki issue token permissions");
            
            // Test current token
            $testResult = $this->testMoodleConnection();
            $testData = json_decode($testResult->getContent(), true);
            
            if (!$testData['success']) {
                throw new \Exception("Token test failed: " . ($testData['message'] ?? 'Unknown error'));
            }
            
            $tokenInfo = $testData['connection_test']['token_permissions'] ?? [];
            
            $issues = [];
            $solutions = [];
            
            if (!$tokenInfo['has_enrol_manual_enrol_users']) {
                $issues[] = "Token tidak memiliki akses ke 'enrol_manual_enrol_users'";
                $solutions[] = "1. Login ke Moodle sebagai admin";
                $solutions[] = "2. Buka Site administration > Server > Web services > External services";
                $solutions[] = "3. Edit service yang digunakan token ini";
                $solutions[] = "4. Tambahkan fungsi 'enrol_manual_enrol_users' dan 'core_enrol_enrol_user'";
                $solutions[] = "5. Simpan perubahan";
            }
            
            if (!$tokenInfo['has_core_enrol_enrol_user']) {
                $issues[] = "Token tidak memiliki akses ke 'core_enrol_enrol_user'";
                $solutions[] = "Sama seperti di atas, pastikan fungsi ini ditambahkan";
            }
            
            return response()->json([
                'success' => true,
                'token_test' => $testData['connection_test'],
                'issues_found' => $issues,
                'solutions' => $solutions,
                'recommendation' => empty($issues) ? 
                    "Token sudah memiliki semua permissions yang diperlukan" :
                    "Perlu memperbarui permissions token di Moodle admin panel"
            ]);
            
        } catch (\Exception $e) {
            Log::error("❌ Error fixTokenPermissions: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Manual enrol for specific users (workaround)
     */
    public function manualEnrolUsers(Request $request)
    {
        try {
            $validated = $request->validate([
                'user_ids' => 'required|array',
                'user_ids.*' => 'integer',
                'course_id' => 'nullable|integer',
                'role_id' => 'nullable|integer'
            ]);
            
            $userIds = $validated['user_ids'];
            $courseId = $validated['course_id'] ?? $this->courseId;
            $roleId = $validated['role_id'] ?? $this->studentRoleId;
            
            Log::info("🛠️ Manual enrol untuk " . count($userIds) . " user ke course {$courseId}");
            
            $tableName = $this->getMoodleTable();
            $results = [];
            
            foreach ($userIds as $userId) {
                try {
                    $user = DB::table($tableName)->where('id', $userId)->first();
                    
                    if (!$user) {
                        $results[] = [
                            'user_id' => $userId,
                            'success' => false,
                            'message' => 'User tidak ditemukan'
                        ];
                        continue;
                    }
                    
                    if (!$user->moodle_user_id) {
                        $results[] = [
                            'user_id' => $userId,
                            'success' => false,
                            'message' => 'User belum memiliki moodle_user_id'
                        ];
                        continue;
                    }
                    
                    // Coba enrol dengan berbagai method
                    $enrolResult = $this->enrolToCourse($user->moodle_user_id, $courseId, $roleId);
                    
                    if ($enrolResult) {
                        DB::table($tableName)
                            ->where('id', $userId)
                            ->update([
                                'enrol_status' => 'enrolled',
                                'last_sync_at' => now(),
                                'sync_notes' => 'Manual enrol berhasil'
                            ]);
                        
                        $results[] = [
                            'user_id' => $userId,
                            'moodle_user_id' => $user->moodle_user_id,
                            'success' => true,
                            'message' => 'Manual enrol berhasil'
                        ];
                    } else {
                        $results[] = [
                            'user_id' => $userId,
                            'moodle_user_id' => $user->moodle_user_id,
                            'success' => false,
                            'message' => 'Manual enrol gagal'
                        ];
                    }
                    
                } catch (\Exception $e) {
                    $results[] = [
                        'user_id' => $userId,
                        'success' => false,
                        'message' => 'Error: ' . $e->getMessage()
                    ];
                }
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Manual enrol process completed',
                'results' => $results
            ]);
            
        } catch (\Exception $e) {
            Log::error("❌ Error manualEnrolUsers: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Check user enrollment status
     */
    public function checkUserEnrollment(Request $request)
    {
        try {
            $validated = $request->validate([
                'va_number' => 'required|string',
                'course_id' => 'nullable|integer'
            ]);
            
            $vaNumber = $validated['va_number'];
            $courseId = $validated['course_id'] ?? $this->courseId;
            
            $tableName = $this->getMoodleTable();
            
            $user = DB::table($tableName)
                ->where('va_number', $vaNumber)
                ->first();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User dengan VA ' . $vaNumber . ' tidak ditemukan'
                ], 404);
            }
            
            $enrollmentData = DB::table('moodle_user_courses')
                ->where('moodle_user_id', $user->moodle_user_id)
                ->where('course_id', $courseId)
                ->first();
            
            $moodleVerified = $this->verifyMoodleEnrollment($user->moodle_user_id, $courseId);
            
            return response()->json([
                'success' => true,
                'user_info' => [
                    'va_number' => $user->va_number,
                    'username' => $user->username,
                    'moodle_user_id' => $user->moodle_user_id,
                    'status' => $user->status,
                    'enrol_status' => $user->enrol_status
                ],
                'enrollment_status' => [
                    'local_database' => $enrollmentData ? [
                        'enrolled' => true,
                        'course_name' => $enrollmentData->fullname,
                        'enrolled_at' => $enrollmentData->created_at
                    ] : ['enrolled' => false],
                    'moodle_verification' => $moodleVerified
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get statistics
     */
    public function getStatistics()
    {
        try {
            $tableName = $this->getMoodleTable();
            
            $stats = [
                'total_users' => DB::table($tableName)->count(),
                'success_users' => DB::table($tableName)->where('status', 'success')->count(),
                'failed_users' => DB::table($tableName)->where('status', 'failed')->count(),
                'pending_users' => DB::table($tableName)->whereNull('status')->count(),
                'enrolled_users' => DB::table($tableName)->where('enrol_status', 'enrolled')->count(),
                'enrol_failed_users' => DB::table($tableName)->where('enrol_status', 'enrol_failed')->count(),
                'no_moodle_id_users' => DB::table($tableName)->whereNull('moodle_user_id')->orWhere('moodle_user_id', 0)->count(),
                'total_course_enrollments' => DB::table('moodle_user_courses')->count(),
                'unique_enrolled_users' => DB::table('moodle_user_courses')->distinct('moodle_user_id')->count('moodle_user_id')
            ];
            
            return response()->json([
                'success' => true,
                'statistics' => $stats,
                'last_updated' => now()->toDateTimeString()
            ]);
            
        } catch (\Exception $e) {
            Log::error("❌ Error getStatistics: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }
    
    // ============================================
    // METHOD-METHOD LAIN YANG DIPERLUKAN
    // ============================================
    
    public function getPendingUsers()
    {
        $tableName = $this->getMoodleTable();
        return response()->json(DB::table($tableName)->where('status', '!=', 'success')->pluck('id'));
    }
    
    public function syncBatchUsers(Request $request)
    {
        $tableName = $this->getMoodleTable();
        $users = DB::table($tableName)->where('status', '!=', 'success')->get();
        $results = [];
        
        foreach ($users as $user) {
            $results[] = $this->registerSingleUserToMoodle($user);
        }
        
        return response()->json([
            'success' => true,
            'total_processed' => count($users),
            'results' => $results
        ]);
    }
    
    public function retryFailedUsers(Request $request)
    {
        try {
            Log::info("🔄 Memulai retry users failed/unregistered");
            
            $validated = $request->validate([
                'user_ids' => 'nullable|array',
                'user_ids.*' => 'integer',
                'limit' => 'nullable|integer|min:1|max:100',
                'retry_type' => 'nullable|in:failed,no_account,enrol_failed,all',
                'force_recreate' => 'nullable|boolean'
            ]);
            
            // Build query based on retry_type
            $query = MasterMoodle::query();
            
            if (!empty($validated['user_ids'])) {
                $query->whereIn('id', $validated['user_ids']);
                $retryType = 'specific';
            } else {
                $retryType = $validated['retry_type'] ?? 'all';
                
                switch ($retryType) {
                    case 'failed':
                        $query->where('status', 'failed');
                        break;
                    case 'no_account':
                        $query->where(function($q) {
                            $q->whereNull('moodle_user_id')
                              ->orWhere('moodle_user_id', '=', 0)
                              ->orWhere('moodle_user_id', '=', '');
                        });
                        break;
                    case 'enrol_failed':
                        $query->where('enrol_status', 'enrol_failed')
                              ->where('status', 'success');
                        break;
                    default: // all
                        $query->where(function($q) {
                            $q->where('status', 'failed')
                              ->orWhereNull('status')
                              ->orWhereNull('moodle_user_id')
                              ->orWhere('moodle_user_id', '=', 0)
                              ->orWhere('moodle_user_id', '=', '')
                              ->orWhere('enrol_status', 'enrol_failed');
                        });
                }
            }
            
            $limit = $validated['limit'] ?? 50;
            $users = $query->limit($limit)->get();
            
            if ($users->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Tidak ada user yang perlu di-retry',
                    'total_users' => 0,
                    'processed_users' => 0,
                    'results' => []
                ]);
            }
            
            Log::info("📊 Akan meretry {$users->count()} user (type: {$retryType})");
            
            $results = [];
            $successCount = 0;
            $failedCount = 0;
            $forceRecreate = $validated['force_recreate'] ?? false;
            
            foreach ($users as $user) {
                try {
                    // Jika force_recreate, hapus moodle_user_id untuk buat ulang
                    if ($forceRecreate && $user->moodle_user_id) {
                        Log::info("🔄 Force recreate untuk user ID: {$user->id}");
                        $user->moodle_user_id = null;
                        $user->status = null;
                        $user->save();
                    }
                    
                    // Panggil method yang sesuai
                    if ($user->status == 'success' && $user->enrol_status == 'enrol_failed') {
                        // Hanya retry enrol saja
                        $result = $this->retryEnrolUser($user);
                    } else {
                        // Buat user baru atau retry full
                        $result = $this->registerSingleUserToMoodle($user);
                    }
                    
                    if ($result['success']) {
                        $successCount++;
                    } else {
                        $failedCount++;
                    }
                    
                    $results[] = [
                        'user_id' => $user->id,
                        'va_number' => $user->va_number,
                        'previous_status' => $user->status,
                        'previous_enrol_status' => $user->enrol_status,
                        'success' => $result['success'],
                        'message' => $result['message'],
                        'moodle_user_id' => $result['moodle_user_id'] ?? null,
                        'new_username' => $result['username'] ?? null
                    ];
                    
                } catch (\Exception $e) {
                    $failedCount++;
                    Log::error("❌ Error retry user ID {$user->id}: " . $e->getMessage());
                    
                    $results[] = [
                        'user_id' => $user->id,
                        'va_number' => $user->va_number,
                        'success' => false,
                        'message' => 'Exception: ' . $e->getMessage()
                    ];
                }
            }
            
            $response = [
                'success' => true,
                'message' => 'Retry process completed',
                'retry_type' => $retryType,
                'force_recreate' => $forceRecreate,
                'total_users' => $users->count(),
                'success_count' => $successCount,
                'failed_count' => $failedCount,
                'results' => $results
            ];
            
            Log::info("✅ Retry process selesai", [
                'total' => $users->count(),
                'success' => $successCount,
                'failed' => $failedCount
            ]);
            
            return response()->json($response);
            
        } catch (\Exception $e) {
            Log::error("💥 Error retryFailedUsers: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Retry hanya enrol untuk user yang sudah success tapi enrol_failed
     */
    private function retryEnrolUser($masterMoodle)
    {
        try {
            if (!$masterMoodle->moodle_user_id) {
                throw new \Exception('Moodle user ID tidak tersedia');
            }
            
            Log::info("🔄 Retry enrol untuk user ID: {$masterMoodle->moodle_user_id}");
            
            $enrolResult = $this->enrolToCourse($masterMoodle->moodle_user_id);
            
            if ($enrolResult) {
                $masterMoodle->update([
                    'enrol_status' => 'enrolled',
                    'last_sync_at' => now(),
                    'sync_notes' => 'Retry enrol berhasil'
                ]);
                
                return [
                    'success' => true,
                    'message' => 'Enrol berhasil di-retry',
                    'moodle_user_id' => $masterMoodle->moodle_user_id
                ];
            } else {
                $masterMoodle->update([
                    'last_sync_at' => now(),
                    'sync_notes' => 'Retry enrol gagal'
                ]);
                
                return [
                    'success' => false,
                    'message' => 'Gagal enrol user'
                ];
            }
            
        } catch (\Exception $e) {
            Log::error("❌ Error retryEnrolUser: " . $e->getMessage());
            
            $masterMoodle->update([
                'last_sync_at' => now(),
                'sync_notes' => 'Error retry enrol: ' . $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Reset user status untuk dicoba ulang
     */
    public function resetUserStatus(Request $request, $userId)
    {
        try {
            $user = MasterMoodle::find($userId);
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User tidak ditemukan'
                ], 404);
            }
            
            $oldStatus = $user->status;
            $oldEnrolStatus = $user->enrol_status;
            
            $user->update([
                'status' => null,
                'enrol_status' => null,
                'error_message' => null,
                'last_sync_at' => now(),
                'sync_notes' => "Reset dari status: {$oldStatus}, enrol: {$oldEnrolStatus}"
            ]);
            
            Log::info("✅ Reset status user ID: {$userId}", [
                'old_status' => $oldStatus,
                'old_enrol' => $oldEnrolStatus
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Status user berhasil di-reset',
                'user_id' => $userId,
                'old_status' => $oldStatus,
                'old_enrol_status' => $oldEnrolStatus
            ]);
            
        } catch (\Exception $e) {
            Log::error("❌ Error resetUserStatus: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Bulk reset users
     */
    public function bulkResetUsers(Request $request)
    {
        try {
            $validated = $request->validate([
                'user_ids' => 'required|array',
                'user_ids.*' => 'integer',
                'reset_type' => 'nullable|in:status,enrol,both'
            ]);
            
            $resetType = $validated['reset_type'] ?? 'both';
            $resetCount = 0;
            
            foreach ($validated['user_ids'] as $userId) {
                $user = MasterMoodle::find($userId);
                
                if ($user) {
                    $updateData = [
                        'last_sync_at' => now(),
                        'sync_notes' => "Bulk reset - type: {$resetType}"
                    ];
                    
                    if ($resetType == 'status' || $resetType == 'both') {
                        $updateData['status'] = null;
                        $updateData['error_message'] = null;
                    }
                    
                    if ($resetType == 'enrol' || $resetType == 'both') {
                        $updateData['enrol_status'] = null;
                    }
                    
                    $user->update($updateData);
                    $resetCount++;
                }
            }
            
            return response()->json([
                'success' => true,
                'message' => "{$resetCount} user berhasil di-reset",
                'reset_type' => $resetType,
                'reset_count' => $resetCount
            ]);
            
        } catch (\Exception $e) {
            Log::error("❌ Error bulkResetUsers: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Register users to Moodle (existing method)
     */
    public function registerUsersToMoodle(Request $request)
    {
        if (!$request->has('id')) {
            return response()->json(['status' => 'error', 'message' => 'ID tidak ditemukan'], 400);
        }

        $userMoodle = MasterMoodle::find($request->id);
        if (!$userMoodle) {
            return response()->json(['status' => 'error', 'message' => 'User tidak ditemukan'], 404);
        }

        /**
         * ============================
         * 1. FORCE USERNAME → 2026
         * ============================
         */
        $oldUsername  = strtolower(trim($userMoodle->username));
        $onlyNumbers  = preg_replace('/[^0-9]/', '', $oldUsername);
        $pureId       = substr($onlyNumbers, -4);
        $newUsername  = '2026' . $pureId;

        /**
         * ============================
         * 2. NAMA LENGKAP
         * ============================
         */
        $namaLengkap = 'MABA';

        $masterUser = DB::table('master_users')->where('id', $userMoodle->master_user_id)->first();
        if ($masterUser) {
            $biodata = DB::table('biodatas')->where('user_id', $masterUser->user_id)->first();
            if ($biodata && !empty($biodata->nama_lengkap)) {
                $namaLengkap = strtoupper(trim($biodata->nama_lengkap));
            }
        }

        $firstname = $newUsername;
        $lastname  = $namaLengkap;
        $email     = 'pmb' . $newUsername . '@ubpkarawang.ac.id';
        $password  = 'Ubp2026*';

        try {
            $moodle = new MoodleRest($this->moodleUrl, $this->moodleToken);

            /**
             * ============================
             * 3. CARI USER MOODLE
             * ============================
             */
            $search = null;

            if (!empty($userMoodle->moodle_user_id)) {
                $search = $moodle->request(
                    'core_user_get_users_by_field',
                    ['field' => 'id', 'values' => [$userMoodle->moodle_user_id]],
                    MoodleRest::METHOD_GET
                );
            }

            if (empty($search) || !isset($search[0]['id'])) {
                $candidates = [
                    $oldUsername,
                    'pmb' . $oldUsername,
                    $onlyNumbers,
                    'pmb' . $onlyNumbers,
                ];

                foreach ($candidates as $uname) {
                    $search = $moodle->request(
                        'core_user_get_users_by_field',
                        ['field' => 'username', 'values' => [$uname]],
                        MoodleRest::METHOD_GET
                    );
                    if (!empty($search) && isset($search[0]['id'])) {
                        break;
                    }
                }
            }

            if (empty($search) || !isset($search[0]['id'])) {
                throw new \Exception('User Moodle tidak ditemukan');
            }

            $moodleUserId = $search[0]['id'];

            /**
             * ============================
             * 4. UPDATE USER
             * ============================
             */
            $moodle->request(
                'core_user_update_users',
                [
                    'users' => [[
                        'id'        => $moodleUserId,
                        'username'  => $newUsername,
                        'password'  => $password,
                        'firstname' => $firstname,
                        'lastname'  => $lastname,
                        'email'     => $email,
                    ]]
                ],
                MoodleRest::METHOD_POST
            );

            /**
             * ============================
             * 5. ENROL + ROLE STUDENT
             * ============================
             */
            $moodle->request(
                'enrol_manual_enrol_users',
                [
                    'enrolments' => [[
                        'roleid'   => $this->studentRoleId,
                        'userid'   => $moodleUserId,
                        'courseid' => $this->courseId,
                    ]]
                ],
                MoodleRest::METHOD_POST
            );

            /**
             * ============================
             * 6. UPDATE DB LOKAL
             * ============================
             */
            $userMoodle->update([
                'username'       => $newUsername,
                'moodle_user_id' => $moodleUserId,
                'status'         => 'success',
                'enrol_status'   => 'enrolled',
                'last_sync_at'   => now(),
                'sync_notes'     => 'Force 2026 + Enrolled as Student'
            ]);

            return response()->json([
                'status'   => 'success',
                'username' => $newUsername,
                'role'     => 'Student',
                'course'   => $this->courseId
            ]);

        } catch (\Exception $e) {
            $userMoodle->update([
                'status'        => 'failed',
                'error_message' => $e->getMessage(),
                'last_sync_at'  => now()
            ]);

            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
}