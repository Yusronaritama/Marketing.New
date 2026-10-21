<?php

namespace App\Http\Controllers\Mahasiswa\Profesi;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\BiodataProfesi; // Wajib dipanggil biar gak error dawg!
use Illuminate\Support\Facades\Auth;

class BiodataProfesiController extends Controller
{
    // Fungsi buat nampilin halaman UI (GET)
    public function create()
    {
        //Ambil data user yang sedang login sekalian load relasi biodatanya
        $user = Auth::user()->load('biodataProfesi');
        $biodata = $user->biodataProfesi;
    
        // Jika BELUM PERNAH ngisi biodata profesi, kita buat objek kosong 
        // lalu kita isi default-nya dari tabel users biar di Blade tinggal panggil secara seragam
        if (!$biodata) {
            $biodata = new \App\Models\BiodataProfesi();
            $biodata->nama = $user->name;
            $biodata->email_aktif = $user->email;
            $biodata->no_wa = $user->phone ?? ''; 
            $biodata->nim = $user->nim ?? '';
        }
    
        return view('mahasiswa.profesi.berkas.biodataprofesi', compact('biodata'));
    }
    
    

    // Fungsi buat nangkep dan nyimpen data pas disubmit (POST)
    public function store(Request $request)
    {
        $userId = Auth::id();

        // 1. Validasi Super Ketat Sesuai Kolom Database
        $validatedData = $request->validate([
            'nama'          => 'required|string|max:255',
            'tempat_lahir'  => 'required|string|max:255',
            'tanggal_lahir' => 'required|date',
            'asal_s1'       => 'required|string|max:255',
            'tahun_lulus'   => 'required|integer|digits:4|min:1900|max:' . (date('Y') + 1),
            'alamat'        => 'required|string',
            'no_wa'         => 'required|string|max:20',
            'nim'           => 'nullable|string|max:50',
            // Pastikan email unik, TAPI kecualikan baris milik user ini sendiri biar bisa di-update
            'email_aktif'   => 'required|email|max:255|unique:biodata_profesi,email_aktif,' . $userId . ',user_id',
        ], [
            // Bumbu pesan error biar bahasanya enak dibaca (Opsional)
            'required' => 'Kolom :attribute wajib diisi ya dawg!',
            'digits'   => 'Format :attribute harus 4 angka (Contoh: 2024)',
            'unique'   => ':attribute ini sudah terdaftar, pakai email lain.',
        ]);

        // 2. Eksekusi Simpan / Update ke Tabel biodata_profesi
        BiodataProfesi::updateOrCreate(
            ['user_id' => $userId], // Kunci pencariannya: ID Maba yang lagi login
            [
                // Kalau ketemu di-update, kalau nggak ketemu di-insert baru
                'nama'          => $validatedData['nama'],
                'tempat_lahir'  => $validatedData['tempat_lahir'],
                'tanggal_lahir' => $validatedData['tanggal_lahir'],
                'asal_s1'       => $validatedData['asal_s1'],
                'tahun_lulus'   => $validatedData['tahun_lulus'],
                'alamat'        => $validatedData['alamat'],
                'no_wa'         => $validatedData['no_wa'],
                'email_aktif'   => $validatedData['email_aktif'],
                'nim'           => $validatedData['nim'],
                'status'        => 'completed',
                
            ]
        );

        // 3. Tendang balik ke form dengan pesan sukses
        return redirect()->route('profesi.dashboard')->with('success', 'Mantap dawg! Biodata profesi berhasil disimpan! 🚀');
    }
}