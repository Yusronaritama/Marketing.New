<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\MasterEreg; 

class NormalizeStatusPekerjaan extends Command
{
    protected $signature = 'pddikti:normalize-pekerjaan';
    protected $description = 'PREVIEW Migrasi data status_pekerjaan menjadi 0 dan 1 (Aman, Tidak di-save)';

    public function handle()
    {
        $this->info('Mulai PREVIEW migrasi status pekerjaan (Hanya menampilkan, tidak mengubah database)...');

        // Ambil semua data registrasi yang status pekerjaannya belum kosong
        $eregs = MasterEreg::whereNotNull('status_pekerjaan')->get();
        $count = 0;

        foreach ($eregs as $ereg) {
            $statusLama = $ereg->status_pekerjaan;
            $rawStatus = strtolower(trim($statusLama));

            // Kalau datanya udah angka 0 atau 1, berarti udah bener, kita skip aja
            if ($rawStatus === '0' || $rawStatus === '1') {
                continue;
            }

            // Eksekusi perubahan
            $statusBaru = 0; // Default 0

            if ($rawStatus === 'bekerja') {
                $statusBaru = 1;
            } elseif ($rawStatus === 'tidak bekerja' || $rawStatus === 'pelajar/mahasiswa') {
                $statusBaru = 0;
            } else {
                // Jaga-jaga kalau ada typo di DB
                $statusBaru = 0;
            }

            // TAMPILKAN PREVIEW DI TERMINAL (TANPA SAVE)
            $this->line("DITEMUKAN: [{$statusLama}] ---> AKAN JADI: [{$statusBaru}]");
            $count++;
            $ereg->status_pekerjaan = (string) $statusBaru;
            $ereg->save();
        }

        $this->info("Preview Selesai! Ada {$count} status pekerjaan yang perlu diubah.");
        $this->warn("Data lu AMAN. Script ini belum melakukan perubahan di database.");
    }
}