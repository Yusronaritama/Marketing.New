<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class DiagnoseMoodle extends Command
{
    protected $signature = 'moodle:diagnose';
    protected $description = 'Diagnosa Moodle API dengan validasi parameter ketat';

    private $moodleUrl   = 'https://elearning2.ubpkarawang.ac.id/webservice/rest/server.php';
    private $moodleToken = '6c7943bb4103dc5748e22780b4fe9467';

    public function handle()
    {
        $this->info('=== MEMULAI DIAGNOSA MOODLE API ===');

        // Test 1: Menggunakan data dari log gagal Anda (Raditya)
        $this->info('--- TEST 1: Replikasi Data Gagal (Raditya) ---');
        $this->testCreateUser('20263381', 'Ubp2026*', 'Raditya', 'Weka Hardahana', 'radityaweka123@gmail.com');

        // Test 2: Menggunakan data minimalis
        $this->info('');
        $this->info('--- TEST 2: Data Minimalis & Clean ---');
        $this->testCreateUser('testuser99', 'Ubp2026*', 'Test', 'Moodle', 'test99@example.com');

        // Test 3: Cek Site Info & Fungsi yang Aktif
        $this->info('');
        $this->info('--- TEST 3: Cek Konfigurasi Web Service ---');
        $this->testSiteInfo();
    }

    private function testCreateUser($username, $password, $firstname, $lastname, $email)
    {
        // 1. Sanitasi Ketat (Moodle API Sangat Sensitif)
        $userData = [
            'username'   => strtolower(trim($username)), // WAJIB Lowercase
            'password'   => (string)$password,
            'firstname'  => trim($firstname),
            'lastname'   => trim($lastname) ?: '.',      // Lastname tidak boleh kosong
            'email'      => strtolower(trim($email)),
            'auth'       => 'manual',                    // Paksa manual auth
            'timezone'   => 'Asia/Jakarta',
            'lang'       => 'en',
        ];

        // 2. Konstruksi Query Params
        // Moodle REST seringkali gagal jika dikirim sebagai JSON nested biasa di beberapa versi PHP.
        // Cara paling aman adalah flat array dengan index eksplisit.
        $params = [
            'wstoken'            => $this->moodleToken,
            'wsfunction'         => 'core_user_create_users',
            'moodlewsrestformat' => 'json',
        ];

        foreach ($userData as $key => $value) {
            $params["users[0][{$key}]"] = $value;
        }

        $this->line("Checking User: {$userData['username']}...");

        try {
            $response = Http::timeout(30)
                ->asForm() // Sangat disarankan menggunakan form-urlencoded untuk Moodle
                ->post($this->moodleUrl, $params);

            if ($response->failed()) {
                $this->error("   ❌ HTTP Error: " . $response->status());
                return;
            }

            $result = $response->json();

            // Cek jika ada Exception dari Moodle
            if (isset($result['exception'])) {
                $this->error("   ❌ MOODLE ERROR: [" . $result['errorcode'] . "]");
                $this->error("   Detail: " . $result['message']);
                if (isset($result['debuginfo'])) {
                    $this->line("   Debug: " . $result['debuginfo']);
                }
            } else {
                $newId = $result[0]['id'] ?? 'unknown';
                $this->info("   ✅ BERHASIL! User ID: " . $newId);
                
                // Cleanup: Hapus user test jika berhasil
                if (is_numeric($newId)) {
                    $this->deleteTestUser($newId);
                }
            }
        } catch (\Exception $e) {
            $this->error("   ❌ EXCEPTION: " . $e->getMessage());
        }
    }

    private function deleteTestUser($userId)
    {
        $params = [
            'wstoken'            => $this->moodleToken,
            'wsfunction'         => 'core_user_delete_users',
            'moodlewsrestformat' => 'json',
            'userids[0]'         => $userId,
        ];

        Http::asForm()->post($this->moodleUrl, $params);
        $this->line("   🗑️ User test ID {$userId} telah dihapus.");
    }

    private function testSiteInfo()
    {
        $params = [
            'wstoken'            => $this->moodleToken,
            'wsfunction'         => 'core_webservice_get_site_info',
            'moodlewsrestformat' => 'json',
        ];

        $response = Http::asForm()->post($this->moodleUrl, $params);
        $result   = $response->json();

        if (isset($result['exception'])) {
            $this->error("   ❌ Gagal mengambil site info: " . $result['message']);
        } else {
            $this->info("   Site Name : " . ($result['sitename'] ?? 'N/A'));
            $this->info("   Moodle Ver: " . ($result['release'] ?? 'N/A'));
            
            // Cek apakah core_user_create_users ada di daftar fungsi
            $functions = collect($result['functions'] ?? [])->pluck('name');
            if ($functions->contains('core_user_create_users')) {
                $this->info("   ✅ Fungsi 'core_user_create_users' TERSEDIA.");
            } else {
                $this->error("   ❌ Fungsi 'core_user_create_users' TIDAK DITEMUKAN dalam token ini.");
            }
        }
    }
}