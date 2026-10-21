<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\SlikBilling;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ExpireUnpaidBills extends Command
{
    /**
     * Nama command yang akan dipanggil di terminal/cron
     */
    protected $signature = 'billing:expire-unpaid';

    /**
     * Deskripsi command
     */
    protected $description = 'Mengubah status tagihan yang sudah lewat tanggal expired menjadi EXPIRED';

    /**
     * Eksekusi logic
     */
    public function handle()
    {
        $now = Carbon::now();

        // 1. Cari tagihan yang:
        //    - Statusnya MASIH 'pending'
        //    - Tanggal expired-nya KURANG DARI waktu sekarang
        //    - Masih aktif (is_active = 1)
        $expiredBills = SlikBilling::where('status', 'pending')
                                   ->where('is_active', 1)
                                   ->where('datetime_expired', '<', $now)
                                   ->get();

        $count = $expiredBills->count();

        if ($count > 0) {
            $this->info("Ditemukan {$count} tagihan yang harus di-expired-kan.");

            foreach ($expiredBills as $bill) {
                // Update status jadi expired
                // Opsional: set is_active = 0 jika ingin menyembunyikan dari user
                $bill->update([
                    'status' => 'expired',
                    'is_active' => 0 // Non-aktifkan agar user bisa buat tagihan baru
                ]);
                
                $this->info("Tagihan Order ID: {$bill->order_id} berhasil di-expired-kan.");
            }

            Log::info("CRON JOB: Berhasil meng-expired-kan {$count} tagihan.");
        } else {
            $this->info("Tidak ada tagihan yang perlu di-expired-kan.");
        }

        return Command::SUCCESS;
    }
}