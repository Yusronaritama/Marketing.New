<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MasterUser;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

class ProdiPageController extends Controller
{
    /**
     * Mendapatkan Kode Prodi berdasarkan Role User yang Login
     */
    private function getProdiCodeByRole()
    {
        $role = Auth::user()->role;

        // Mapping Role ke Kode Prodi sesuai data seeder/database Anda
        $map = [
            'admin_teknik_mesin'        => '21201',
            'admin_teknik_industri'     => '26201',
            'admin_teknik_informatika'  => '55201',
            'admin_sistem_informasi'    => '57201',
            'admin_manajemen_s2'        => '61114',
            'admin_akuntansi'           => '62201',
            'admin_psikologi'           => '73201',
            'admin_ilmu_hukum'          => '74201',
            'admin_pendidikan_guru_sekolah_dasar' => '86206',
            'admin_pendidikan_pancasila_dan_kewarganegaraan' => '87205',
            'admin_pendidikan_agama_islam' => '86230',
            'admin_manajemen'           => '61201',
        ];

        return $map[$role] ?? null;
    }

    public function index(Request $request)
    {
        $userdatas = user::with('MasterUser:id,user_id,trx_id')
                    ->select('id','email','phone')
                    ->get()
                    ->keyBy('email');
                    
                    
        $prodiCode = $this->getProdiCodeByRole();

        if (!$prodiCode) {
            return abort(403, 'Role Anda tidak terdaftar sebagai Admin Program Studi.');
        }

        $search = $request->get('search');
        $query = DB::table('master_moodle_lulus')
            ->where('prodi_lulus', $prodiCode);

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('nama_lengkap', 'LIKE', "%$search%")
                  ->orWhere('email', 'LIKE', "%$search%");
            });
        }

        $data = $query->paginate(20);
        $allEmails = $query->pluck('email')->toArray();

        $stats = [
            'total'    => count($allEmails),
            'verified' => DB::table('prodi_verifications')->whereIn('email', $allEmails)->where('status_verifikasi', 'verified')->count(),
            'rejected' => DB::table('prodi_verifications')->whereIn('email', $allEmails)->where('status_verifikasi', 'rejected')->count(),
            'pending'  => DB::table('prodi_verifications')->whereIn('email', $allEmails)->where('status_verifikasi', 'pending')->count(),
        ];
        
        $stats['unprocessed'] = $stats['total'] - ($stats['verified'] + $stats['rejected'] + $stats['pending']);

        return view('admin.prodipage.index', compact('data', 'stats', 'search', 'userdatas'));
    }

    public function validateStep(Request $request, $id)
    {
        $moodleLulus = DB::table('master_moodle_lulus')->where('id', $id)->first();

        if (!$moodleLulus) {
            return redirect()->back()->with('error', 'Data tidak ditemukan.');
        }

        // Cari user & master_ereg
        $user = DB::table('users')->where('email', $moodleLulus->email)->first();
        if (!$user) return redirect()->back()->with('error', 'User tidak ditemukan.');

        $masterEreg = DB::table('master_ereg')->where('user_id', $user->id)->first();
        if (!$masterEreg) return redirect()->back()->with('error', 'Data pendaftaran tidak ditemukan.');

        // Simpan ke tabel verifikasi prodi
        DB::table('prodi_verifications')->updateOrInsert(
            [
                'user_id'        => $user->id,
                'master_ereg_id' => $masterEreg->id,
            ],
            [
                'nama_lengkap'      => $moodleLulus->nama_lengkap,
                'email'             => $moodleLulus->email,
                'kode_prodi'        => $moodleLulus->prodi_lulus,
                'status_verifikasi' => $request->status_verifikasi,
                'verified_by'       => Auth::id(),
                'nama_verifikator'  => Auth::user()->name,
                'tanggal_verifikasi'=> now(),
                'keterangan'        => $request->keterangan ?? 'Validated by Admin Prodi',
                'updated_at'        => now(),
                'created_at'        => DB::raw('IFNULL(created_at, NOW())'),
            ]
        );

        return redirect()->back()->with('success', 'Verifikasi ' . $moodleLulus->nama_lengkap . ' berhasil diperbarui.');
    }
}