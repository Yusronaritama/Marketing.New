<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\JadwalUjianFarmasi;
use Carbon\Carbon;

class JadwalUjianController extends Controller
{
    public function create()
    {
        $jadwalAktif = JadwalUjianFarmasi::where('is_active', true)->latest()->first();
        return view('admin.jadwal.create', compact('jadwalAktif'));
    }

    public function store(Request $request)
    {
        // 1. Validasi: Ubah 'required' jadi 'nullable'
        $request->validate([
            'tanggal_ujian' => 'nullable|date', // Boleh kosong
            'waktu_ujian'   => 'nullable',      // Boleh kosong
            'deskripsi'     => 'required|string|max:1000', // Deskripsi tetap wajib
        ]);

        // 2. Non-aktifkan jadwal lama
        JadwalUjianFarmasi::where('is_active', true)->update(['is_active' => false]);

        // 3. Simpan Baru
        // Jika form kosong, Laravel akan otomatis menyimpan NULL ke database
        JadwalUjianFarmasi::create([
            'tanggal_ujian' => $request->tanggal_ujian,
            'waktu_ujian'   => $request->waktu_ujian,
            'deskripsi'     => $request->deskripsi,
            'is_active'     => true
        ]);

        return back()->with('success', 'Jadwal Ujian Farmasi berhasil diperbarui!');
    }

    // --- LOGIKA HAPUS / RESET JADWAL ---
    public function destroy()
    {
        JadwalUjianFarmasi::where('is_active', true)->update(['is_active' => false]);

        return back()->with('success', 'Jadwal berhasil dihapus. Pengumuman ditutup.');
    }
}