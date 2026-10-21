<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PromoController extends Controller
{
    public function promoUsage(Request $request)
    {
        if (Auth::user()->role !== 'admin') {
            abort(403, 'Akses Ditolak.');
        }

        // 1. Ambil semua daftar kode promo untuk dropdown filter
        $promoCodes = DB::table('promo_codes')
            ->orderBy('created_at', 'desc')
            ->get();

        // 2. Ambil daftar unik "Jalur Pendaftaran" dari master_ereg
        $jalurPendaftaranList = DB::table('master_ereg')
            ->select('jalur_pendaftaran')
            ->whereNotNull('jalur_pendaftaran')
            ->distinct()
            ->orderBy('jalur_pendaftaran', 'asc')
            ->pluck('jalur_pendaftaran');

        // ===============================================================
        // 3. QUERY UTAMA (DIPERBAIKI: Menggunakan tabel master_users)
        // ===============================================================
        $query = DB::table('master_users')
            ->join('users', 'users.id', '=', 'master_users.user_id')
            ->leftJoin('promo_codes', 'promo_codes.code', '=', 'master_users.promo_code_used')
            ->leftJoin('master_ereg', 'master_ereg.user_id', '=', 'master_users.user_id')
            ->leftJoin('program_studi', 'program_studi.kode_prodi', '=', 'master_ereg.jurusan1')
            ->whereNotNull('master_users.promo_code_used')
            ->where('master_users.promo_code_used', '!=', '');

        // Logika Filter 1: Kode Promo
        if ($request->filled('kode_promo')) {
            $query->where('master_users.promo_code_used', $request->kode_promo);
        }

        // Logika Filter 2: Jalur Pendaftaran
        if ($request->filled('jalur_pendaftaran')) {
            $query->where('master_ereg.jalur_pendaftaran', $request->jalur_pendaftaran);
        }

        // ===============================================================
        // 4. Hitung Total Keseluruhan
        // ===============================================================
        $totalTagihanAsli  = (clone $query)->sum('master_users.original_amount');
        $totalDiskon       = (clone $query)->sum('master_users.discount_amount');
        $totalTagihanAkhir = (clone $query)->sum('master_users.va_amount');
        $totalPengguna     = (clone $query)->count(DB::raw('DISTINCT master_users.id'));

        // ===============================================================
        // 5. Hitung Khusus Mahasiswa S2 (MENGABAIKAN PROMO)
        // ===============================================================
        $queryS2 = DB::table('master_users')
            ->join('master_ereg', 'master_ereg.user_id', '=', 'master_users.user_id')
            // Anggap 61114 adalah prodi S2, pastikan tidak tertukar
            ->where('master_ereg.jurusan1', '61114'); 
        
        if ($request->filled('jalur_pendaftaran')) {
            $queryS2->where('master_ereg.jalur_pendaftaran', $request->jalur_pendaftaran);
        }

        $totalPenggunaS2   = $queryS2->count(DB::raw('DISTINCT master_users.id'));
        $totalPendapatanS2 = $queryS2->sum('master_users.va_amount');

        // ===============================================================
        // 6. Ambil data dengan Pagination
        // ===============================================================
        $penggunaPromo = $query->select(
            // Nama & Email
            DB::raw('COALESCE(master_ereg.nama, users.name) as name'),
            DB::raw('COALESCE(master_ereg.gmail, users.email) as email'),
            
            // Kolom dari master_users
            'master_users.va_status',
            'master_users.promo_code_used',
            'master_users.original_amount',
            'master_users.discount_amount',
            'master_users.va_amount as final_amount',
            'master_users.paid_at', // <-- Memakai paid_at asli dari master_users
            
            // Relasi
            'promo_codes.description as asal_sekolah',
            'promo_codes.discount as discount_percentage',
            'program_studi.nama_prodi',
            'program_studi.jenjang', // <-- Mengatasi UI Jenjang yang tidak terdeteksi
            'master_ereg.jalur_pendaftaran'
        )
        ->orderBy('master_users.created_at', 'desc')
        ->paginate(25);

        // Pertahankan query string di pagination
        $penggunaPromo->appends($request->all());

        return view('admin.promo.usage', compact(
            'penggunaPromo', 'promoCodes', 'jalurPendaftaranList',
            'totalTagihanAsli', 'totalDiskon', 'totalTagihanAkhir', 'totalPengguna',
            'totalPenggunaS2', 'totalPendapatanS2'
        ));
    }
}