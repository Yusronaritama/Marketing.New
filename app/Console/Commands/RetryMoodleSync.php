<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\MasterUser;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;

class RetryMoodleSync extends Command
{
    protected $signature = 'moodle:retry-sync';
    protected $description = 'Perbaiki data Moodle: Paksa Format NIM+NAMA dan Update Akun Lama';

    private $moodleUrl = 'https://elearning2.ubpkarawang.ac.id/webservice/rest/server.php';
    private $moodleToken = '6c7943bb4103dc5748e22780b4fe9467';
    private $courseId = 2;
    private $roleId = 5;

    public function handle()
    {
        // Ambil semua user PAID (tanpa filter status success, agar semua dicek ulang)
        $usersToSync = MasterUser::with('user')->where('va_status', 'paid')->get();
        $count = $usersToSync->count();

        $this->info("⚡ Memproses {$count} user untuk standarisasi...");
        $bar = $this->output->createProgressBar($count);
        $bar->start();

        foreach ($usersToSync as $masterUser) {
            if (!$masterUser->user) {
                $bar->advance(); continue;
            }

            try {
                // 1. SIAPKAN DATA STANDAR (TARGET)
                $cleanVa = preg_replace('/\D/', '', (string)$masterUser->va_number);
                $targetUsername = '2026' . substr($cleanVa, -4); // NIM 2026xxxx
                $targetEmail = 'pmb' . $targetUsername . '@ubpkarawang.ac.id'; // Email Kampus
                $password = 'Ubp2026*';

                // FORMAT NAMA BARU (NIM + NAMA)
                // Contoh: "20261310 ABDILLAH DAFFA..."
                $cleanName = strtoupper(preg_replace('/[^A-Za-z0-9 ]/', '', $masterUser->user->name));
                $firstname = $targetUsername . ' ' . trim($cleanName); 
                $lastname = '.'; // Moodle wajib isi lastname, kita pakai titik saja

                // Data yang akan dikirim ke Moodle
                $userData = [
                    'username'  => $targetUsername,
                    'password'  => $password,
                    'firstname' => $firstname, // Ini kunci agar nama ada NIM-nya
                    'lastname'  => $lastname,
                    'email'     => $targetEmail,
                ];

                // 2. LOGIKA UPDATE PINTAR
                // Cek apakah user sudah ada? (Cek by Username BARU atau Email LAMA)
                $moodleUserId = $this->findAndFixUser($userData, $masterUser->user->email);

                // 3. ENROL USER
                if ($moodleUserId) {
                    $this->enrolMoodleUser($moodleUserId);
                    
                    // Update Status di DB Lokal
                    DB::table('master_moodle')->updateOrInsert(
                        ['master_user_id' => $masterUser->id],
                        [
                            'moodle_user_id' => $moodleUserId,
                            'username'       => $targetUsername,
                            'status'         => 'success',
                            'updated_at'     => now(),
                            'sync_notes'     => 'Fixed: Name format updated'
                        ]
                    );
                }

            } catch (\Exception $e) {
                // Silent error agar loop lanjut
            }
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info('🚀 Selesai! Nama user sekarang seharusnya diawali NIM.');
    }

    /**
     * Mencari user, jika ketemu -> Update. Jika tidak -> Create.
     * Mengatasi masalah duplikat gmail.
     */
    private function findAndFixUser($targetData, $oldDbEmail)
    {
        // SKENARIO 1: Cek apakah Username "2026xxxx" sudah ada?
        $existingId = $this->getMoodleUserId('username', $targetData['username']);

        if ($existingId) {
            // Jika ada, UPDATE datanya (supaya namanya jadi ada NIM-nya)
            $this->updateMoodleUser($existingId, $targetData);
            return $existingId;
        }

        // SKENARIO 2: Cek apakah Email LAMA (Gmail) ada di Moodle?
        // Ini untuk memperbaiki user "Maesaroh (Gmail)" menjadi "2026... (UBP)"
        if (!empty($oldDbEmail)) {
            $existingIdByEmail = $this->getMoodleUserId('email', $oldDbEmail);
            
            if ($existingIdByEmail) {
                // HATI-HATI: Kita update akun gmail lama menjadi akun kampus baru
                // Kita perlu update Username-nya juga!
                $this->updateMoodleUser($existingIdByEmail, $targetData);
                return $existingIdByEmail;
            }
        }

        // SKENARIO 3: Belum ada sama sekali -> Buat Baru
        return $this->createMoodleUser($targetData);
    }

    private function createMoodleUser($data)
    {
        $params = [
            'wstoken' => $this->moodleToken,
            'wsfunction' => 'core_user_create_users',
            'moodlewsrestformat' => 'json',
            'users' => [0 => $data]
        ];
        $response = Http::asForm()->post($this->moodleUrl, $params);
        $result = $response->json();
        return $result[0]['id'] ?? null;
    }

    private function updateMoodleUser($id, $data)
    {
        $updateData = [
            'id'        => $id,
            'username'  => $data['username'],  // Update username (penting untuk migrasi dari gmail)
            'email'     => $data['email'],     // Update email
            'firstname' => $data['firstname'], // Update Nama (tambah NIM)
            'lastname'  => $data['lastname'],
            'password'  => $data['password']
        ];

        $params = [
            'wstoken' => $this->moodleToken,
            'wsfunction' => 'core_user_update_users',
            'moodlewsrestformat' => 'json',
            'users' => [0 => $updateData]
        ];
        Http::asForm()->post($this->moodleUrl, $params);
    }

    private function getMoodleUserId($field, $value)
    {
        $params = [
            'wstoken' => $this->moodleToken,
            'wsfunction' => 'core_user_get_users_by_field',
            'moodlewsrestformat' => 'json',
            'field' => $field,
            'values' => [0 => $value]
        ];
        $response = Http::asForm()->post($this->moodleUrl, $params);
        $result = $response->json();
        return $result[0]['id'] ?? null;
    }

    private function enrolMoodleUser($userId)
    {
        $params = [
            'wstoken' => $this->moodleToken,
            'wsfunction' => 'enrol_manual_enrol_users',
            'moodlewsrestformat' => 'json',
            'enrolments' => [['roleid' => $this->roleId, 'userid' => $userId, 'courseid' => $this->courseId]]
        ];
        Http::asForm()->post($this->moodleUrl, $params);
    }
}