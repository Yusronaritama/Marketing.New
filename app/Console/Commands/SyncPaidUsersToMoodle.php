<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\MasterUser;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class SyncPaidUsersToMoodle extends Command
{
    protected $signature = 'moodle:sync-paid';
    protected $description = 'Sync PAID users to Moodle with direct API integration';

    private $moodleUrl;
    private $moodleToken;
    private $courseId;
    private $roleId;

    public function __construct()
    {
        parent::__construct();
        $this->moodleUrl = env('MOODLE_URL');
        $this->moodleToken = '38dbd3160152ae78ce08e7820b553147';
        $this->courseId = env('MOODLE_COURSE_ID', 2);
        $this->roleId = env('MOODLE_ROLE_ID', 5);
    }

    public function handle()
    {
        $this->info('🔍 Mencari user PAID yang belum terdaftar di Moodle...');

        $usersToSync = MasterUser::with('user')
            ->where('va_status', 'paid')
            ->whereDoesntHave('moodleStatus', function($query) {
                $query->where('status', 'success');
            })
            ->get();

        $count = $usersToSync->count();

        if ($count === 0) {
            $this->info('✅ Tidak ada user yang perlu disinkronisasi.');
            return;
        }

        $this->info("⚡ Ditemukan {$count} user. Memproses langsung ke Moodle API...");
        $bar = $this->output->createProgressBar($count);
        $bar->start();

        foreach ($usersToSync as $masterUser) {
            if (!$masterUser->user) {
                $bar->advance();
                continue;
            }

            $username = null;

            try {
                $vaNumber = (string) $masterUser->va_number;
                $cleanVa = preg_replace('/\D/', '', $vaNumber);
                $username = '2026' . substr($cleanVa, -4);
                $password = 'Ubp2026*';

                $fullName = trim($masterUser->user->name);
                $parts = explode(' ', $fullName);
                $firstname = $parts[0];
                $lastname = count($parts) > 1 ? implode(' ', array_slice($parts, 1)) : '-';

                $email = strtolower(trim($masterUser->user->email));
                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    throw new \Exception("Email tidak valid: {$email}");
                }

                $moodleUserId = $this->createMoodleUser([
                    'username' => strtolower($username),
                    'password' => $password,
                    'firstname' => $firstname,
                    'lastname' => $lastname,
                    'email' => $email,
                    'auth' => 'manual',
                ]);

                if (!$moodleUserId) {
                    throw new \Exception("Gagal mendapatkan Moodle User ID");
                }

                $enrolSuccess = $this->enrolMoodleUser($moodleUserId);

                DB::table('master_moodle')->updateOrInsert(
                    ['master_user_id' => $masterUser->id],
                    [
                        'moodle_user_id' => $moodleUserId,
                        'username' => $username,
                        'status' => 'success',
                        'enrol_status' => $enrolSuccess ? 'enrolled' : 'enrol_failed',
                        'created_at' => now(),
                        'updated_at' => now(),
                        'last_sync_at' => now(),
                        'sync_notes' => 'Direct Sync (Laravel)'
                    ]
                );

            } catch (\Exception $e) {
                $this->error("\n❌ Gagal {$masterUser->user->name}: " . $e->getMessage());
                
                DB::table('master_moodle')->updateOrInsert(
                    ['master_user_id' => $masterUser->id],
                    [
                        'username' => $username ?? $masterUser->va_number,
                        'status' => 'failed',
                        'error_message' => substr($e->getMessage(), 0, 250),
                        'updated_at' => now()
                    ]
                );
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info('🚀 Proses selesai!');
    }

    private function createMoodleUser($data)
    {
        $userData = [
            'username' => $data['username'],
            'password' => $data['password'],
            'firstname' => $data['firstname'],
            'lastname' => $data['lastname'],
            'email' => $data['email'],
            'auth' => $data['auth'] ?? 'manual',
            'timezone' => 'Asia/Jakarta',
            'lang' => 'id',
        ];

        $params = [
            'wstoken' => $this->moodleToken,
            'wsfunction' => 'core_user_create_users',
            'moodlewsrestformat' => 'json',
        ];

        foreach ($userData as $key => $value) {
            $params["users[0][{$key}]"] = $value;
        }

        $response = Http::asForm()->post($this->moodleUrl, $params);
        $result = $response->json();

        if (isset($result['exception'])) {
            if ($result['errorcode'] === 'usernameexists') {
                return $this->getMoodleUserId($data['username']);
            }
            throw new \Exception("Moodle Create Error: " . ($result['message'] ?? 'Unknown error'));
        }

        if (isset($result[0]['id'])) {
            return $result[0]['id'];
        }

        throw new \Exception("Unexpected Moodle response format");
    }

    private function getMoodleUserId($username)
    {
        $params = [
            'wstoken' => $this->moodleToken,
            'wsfunction' => 'core_user_get_users_by_field',
            'moodlewsrestformat' => 'json',
            'field' => 'username',
            'values[0]' => $username,
        ];

        $response = Http::asForm()->post($this->moodleUrl, $params);
        $result = $response->json();

        if (isset($result[0]['id'])) {
            return $result[0]['id'];
        }

        return null;
    }

    private function enrolMoodleUser($userId)
    {
        $params = [
            'wstoken' => $this->moodleToken,
            'wsfunction' => 'enrol_manual_enrol_users',
            'moodlewsrestformat' => 'json',
            'enrolments[0][roleid]' => $this->roleId,
            'enrolments[0][userid]' => $userId,
            'enrolments[0][courseid]' => $this->courseId,
        ];

        $response = Http::asForm()->post($this->moodleUrl, $params);
        $result = $response->json();

        if (empty($result) || $result === null) {
            return true;
        }

        if (isset($result['exception'])) {
            \Log::error('Moodle Enrol Error', $result);
            return false;
        }

        return false;
    }
}