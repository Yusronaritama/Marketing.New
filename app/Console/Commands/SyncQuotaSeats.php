<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\SlikBilling;
use App\Models\MasterEreg;
use App\Models\ProgramStudi;

class SyncQuotaSeats extends Command
{
    protected $signature = 'quota:sync';
    protected $description = 'Menyapu transaksi PAID, menandai kuota diambil, dan sinkronisasi kuota';

    public function handle()
    {
        $this->info('🚀 Memulai Sapu Bersih Transaksi...');
        
        $diupdate = 0;

        SlikBilling::whereRaw('LOWER(status) IN (?, ?)', ['paid', 'lunas'])
            ->orderBy('datetime_payment', 'asc')
            ->chunk(100, function ($billings) use (&$diupdate) {
                
                foreach ($billings as $bill) {
                    try {
                        $ereg = MasterEreg::where('user_id', $bill->user_id)->first();

                        // Skip jika user tidak ditemukan atau SUDAH dapat stempel lunas
                        if (!$ereg || $ereg->is_quota_taken == 1) {
                            continue; 
                        }

                        // [BARU] ALARM LOGGING KE LARAVEL.LOG JIKA BEDA PRODI
                        if ($ereg->jurusan_lulusan !== $bill->kode_prodi) {
                            // Tarik nama prodi dari database biar gampang dibaca
                            $namaProdiBill = \App\Models\ProgramStudi::where('kode_prodi', $bill->kode_prodi)->value('nama_prodi') ?? 'Unknown';
                            $namaProdiEreg = \App\Models\ProgramStudi::where('kode_prodi', $ereg->jurusan_lulusan)->value('nama_prodi') ?? 'Unknown';

                            $pesanMutasi = "[MUTASI PRODI - PMB] User ID: {$bill->user_id} bayar tagihan {$namaProdiBill} ({$bill->kode_prodi}), tapi final jurusan di Ereg adalah {$namaProdiEreg} ({$ereg->jurusan_lulusan}). Kuota dialokasikan ke {$namaProdiEreg}.";
                            
                            // Tulis peringatan ke storage/logs/laravel.log
                            Log::warning($pesanMutasi);
                            
                            // Tetap tampilkan di terminal biar admin tahu pas nge-run manual
                            $this->warn("⚠️ " . $pesanMutasi);
                        }

                        // 1. CUMA beri stempel lunas. Jangan sentuh jurusan_lulusan di sini!
                        $ereg->is_quota_taken = 1;
                        
                        $ereg->save();
                        $diupdate++;

                        $this->info("✅ [LUNAS] User {$bill->user_id} -> Mendapat stempel is_quota_taken.");

                    } catch (\Exception $e) {
                        $this->error("Err User {$bill->user_id}: " . $e->getMessage());
                    }
                }
            });

        // 3. JIKA ADA YANG BERUBAH, HITUNG ULANG SEMUA KUOTA
        if ($diupdate > 0) {
            $this->info("🔄 Mensinkronisasi ulang kuota seluruh Program Studi...");
            
            ProgramStudi::all()->each(function($prodi) {
                $prodi->syncDayaTampung();
            });
        }

        $this->info("🎯 Selesai! {$diupdate} pendaftar baru berhasil disinkronkan ke kuotanya masing-masing.");
        return Command::SUCCESS;
    }
}