<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ProgramStudi;

class RecalculateQuota extends Command
{
    protected $signature = 'quota:recalculate';
    protected $description = 'Hitung sisa kuota dengan rumus akurat (berdasarkan jurusan_lulusan)';

    public function handle()
    {
        $this->info('🔄 Sedang menghitung ulang kuota seluruh Prodi menggunakan Rumus Suci...');

        $prodis = ProgramStudi::orderBy('kode_prodi', 'asc')->get();
        
        $tabelData = [];
        $totalSeluruhMhs = 0;

        foreach ($prodis as $prodi) {
            
            // 1. PANGGIL RUMUS SUCI DARI MODEL!
            // Otomatis ngitung dari 'jurusan_lulusan' dan 'is_quota_taken' = 1
            $prodi->syncDayaTampung();

            // 2. Ambil data yang sudah terupdate untuk ditampilkan di tabel terminal
            $tabelData[] = [
                $prodi->kode_prodi,
                $prodi->nama_prodi,
                $prodi->daya_tampung ?? 0,     
                $prodi->jumlah_pendaftar,    // Angka akurat
                $prodi->kuota                // Sisa kursi akurat
            ];

            $totalSeluruhMhs += $prodi->jumlah_pendaftar;
        }

        // Tampilkan Tabel di Terminal (Biar enak kalau nge-debug via SSH)
        $headers = ['Kode', 'Nama Prodi', 'Daya Tampung', 'Jml Pendaftar', 'Sisa Kuota'];
        $this->table($headers, $tabelData);

        $this->info("✅ Selesai. Kuota 100% tersinkronisasi. Total Mahasiswa Lunas & Dapat Kursi: " . $totalSeluruhMhs);
        
        return Command::SUCCESS;
    }
}