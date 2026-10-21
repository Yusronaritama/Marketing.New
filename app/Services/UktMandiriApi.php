<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB; 
use Exception;
use App\Models\SlikBilling; 


class UktMandiriApi
{
    /**
     * Membuat VA Baru (Create Billing)
     * Otomatis membersihkan tagihan lama (Ghost VA) sebelum buat baru.
     */
    public function createBilling($user, $amount, $customName = null, $customExpired = null)
    {
        // 1. JALANKAN GHOST VA CLEANUP TERLEBIH DAHULU
        $this->cleanupGhostBills($user->id);

        try {
            // 2. GENERATE ID PENDEK
            $tahun = date('Y');
            $phone = $user->phone ?? $user->no_hp ?? '08123456789';
            $cleanPhone = preg_replace('/\D/', '', $phone);
            
            // Ambil 4 digit terakhir HP (jika ada), atau random 4 digit
            $last4Digits = (strlen($cleanPhone) >= 4) ? substr($cleanPhone, -4) : rand(1000, 9999);
            
            // TRX ID (NIM Sementara untuk Bank)
            $trxId = $tahun . $last4Digits . str_pad(rand(0, 999), 3, '0', STR_PAD_LEFT);
            
            // Expired 5 Hari
            $carbonDate = $customExpired ?? now()->addDays(5);
            $expiredDate = $carbonDate->format('Y-m-d H:i:s'); 

            // ==========================================================
            // 3. LOGIKA NAMA CUSTOMER (ORIGINAL / TANPA "UKT")
            // ==========================================================
            
            // Ambil nama asli (Prioritas Custom Name / Ereg)
            $finalName = $customName ?? $user->name;

            // Bersihkan & Potong (Max 30 Karakter sesuai aturan Bank)
            // Hanya mengambil huruf, angka, dan spasi
            $finalNameClean = preg_replace('/[^A-Za-z0-9 ]/', '', substr($finalName, 0, 30));
            
            $payload = [
                'user' => [
                    'name'      => $finalNameClean, 
                    'email'     => $user->email,
                    'handphone' => $phone,
                    'nim'       => $trxId, 
                ],
                'total_bayar'      => $amount,
                'datetime_expired' => $expiredDate,
                'url_callback'     => config('app.url') . '/api/registrasi_ukt',
                'description'      => 'REGISTRASI_PMB:' . $trxId . '-' . strtoupper(substr($finalNameClean, 0, 20))
            ];

            $urlSlik = 'https://slik.ubpkarawang.ac.id/api/mandiri/create_va_pmb';

            Log::info("📤 Request VA Mandiri ke SLIK", ['url' => $urlSlik, 'payload' => $payload]);

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
                'body'   => $response->body()
            ]);
            return null;

        } catch (Exception $e) {
            Log::error('💥 Exception Mandiri Service (Create): ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Fitur Ghost VA Cleanup
     * Mencari tagihan lama yang belum lunas, menghapusnya di bank, dan mematikannya di DB.
     */
    public function cleanupGhostBills($userId)
    {
        try {
            // Cari tagihan Pending/Expired yang masih aktif
            $oldBills = SlikBilling::where('user_id', $userId)
                ->whereIn('status', ['pending', 'expired'])
                ->where('is_active', 1)
                ->get();

            if ($oldBills->isEmpty()) {
                return; 
            }

            Log::info("🧹 Mulai Cleanup Ghost VA untuk User ID: $userId (" . $oldBills->count() . " tagihan)");

            foreach ($oldBills as $oldBill) {
                // 1. Hapus di Bank
                $this->deleteVA($oldBill->trx_id, $oldBill->virtual_account);

                // 2. Matikan di Database
                $oldBill->update([
                    'is_active'   => 0,
                    'status'      => 'cancel',
                    'description' => DB::raw("CONCAT(COALESCE(description, ''), ' [REPLACED BY NEW BILL]')")
                ]);
            }

        } catch (Exception $e) {
            Log::warning("⚠️ Cleanup Ghost VA Gagal: " . $e->getMessage());
        }
    }

    /**
     * Delete VA (Cleanup) - Mengirim Request Hapus ke Bank
     */
    public function deleteVA($trxId, $vaNumber)
    {
        try {
            $urlSlik = 'https://slik.ubpkarawang.ac.id/api/mandiri/delete_va_pmb';
            
            $payload = [
                'nim'             => $trxId,     
                'virtual_account' => $vaNumber
            ];

            Log::info("🗑️ DELETE VA REQUEST:", $payload);

            $response = Http::timeout(5)->asJson()->post($urlSlik, $payload);
            
            Log::info("🗑️ DELETE VA RESPONSE: " . $response->body());

            if ($response->successful()) {
                return true; 
            }
            
            return false;

        } catch (Exception $e) {
            Log::warning('⚠️ Gagal Hapus VA Lama (Exception): ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Cek Status Pembayaran
     */
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

    /**
     * Cek Status VA Konversi (Jalur Ekstensi/Pindahan/RPL)
     */
    public function getConversionVaStatus($trxId)
    {
        $url = "https://slik.ubpkarawang.ac.id/api/mandiri/check_status_va?kode_daftar=" . $trxId;
        try {
            $response = Http::timeout(10)->retry(2, 100)->get($url);
            if ($response->successful()) {
                $result = $response->json();
                if (isset($result['status']) && $result['status'] === 'success') {
                    return $result['data'];
                }
            }
        } catch (\Exception $e) {
            Log::warning("⚠️ Gagal mengecek status VA Konversi ke SLIK: " . $e->getMessage());
        }
        return null;
    }
}