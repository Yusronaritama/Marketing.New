<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DataMahasiswaFinal;
use App\Models\SlikBilling;
use Illuminate\Support\Facades\Auth;

class MahasiswaDataController extends Controller
{
    /**
     * Menampilkan Form Data Final (PDDIKTI)
     * Diakses dari Dashboard setelah pembayaran lunas.
     */
    public function index()
    {
        $user = Auth::user();
        
        // Cek Pembayaran lagi untuk keamanan ganda (selain middleware)
        $billing = SlikBilling::where('user_id', $user->id)->latest()->first();
        $isPaid = $billing && ($billing->status == 'paid' || $billing->status == 'settled');

        if (!$isPaid) {
            return redirect()->route('mahasiswa.pembayaran.ukt')
                ->with('error', 'Mohon lunasi pembayaran sebelum mengisi Data Mahasiswa.');
        }

        // Ambil data jika sudah pernah diisi
        $data = DataMahasiswaFinal::firstOrNew(['user_id' => $user->id]);
        
        // Ambil biodata awal untuk auto-fill form
        $biodataAwal = $user->biodata;

        return view('mahasiswa.data.index', compact('data', 'biodataAwal', 'user'));
    }

    /**
     * Menyimpan Data Final
     */
    public function store(Request $request)
    {
        // 1. Validasi Data Lengkap (Sesuai standar PDDIKTI)
        $validated = $request->validate([
            // Pribadi
            'nama_mahasiswa' => 'required|string|max:100',
            'jenis_kelamin' => 'required|in:L,P',
            'nik' => 'required|numeric|digits:16',
            'nisn' => 'required|string|size:10',
            'tempat_lahir' => 'required|string|max:32',
            'tanggal_lahir' => 'required|date',
            'email' => 'required|email',
            'handphone' => 'required|string|max:20',
            'kewarganegaraan' => 'required|string|max:2',
            'id_agama' => 'required|integer',

            // Alamat
            'jalan' => 'required|string|max:80',
            'rt' => 'nullable|numeric',
            'rw' => 'nullable|numeric',
            'dusun' => 'nullable|string|max:60',
            'kelurahan' => 'required|string|max:60',
            'kode_pos' => 'nullable|numeric',
            'id_wilayah' => 'required|string', 
            'id_jenis_tinggal' => 'nullable|integer',
            'id_alat_transportasi' => 'nullable|integer',

            // Ayah
            'nama_ayah' => 'nullable|string|max:100',
            'nik_ayah' => 'nullable|numeric|digits:16',
            'tanggal_lahir_ayah' => 'nullable|date',
            'id_jenjang_pendidikan_ayah' => 'nullable|integer',
            'id_pekerjaan_ayah' => 'nullable|integer',
            'id_penghasilan_ayah' => 'nullable|integer',

            // Ibu
            'nama_ibu_kandung' => 'required|string|max:100',
            'nik_ibu' => 'nullable|numeric|digits:16',
            'tanggal_lahir_ibu' => 'nullable|date',
            'id_jenjang_pendidikan_ibu' => 'nullable|integer',
            'id_pekerjaan_ibu' => 'nullable|integer',
            'id_penghasilan_ibu' => 'nullable|integer',

            // KPS
            'penerima_kps' => 'required|boolean',
            'no_kps' => 'nullable|required_if:penerima_kps,1|string|max:80',
        ]);

        $user = auth()->user();

        // 2. Simpan ke Tabel DataMahasiswaFinal
        DataMahasiswaFinal::updateOrCreate(
            ['user_id' => $user->id],
            $validated
        );

        // 3. Update Step MasterEreg ke 5 (Menandakan data final sudah diisi)
        $user->masterEreg()->update([
            'step_completed' => 5
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Data Mahasiswa berhasil disimpan.',
            'redirect_url' => route('mahasiswa.ringkasan')
        ]);
    }
}