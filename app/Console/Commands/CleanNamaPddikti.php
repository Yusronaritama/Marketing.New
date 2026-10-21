<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\DataMahasiswaFinal; 

class CleanNamaPddikti extends Command
{
    protected $signature = 'pddikti:clean-nama';
    // Deskripsi diganti biar nggak mengecoh
    protected $description = 'Membersihkan dan menyimpan nama mahasiswa tanpa gelar untuk PDDIKTI (ACTION MODE)';

    public function handle()
    {
        $this->info('Mulai EKSEKUSI pembersihan nama di Database...');

        // CUMA ngambil nama yang ada tanda KOMA (,) atau TITIK (.) biar terminal lu nggak nyepam
        $mahasiswa = DataMahasiswaFinal::whereNotNull('nama_mahasiswa')
            ->where(function ($query) {
                $query->where('nama_mahasiswa', 'like', '%,%')
                      ->orWhere('nama_mahasiswa', 'like', '%.%');
            })
            ->get();
        
        $count = 0;

        foreach ($mahasiswa as $mhs) {
            $namaAsli = $mhs->nama_mahasiswa;
            $namaBersih = $namaAsli;

            // 1. Potong dari koma (untuk gelar seperti ", S.Kom")
            $namaBersih = explode(',', $namaBersih)[0];

            // 2. Hapus gelar spesifik yang pakai titik di belakang nama
            $namaBersih = preg_replace('/[.,]?\s*(S\.Pd\.I|S\.Pd|S\.E\.I|S\.E|SE|S\.Kom|M\.Kom|A\.Md(?:\.\w+)?|S\.T|ST|S\.H\.I|S\.H|SH|S\.Ak|S\.M|S\.Farm|Apt|M\.Si|S\.Ag|M\.Ag|M\.Pd\.I|M\.Pd|S\.Sos|S\.IP|S\.Tr(?:\.\w+)?)\b\.?/i', '', $namaBersih);

            // 3. Bersihin titik atau spasi yang nyangkut di akhir nama
            $namaBersih = rtrim($namaBersih, '. ');

            // 4. Kalau ada gelar awalan kayak "H." atau "Hj.", bersihin juga (Opsional)
            $namaBersih = preg_replace('/^(H\.|Hj\.)\s+/i', '', $namaBersih);

            // 5. Rapihin spasi dobel
            $namaBersih = trim(preg_replace('/\s+/', ' ', $namaBersih));

            // Tampilkan ke terminal KALAU namanya beneran berubah dan SAVE ke DB
            if ($namaAsli !== $namaBersih) {
                $this->line("DIUBAH: [{$namaAsli}] ---> MENJADI: [{$namaBersih}]");
                $count++;
                
                // Eksekusi Update ke Database
                $mhs->nama_mahasiswa = $namaBersih;
                $mhs->save();
            }
        }

        $this->info("Eksekusi Selesai! Total {$count} nama berhasil dibersihkan dan disimpan ke Database.");
    }
}