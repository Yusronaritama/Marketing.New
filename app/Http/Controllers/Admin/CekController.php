<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CekController extends Controller
{
    public function index(Request $request)
    {
        // Inisiasi query dasar
        $query = DB::table('master_moodle_lulus')
            ->select(
                'master_moodle_lulus.*',
                'users.phone',
                'master_users.gelombang'
            )
            ->leftJoin('users', 'master_moodle_lulus.email', '=', 'users.email')
            ->leftJoin('master_users', 'users.id', '=', 'master_users.user_id')
            ->where(function ($q) {
                $q->where('master_moodle_lulus.nama_prodi_lulus', 'Unknown')
                  ->orWhereNull('master_moodle_lulus.prodi_lulus');
            });

        // Filter berdasarkan tanggal lulus (Format input dari user: DD-MM-YYYY HH:mm)
        if ($request->filled('tanggal_lulus')) {
            try {
                // Ubah format 17-04-2026 15:37 menjadi 2026-04-17 15:37
                $formattedDate = Carbon::createFromFormat('d-m-Y H:i', $request->tanggal_lulus)->format('Y-m-d H:i');
                // Gunakan LIKE agar mengabaikan detik (karena DB mungkin menyimpan detik .00)
                $query->where('master_moodle_lulus.tanggal_lulus', 'like', $formattedDate . '%');
            } catch (\Exception $e) {
                // Abaikan filter jika format tanggal yang dimasukkan tidak valid
            }
        }

        // Filter berdasarkan nilai quiz
        if ($request->filled('nilai')) {
            $query->where('master_moodle_lulus.nilai_quiz', $request->nilai);
        }

        $lulusanUnknown = $query->orderBy('master_moodle_lulus.tanggal_lulus', 'desc')->get();

        return view('admin.cek.index', compact('lulusanUnknown'));
    }
public function filterSpesifik(Request $request)
{
    $filterNama    = $request->query('nama');
    $filterBiodata = $request->query('biodata'); 
    $filterEreg    = $request->query('ereg'); 
    $filterTanggal = $request->query('tanggal'); 

    $query = DB::table('master_moodle_lulus')
        ->select(
            'master_moodle_lulus.*',
            'users.phone',
            'master_users.gelombang',
            'biodatas.id as biodata_id',
            'master_ereg.id as ereg_id'
        )
        ->leftJoin('users', 'master_moodle_lulus.email', '=', 'users.email')
        ->leftJoin('master_users', 'users.id', '=', 'master_users.user_id')
        ->leftJoin('biodatas', 'users.id', '=', 'biodatas.user_id')
        ->leftJoin('master_ereg', 'users.id', '=', 'master_ereg.user_id')
        ->where('master_moodle_lulus.nilai_quiz', 50);

    // Filter Nama (Searching)
    if ($request->filled('nama')) {
        $query->where('master_moodle_lulus.nama_lengkap', 'like', '%' . $filterNama . '%');
    }

    if ($request->filled('tanggal')) {
        $query->where('master_moodle_lulus.tanggal_lulus', 'like', $filterTanggal . '%');
    }

    if ($filterBiodata === 'sudah') $query->whereNotNull('biodatas.id');
    elseif ($filterBiodata === 'belum') $query->whereNull('biodatas.id');

    if ($filterEreg === 'sudah') $query->whereNotNull('master_ereg.id');
    elseif ($filterEreg === 'belum') $query->whereNull('master_ereg.id');

    $lulusanFilter = $query->orderBy('master_moodle_lulus.tanggal_lulus', 'desc')->get();

    return view('admin.cek.filter_spesifik', compact('lulusanFilter', 'filterBiodata', 'filterEreg', 'filterTanggal', 'filterNama'));
}
public function destroy($id)
{
    try {
        // Hapus data berdasarkan ID primer
        DB::table('master_moodle_lulus')->where('id', $id)->delete();
        
        return back()->with('success', 'Data lulusan berhasil dihapus dari sistem.');
    } catch (\Exception $e) {
        return back()->with('error', 'Gagal menghapus data. Silakan coba lagi.');
    }
}
}