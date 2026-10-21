<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class MoodleModel extends Model
{
    protected $moodleRest;
    protected $moodleBaseUrl = 'https://elearning2.ubpkarawang.ac.id';

    public function __construct()
    {
        require_once(app_path('Libraries/MoodleRest.php'));
        $this->moodleRest = new \App\Libraries\MoodleRest(
            'https://elearning2.ubpkarawang.ac.id/webservice/rest/server.php',
            '9d812708c38116dbbe03864ccb860b52',
            'array'
        );
    }

    /**
     * MENDAPATKAN NILAI QUIZ UNTUK USERS DARI MASTER_MOODLE
     */
    public function getQuizNilaiForMasterMoodleUsers($quizId = 1197, $limit = 50)
    {
        try {
            Log::info('Getting quiz nilai for master_moodle users', [
                'quiz_id' => $quizId,
                'limit' => $limit
            ]);

            // 1. Ambil data users dari master_moodle
            $usersResult = $this->getUsersFromMasterMoodle($limit);

            if (!$usersResult['success'] || empty($usersResult['users'])) {
                return [
                    'success' => false,
                    'error' => 'no_users_found',
                    'message' => 'Tidak ditemukan data users dari master_moodle'
                ];
            }

            $results = [];
            $processed = 0;

            foreach ($usersResult['users'] as $user) {
                if ($processed >= $limit) break;

                try {
                    $moodleUserId = $user['moodle_user_id'];

                    // 2. Ambil data pendaftaran untuk informasi tambahan
                    $pendaftaranData = $this->getPendaftaranData($user['master_user_id']);

                    // 3. Ambil quiz attempts untuk user
                    $attemptParams = [
                        'quizid' => (int)$quizId,
                        'userid' => (int)$moodleUserId
                    ];

                    Log::info("Getting quiz attempts for user: {$moodleUserId}", $attemptParams);

                    $attempts = $this->moodleRest->request('mod_quiz_get_user_attempts', $attemptParams);

                    // Handle exception
                    if (isset($attempts['exception'])) {
                        $results[] = [
                            'master_moodle_id' => $user['master_moodle_id'],
                            'master_user_id' => $user['master_user_id'],
                            'moodle_user_id' => $moodleUserId,
                            'username' => $user['username'],
                            'nama' => $pendaftaranData['nama'] ?? 'Tidak diketahui',
                            'email' => $pendaftaranData['email'] ?? 'Tidak diketahui',
                            'prodi_pilihan1' => $pendaftaranData['prodi_pilihan1'] ?? '',
                            'prodi_pilihan2' => $pendaftaranData['prodi_pilihan2'] ?? '',
                            'status' => 'Error',
                            'error' => $attempts['exception'],
                            'nilai' => 0,
                            'attempt_count' => 0,
                            'attempt_details' => [],
                            'kelulusan_info' => null
                        ];
                        $processed++;
                        continue;
                    }

                    // Proses data attempts
                    $sumgrades = 0;
                    $attemptCount = 0;
                    $attemptDetails = [];
                    $latestAttempt = null;

                    if (isset($attempts['attempts']) && is_array($attempts['attempts'])) {
                        $attemptCount = count($attempts['attempts']);

                        foreach ($attempts['attempts'] as $attempt) {
                            $attemptDetail = [
                                'attempt_id' => $attempt['id'],
                                'state' => $attempt['state'],
                                'sumgrades' => $attempt['sumgrades'] ?? 0,
                                'timestart' => $attempt['timestart'],
                                'timefinish' => $attempt['timefinish'] ?? null,
                                'attempt' => $attempt['attempt'] ?? 1
                            ];

                            $attemptDetails[] = $attemptDetail;

                            // Ambil nilai dari attempt terakhir yang selesai
                            if (($attempt['state'] === 'finished' || $attempt['state'] === 'inprogress') && isset($attempt['sumgrades'])) {
                                if ($latestAttempt === null || $attempt['id'] > $latestAttempt['id']) {
                                    $latestAttempt = $attempt;
                                    $sumgrades = $attempt['sumgrades'];
                                }
                            }
                        }

                        // Jika tidak ada attempt yang selesai, ambil attempt terakhir
                        if ($latestAttempt === null && !empty($attempts['attempts'])) {
                            $latestAttempt = end($attempts['attempts']);
                            $sumgrades = $latestAttempt['sumgrades'] ?? 0;
                        }
                    }

                    // Tentukan status kelulusan berdasarkan passing grade
                    $kelulusanInfo = null;
                    $status = 'Tidak ada attempts';

                    if ($attemptCount > 0) {
                        if ($sumgrades > 0) {
                            $kelulusanInfo = $this->determineKelulusan(
                                $sumgrades,
                                $pendaftaranData['prodi_pilihan1'] ?? '',
                                $pendaftaranData['prodi_pilihan2'] ?? ''
                            );
                            $status = $kelulusanInfo['status'];
                        } else {
                            $status = 'Sudah attempt tapi belum ada nilai';
                            $kelulusanInfo = [
                                'status' => 'Belum ada nilai',
                                'nilai' => $sumgrades
                            ];
                        }
                    }

                    $results[] = [
                        'master_moodle_id' => $user['master_moodle_id'],
                        'master_user_id' => $user['master_user_id'],
                        'moodle_user_id' => $moodleUserId,
                        'username' => $user['username'],
                        'nama' => $pendaftaranData['nama'] ?? 'Tidak diketahui',
                        'email' => $pendaftaranData['email'] ?? 'Tidak diketahui',
                        'prodi_pilihan1' => $pendaftaranData['prodi_pilihan1'] ?? '',
                        'prodi_pilihan2' => $pendaftaranData['prodi_pilihan2'] ?? '',
                        'status' => $status,
                        'nilai' => $sumgrades,
                        'attempt_count' => $attemptCount,
                        'attempt_details' => $attemptDetails,
                        'quiz_id' => $quizId,
                        'latest_attempt' => $latestAttempt,
                        'kelulusan_info' => $kelulusanInfo
                    ];

                    $processed++;

                    // Delay kecil untuk menghindari rate limiting
                    usleep(200000); // 0.2 detik

                } catch (\Exception $e) {
                    Log::error("Error processing user {$user['moodle_user_id']}: " . $e->getMessage());

                    $results[] = [
                        'master_moodle_id' => $user['master_moodle_id'],
                        'master_user_id' => $user['master_user_id'],
                        'moodle_user_id' => $user['moodle_user_id'],
                        'username' => $user['username'],
                        'nama' => 'Error',
                        'email' => 'Error',
                        'status' => 'Error',
                        'error' => $e->getMessage(),
                        'nilai' => 0,
                        'attempt_count' => 0,
                        'attempt_details' => [],
                        'kelulusan_info' => null
                    ];
                    $processed++;
                }
            }

            // Generate summary
            $summary = $this->generateNilaiSummary($results);

            return [
                'success' => true,
                'quiz_id' => $quizId,
                'total_users' => count($results),
                'users' => $results,
                'summary' => $summary
            ];
        } catch (\Exception $e) {
            Log::error('MoodleModel getQuizNilaiForMasterMoodleUsers Error: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'Terjadi kesalahan saat mengambil data nilai quiz'
            ];
        }
    }

    /**
     * GENERATE SUMMARY UNTUK NILAI QUIZ
     */
    private function generateNilaiSummary($users)
    {
        $summary = [
            'total_users' => count($users),
            'total_nilai' => 0,
            'total_attempts' => 0,
            'lulus' => 0,
            'tidak_lulus' => 0,
            'belum_attempt' => 0,
            'error' => 0,
            'sudah_attempt_tanpa_nilai' => 0,
            'rata_rata_nilai' => 0,
            'nilai_tertinggi' => 0,
            'nilai_terendah' => 100,
            'users_with_nilai' => 0
        ];

        $totalNilai = 0;
        $usersWithNilai = 0;
        $nilaiArray = [];

        foreach ($users as $user) {
            $summary['total_attempts'] += $user['attempt_count'];

            if ($user['status'] === 'Error') {
                $summary['error']++;
            } else if ($user['attempt_count'] === 0) {
                $summary['belum_attempt']++;
            } else if (strpos($user['status'], 'Lulus') !== false) {
                $summary['lulus']++;
                if ($user['nilai'] > 0) {
                    $totalNilai += $user['nilai'];
                    $usersWithNilai++;
                    $nilaiArray[] = $user['nilai'];

                    // Update nilai tertinggi dan terendah
                    if ($user['nilai'] > $summary['nilai_tertinggi']) {
                        $summary['nilai_tertinggi'] = $user['nilai'];
                    }
                    if ($user['nilai'] < $summary['nilai_terendah']) {
                        $summary['nilai_terendah'] = $user['nilai'];
                    }
                }
            } else if ($user['status'] === 'Tidak Lulus') {
                $summary['tidak_lulus']++;
                if ($user['nilai'] > 0) {
                    $totalNilai += $user['nilai'];
                    $usersWithNilai++;
                    $nilaiArray[] = $user['nilai'];

                    // Update nilai tertinggi dan terendah
                    if ($user['nilai'] > $summary['nilai_tertinggi']) {
                        $summary['nilai_tertinggi'] = $user['nilai'];
                    }
                    if ($user['nilai'] < $summary['nilai_terendah']) {
                        $summary['nilai_terendah'] = $user['nilai'];
                    }
                }
            } else if ($user['status'] === 'Sudah attempt tapi belum ada nilai') {
                $summary['sudah_attempt_tanpa_nilai']++;
            }
        }

        $summary['total_nilai'] = $totalNilai;
        $summary['users_with_nilai'] = $usersWithNilai;
        $summary['rata_rata_nilai'] = $usersWithNilai > 0 ? round($totalNilai / $usersWithNilai, 2) : 0;

        // Jika tidak ada nilai, set nilai terendah ke 0
        if ($summary['nilai_terendah'] === 100) {
            $summary['nilai_terendah'] = 0;
        }

        return $summary;
    }

    /**
     * MENDAPATKAN DATA USER DARI MASTER_MOODLE
     */
    public function getUsersFromMasterMoodle($limit = 50, $status = 'success')
    {
        try {
            Log::info('Getting users from master_moodle table', [
                'limit' => $limit,
                'status' => $status
            ]);

            $users = DB::table('master_moodle')
                ->where('status', $status)
                ->where('enrol_status', 'enrolled')
                ->whereNotNull('moodle_user_id')
                ->where('moodle_user_id', '!=', '')
                ->select(
                    'id',
                    'master_user_id',
                    'moodle_user_id',
                    'username',
                    'status',
                    'enrol_status',
                    'error_message',
                    'created_at',
                    'updated_at'
                )
                ->limit($limit)
                ->get();

            $formattedUsers = [];
            foreach ($users as $user) {
                $formattedUsers[] = [
                    'master_moodle_id' => $user->id,
                    'master_user_id' => $user->master_user_id,
                    'moodle_user_id' => $user->moodle_user_id,
                    'username' => $user->username,
                    'status' => $user->status,
                    'enrol_status' => $user->enrol_status,
                    'error_message' => $user->error_message,
                    'created_at' => $user->created_at,
                    'updated_at' => $user->updated_at
                ];
            }

            Log::info('Found users from master_moodle', ['count' => count($formattedUsers)]);

            return [
                'success' => true,
                'total_users' => count($formattedUsers),
                'users' => $formattedUsers
            ];
        } catch (\Exception $e) {
            Log::error('Error getting users from master_moodle: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'Gagal mengambil data dari master_moodle'
            ];
        }
    }

    /**
     * MENDAPATKAN QUIZ ATTEMPTS UNTUK USER DARI MASTER_MOODLE
     */
    public function getQuizAttemptsForMasterMoodleUsers($quizId = 1197, $limit = 50)
    {
        try {
            Log::info('Getting quiz attempts for master_moodle users', [
                'quiz_id' => $quizId,
                'limit' => $limit
            ]);

            // 1. Ambil data users dari master_moodle
            $usersResult = $this->getUsersFromMasterMoodle($limit);

            if (!$usersResult['success'] || empty($usersResult['users'])) {
                return [
                    'success' => false,
                    'error' => 'no_users_found',
                    'message' => 'Tidak ditemukan data users dari master_moodle'
                ];
            }

            $results = [];
            $processed = 0;

            foreach ($usersResult['users'] as $user) {
                if ($processed >= $limit) break;

                try {
                    $moodleUserId = $user['moodle_user_id'];

                    // 2. Ambil data pendaftaran untuk informasi tambahan
                    $pendaftaranData = $this->getPendaftaranData($user['master_user_id']);

                    // 3. Ambil quiz attempts untuk user
                    $attemptParams = [
                        'quizid' => (int)$quizId,
                        'userid' => (int)$moodleUserId
                    ];

                    $attempts = $this->moodleRest->request('mod_quiz_get_user_attempts', $attemptParams);

                    // Handle exception
                    if (isset($attempts['exception'])) {
                        $results[] = [
                            'master_moodle_id' => $user['master_moodle_id'],
                            'master_user_id' => $user['master_user_id'],
                            'moodle_user_id' => $moodleUserId,
                            'username' => $user['username'],
                            'nama' => $pendaftaranData['nama'] ?? 'Tidak diketahui',
                            'email' => $pendaftaranData['email'] ?? 'Tidak diketahui',
                            'prodi_pilihan1' => $pendaftaranData['prodi_pilihan1'] ?? '',
                            'prodi_pilihan2' => $pendaftaranData['prodi_pilihan2'] ?? '',
                            'status' => 'Error',
                            'error' => $attempts['exception'],
                            'nilai' => 0,
                            'attempt_count' => 0
                        ];
                        $processed++;
                        continue;
                    }

                    // Proses data attempts
                    $sumgrades = 0;
                    $attemptCount = 0;
                    $attemptDetails = [];

                    if (isset($attempts['attempts']) && is_array($attempts['attempts'])) {
                        $attemptCount = count($attempts['attempts']);

                        foreach ($attempts['attempts'] as $attempt) {
                            $attemptDetails[] = [
                                'attempt_id' => $attempt['id'],
                                'state' => $attempt['state'],
                                'sumgrades' => $attempt['sumgrades'] ?? 0,
                                'timestart' => $attempt['timestart'],
                                'timefinish' => $attempt['timefinish'] ?? null
                            ];

                            if (isset($attempt['sumgrades'])) {
                                $sumgrades = $attempt['sumgrades'];
                            }
                        }
                    }

                    // Tentukan status
                    $status = 'Tidak ada attempts';
                    if ($attemptCount > 0) {
                        if ($sumgrades > 0) {
                            $status = 'Lulus'; // Versi sederhana
                        } else {
                            $status = 'Sudah attempt tapi belum ada nilai';
                        }
                    }

                    $results[] = [
                        'master_moodle_id' => $user['master_moodle_id'],
                        'master_user_id' => $user['master_user_id'],
                        'moodle_user_id' => $moodleUserId,
                        'username' => $user['username'],
                        'nama' => $pendaftaranData['nama'] ?? 'Tidak diketahui',
                        'email' => $pendaftaranData['email'] ?? 'Tidak diketahui',
                        'prodi_pilihan1' => $pendaftaranData['prodi_pilihan1'] ?? '',
                        'prodi_pilihan2' => $pendaftaranData['prodi_pilihan2'] ?? '',
                        'status' => $status,
                        'nilai' => $sumgrades,
                        'attempt_count' => $attemptCount,
                        'attempt_details' => $attemptDetails,
                        'quiz_id' => $quizId
                    ];

                    $processed++;
                } catch (\Exception $e) {
                    Log::error("Error processing user {$user['moodle_user_id']}: " . $e->getMessage());

                    $results[] = [
                        'master_moodle_id' => $user['master_moodle_id'],
                        'master_user_id' => $user['master_user_id'],
                        'moodle_user_id' => $user['moodle_user_id'],
                        'username' => $user['username'],
                        'status' => 'Error',
                        'error' => $e->getMessage(),
                        'nilai' => 0,
                        'attempt_count' => 0
                    ];
                    $processed++;
                }
            }

            return [
                'success' => true,
                'quiz_id' => $quizId,
                'total_users' => count($results),
                'users' => $results,
                'summary' => $this->generateSummary($results)
            ];
        } catch (\Exception $e) {
            Log::error('MoodleModel getQuizAttemptsForMasterMoodleUsers Error: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'Terjadi kesalahan saat mengambil data quiz attempts'
            ];
        }
    }
 public function getMoodleRest()
    {
        if (!$this->moodleRest) {
            try {
                require_once(app_path('Libraries/MoodleRest.php'));
                $this->moodleRest = new \App\Libraries\MoodleRest(
                    'https://elearning2.ubpkarawang.ac.id/webservice/rest/server.php',
                    '9d812708c38116dbbe03864ccb860b52',
                    'array'
                );
            } catch (\Exception $e) {
                Log::error('Error re-initializing MoodleRest: ' . $e->getMessage());
                return null;
            }
        }
    }   
    private function getPendaftaranData($masterMoodleUserId)
    {
        try {
            // 1. Ambil user_id dari master_users berdasarkan master_moodle.master_user_id
            $masterUser = DB::table('master_users')
                ->where('id', $masterMoodleUserId)
                ->select('user_id')
                ->first();

            if (!$masterUser || !$masterUser->user_id) {
                return [
                    'nama' => 'Tidak diketahui',
                    'email' => 'Tidak diketahui',
                    'prodi_pilihan1' => '',
                    'prodi_pilihan2' => '',
                    'data_source' => 'no_master_user'
                ];
            }

            $userId = $masterUser->user_id;

            // 2. Ambil data user dari tabel users
            $user = DB::table('users')
                ->where('id', $userId)
                ->select('name as nama', 'email')
                ->first();

            if (!$user) {
                return [
                    'nama' => 'Tidak diketahui',
                    'email' => 'Tidak diketahui',
                    'prodi_pilihan1' => '',
                    'prodi_pilihan2' => '',
                    'data_source' => 'no_user'
                ];
            }

            $nama = $user->nama;
            $email = $user->email;

            // 3. Ambil data prodi dari master_ereg
            $masterEreg = DB::table('master_ereg')
                ->where('user_id', $userId)
                ->select('jurusan1 as prodi_pilihan1', 'jurusan2 as prodi_pilihan2')
                ->first();

            $prodiPilihan1 = '';
            $prodiPilihan2 = '';
            $dataSource = 'master_ereg';

            if ($masterEreg) {
                $prodiPilihan1 = $masterEreg->prodi_pilihan1 ?? '';
                $prodiPilihan2 = $masterEreg->prodi_pilihan2 ?? '';
            } else {
                $dataSource = 'fallback';
                // Tambahkan log untuk debugging
                Log::warning('Data master_ereg tidak ditemukan untuk user_id: ' . $userId, [
                    'master_moodle_user_id' => $masterMoodleUserId,
                    'user_id' => $userId
                ]);
            }

            return [
                'nama' => $nama,
                'email' => $email,
                'prodi_pilihan1' => $prodiPilihan1,
                'prodi_pilihan2' => $prodiPilihan2,
                'user_id' => $userId,
                'data_source' => $dataSource
            ];
        } catch (\Exception $e) {
            Log::error('Error getting pendaftaran data for master_user_id ' . $masterMoodleUserId . ': ' . $e->getMessage());
            return [
                'nama' => 'Tidak diketahui',
                'email' => 'Tidak diketahui',
                'prodi_pilihan1' => '',
                'prodi_pilihan2' => '',
                'data_source' => 'error'
            ];
        }
    }

    /**
     * METHOD: Mengambil data user dari URL profile Moodle
     */
    public function getUserFromProfileUrl($userId, $courseId = 384)
    {
        try {
            $url = "{$this->moodleBaseUrl}/user/view.php?id={$userId}&course={$courseId}";

            Log::info('Fetching user from profile URL', ['url' => $url]);

            // Gunakan HTTP client untuk mengambil data
            $response = Http::timeout(30)->get($url);

            if (!$response->successful()) {
                throw new \Exception("HTTP request failed with status: " . $response->status());
            }

            $htmlContent = $response->body();

            // Parse data user dari HTML
            $userData = $this->parseUserFromHtml($htmlContent, $userId);

            return [
                'success' => true,
                'user' => $userData,
                'profile_url' => $url
            ];
        } catch (\Exception $e) {
            Log::error('Error fetching user from profile URL: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'Gagal mengambil data user dari profile URL'
            ];
        }
    }

    /**
     * METHOD: Parse data user dari HTML profile
     */
    private function parseUserFromHtml($html, $userId)
    {
        $userData = [
            'user_id' => $userId,
            'fullname' => '',
            'email' => '',
            'department' => '',
            'roles' => [],
            'enrolled_courses' => []
        ];

        try {
            // Gunakan DOMDocument untuk parsing HTML
            $dom = new \DOMDocument();
            @$dom->loadHTML($html);
            $xpath = new \DOMXPath($dom);

            // Ambil fullname dari tag h2
            $fullnameNodes = $xpath->query('//h2');
            if ($fullnameNodes->length > 0) {
                $userData['fullname'] = trim($fullnameNodes->item(0)->textContent);
            }

            // Ambil email dari elemen dengan class atau teks tertentu
            $emailPattern = '/[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}/';
            preg_match($emailPattern, $html, $emailMatches);
            if (!empty($emailMatches)) {
                $userData['email'] = $emailMatches[0];
            }

            // Cari informasi lainnya berdasarkan pola HTML Moodle
            $tableRows = $xpath->query('//table//tr');
            foreach ($tableRows as $row) {
                $cells = $xpath->query('./td', $row);
                if ($cells->length >= 2) {
                    $label = trim($cells->item(0)->textContent);
                    $value = trim($cells->item(1)->textContent);

                    if (strpos($label, 'Email') !== false) {
                        $userData['email'] = $value;
                    } elseif (strpos($label, 'Department') !== false) {
                        $userData['department'] = $value;
                    }
                }
            }

            return $userData;
        } catch (\Exception $e) {
            Log::error('Error parsing user HTML: ' . $e->getMessage());
            return $userData;
        }
    }

    /**
     * MENDAPATKAN USERS BERDASARKAN EMAIL DARI COURSE
     */
    public function getUsersByEmailFromCourse($courseId = 384, $emails = [])
    {
        try {
            Log::info('Getting users by email from course', [
                'course_id' => $courseId,
                'email_count' => count($emails)
            ]);

            // 1. Coba ambil melalui Web Service terlebih dahulu
            $enrolledUsers = $this->moodleRest->request('core_enrol_get_enrolled_users', [
                'courseid' => (int)$courseId
            ]);

            if (isset($enrolledUsers['exception'])) {
                Log::warning('Web service failed, trying alternative method', [
                    'error' => $enrolledUsers['exception']
                ]);

                // Fallback: Coba ambil melalui database master_moodle
                return $this->getUsersByEmailFromMasterMoodle($emails);
            }

            // 2. Filter users berdasarkan email
            $filteredUsers = [];
            $foundEmails = [];
            $notFoundEmails = [];

            foreach ($emails as $email) {
                $found = false;
                foreach ($enrolledUsers as $user) {
                    if (isset($user['email']) && strtolower($user['email']) === strtolower($email)) {
                        $filteredUsers[] = [
                            'user_id' => $user['id'],
                            'username' => $user['username'] ?? '',
                            'email' => $user['email'],
                            'fullname' => $user['fullname'] ?? '',
                            'firstname' => $user['firstname'] ?? '',
                            'lastname' => $user['lastname'] ?? '',
                            'roles' => $this->extractUserRoles($user)
                        ];
                        $foundEmails[] = $email;
                        $found = true;
                        break;
                    }
                }

                if (!$found) {
                    $notFoundEmails[] = $email;
                }
            }

            Log::info('Users filtered by email', [
                'total_enrolled' => count($enrolledUsers),
                'found_count' => count($filteredUsers),
                'not_found_count' => count($notFoundEmails)
            ]);

            return [
                'success' => true,
                'course_id' => $courseId,
                'total_enrolled_users' => count($enrolledUsers),
                'found_users' => $filteredUsers,
                'found_count' => count($filteredUsers),
                'not_found_emails' => $notFoundEmails,
                'not_found_count' => count($notFoundEmails)
            ];
        } catch (\Exception $e) {
            Log::error('Error getting users by email from course: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'Terjadi kesalahan saat mengambil data users dari course'
            ];
        }
    }

    /**
     * METHOD: Ambil users dari master_moodle berdasarkan email
     */
    private function getUsersByEmailFromMasterMoodle($emails)
    {
        try {
            Log::info('Getting users by email from master_moodle');

            $foundUsers = [];
            $notFoundEmails = [];

            foreach ($emails as $email) {
                // Cari di tabel users berdasarkan email
                $user = DB::table('users')
                    ->where('email', $email)
                    ->select('id', 'name as fullname')
                    ->first();

                if ($user) {
                    // Cari di master_users berdasarkan user_id
                    $masterUser = DB::table('master_users')
                        ->where('user_id', $user->id)
                        ->select('id')
                        ->first();

                    if ($masterUser) {
                        // Cari di master_moodle berdasarkan master_user_id
                        $moodleUser = DB::table('master_moodle')
                            ->where('master_user_id', $masterUser->id)
                            ->where('status', 'success')
                            ->where('enrol_status', 'enrolled')
                            ->select(
                                'moodle_user_id as user_id',
                                'username'
                            )
                            ->first();

                        if ($moodleUser) {
                            $foundUsers[] = [
                                'user_id' => $moodleUser->user_id,
                                'username' => $moodleUser->username,
                                'email' => $email,
                                'fullname' => $user->fullname,
                                'source' => 'master_users'
                            ];
                        } else {
                            $notFoundEmails[] = $email;
                        }
                    } else {
                        $notFoundEmails[] = $email;
                    }
                } else {
                    $notFoundEmails[] = $email;
                }
            }

            return [
                'success' => true,
                'found_users' => $foundUsers,
                'found_count' => count($foundUsers),
                'not_found_emails' => $notFoundEmails,
                'not_found_count' => count($notFoundEmails),
                'method' => 'master_users'
            ];
        } catch (\Exception $e) {
            Log::error('Error getting users by email from master_users: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    /**
     * MENDAPATKAN PASSING GRADE PROGRAM STUDI
     */
    private function getPassingGrade($kodeProdi)
    {
        try {
            $prodi = DB::table('program_studi')
                ->where('kode_prodi', $kodeProdi)
                ->select('passing_grade')
                ->first();

            if ($prodi && $prodi->passing_grade) {
                return (float)$prodi->passing_grade;
            }

            // Default passing grade jika tidak ditemukan
            return $this->getDefaultPassingGrade($kodeProdi);
        } catch (\Exception $e) {
            Log::error('Error getting passing grade for prodi ' . $kodeProdi . ': ' . $e->getMessage());
            return $this->getDefaultPassingGrade($kodeProdi);
        }
    }

    /**
     * DEFAULT PASSING GRADE BERDASARKAN KODE PRODI
     */
    private function getDefaultPassingGrade($kodeProdi)
    {
        $defaultPassingGrades = [
            '21201' => 17, // Teknik Mesin
            '26201' => 17, // Teknik Elektro
            '48201' => 1,  // Khusus 48201 nilai minimum sangat rendah
            '55201' => 17,
            '57201' => 17,
            '61201' => 20,
            '62201' => 17,
            '73201' => 20,
            '74201' => 17,
            '86206' => 17,
            '87205' => 5,
            '86230' => 5,
        ];

        return $defaultPassingGrades[$kodeProdi] ?? 17; // Default 17 jika tidak ada
    }

    /**
     * TENTUKAN STATUS KELULUSAN BERDASARKAN PASSING GRADE
     */
    private function determineKelulusan($nilai, $prodiPilihan1, $prodiPilihan2)
    {
        // Ambil passing grade dari database
        $passingGradePilihan1 = $this->getPassingGrade($prodiPilihan1);
        $passingGradePilihan2 = $this->getPassingGrade($prodiPilihan2);

        // Cek pilihan 1
        if ($nilai >= $passingGradePilihan1) {
            return [
                'status' => 'Lulus - Pilihan 1',
                'prodi_lulus' => $prodiPilihan1,
                'passing_grade' => $passingGradePilihan1,
                'selisih' => $nilai - $passingGradePilihan1
            ];
        }
        // Cek pilihan 2
        else if ($nilai >= $passingGradePilihan2) {
            return [
                'status' => 'Lulus - Pilihan 2',
                'prodi_lulus' => $prodiPilihan2,
                'passing_grade' => $passingGradePilihan2,
                'selisih' => $nilai - $passingGradePilihan2
            ];
        }
        // Tidak lulus
        else {
            $selisih1 = $passingGradePilihan1 - $nilai;
            $selisih2 = $passingGradePilihan2 - $nilai;

            return [
                'status' => 'Tidak Lulus',
                'prodi_pilihan1' => $prodiPilihan1,
                'prodi_pilihan2' => $prodiPilihan2,
                'passing_grade_p1' => $passingGradePilihan1,
                'passing_grade_p2' => $passingGradePilihan2,
                'selisih_p1' => $selisih1,
                'selisih_p2' => $selisih2,
                'keterangan' => "Butuh {$selisih1} point untuk P1, {$selisih2} point untuk P2"
            ];
        }
    }

    /**
     * GENERATE SUMMARY STATISTIK
     */
    private function generateSummary($users)
    {
        $summary = [
            'total_users' => count($users),
            'total_nilai' => 0,
            'total_attempts' => 0,
            'lulus' => 0,
            'tidak_lulus' => 0,
            'belum_attempt' => 0,
            'error' => 0,
            'rata_rata_nilai' => 0
        ];

        $totalNilai = 0;
        $usersWithNilai = 0;

        foreach ($users as $user) {
            $summary['total_attempts'] += $user['attempt_count'];

            if ($user['status'] === 'Error') {
                $summary['error']++;
            } else if ($user['attempt_count'] === 0) {
                $summary['belum_attempt']++;
            } else if (strpos($user['status'], 'Lulus') !== false) {
                $summary['lulus']++;
                $totalNilai += $user['nilai'];
                $usersWithNilai++;
            } else if ($user['status'] === 'Tidak Lulus') {
                $summary['tidak_lulus']++;
                $totalNilai += $user['nilai'];
                $usersWithNilai++;
            } else if ($user['status'] === 'Sudah attempt tapi belum ada nilai') {
                // Tidak dihitung dalam rata-rata
            }
        }

        $summary['total_nilai'] = $totalNilai;
        $summary['rata_rata_nilai'] = $usersWithNilai > 0 ? round($totalNilai / $usersWithNilai, 2) : 0;

        return $summary;
    }

    /**
     * GET QUIZ INFORMATION
     */
    public function getQuizInfo($quizId = 1197)
    {
        try {
            // Coba ambil informasi quiz
            $quizzes = $this->moodleRest->request('mod_quiz_get_quizzes_by_courses', [
                'courseids' => [384] // Course ID sesuai contoh
            ]);

            if (isset($quizzes['exception'])) {
                return [
                    'success' => false,
                    'error' => $quizzes['exception']
                ];
            }

            $quizInfo = null;
            if (isset($quizzes['quizzes']) && is_array($quizzes['quizzes'])) {
                foreach ($quizzes['quizzes'] as $quiz) {
                    if ($quiz['id'] == $quizId) {
                        $quizInfo = $quiz;
                        break;
                    }
                }
            }

            return [
                'success' => true,
                'quiz_id' => $quizId,
                'quiz_info' => $quizInfo,
                'message' => $quizInfo ? 'Quiz ditemukan' : 'Quiz tidak ditemukan'
            ];
        } catch (\Exception $e) {
            Log::error('Error getting quiz info: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * MENDAPATKAN QUIZ ATTEMPTS BERDASARKAN EMAIL
     */
    public function getQuizAttemptsForUsersByEmail($courseId = 384, $quizId = 1197, $emails = [], $limit = 50)
    {
        try {
            // 1. Dapatkan user IDs berdasarkan email
            $usersResult = $this->getUsersByEmailFromCourse($courseId, $emails);

            if (!$usersResult['success']) {
                return [
                    'success' => false,
                    'error' => $usersResult['error'],
                    'message' => 'Gagal mendapatkan data users'
                ];
            }

            $results = [];
            $processed = 0;

            // 2. Ambil quiz attempts untuk setiap user yang ditemukan
            foreach ($usersResult['found_users'] as $user) {
                if ($processed >= $limit) break;

                try {
                    $attemptParams = [
                        'quizid' => (int)$quizId,
                        'userid' => (int)$user['user_id']
                    ];

                    $attempts = $this->moodleRest->request('mod_quiz_get_user_attempts', $attemptParams);

                    // Proses data attempts
                    $sumgrades = 0;
                    $attemptCount = 0;
                    $attemptDetails = [];

                    if (isset($attempts['attempts']) && is_array($attempts['attempts'])) {
                        $attemptCount = count($attempts['attempts']);

                        foreach ($attempts['attempts'] as $attempt) {
                            $attemptDetails[] = [
                                'attempt_id' => $attempt['id'],
                                'state' => $attempt['state'],
                                'sumgrades' => $attempt['sumgrades'] ?? 0,
                                'timestart' => $attempt['timestart'],
                                'timefinish' => $attempt['timefinish'] ?? null
                            ];

                            if (isset($attempt['sumgrades'])) {
                                $sumgrades = $attempt['sumgrades'];
                            }
                        }
                    }

                    $results[] = array_merge($user, [
                        'status' => $attemptCount > 0 ? ($sumgrades > 0 ? 'Lulus' : 'Sudah attempt') : 'Tidak ada attempts',
                        'nilai' => $sumgrades,
                        'attempt_count' => $attemptCount,
                        'attempt_details' => $attemptDetails
                    ]);

                    $processed++;
                } catch (\Exception $e) {
                    Log::error("Error processing user {$user['user_id']}: " . $e->getMessage());

                    $results[] = array_merge($user, [
                        'status' => 'Error',
                        'error' => $e->getMessage(),
                        'nilai' => 0,
                        'attempt_count' => 0
                    ]);
                    $processed++;
                }
            }

            return [
                'success' => true,
                'course_id' => $courseId,
                'quiz_id' => $quizId,
                'users' => $results,
                'not_found_emails' => $usersResult['not_found_emails'],
                'summary' => $this->generateSummary($results)
            ];
        } catch (\Exception $e) {
            Log::error('Error getting quiz attempts for users by email: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'Terjadi kesalahan saat mengambil data quiz attempts'
            ];
        }
    }

    /**
     * METHOD: Ambil multiple users dari profile URLs
     */
    public function getUsersFromProfileUrls($userIds = [], $courseId = 384)
    {
        try {
            $results = [];

            foreach ($userIds as $userId) {
                $userResult = $this->getUserFromProfileUrl($userId, $courseId);
                $results[] = [
                    'user_id' => $userId,
                    'success' => $userResult['success'],
                    'data' => $userResult['success'] ? $userResult['user'] : null,
                    'error' => $userResult['success'] ? null : $userResult['error']
                ];
            }

            return [
                'success' => true,
                'total_users' => count($results),
                'users' => $results
            ];
        } catch (\Exception $e) {
            Log::error('Error getting users from profile URLs: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Helper method untuk extract user roles
     */
    private function extractUserRoles($user)
    {
        $roles = [];
        if (isset($user['roles']) && is_array($user['roles'])) {
            foreach ($user['roles'] as $role) {
                $roles[] = [
                    'role_id' => $role['roleid'] ?? '',
                    'role_name' => $role['name'] ?? '',
                    'role_shortname' => $role['shortname'] ?? ''
                ];
            }
        }
        return $roles;
    }
}
