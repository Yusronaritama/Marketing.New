<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Illuminate\Support\Str;
use App\Libraries\MoodleRest;

class MoodleRetryController extends Controller
{
    private $moodle;
    private $courseId = 2; 
    private $studentRoleId = 5;

    public function __construct()
    {
        $moodleUrl   = 'https://elearning2.ubpkarawang.ac.id/webservice/rest/server.php';
        $moodleToken = '47d4b340520e06fbb1d21127d683ae60';
        
        $this->moodle = new MoodleRest($moodleUrl, $moodleToken);
    }

    public function index()
    {
        $failedUsers = DB::table('master_moodle')
            ->join('master_users', 'master_moodle.master_user_id', '=', 'master_users.id')
            ->join('users', 'master_users.user_id', '=', 'users.id')
            ->select(
                'master_moodle.*',
                'master_users.va_number',
                'users.name',
                'users.email'
            )
            ->where(function($query) {
                $query->where('master_moodle.status', 'failed')
                      ->orWhere(function($q) {
                          $q->where('master_moodle.status', 'success')
                            ->where('master_moodle.enrol_status', 'enrol_failed');
                      });
            })
            ->orderBy('master_moodle.updated_at', 'desc')
            ->paginate(20);

        return view('admin.moodle.failed', compact('failedUsers'));
    }

    public function retryBatchAjax()
    {
        // PENTING: Kurangi batch size untuk hindari timeout
        $limit = 2; // Dari 5 menjadi 2
        
        // Perpanjang timeout
        set_time_limit(120); // 2 menit (cukup untuk 2 user)
        ini_set('max_execution_time', 120);

        try {
            // PRIORITAS 1: Failed Creation
            $failedCreationRecords = DB::table('master_moodle')
                ->join('master_users', 'master_moodle.master_user_id', '=', 'master_users.id')
                ->join('users', 'master_users.user_id', '=', 'users.id')
                ->select('master_moodle.*', 'master_users.va_number', 'users.name', 'users.email')
                ->where('master_moodle.status', 'failed')
                ->orderBy('master_moodle.updated_at', 'asc') 
                ->limit($limit)
                ->get();

            if ($failedCreationRecords->isNotEmpty()) {
                return $this->processUserCreation($failedCreationRecords);
            }

            // PRIORITAS 2: Enrol Failed
            $failedEnrolRecords = DB::table('master_moodle')
                ->join('master_users', 'master_moodle.master_user_id', '=', 'master_users.id')
                ->join('users', 'master_users.user_id', '=', 'users.id')
                ->select('master_moodle.*', 'master_users.va_number', 'users.name', 'users.email')
                ->where('master_moodle.status', 'success')
                ->where('master_moodle.enrol_status', 'enrol_failed')
                ->orderBy('master_moodle.updated_at', 'asc')
                ->limit($limit)
                ->get();

            if ($failedEnrolRecords->isEmpty()) {
                return response()->json([
                    'status' => 'done',
                    'message' => 'Semua data berhasil diproses!'
                ]);
            }

            return $this->processEnrolOnly($failedEnrolRecords);

        } catch (\Exception $e) {
            Log::error("Batch Error: " . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
                'remaining' => $this->getRemainingCount()
            ], 500);
        }
    }

    private function processUserCreation($records)
    {
        $successCount = 0;
        $failCount = 0;

        foreach ($records as $record) {
            try {
                $username = !empty($record->username) ? trim($record->username) : trim($record->va_number);
                $safeName = $record->name ?? 'Mahasiswa Baru';
                
                if (empty($username)) throw new \Exception("Username/VA Number kosong.");

                // 1. Cek User dengan timeout handling
                $existingUser = $this->getMoodleUserByUsername($username);
                $moodleUserId = null;

                if ($existingUser) {
                    $moodleUserId = $existingUser['id'];
                    Log::info("User found: {$username} (ID: {$moodleUserId})");
                } else {
                    // Create User
                    $nameParts = $this->splitName($safeName);
                    $userData = [
                        'username'     => $username,
                        'password'     => 'Ubp2026*',
                        'firstname'    => $nameParts['firstname'],
                        'lastname'     => $nameParts['lastname'],
                        'email'        => $this->generateEmail($username),
                        'auth'         => 'manual',
                        'customfields' => [
                            ['type' => 'nip', 'value' => $username],
                            ['type' => 'klas', 'value' => 'usm2025'],
                            ['type' => 'nomortelpon', 'value' => '628123456789']
                        ]
                    ];
                    
                    Log::info("Creating user: {$username}");
                    $createResult = $this->createSingleMoodleUser($userData);
                    
                    if (!$createResult || !isset($createResult['id'])) {
                        // Handle "Username already exists"
                        if(isset($createResult['message']) && str_contains(strtolower($createResult['message']), 'username already exists')) {
                             Log::info("Username exists, retrying find: {$username}");
                             $retryFind = $this->getMoodleUserByUsername($username);
                             if($retryFind) {
                                 $moodleUserId = $retryFind['id'];
                             } else {
                                 throw new \Exception("User exists tapi tidak ditemukan");
                             }
                        } else {
                            $errMsg = isset($createResult['message']) ? $createResult['message'] : 'Unknown error';
                            throw new \Exception("Gagal create: " . $errMsg);
                        }
                    } else {
                        $moodleUserId = $createResult['id'];
                        Log::info("User created: {$username} (ID: {$moodleUserId})");
                    }
                }

                // Update DB
                DB::table('master_moodle')
                    ->where('id', $record->id)
                    ->update([
                        'moodle_user_id' => $moodleUserId,
                        'username'       => $username,
                        'status'         => 'success',
                        'error_message'  => null,
                        'sync_notes'     => 'User ready, enrolling...',
                        'updated_at'     => Carbon::now()
                    ]);

                // 2. Enrol User dengan delay kecil
                sleep(1); // Delay 1 detik sebelum enrol
                
                Log::info("Enrolling user ID {$moodleUserId} to course {$this->courseId}");
                $enrolResult = $this->enrolSingleUser($moodleUserId, $this->courseId, $this->studentRoleId);

                if ($enrolResult === true) {
                    DB::table('master_moodle')->where('id', $record->id)->update([
                        'enrol_status' => 'enrolled',
                        'last_sync_at' => Carbon::now(),
                        'sync_notes'   => 'Success',
                        'updated_at'   => Carbon::now()
                    ]);
                    Log::info("Enrol success for user {$username}");
                    $successCount++;
                } else {
                    DB::table('master_moodle')->where('id', $record->id)->update([
                        'enrol_status' => 'enrol_failed',
                        'sync_notes'   => 'User created, enrol failed',
                        'updated_at'   => Carbon::now()
                    ]);
                    Log::warning("Enrol failed for user {$username}");
                    $successCount++; // User tetap sukses dibuat
                }

                // Delay antar user
                sleep(2);

            } catch (\Exception $e) {
                DB::table('master_moodle')->where('id', $record->id)->update([
                    'status'        => 'failed',
                    'enrol_status'  => 'enrol_failed',
                    'error_message' => Str::limit($e->getMessage(), 255),
                    'last_sync_at'  => Carbon::now(),
                    'updated_at'    => Carbon::now()
                ]);
                $failCount++;
                Log::error("Failed processing {$record->username}: " . $e->getMessage());
            }
        }

        $remaining = $this->getRemainingCount();

        return response()->json([
            'status' => 'partial',
            'processed_count' => count($records),
            'success' => $successCount,
            'fail' => $failCount,
            'remaining' => $remaining,
            'message' => "Proses Create: Sukses {$successCount}, Gagal {$failCount}",
            'process_type' => 'user_creation'
        ]);
    }

    private function processEnrolOnly($records)
    {
        $successCount = 0;
        $failCount = 0;

        foreach ($records as $record) {
            try {
                if (empty($record->moodle_user_id)) {
                    $username = $record->username ?? $record->va_number;
                    Log::info("Finding user for enrol: {$username}");
                    $existing = $this->getMoodleUserByUsername($username);
                    
                    if($existing) {
                        $record->moodle_user_id = $existing['id'];
                        DB::table('master_moodle')->where('id', $record->id)->update([
                            'moodle_user_id' => $existing['id']
                        ]);
                    } else {
                        throw new \Exception("User tidak ditemukan di Moodle");
                    }
                }

                Log::info("Enrolling user ID {$record->moodle_user_id}");
                $enrolResult = $this->enrolSingleUser($record->moodle_user_id, $this->courseId, $this->studentRoleId);

                if ($enrolResult === true) {
                    DB::table('master_moodle')->where('id', $record->id)->update([
                        'enrol_status'   => 'enrolled',
                        'error_message'  => null,
                        'last_sync_at'   => Carbon::now(),
                        'sync_notes'     => 'Enrolled (Retry)',
                        'updated_at'     => Carbon::now()
                    ]);
                    Log::info("Enrol success for ID {$record->moodle_user_id}");
                    $successCount++;
                } else {
                    throw new \Exception("Enrol API failed");
                }

                sleep(2); // Delay antar enrol

            } catch (\Exception $e) {
                $statusUpdate = str_contains(strtolower($e->getMessage()), 'tidak ditemukan') ? 'failed' : 'success';
                
                DB::table('master_moodle')->where('id', $record->id)->update([
                    'status'        => $statusUpdate,
                    'enrol_status'  => 'enrol_failed',
                    'error_message' => Str::limit($e->getMessage(), 255),
                    'updated_at'    => Carbon::now()
                ]);
                $failCount++;
                Log::error("Enrol error: " . $e->getMessage());
            }
        }

        $remaining = $this->getRemainingCount();

        return response()->json([
            'status' => 'partial',
            'processed_count' => count($records),
            'success' => $successCount,
            'fail' => $failCount,
            'remaining' => $remaining,
            'message' => "Proses Enrol: Sukses {$successCount}, Gagal {$failCount}",
            'process_type' => 'enrol_only'
        ]);
    }

    // Helper untuk hitung sisa
    private function getRemainingCount()
    {
        return DB::table('master_moodle')->where('status', 'failed')->count() 
             + DB::table('master_moodle')
                 ->where('status', 'success')
                 ->where('enrol_status', 'enrol_failed')
                 ->count();
    }

    // ==========================================
    // API FUNCTIONS
    // ==========================================

    private function getMoodleUserByUsername($username) {
        try {
            $response = $this->moodle->request('core_user_get_users_by_field', [
                'field' => 'username', 
                'values' => [$username]
            ]);
            return (is_array($response) && count($response) > 0) ? $response[0] : null;
        } catch (\Exception $e) { 
            Log::error("Get User Error for {$username}: " . $e->getMessage());
            return null; 
        }
    }

    private function createSingleMoodleUser($userData) {
        try {
            $response = $this->moodle->request(
                'core_user_create_users', 
                ['users' => [$userData]], 
                MoodleRest::METHOD_POST
            );
            
            if (isset($response['exception'])) {
                return ['message' => $response['message']];
            }
            
            return (is_array($response) && count($response) > 0) ? $response[0] : $response;
        } catch (\Exception $e) { 
            Log::error("Create User Error: " . $e->getMessage());
            return ['message' => $e->getMessage()]; 
        }
    }

    private function enrolSingleUser($userId, $courseId, $roleId) {
        try {
            $this->moodle->request('enrol_manual_enrol_users', [
                'enrolments' => [[
                    'roleid' => $roleId, 
                    'userid' => $userId, 
                    'courseid' => $courseId
                ]]
            ], MoodleRest::METHOD_POST);
            
            return true;
        } catch (\Exception $e) { 
            Log::error("Enrol Error for user {$userId}: " . $e->getMessage());
            return false; 
        }
    }

    private function splitName($fullname) {
        $parts = explode(' ', trim($fullname));
        $lastname = array_pop($parts);
        $firstname = implode(' ', $parts);
        return [
            'firstname' => $firstname ?: $lastname, 
            'lastname' => $lastname ?: 'Mahasiswa'
        ];
    }

    private function generateEmail($username) {
        return $username . '@ubpkarawang.ac.id';
    }
}