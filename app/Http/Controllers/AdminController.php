<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class AdminController extends Controller
{
    public function index()
    {
        $token = '6c7943bb4103dc5748e22780b4fe9467';
        $domain = 'https://elearning2.ubpkarawang.ac.id/webservice/rest/server.php';
        $courseId = 2;
        $targetInstance = 2; // ID Kuis (Gelombang 1)

        // =====================================================================
        // LANGKAH 1: Cari Kuis Target (Berdasarkan kode Anda sebelumnya)
        // =====================================================================
        $urlCourse = "{$domain}?wstoken={$token}&wsfunction=core_course_get_contents&moodlewsrestformat=json&courseid={$courseId}";
        $responseCourse = Http::get($urlCourse);

        if (!$responseCourse->successful()) {
            dd('Gagal terhubung ke API Moodle saat mengambil course contents');
        }

        $sections = $responseCourse->json();
        $kuisTarget = null; 

        foreach ($sections as $section) {
            if (!empty($section['modules'])) {
                foreach ($section['modules'] as $module) {
                    if (isset($module['modname']) && $module['modname'] === 'quiz' && $module['instance'] == $targetInstance) {
                        $kuisTarget = [
                            'id'       => $module['id'],
                            'url'      => $module['url'],
                            'name'     => $module['name'],
                            'instance' => $module['instance']
                        ];
                        break 2; // Hentikan pencarian jika kuis ditemukan
                    }
                }
            }
        }

        // Jika kuis tidak ditemukan, hentikan proses
        if (!$kuisTarget) {
            dd("Kuis dengan instance {$targetInstance} tidak ditemukan di dalam course ini.");
        }


        // =====================================================================
        // LANGKAH 2: Ambil semua user/mahasiswa yang terdaftar di course ini
        // =====================================================================
        $responseUsers = Http::get($domain, [
            'wstoken'            => $token,
            'wsfunction'         => 'core_enrol_get_enrolled_users',
            'moodlewsrestformat' => 'json',
            'courseid'           => $courseId
        ]);

        if (!$responseUsers->successful()) {
            dd('Gagal mengambil data peserta ujian dari course');
        }

        $users = $responseUsers->json();
        if (isset($users['exception'])) {
            dd(['status' => 'Fungsi API enrol belum diizinkan', 'detail' => $users]);
        }

        $pesertaSelesai = [];

        // =====================================================================
        // LANGKAH 3: Cek attempt quiz & hitung nilai akhir
        // =====================================================================
        foreach ($users as $user) {
            $responseAttempt = Http::get($domain, [
                'wstoken'            => $token,
                'wsfunction'         => 'mod_quiz_get_user_attempts',
                'moodlewsrestformat' => 'json',
                'quizid'             => $targetInstance,
                'userid'             => $user['id'],
                'status'             => 'finished' // Hanya ambil yang sudah submit
            ]);

            if ($responseAttempt->successful()) {
                $attemptData = $responseAttempt->json();

                if (isset($attemptData['attempts']) && !empty($attemptData['attempts'])) {
                    foreach ($attemptData['attempts'] as $attempt) {
                        
                        // --- PROSES KONVERSI NILAI ---
                        // Mengambil marks mentah (maksimal 200.00)
                        $nilaiMentah = $attempt['sumgrades'] ?? 0;
                        
                        // Rumus: (Nilai Mentah / Maksimal Marks) * Skala Grade Moodle
                        $nilaiAkhir = ($nilaiMentah / 200) * 10;
                        
                        // Memformat menjadi 2 angka di belakang koma (misal: 0.00)
                        $nilaiAkhirFormatted = number_format($nilaiAkhir, 2);

                        $pesertaSelesai[] = [
                            'user_id'       => $user['id'],
                            'nama_peserta'  => $user['fullname'] ?? trim(($user['firstname'] ?? '') . ' ' . ($user['lastname'] ?? '')),
                            'waktu_mulai'   => date('Y-m-d H:i:s', $attempt['timestart']),
                            'waktu_selesai' => date('Y-m-d H:i:s', $attempt['timefinish']),
                            'marks_mentah'  => $nilaiMentah, // Poin asli dari API Moodle
                            'grade_akhir'   => $nilaiAkhirFormatted // Nilai skala 10.00
                        ];
                    }
                }
            }
        }

        // =====================================================================
        // TAMPILKAN HASIL AKHIR
        // =====================================================================
        dd([
            'status' => 'Berhasil mengambil data ujian peserta',
            'detail_kuis' => $kuisTarget,
            'total_peserta_selesai' => count($pesertaSelesai),
            'daftar_nilai_peserta' => $pesertaSelesai
        ]);
    }
}