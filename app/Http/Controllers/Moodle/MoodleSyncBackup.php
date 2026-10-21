<?php

namespace App\Http\Controllers\Moodle;

use App\Http\Controllers\Controller;
use App\Models\MasterUser;
use App\Models\MasterMoodle;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use App\Libraries\MoodleRest;

class MoodleSyncController extends Controller
{
    private $moodleToken;
    private $moodleUrl;

    public function __construct()
    {
        $this->moodleUrl = 'https://elearning2.ubpkarawang.ac.id/webservice/rest/server.php';
        $this->moodleToken = '47d4b340520e06fbb1d21127d683ae60'; // Gunakan token yang valid
    }

    /**
     * Daftarkan semua user dari master_users ke Moodle
     */
    public function registerUsersToMoodle()
    {
        // Ambil ID user yang sudah berhasil disinkronisasi
        $syncedUserIds = MasterMoodle::where('status', 'success')->pluck('master_user_id')->toArray();

        // Ambil user yang belum ada di master_moodle
        $users = MasterUser::with('user')->whereNotIn('id', $syncedUserIds)->get();

        if ($users->isEmpty()) {
            return response()->json([
                'success' => true,
                'message' => 'Semua user sudah tersinkronisasi dengan Moodle.',
                'total' => 0,
                'created' => 0,
                'failed' => 0,
                'details' => [
                    'created_users' => [],
                    'failed_users' => [],
                ]
            ]);
        }

        $moodle = new MoodleRest($this->moodleUrl, $this->moodleToken);

        $createdUsers = [];
        $failedUsers = [];
        
        foreach ($users as $u) {
            $username = null;
            try {
                // 1. Cek apakah user sudah ada di Moodle
                $username = $this->generateMoodleUsername($u);
                
                // Cek keberadaan user di Moodle
                $existingUser = $this->checkExistingMoodleUser($username, $u->va_number);
                
                if ($existingUser) {
                    // User sudah ada di Moodle, hanya perlu enrol ke course
                    $moodleUserId = $existingUser['id'];
                    $enrolResult = $this->enrolToCourse($moodleUserId);
                    
                    $createdUsers[] = [
                        'user_id' => $u->id, 
                        'moodle_id' => $moodleUserId, 
                        'username' => $username,
                        'password' => $existingUser['password'] ?? 'password123'
                    ];

                    MasterMoodle::updateOrCreate(
                        ['master_user_id' => $u->id], 
                        [
                            'moodle_user_id' => $moodleUserId,
                            'username'       => $username,
                            'password'       => $existingUser['password'] ?? 'password123',
                            'status'         => 'success',
                            'enrol_status'   => $enrolResult ? 'enrolled' : 'enrol_failed',
                            'error_message'  => null
                        ]
                    );
                    
                    Log::info("✅ User sudah ada di Moodle, berhasil enrol", [
                        'user_id' => $u->id,
                        'username' => $username,
                        'moodle_id' => $moodleUserId
                    ]);
                    
                    continue;
                }
                
                // 2. User belum ada, buat baru
                $password = $this->generateMoodlePassword($u);
                $userData = [
                    'users' => [
                        [
                            'username' => $username,
                            'password' => $password,
                            'lastname' => $u->user->name ?? 'Peserta',
                            'firstname' => $u->va_number,
                            'email' => $this->generateMoodleEmail($u),
                            'auth' => 'manual', // Tentukan metode autentikasi
                            'mailformat' => 1, // Format email (1 = HTML, 0 = Plain text)
                            'maildisplay' => 2, // Tampilan email (2 = Allow everyone to see my email address)
                            'city' => 'Karawang',
                            'country' => 'ID',
                            'timezone' => 'Asia/Jakarta',
                            'customfields' => [
                                ['type' => 'nip', 'value' => $u->va_number],
                                ['type' => 'klas', 'value' => 'usm2025'],
                                ['type' => 'nomortelpon', 'value' => $u->user->phone ?? '089797979899'],
                            ],
                        ]
                    ],
                ];

                Log::info("📤 Mengirim data user baru ke Moodle", [
                    'username' => $username,
                    'va_number' => $u->va_number,
                    'data' => $userData
                ]);

                $response = $moodle->request('core_user_create_users', $userData);

                if (isset($response[0]['id'])) {
                    $moodleUserId = $response[0]['id'];
                    $createdUsers[] = [
                        'user_id' => $u->id, 
                        'moodle_id' => $moodleUserId, 
                        'username' => $username,
                        'password' => $password
                    ];

                    // Enrol ke course
                    $enrolResult = $this->enrolToCourse($moodleUserId);

                    MasterMoodle::updateOrCreate(
                        ['master_user_id' => $u->id], 
                        [
                            'moodle_user_id' => $moodleUserId,
                            'username'       => $username,
                            'password'       => $password,
                            'status'         => 'success',
                            'enrol_status'   => $enrolResult ? 'enrolled' : 'enrol_failed',
                            'error_message'  => null
                        ]
                    );

                    Log::info("✅ User Moodle berhasil dibuat dan di-enrol", [
                        'user_id' => $u->id,
                        'username' => $username,
                        'moodle_id' => $moodleUserId
                    ]);

                } else {
                    $errorMessage = $this->parseMoodleError($response);
                    $failedUsers[] = [
                        'user_id' => $u->id, 
                        'username' => $username, 
                        'error' => $errorMessage
                    ];

                    MasterMoodle::updateOrCreate(
                        ['master_user_id' => $u->id],
                        [
                            'username'      => $username,
                            'status'        => 'failed',
                            'enrol_status'  => 'not_enrolled',
                            'error_message' => $errorMessage
                        ]
                    );
                    
                    Log::error("❌ Gagal membuat user Moodle", [
                        'user_id' => $u->id,
                        'username' => $username,
                        'error' => $errorMessage,
                        'response' => $response
                    ]);
                }

            } catch (\Throwable $th) {
                $errorMessage = $th->getMessage();
                
                // Periksa apakah error karena user sudah ada
                if (str_contains(strtolower($errorMessage), 'username already exists') || 
                    str_contains(strtolower($errorMessage), 'duplicate entry')) {
                    
                    // Coba dapatkan user yang sudah ada
                    $existingUser = $this->getMoodleUserByUsername($username);
                    
                    if ($existingUser) {
                        $moodleUserId = $existingUser['id'];
                        $enrolResult = $this->enrolToCourse($moodleUserId);
                        
                        $createdUsers[] = [
                            'user_id' => $u->id, 
                            'moodle_id' => $moodleUserId, 
                            'username' => $username,
                            'password' => 'password123' // Default password karena sudah ada
                        ];

                        MasterMoodle::updateOrCreate(
                            ['master_user_id' => $u->id], 
                            [
                                'moodle_user_id' => $moodleUserId,
                                'username'       => $username,
                                'password'       => 'password123',
                                'status'         => 'success',
                                'enrol_status'   => $enrolResult ? 'enrolled' : 'enrol_failed',
                                'error_message'  => 'User sudah ada di Moodle'
                            ]
                        );
                        
                        Log::info("🔄 User sudah ada, berhasil diperbarui", [
                            'user_id' => $u->id,
                            'username' => $username,
                            'moodle_id' => $moodleUserId
                        ]);
                        
                        continue;
                    }
                }
                
                Log::error("💥 Exception saat membuat user Moodle", [
                    'user_id' => $u->id,
                    'username' => $username ?? $u->va_number,
                    'error' => $errorMessage,
                    'trace' => $th->getTraceAsString()
                ]);

                $failedUsers[] = [
                    'user_id' => $u->id, 
                    'username' => $username ?? $u->va_number, 
                    'error' => $errorMessage
                ];

                MasterMoodle::updateOrCreate(
                    ['master_user_id' => $u->id],
                    [
                        'username'      => $username ?? $u->va_number,
                        'status'        => 'failed',
                        'enrol_status'  => 'not_enrolled',
                        'error_message' => $errorMessage
                    ]
                );
            }
        }

        return response()->json([
            'success' => true,
            'total' => count($users),
            'created' => count($createdUsers),
            'failed' => count($failedUsers),
            'details' => [
                'created_users' => $createdUsers,
                'failed_users' => $failedUsers,
            ]
        ]);
    }

    /**
     * Cek apakah user sudah ada di Moodle
     */
    private function checkExistingMoodleUser($username, $vaNumber)
    {
        try {
            $moodle = new MoodleRest($this->moodleUrl, $this->moodleToken);
            
            // Coba cari berdasarkan username
            $params = [
                'field' => 'username',
                'values' => [$username]
            ];
            
            $response = $moodle->request('core_user_get_users_by_field', $params);
            
            if (!empty($response)) {
                return [
                    'id' => $response[0]['id'],
                    'username' => $response[0]['username'],
                    'email' => $response[0]['email'],
                    'password' => 'password123' // Password default untuk user yang sudah ada
                ];
            }
            
            // Coba cari berdasarkan email
            $params = [
                'field' => 'email',
                'values' => [$this->generateMoodleEmailFromVa($vaNumber)]
            ];
            
            $response = $moodle->request('core_user_get_users_by_field', $params);
            
            if (!empty($response)) {
                return [
                    'id' => $response[0]['id'],
                    'username' => $response[0]['username'],
                    'email' => $response[0]['email'],
                    'password' => 'password123'
                ];
            }
            
            return null;
            
        } catch (\Throwable $th) {
            Log::warning("⚠️ Gagal cek existing user di Moodle", [
                'username' => $username,
                'error' => $th->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Get Moodle user by username
     */
    private function getMoodleUserByUsername($username)
    {
        try {
            $moodle = new MoodleRest($this->moodleUrl, $this->moodleToken);
            
            $params = [
                'field' => 'username',
                'values' => [$username]
            ];
            
            $response = $moodle->request('core_user_get_users_by_field', $params);
            
            if (!empty($response) && isset($response[0]['id'])) {
                return $response[0];
            }
            
            return null;
            
        } catch (\Throwable $th) {
            Log::warning("⚠️ Gagal get user by username", [
                'username' => $username,
                'error' => $th->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Parse error response dari Moodle
     */
    private function parseMoodleError($response)
    {
        if (is_array($response)) {
            if (isset($response['exception'])) {
                return $response['exception'] . ': ' . ($response['message'] ?? 'No message');
            }
            if (isset($response['errorcode'])) {
                return $response['errorcode'] . ': ' . ($response['message'] ?? 'No message');
            }
            if (isset($response[0]['errorcode'])) {
                return $response[0]['errorcode'] . ': ' . ($response[0]['message'] ?? 'No message');
            }
        }
        
        return is_string($response) ? $response : 'Unknown Moodle API error';
    }

    /**
     * Generate username yang valid untuk Moodle
     */
    private function generateMoodleUsername($masterUser)
    {
        // Ambil 4 angka terakhir dari va_number
        $vaLast4Digits = $this->getLast4Digits($masterUser->va_number);
        
        // Format: pmb2025 + 4 digit terakhir va_number
        $baseUsername = 'pmb2025' . $vaLast4Digits;
        
        // Bersihkan username dari karakter khusus
        $username = preg_replace('/[^a-zA-Z0-9_.-]/', '', $baseUsername);
        
        // Pastikan username tidak kosong
        if (empty($username)) {
            $username = 'pmb' . $masterUser->va_number;
        }
        
        // Potong jika terlalu panjang
        $username = substr($username, 0, 100);
        
        return strtolower($username);
    }

    /**
     * Generate password dari nomor handphone
     */
    private function generateMoodlePassword($masterUser)
    {
        try {
            $user = User::find($masterUser->user_id);
            
            if (!$user || empty($user->phone)) {
                Log::warning("⚠️ User atau nomor handphone tidak ditemukan", [
                    'master_user_id' => $masterUser->id,
                    'user_id' => $masterUser->user_id
                ]);
                return 'password123';
            }
            
            $password = $this->convertPhonePrefix($user->phone);
            
            // Validasi password minimal 8 karakter
            if (strlen($password) < 8) {
                $password = 'password123';
            }
            
            return $password;
            
        } catch (\Throwable $th) {
            Log::error("💥 Gagal generate password", [
                'master_user_id' => $masterUser->id,
                'error' => $th->getMessage()
            ]);
            return 'password123';
        }
    }

    /**
     * Konversi awalan nomor handphone
     */
    private function convertPhonePrefix($phoneNumber)
    {
        $cleanPhone = preg_replace('/[^0-9]/', '', $phoneNumber);
        
        if (str_starts_with($cleanPhone, '628')) {
            return '08' . substr($cleanPhone, 3);
        }
        
        if (str_starts_with($cleanPhone, '62')) {
            return '08' . substr($cleanPhone, 2);
        }
        
        if (!str_starts_with($cleanPhone, '08')) {
            // Jika tidak diawali dengan 08, tambahkan
            return '08' . $cleanPhone;
        }
        
        return $cleanPhone;
    }

    /**
     * Ambil 4 digit terakhir dari va_number
     */
    private function getLast4Digits($vaNumber)
    {
        $vaString = (string) $vaNumber;
        $last4Digits = substr($vaString, -4);
        
        if (!preg_match('/^\d{4}$/', $last4Digits)) {
            Log::warning("⚠️ VA number tidak valid", ['va_number' => $vaNumber]);
            return '0000';
        }
        
        return $last4Digits;
    }

    /**
     * Generate email untuk Moodle
     */
    private function generateMoodleEmail($masterUser)
    {
        return $this->generateMoodleEmailFromVa($masterUser->va_number);
    }
    
    private function generateMoodleEmailFromVa($vaNumber)
    {
        return 'pmb' . $vaNumber . '@pmb.ubpkarawang.ac.id';
    }

    /**
     * Enrol user ke course Moodle
     */
    private function enrolToCourse($moodleUserId)
    {
        try {
            $moodle = new MoodleRest($this->moodleUrl, $this->moodleToken);

            $enrolments = [
                'enrolments' => [[
                    'roleid' => 5, // Student role
                    'userid' => $moodleUserId,
                    'courseid' => 384, // ID course target
                ]]
            ];

            $response = $moodle->request('enrol_manual_enrol_users', $enrolments);
            
            Log::info("✅ Berhasil enrol user ke course", [
                'moodle_user_id' => $moodleUserId,
                'course_id' => 384,
                'response' => $response
            ]);
            
            return true;

        } catch (\Throwable $th) {
            Log::error('❌ Gagal enrol user Moodle', [
                'moodle_user_id' => $moodleUserId,
                'error' => $th->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Registrasi single user ke Moodle
     */
    public function registerSingleUserToMoodle($masterUser)
    {
        // Logika sama dengan registerUsersToMoodle, tapi untuk single user
        // Anda bisa memanggil fungsi di atas dengan array berisi satu user
        $result = $this->registerUsersToMoodle();
        return response()->json($result);
    }

    /**
     * Get Moodle account info
     */
    public function getMoodleAccountInfo($masterUserId)
    {
        $moodleAccount = MasterMoodle::where('master_user_id', $masterUserId)->first();
        
        if (!$moodleAccount) {
            return response()->json([
                'success' => false,
                'message' => 'Akun Moodle belum dibuat'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'moodle_user_id' => $moodleAccount->moodle_user_id,
                'username' => $moodleAccount->username,
                'password' => $moodleAccount->password,
                'status' => $moodleAccount->status,
                'enrol_status' => $moodleAccount->enrol_status,
                'created_at' => $moodleAccount->created_at,
                'updated_at' => $moodleAccount->updated_at
            ]
        ]);
    }
}