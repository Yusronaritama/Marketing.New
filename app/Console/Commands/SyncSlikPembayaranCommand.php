<?php

namespace App\Console\Commands;

use App\Http\Controllers\Admin\GenerateNimController;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class SyncSlikPembayaranCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pmb:sync-slik
                            {--limit=50 : Batas jumlah data yang dikirim per batch (default: 50)}
                            {--delay=500 : Jeda waktu milidetik antar pengiriman request (default: 500ms)}
                            {--jenjang= : Filter jenjang tertentu (S1, S2, Profesi)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Kirim data tagihan/pembayaran mahasiswa baru ke SLIK secara bertahap tanpa membebani server';

    /**
     * Execute the console command.
     */
    public function handle(GenerateNimController $controller)
    {
        $limit   = (int) ($this->option('limit') ?: 50);
        $delayMs = (int) ($this->option('delay') ?: 500);
        $jenjang = $this->option('jenjang');

        $this->info("=== MEMULAI SINKRONISASI SLIK ===");
        $this->line("Limit per batch: {$limit} | Delay antar data: {$delayMs}ms" . ($jenjang ? " | Jenjang: {$jenjang}" : ""));

        // Cegah overlapping dengan lock yang sama dengan Web & API
        $lockKey = 'sync_slik_bulk_lock';
        $lock = Cache::lock($lockKey, 900); // 15 menit lock

        if (!$lock->get()) {
            $this->warn("⚠️  Proses pengiriman ke SLIK sedang berjalan di proses lain (Web / API / Scheduler).");
            $this->warn("Dibatalkan untuk mencegah overlapping.");
            return Command::SUCCESS;
        }

        try {
            $progressBar = null;

            $result = $controller->executeBulkSendToSlik(
                limit: $limit,
                delayMs: $delayMs,
                jenjang: $jenjang,
                onProgress: function ($record, $detail, $current, $total) use (&$progressBar) {
                    if (!$progressBar) {
                        $progressBar = $this->output->createProgressBar($total);
                        $progressBar->start();
                    }
                    $progressBar->advance();
                }
            );

            if ($progressBar) {
                $progressBar->finish();
                $this->newLine(2);
            }

            if ($result['diproses'] === 0) {
                $this->info("ℹ️  " . $result['message']);
                return Command::SUCCESS;
            }

            $this->info("✅ Berhasil dikirim : {$result['berhasil']}");
            if ($result['gagal'] > 0) {
                $this->error("❌ Gagal/Tertunda   : {$result['gagal']}");
            }
            $this->line("📊 Sisa antrean    : {$result['sisa_antrean']}");

            return Command::SUCCESS;

        } catch (\Throwable $e) {
            $this->error("Terjadi error: " . $e->getMessage());
            return Command::FAILURE;
        } finally {
            $lock->release();
        }
    }
}
