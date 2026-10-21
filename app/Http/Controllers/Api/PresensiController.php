<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class PresensiController extends Controller
{
    /**
     * Helper untuk response JSON dengan CORS Header
     */
    private function jsonResponse(array $payload, int $status = 200)
    {
        return response()->json($payload, $status, [
            'Access-Control-Allow-Origin'  => '*',
            'Access-Control-Allow-Methods' => 'GET, POST, OPTIONS',
            'Access-Control-Allow-Headers' => 'Content-Type, Authorization',
        ]);
    }

    /**
     * 1. Endpoint Presensi (Masuk & Pulang)
     * Route: POST/GET /presensi/presensi
     */
    public function presensi(Request $request)
    {
        // Handle CORS preflight
        if ($request->isMethod('OPTIONS')) {
            return $this->jsonResponse(['status' => 'ok'], 200);
        }

        $idPendaftar = $request->input('id_pendaftar') 
                    ?? $request->input('id') 
                    ?? $request->input('qr_code') 
                    ?? $request->input('qrcode') 
                    ?? $request->input('trx_id');
        $status      = strtolower(trim((string) ($request->input('status') ?? 'masuk')));
        $email       = trim((string) $request->input('email'));

        if (empty($idPendaftar)) {
            return $this->jsonResponse([
                'messages' => 'Parameter id_pendaftar wajib diisi',
            ], 400);
        }

        // Ekstrak angka jika formatnya seperti "Nama - 202612345" atau "TRX-..."
        if (preg_match('/(?:.*-\s*)?([0-9]{5,20})/', (string) $idPendaftar, $matches)) {
            $extractedId = $matches[1];
        } else {
            $extractedId = (string) $idPendaftar;
        }

        // Cek apakah peserta terdaftar (di master_virtual, pendaftarans, data_mahasiswa_finals, master_users, atau pendaftaran)
        $cek = DB::table('master_virtual')
            ->where('va', $idPendaftar)
            ->orWhere('va', $extractedId)
            ->orWhere('trx_id', $idPendaftar)
            ->first();

        if (!$cek) {
            $cek = DB::table('pendaftarans')
                ->where('id', $idPendaftar)
                ->orWhere('id', $extractedId)
                ->orWhere('user_id', $idPendaftar)
                ->first();
        }

        if (!$cek) {
            $cek = DB::table('data_mahasiswa_finals')
                ->where('id', $idPendaftar)
                ->orWhere('id', $extractedId)
                ->orWhere('user_id', $idPendaftar)
                ->first();
        }

        if (!$cek) {
            $cek = DB::table('master_users')
                ->where('trx_id', $idPendaftar)
                ->orWhere('trx_id', $extractedId)
                ->orWhere('user_id', $idPendaftar)
                ->first();
        }

        if (!$cek) {
            $cek = DB::table('slik_billings')
                ->where('trx_id', $idPendaftar)
                ->orWhere('trx_id', $extractedId)
                ->orWhere('virtual_account', $idPendaftar)
                ->orWhere('virtual_account', $extractedId)
                ->first();
        }

        if (!$cek && Schema::hasTable('pendaftaran')) {
            $cek = DB::table('pendaftaran')
                ->where('id', $idPendaftar)
                ->orWhere('id', $extractedId)
                ->first();
        }

        if (!$cek) {
            return $this->jsonResponse([
                'messages' => 'Peserta tidak terdaftar',
            ], 404);
        }

        $todayDate = date('Y-m-d');
        $currentTime = date('H:i:s');
        $idKey = (string) $idPendaftar;

        if ($status === 'masuk') {
            $checkAbsensi = DB::table('absensi_pmb')
                ->where('id_pendaftar', $idKey)
                ->where('tanggal', $todayDate)
                ->whereNotNull('jam_masuk')
                ->first();

            if ($checkAbsensi) {
                return $this->jsonResponse([
                    'messages' => 'Anda sudah absensi masuk sebelumnya',
                ], 401);
            } else {
                DB::table('absensi_pmb')->insert([
                    'id_pendaftar' => $idKey,
                    'jam_masuk'    => $currentTime,
                    'tanggal'      => $todayDate,
                    'email_masuk'  => $email,
                    'created_at'   => now(),
                    'updated_at'   => now(),
                ]);

                return $this->jsonResponse([
                    'messages'    => 'Absensi masuk berhasil',
                    'status_code' => '000',
                ], 200);
            }
        } else {
            // Status pulang
            $checkAbsensi = DB::table('absensi_pmb')
                ->where('id_pendaftar', $idKey)
                ->where('tanggal', $todayDate)
                ->first();

            if ($checkAbsensi) {
                if (!is_null($checkAbsensi->jam_pulang)) {
                    return $this->jsonResponse([
                        'messages' => 'Anda sudah presensi pulang',
                    ], 401);
                } else {
                    DB::table('absensi_pmb')
                        ->where('id_pendaftar', $idKey)
                        ->where('tanggal', $todayDate)
                        ->update([
                            'jam_pulang'   => $currentTime,
                            'email_pulang' => $email,
                            'updated_at'   => now(),
                        ]);

                    return $this->jsonResponse([
                        'messages'    => 'presensi pulang berhasil',
                        'status_code' => '000',
                    ], 200);
                }
            } else {
                return $this->jsonResponse([
                    'messages' => 'Anda belum presensi masuk',
                ], 404);
            }
        }
    }

    /**
     * Alias method untuk handlePresensi jika ada sistem lama yang memanggilnya
     */
    public function handlePresensi(Request $request)
    {
        return $this->presensi($request);
    }

    /**
     * 2. Endpoint Daftar Presensi by Email (Masuk & Pulang)
     * Route: POST/GET /presensi/daftar_presensi_by_email
     */
    public function daftar_presensi_by_email(Request $request)
    {
        if ($request->isMethod('OPTIONS')) {
            return $this->jsonResponse(['status' => 'ok'], 200);
        }

        $email = trim((string) ($request->input('email') ?? ''));

        $data = DB::table('absensi_pmb')
            ->leftJoin('master_virtual', 'master_virtual.va', '=', 'absensi_pmb.id_pendaftar')
            ->where('absensi_pmb.email_masuk', $email)
            ->orWhere('absensi_pmb.email_pulang', $email)
            ->select('absensi_pmb.*', 'master_virtual.va', 'master_virtual.nama')
            ->orderBy('absensi_pmb.id', 'desc')
            ->get();

        return $this->jsonResponse([
            'messages'    => 'Success Get Data',
            'status_code' => '000',
            'data'        => $data,
        ], 200);
    }

    /**
     * 3. Endpoint Daftar Presensi by Email Pulang
     * Route: POST/GET /presensi/daftar_presensi_by_email_pulang
     */
    public function daftar_presensi_by_email_pulang(Request $request)
    {
        if ($request->isMethod('OPTIONS')) {
            return $this->jsonResponse(['status' => 'ok'], 200);
        }

        $email = trim((string) ($request->input('email') ?? ''));

        $data = DB::table('absensi_pmb')
            ->leftJoin('master_virtual', 'master_virtual.va', '=', 'absensi_pmb.id_pendaftar')
            ->where('absensi_pmb.email_pulang', $email)
            ->select('absensi_pmb.*', 'master_virtual.va', 'master_virtual.nama')
            ->orderBy('absensi_pmb.id', 'desc')
            ->get();

        return $this->jsonResponse([
            'messages'    => 'Success Get Data',
            'status_code' => '000',
            'data'        => $data,
        ], 200);
    }
}
