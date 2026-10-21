<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ApiPendaftarController extends Controller
{
    public function getPendaftar(Request $request)
    {
        // 1. Validasi Token Admin
        $validToken = 'ListUsers'; 
        $token = $request->query('token') ?? $request->bearerToken();

        if (!$token || $token !== $validToken) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized. Token tidak valid.'
            ], 401);
        }

        // 2. Ambil parameter pencarian
        $search = $request->query('search');

        // PERUBAHAN DISINI: Jika parameter search tidak dikirim atau kosong
        if (empty($search)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Parameter pencarian diisi.'
            ], 400);
        }

        // Validasi panjang karakter sesuai request (min 5 digit)
        if (strlen($search) < 5) {
            return response()->json([
                'status' => 'error',
                'message' => 'Kata kunci pencarian (kode/nama) harus minimal 5 karakter.'
            ], 400);
        }

        // 3. Jalankan Query jika validasi di atas lolos
        try {
            $pendaftar = DB::table('users')
                ->join('master_users', 'users.id', '=', 'master_users.user_id')
                ->join('master_moodle_lulus', 'master_users.id', '=', 'master_moodle_lulus.master_user_id')
                ->select(
                    'users.name as nama',
                    'master_users.trx_id as kode_pendaftar',
                    'master_moodle_lulus.nama_prodi_lulus as prodi'
                )
                ->where(function($q) use ($search) {
                    $q->where('master_users.trx_id', 'LIKE', '%' . $search . '%')
                      ->orWhere('users.name', 'LIKE', '%' . $search . '%');
                })
                ->get();

            return response()->json([
                'status' => 'success',
                'total_data' => $pendaftar->count(),
                'data' => $pendaftar
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengambil data: ' . $e->getMessage()
            ], 500);
        }
    }
}