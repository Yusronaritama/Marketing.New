<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\MasterUser;
use App\Services\MandiriApiService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CheckMandiriPayment extends Command
{
    protected $signature = 'payment:check-mandiri';
    protected $description = 'Cek status pembayaran Mandiri VA ke SLIK dan sinkronisasi ke Node.js';

    public function handle()
    {
        // 1. Ambil data pending terbaru (limit dikurangi agar tidak overload)
        $pendingPayments = MasterUser::where('va_status', 'pending')
            ->where('created_at', '>', now()->subDays(2))
            ->limit(10)
            ->get();

        if ($pendingPayments->isEmpty()) {
            $this->info("😴 Tidak ada pembayaran pending dalam 2 hari terakhir.");
            return;
        }

        $service = new MandiriApiService();
        // Pastikan IP Node.js ini sudah benar
        $nodeJsUrl = 'http://103.23.198.160:3008/api/callback/mandiri';

        foreach ($pendingPayments as $payment) {
            $this->info("🔍 Memeriksa TRX: {$payment->trx_id} | VA: {$payment->va_number}");
            
            // Masukkan va_number sebagai argumen kedua
            $slikData = $service->getPaymentStatus($payment->trx_id, $payment->va_number);

            if ($slikData) {
                $status = strtoupper($slikData['status'] ?? 'PENDING');
                $this->line("📊 Status SLIK: " . $status);

                if (in_array($status, ['PAID', 'SETTLED', 'LUNAS', 'SUCCESS'])) {
                    $this->warn("💰 Pembayaran TERDETEKSI LUNAS!");

                    try {
                        $response = Http::timeout(10)->post($nodeJsUrl, [
                            'id_pendaftar' => $payment->trx_id,
                            'no_va'        => $payment->va_number,
                            'amount'       => $payment->va_amount,
                            'status'       => 'PAID'
                        ]);

                        if ($response->successful() || $response->body() == 'true') {
                            $this->info("✅ Callback Node.js Sukses.");
                            
                            $payment->update([
                                'va_status' => 'paid',
                                'paid_at' => now(),
                                'payment_method' => 'MANDIRI_VA_SYNC'
                            ]);
                        }
                    } catch (\Exception $e) {
                        $this->error("❌ Gagal kontak Node.js: " . $e->getMessage());
                    }
                } else {
                    $this->info("⏳ Belum dibayar.");
                }
            } else {
                $this->error("💀 TRX {$payment->trx_id} tidak ditemukan di server SLIK (Semua endpoint 404/Error).");
            }
            
            sleep(1);
        }
    }
}