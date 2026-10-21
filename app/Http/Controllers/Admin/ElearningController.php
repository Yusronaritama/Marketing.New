<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PendaftarGetNim;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * ElearningController
 *
 * Menangani seluruh integrasi ke Moodle E-Learning (create user, bulk create,
 * dan diagnosa field yang wajib diisi). Dipisahkan dari GenerateNimController
 * agar tanggung jawab masing-masing controller lebih jelas:
 *   - GenerateNimController -> generate NIM, kirim SIPT, kirim SLIK
 *   - ElearningController   -> kirim/registrasi akun E-Learning
 */
class ElearningController extends Controller
{
    private const TOKEN      = '5675caf640a47a04ef039ac2dc68ea1c';
    private const SERVER_URL = 'https://elearning.ubpkarawang.ac.id/webservice/rest/server.php';

    // =========================================================================
    // KIRIM KE E-LEARNING (Satuan)
    // =========================================================================
    public function sendToElearning(Request $request, int $nimId)
    {
        $record = PendaftarGetNim::with(['mahasiswa', 'user'])->findOrFail($nimId);

        if ($record->message_elearning === 'Success') {
            return back()->with('error', 'Data sudah terkirim ke E-Learning sebelumnya.');
        }

        $res = $this->processSendToElearning($record);

        if ($res['success']) {
            return back()->with('success', "E-Learning Sukses: {$res['message']} (NIM {$record->nim})");
        }

        return back()->with('error', "Gagal E-Learning: {$res['message']} (NIM {$record->nim})");
    }

    // =========================================================================
    // KIRIM MASSAL KE E-LEARNING (Bisa per limit / 10 orang)
    // =========================================================================
    public function sendBulkToElearning(Request $request, $limit = null)
    {
        $limit   = $limit ? (int) $limit : ($request->filled('limit') ? (int) $request->limit : null);
        $jenjang = $request->get('jenjang');

        Log::info("--- MEMULAI BULK SEND TO E-LEARNING " . ($jenjang ? "JENJANG: [{$jenjang}]" : "SEMUA JENJANG") . ($limit ? " (LIMIT: {$limit} DATA)" : "") . " ---");

        // Ambil data yang BELUM sukses terkirim ke E-Learning
        $query = PendaftarGetNim::with(['mahasiswa', 'user'])
            ->where(function ($q) {
                $q->whereNull('message_elearning')
                  ->orWhere('message_elearning', '!=', 'Success');
            });

        if (!empty($jenjang)) {
            $query->where('jenjang', $jenjang);
        }

        if ($limit && $limit > 0) {
            $query->limit($limit);
        }

        $records = $query->get();
        $totalAntrean = $records->count();

        Log::info("Ditemukan {$totalAntrean} record NIM yang siap dikirim ke E-Learning.");

        if ($totalAntrean === 0) {
            return back()->with('info', 'Semua akun mahasiswa sudah berhasil dibuat di E-Learning.');
        }

        $successCount = 0;
        $failedCount  = 0;
        $messages     = [];

        foreach ($records as $record) {
            $res = $this->processSendToElearning($record);

            if ($res['success']) {
                $successCount++;
            } else {
                $failedCount++;
                $messages[] = "NIM {$record->nim}: {$res['message']}";
            }
        }

        Log::info("--- BULK E-LEARNING SELESAI --- Berhasil: {$successCount} | Gagal: {$failedCount}");

        $msg = "Bulk Kirim E-Learning Selesai" . ($limit ? " (Batch {$limit} data)" : "") . "! Berhasil: <strong>{$successCount}</strong>, Gagal/Tertunda: <strong>{$failedCount}</strong>.";
        if (!empty($messages)) {
            $msg .= '<br><small class="text-xs">' . implode('<br>', array_slice($messages, 0, 5)) . (count($messages) > 5 ? '<br>...dan lainnya.' : '') . '</small>';
        }

        return back()->with($successCount > 0 ? 'success' : 'error', $msg);
    }

    // =========================================================================
    // HELPERS PRIVATE
    // =========================================================================

    private function processSendToElearning(PendaftarGetNim $record): array
    {
        $token     = self::TOKEN;
        $serverUrl = self::SERVER_URL;

        $mahasiswa = $record->mahasiswa;
        $userId    = $record->user_id ?? $mahasiswa?->user_id ?? $record->user?->id;

        // 1. Ambil data master_users & users
        $masterUser = DB::table('master_users')->where('user_id', $userId)->first();
        $user       = DB::table('users')->where('id', $userId)->first();

        // 2. Format Username: Alfanumerik murni (pola baru agar tidak bentrok dengan akun dummy test)
        // Contoh hasil: pkkmb2620269793011 (pkkmb26 + trx_id)
        $trxId          = !empty($masterUser?->trx_id) ? $masterUser->trx_id : $record->nim;
        $cleanTrxId     = preg_replace('/[^0-9]/', '', (string) $trxId);
        $moodleUsername = strtolower('pkkmb-26' . $cleanTrxId);

        // 3. Format Password berbasis nomor HP + Ubp@26
        $rawPhone     = preg_replace('/[^0-9]/', '', (string) ($user?->phone ?? $mahasiswa?->handphone ?? ''));
        $passwordBase = !empty($rawPhone) ? $rawPhone : (string) $cleanTrxId;
        $password     = $passwordBase . 'Ubp@26';

        // 4. Nama Depan & Belakang
        $rawNama   = trim($mahasiswa?->nama_mahasiswa ?? ($user?->name ?? 'Mahasiswa'));
        $cleanNama = preg_replace("/[`'’\"\\\]/", '', $rawNama);
        $cleanNama = preg_replace('/[^a-zA-Z0-9\s]/', ' ', $cleanNama);
        $cleanNama = trim(preg_replace('/\s+/', ' ', $cleanNama));

        $namaParts = explode(' ', $cleanNama);
        $firstname = trim(array_shift($namaParts) ?: 'Mahasiswa');
        $lastname  = !empty($namaParts) ? trim(implode(' ', $namaParts)) : $firstname;

        // 5. Email
        $rawEmail   = strtolower(trim($record->email_mhs ?? ($user?->email ?? '')));
        $cleanEmail = filter_var($rawEmail, FILTER_SANITIZE_EMAIL);
        if (empty($cleanEmail) || !filter_var($cleanEmail, FILTER_VALIDATE_EMAIL)) {
            $cleanEmail = strtolower($moodleUsername . '@mhs.ubpkarawang.ac.id');
        }

        // 6. Payload Moodle
        $payload = [
            'wstoken'            => $token,
            'wsfunction'         => 'core_user_create_users',
            'moodlewsrestformat' => 'json',
            'users' => [
                [
                    'username'   => $moodleUsername,
                    'password'   => $password,
                    'firstname'  => $firstname,
                    'lastname'   => $lastname,
                    'email'      => $cleanEmail,
                    'idnumber'   => (string) $cleanTrxId,
                    'department' => (string) ($record->kode_prodi ?? '00000'),
                    'city'       => 'Karawang',
                    'country'    => 'ID',
                ]
            ]
        ];

        try {
            $response = Http::asForm()->post($serverUrl, $payload);
            $resJson  = $response->json();

            // 1. Respon Sukses (Kembalian array berisi ID user yang baru dibuat)
            if (is_array($resJson) && isset($resJson[0]['id'])) {
                $record->update(['message_elearning' => 'Success']);
                Log::info("E-Learning: Sukses buat akun {$moodleUsername} (Moodle User ID: {$resJson[0]['id']})");

                return [
                    'success' => true,
                    'message' => "Akun {$moodleUsername} berhasil dibuat di E-Learning (ID: {$resJson[0]['id']})"
                ];
            }

            $errorMsg  = $resJson['message'] ?? ($resJson['errorcode'] ?? $response->body());
            $errorCode = $resJson['errorcode'] ?? null;

            // 2. Deteksi jika akun sudah terdaftar sebelumnya
            if (
                str_contains(strtolower($errorMsg), 'already') ||
                str_contains(strtolower($errorMsg), 'exists') ||
                str_contains(strtolower($errorMsg), 'duplicate')
            ) {
                $record->update(['message_elearning' => 'Success']);
                Log::info("E-Learning: {$moodleUsername} sudah terdaftar sebelumnya.");
                return [
                    'success' => true,
                    'message' => "Akun {$moodleUsername} sudah terdaftar sebelumnya di E-Learning."
                ];
            }

            // 3. Catat Error
            $cleanError = strip_tags($errorMsg);
            if (isset($resJson['debuginfo'])) {
                $cleanError .= ' | ' . strip_tags($resJson['debuginfo']);
            }

            $record->update(['message_elearning' => substr($cleanError, 0, 255)]);

            Log::warning("E-Learning Gagal: {$moodleUsername} - {$cleanError}", [
                'payload_sent'      => $payload['users'][0],
                'http_status'       => $response->status(),
                'moodle_errorcode'  => $errorCode,
                'raw_response'      => $response->body(),
            ]);

            return [
                'success' => false,
                'message' => "Moodle Error: {$cleanError}"
            ];

        } catch (\Throwable $e) {
            $record->update(['message_elearning' => substr($e->getMessage(), 0, 255)]);
            Log::error("E-Learning Exception: {$moodleUsername} - " . $e->getMessage());

            return [
                'success' => false,
                'message' => "Exception: " . $e->getMessage()
            ];
        }
    }

    /**
     * Utilitas diagnosa: mencoba mendeteksi field mana yang membuat Moodle
     * menolak pembuatan user, dengan memakai akun uji-coba yang aman
     * (tidak memakai data asli mahasiswa selain field yang sedang diuji),
     * lalu menghapusnya kembali setelah selesai diuji.
     */
    private function diagnoseElearningRequiredField(string $serverUrl, string $token, array $realFields): ?array
    {
        $uniqueTag = substr(md5(uniqid('', true)), 0, 8);

        $safeBaseline = [
            'username'  => "diagtest-{$uniqueTag}1",
            'password'  => 'DiagTest@26',
            'firstname' => 'Diag',
            'lastname'  => 'Test',
            'email'     => "diagtest.{$uniqueTag}@mhs.ubpkarawang.ac.id",
        ];

        foreach (array_keys($safeBaseline) as $fieldUnderTest) {
            $testUser = $safeBaseline;

            if ($fieldUnderTest === 'username') {
                // JANGAN gunakan username asli mahasiswa secara langsung di sini.
                // Cukup uji apakah KARAKTER/FORMAT-nya yang membuat Moodle menolak,
                // dengan menempelkannya ke prefix unik yang mustahil bentrok dengan
                // akun mahasiswa manapun.
                $testUser['username'] = 'diagfmt-' . $uniqueTag . '-' . $realFields['username'];
            } else {
                $testUser[$fieldUnderTest] = $realFields[$fieldUnderTest];
                $testUser['username']      = $safeBaseline['username'] . $fieldUnderTest;
            }

            $testPayload = [
                'wstoken'            => $token,
                'wsfunction'         => 'core_user_create_users',
                'moodlewsrestformat' => 'json',
                'users'              => [$testUser],
            ];

            $resp = Http::asForm()->post($serverUrl, $testPayload);
            $json = $resp->json();

            if (is_array($json) && isset($json[0]['id'])) {
                $delResp = Http::asForm()->post($serverUrl, [
                    'wstoken'            => $token,
                    'wsfunction'         => 'core_user_delete_users',
                    'moodlewsrestformat' => 'json',
                    'userids'            => [$json[0]['id']],
                ]);
                $delJson = $delResp->json();

                // core_user_delete_users normalnya mengembalikan array kosong []
                // ketika berhasil. Kalau bukan itu yang didapat, JANGAN diam-diam
                // dilewati — catat sebagai error keras supaya admin tahu ada akun
                // uji-coba yang harus dihapus manual.
                $deleteOk = is_array($delJson) && count($delJson) === 0;

                if (!$deleteOk) {
                    Log::error(
                        "E-Learning DIAGNOSTIC: GAGAL menghapus akun uji coba diagnostik "
                        . "(Moodle User ID: {$json[0]['id']}, username: {$testUser['username']}). "
                        . "Akun sampah ini TERTINGGAL di Moodle dan HARUS dihapus manual "
                        . "lewat admin Moodle untuk menghindari kebocoran akun palsu.",
                        ['delete_response' => $delResp->body()]
                    );
                }

                continue;
            }

            return [
                'field'        => $fieldUnderTest,
                'value'        => $realFields[$fieldUnderTest] ?? null,
                'raw_response' => $resp->body(),
            ];
        }

        return null;
    }
}