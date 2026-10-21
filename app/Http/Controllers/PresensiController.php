<?php

namespace App\Http\Controllers;

use App\Models\AbsensiPmb;
use App\Models\DataMahasiswaFinal;
use App\Models\MasterUser;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PresensiController extends Controller
{
    /**
     * Header CORS default untuk akses scanner eksternal / mobile app.
     */
    private function corsHeaders(): array
    {
        return [
            'Access-Control-Allow-Origin'  => '*',
            'Access-Control-Allow-Methods' => 'GET, POST, OPTIONS',
            'Access-Control-Allow-Headers' => 'Content-Type, Authorization, X-Requested-With',
        ];
    }

    /**
     * Respon JSON dengan CORS headers.
     */
    private function jsonResponse(array $data, int $status = 200): JsonResponse
    {
        return response()->json($data, $status, $this->corsHeaders(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    /**
     * POST /presensi/presensi
     * Input: id_pendaftar, status (masuk/pulang), email (email petugas scanner)
     */
    public function presensi(Request $request)
    {
        if ($request->isMethod('options')) {
            return response('', 200, $this->corsHeaders());
        }

        $idPendaftar = trim((string) $request->input('id_pendaftar'));
        $status      = strtolower(trim((string) $request->input('status'))); // 'masuk' atau 'pulang'
        $email       = trim((string) $request->input('email'));
        $today       = Carbon::now('Asia/Jakarta')->format('Y-m-d');
        $currentTime = Carbon::now('Asia/Jakarta')->format('H:i:s');

        if (empty($idPendaftar)) {
            return $this->jsonResponse([
                'messages' => 'Parameter id_pendaftar wajib diisi',
            ], 400);
        }

        // Cek keberadaan pendaftar di berbagai tabel
        $pendaftar = MasterUser::where('trx_id', $idPendaftar)
            ->orWhere('va_number', $idPendaftar)
            ->first();

        if (!$pendaftar) {
            $nimRecord = DB::table('pendaftar_get_nim')
                ->where('nim', $idPendaftar)
                ->orWhere('user_id', $idPendaftar)
                ->first();

            if ($nimRecord) {
                $pendaftar = MasterUser::where('user_id', $nimRecord->user_id)->first();
            }
        }

        if (!$pendaftar) {
            $userRecord = User::where('id', $idPendaftar)
                ->orWhere('phone', $idPendaftar)
                ->orWhere('email', $idPendaftar)
                ->first();

            if ($userRecord) {
                $pendaftar = MasterUser::where('user_id', $userRecord->id)->first();
            }
        }

        if (!$pendaftar) {
            $dataFinal = DataMahasiswaFinal::where('id', $idPendaftar)
                ->orWhere('user_id', $idPendaftar)
                ->first();

            if ($dataFinal) {
                $pendaftar = MasterUser::where('user_id', $dataFinal->user_id)->first();
            }
        }

        if (!$pendaftar) {
            $slikRecord = DB::table('slik_billings')
                ->where('trx_id', $idPendaftar)
                ->orWhere('virtual_account', $idPendaftar)
                ->first();

            if ($slikRecord) {
                $pendaftar = MasterUser::where('user_id', $slikRecord->user_id)->first();
            }
        }

        if (!$pendaftar) {
            return $this->jsonResponse([
                'messages' => 'Peserta tidak terdaftar',
            ], 404);
        }

        // Gunakan ID Pendaftar yang dikirim (atau trx_id)
        $cleanId = $pendaftar->trx_id ?? $idPendaftar;

        if ($status === 'masuk') {
            $checkAbsensi = AbsensiPmb::where(function ($q) use ($idPendaftar, $cleanId) {
                    $q->where('id_pendaftar', $idPendaftar)
                      ->orWhere('id_pendaftar', $cleanId);
                })
                ->where('tanggal', $today)
                ->whereNotNull('jam_masuk')
                ->first();

            if ($checkAbsensi) {
                return $this->jsonResponse([
                    'messages' => 'Anda sudah absensi masuk sebelumnya',
                ], 401);
            }

            AbsensiPmb::create([
                'id_pendaftar' => $cleanId,
                'jam_masuk'    => $currentTime,
                'tanggal'      => $today,
                'email_masuk'  => $email,
            ]);

            Log::info("Presensi MASUK: {$cleanId} oleh petugas {$email}");

            return $this->jsonResponse([
                'messages'    => 'Absensi masuk berhasil',
                'status_code' => '000',
            ], 200);

        } else {
            // Status: pulang
            $checkAbsensi = AbsensiPmb::where(function ($q) use ($idPendaftar, $cleanId) {
                    $q->where('id_pendaftar', $idPendaftar)
                      ->orWhere('id_pendaftar', $cleanId);
                })
                ->where('tanggal', $today)
                ->first();

            if ($checkAbsensi) {
                if (!is_null($checkAbsensi->jam_pulang)) {
                    return $this->jsonResponse([
                        'messages' => 'Anda sudah presensi pulang',
                    ], 401);
                }

                $checkAbsensi->update([
                    'jam_pulang'   => $currentTime,
                    'email_pulang' => $email,
                ]);

                Log::info("Presensi PULANG: {$cleanId} oleh petugas {$email}");

                return $this->jsonResponse([
                    'messages'    => 'presensi pulang berhasil',
                    'status_code' => '000',
                ], 200);

            } else {
                return $this->jsonResponse([
                    'messages' => 'Anda belum presensi masuk',
                ], 404);
            }
        }
    }

    /**
     * POST /presensi/daftar_presensi_by_email
     * Mengambil daftar presensi (masuk/pulang) berdasarkan email petugas scanner
     */
    public function daftar_presensi_by_email(Request $request)
    {
        if ($request->isMethod('options')) {
            return response('', 200, $this->corsHeaders());
        }

        $email = trim((string) $request->input('email'));

        $data = DB::table('absensi_pmb')
            ->leftJoin('master_users', 'master_users.trx_id', '=', 'absensi_pmb.id_pendaftar')
            ->leftJoin('users', 'users.id', '=', 'master_users.user_id')
            ->leftJoin('data_mahasiswa_finals', 'data_mahasiswa_finals.user_id', '=', 'master_users.user_id')
            ->leftJoin('master_ereg', 'master_ereg.user_id', '=', 'master_users.user_id')
            ->where(function ($q) use ($email) {
                $q->where('absensi_pmb.email_masuk', $email)
                  ->orWhere('absensi_pmb.email_pulang', $email);
            })
            ->select(
                'absensi_pmb.*',
                DB::raw('absensi_pmb.id_pendaftar as va'),
                DB::raw('COALESCE(data_mahasiswa_finals.nama_mahasiswa, master_ereg.nama, users.name, absensi_pmb.id_pendaftar) as nama')
            )
            ->orderBy('absensi_pmb.id', 'desc')
            ->get();

        return $this->jsonResponse([
            'messages'    => 'Success Get Data',
            'status_code' => '000',
            'data'        => $data,
        ], 200);
    }

    /**
     * POST /presensi/daftar_presensi_by_email_pulang
     * Mengambil daftar presensi pulang berdasarkan email petugas scanner
     */
    public function daftar_presensi_by_email_pulang(Request $request)
    {
        if ($request->isMethod('options')) {
            return response('', 200, $this->corsHeaders());
        }

        $email = trim((string) $request->input('email'));

        $data = DB::table('absensi_pmb')
            ->leftJoin('master_users', 'master_users.trx_id', '=', 'absensi_pmb.id_pendaftar')
            ->leftJoin('users', 'users.id', '=', 'master_users.user_id')
            ->leftJoin('data_mahasiswa_finals', 'data_mahasiswa_finals.user_id', '=', 'master_users.user_id')
            ->leftJoin('master_ereg', 'master_ereg.user_id', '=', 'master_users.user_id')
            ->where('absensi_pmb.email_pulang', $email)
            ->select(
                'absensi_pmb.*',
                DB::raw('absensi_pmb.id_pendaftar as va'),
                DB::raw('COALESCE(data_mahasiswa_finals.nama_mahasiswa, master_ereg.nama, users.name, absensi_pmb.id_pendaftar) as nama')
            )
            ->orderBy('absensi_pmb.id', 'desc')
            ->get();

        return $this->jsonResponse([
            'messages'    => 'Success Get Data',
            'status_code' => '000',
            'data'        => $data,
        ], 200);
    }
}
