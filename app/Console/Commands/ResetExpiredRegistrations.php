<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\MasterEreg;
use App\Models\ProgramStudi;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ResetExpiredRegistrations extends Command
{
    /**
     * Tambahkan opsi {--dry-run} agar bisa mode simulasi
     */
    protected $signature = 'pmb:reset-expired {--dry-run : Hanya simulasi/cek data tanpa melakukan reset}';

    protected $description = 'Mereset pendaftaran kadaluwarsa. Gunakan --dry-run untuk simulasi.';

    public function handle()
    {
        // Cek apakah user menjalankan dengan flag --dry-run
        $isDryRun = $this->option('dry-run');

        if ($isDryRun) {
            $this->warn("=== MODE SIMULASI (DRY RUN) ===");
            $this->warn("Data tidak akan dihapus. Hanya menampilkan daftar target.");
        } else {
            $this->info("=== MODE EKSEKUSI ===");
            $this->info("Data akan direset secara permanen.");
        }
        
        // 1. Tentukan Batas Waktu
        $deadline = Carbon::now()->subDays(3);
        
        // 2. Cari User Zombie
        $expiredRegistrations = MasterEreg::where('status', '!=', 'completed')
                                ->where('step_completed', '<', 4)
                                ->where('created_at', '<', $deadline)
                                ->get();

        $count = $expiredRegistrations->count();
        
        if ($count == 0) {
            $this->info('Aman. Tidak ada data kadaluwarsa ditemukan.');
            return 0;
        }

        $this->info("Ditemukan {$count} data yang sudah expired.");

        foreach ($expiredRegistrations as $reg) {
            
            // JIKA MODE DRY RUN: Cuma print info, lalu lanjut ke user berikutnya
            if ($isDryRun) {
                $created = Carbon::parse($reg->created_at)->format('d M Y H:i');
                $this->line("- User ID: {$reg->user_id} | Daftar: {$created} | Prodi: {$reg->jurusan1} & {$reg->jurusan2}");
                continue; // STOP DISINI, JANGAN EKSEKUSI KE BAWAH
            }

            // JIKA BUKAN DRY RUN: Lakukan Reset & Sync (Logic Asli)
            try {
                $oldJurusan1 = $reg->jurusan1;
                $oldJurusan2 = $reg->jurusan2;

                $reg->forceFill([
                    'jurusan1' => null, 
                    'jurusan2' => null,
                    // 'alasan_jurusan_1' => null, 
                    // 'alasan_jurusan_2' => null,
                    'jalur_pendaftaran' => null, 
                    // 'kode_promo' => null, // Kode promo aman
                    'step_completed' => 1, 
                    'created_at' => Carbon::now(), 
                ])->save();

                if ($oldJurusan1) {
                    ProgramStudi::withoutGlobalScopes()->where('kode_prodi', $oldJurusan1)->first()?->syncDayaTampung();
                }
                if ($oldJurusan2) {
                    ProgramStudi::withoutGlobalScopes()->where('kode_prodi', $oldJurusan2)->first()?->syncDayaTampung();
                }

                $this->info("User ID {$reg->user_id} -> BERHASIL DIRESET.");

            } catch (\Exception $e) {
                Log::error("Gagal reset user {$reg->user_id}: " . $e->getMessage());
                $this->error("Gagal pada user ID: {$reg->user_id}");
            }
        }

        if ($isDryRun) {
            $this->warn("Simulasi selesai. Tidak ada perubahan database.");
        } else {
            $this->info("Pembersihan selesai!");
        }
        
        return 0;
    }
}