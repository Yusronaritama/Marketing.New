<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class MandiriApiService
{
    /**
     * Membuat VA Baru
     * Tambahkan parameter $customName untuk nama dari master_ereg
     */
public function createBilling($user, $amount, $customName = null, $prefix = 'PENDAFTARAN_PMB')
    {
        try {
            // 1. Generate ID Pendek (Untuk ATM)
            // Format: Tahun + 4 Digit HP + 3 Digit Random
            $tahun = date('Y');
            $phone = $user->phone ?? $user->no_hp ?? '08123456789';
            $cleanPhone = preg_replace('/\D/', '', $phone);
            $last4Digits = (strlen($cleanPhone) >= 4) ? substr($cleanPhone, -4) : rand(1000, 9999);
            
            // TRX ID Pendek (Sekitar 11-12 digit)
            $trxId = $tahun . $last4Digits . str_pad(rand(0, 999), 3, '0', STR_PAD_LEFT);
            
            $expiredDate = now()->addDays(5)->format('Y-m-d H:i:s'); 

            // 2. Logic Nama (Prioritaskan Custom Name / Ereg)
            $finalName = $customName ?? $user->name;
            
            // Bersihkan nama (Hanya huruf angka spasi, max 30 char untuk Bank)
            $finalNameClean = preg_replace('/[^A-Za-z0-9 ]/', '', substr($finalName, 0, 30));
            
            $callbackPath = ($prefix === 'PENDAFTARAN_PMB') ? '/api/registrasi_pendaftaran' : '/api/registrasi_ukt';

            $payload = [
                'user' => [
                    'name'      => $finalNameClean, 
                    'email'     => $user->email,
                    'handphone' => $phone,
                    'nim'       => $trxId, 
                ],
                'total_bayar'      => $amount,
                'datetime_expired' => $expiredDate,
                'url_callback'     => config('app.url') . $callbackPath,
                'description'      => $prefix . ':' . $trxId . '-' . strtoupper(substr($finalNameClean, 0, 20))
            ];

            $urlSlik = 'https://slik.ubpkarawang.ac.id/api/mandiri/create_va_pendaftaranpmb';

            Log::info("📤 Request VA Mandiri ke SLIK | Prefix: $prefix", ['url' => $urlSlik, 'payload' => $payload]);

            $response = Http::timeout(45)->asJson()->post($urlSlik, $payload);

            if ($response->successful()) {
                $result = $response->json();
                if (isset($result['status']) && ($result['status'] === 'success' || $result['status'] === true)) {
                    return [
                        'virtual_account' => $result['data']['virtual_account'] ?? $result['data']['va_number'],
                        'trx_id'          => $trxId, 
                        'amount'          => $amount,
                        'expired_at'      => $expiredDate,
                        'response_raw'    => $result
                    ];
                }
            }

            Log::error('❌ Gagal API Mandiri SLIK', [
                'status' => $response->status(),
                'res'    => $response->body()
            ]);
            return null;

        } catch (Exception $e) {
            Log::error('💥 Exception Mandiri Service: ' . $e->getMessage());
            return null;
        }
    }
    public function getPaymentStatus($trxId)
    {
        $endpoints = [
            'https://slik.ubpkarawang.ac.id/api/mandiri/check_status_ukt'
        ];

        foreach ($endpoints as $url) {
            try {
                $response = Http::timeout(10)->retry(2, 100)->asJson()->post($url, [
                    'nim' => $trxId 
                ]);

                if ($response->successful()) {
                    $result = $response->json();
                    if (isset($result['status']) && $result['status'] === 'success') {
                        return $result['data'];
                    }
                }
            } catch (Exception $e) {
                Log::warning("⚠️ Koneksi gagal ke $url: " . $e->getMessage());
                continue; 
            }
        }
        return null;
    }
    
    public function deleteVA($trxId, $vaNumber)
    {
        try {
            $urlSlik = 'https://slik.ubpkarawang.ac.id/api/mandiri/delete_va_pmb';
            
            $payload = [
                'nim'             => $trxId,     
                'virtual_account' => $vaNumber
            ];

            $response = Http::timeout(5)->asJson()->post($urlSlik, $payload);
            return true; 

        } catch (Exception $e) {
            Log::warning('Gagal Hapus VA Lama (Ghost VA): ' . $e->getMessage());
            return false;
        }
    }
    public function canRegenerateVA($master)
{
    // Jika sudah lunas, tidak boleh regenerate
    if ($master->va_status === 'paid') {
        return false;
    }

    // Izinkan jika sudah expired atau jika admin ingin memaksa update billing
    return true; 
}

/**
 * Method pembungkus agar sesuai dengan pemanggilan di controller
 */
public function regenerateVirtualAccount($user, $amount)
{
    // Mandiri menggunakan createBilling untuk memperbarui data
    $result = $this->createBilling($user, $amount, null, 'PENDAFTARAN_PMB');

    if ($result) {
        return [
            'status' => true,
            'trx_id' => $result['trx_id'],
            'virtual_account' => $result['virtual_account'],
            'datetime_expired' => $result['expired_at'],
            'amount' => $result['amount'],
            'operation' => 'updateBilling'
        ];
    }

    return ['status' => false];
}
}