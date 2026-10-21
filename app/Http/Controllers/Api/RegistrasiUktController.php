<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

// Tambahkan Models ini agar sistem mengenali tabel prodi pendaftar
use App\Models\SlikBilling;
use App\Models\MasterEreg;
use App\Models\MasterUser;
use App\Models\MasterMoodleLulus;

class RegistrasiUktController extends Controller
{
    /**
     * PURE Webhook / Callback dari Bank Mandiri/SLIK
     * Route: POST /api/registrasi_ukt
     */
    public function handleRegistrasiUkt(Request $request)
    {
        $rawContent = file_get_contents('php://input');
        
        Log::info('--------------------------------------------------');
        Log::info('HIT PURE CALLBACK: ' . now());
        Log::info('IP PENGIRIM: ' . $request->ip()); 
        Log::info('RAW BODY: ' . $rawContent);
        Log::info('--------------------------------------------------');
        
        // 1. BACA DATA JSON
        $data = json_decode($rawContent, true);
        if (!$data) $data = $request->all();

        // 2. NORMALISASI VARIABEL
        $no_va = $data['no_va'] ?? $data['virtual_account'] ?? $data['va_number'] ?? null;
        $trx_id = $data['trx_id'] ?? $data['payment_ntb'] ?? null;

        if (!$no_va) {
            Log::error("Format Data Salah (VA tidak ditemukan)");
            return response()->json(['status' => 'error', 'message' => 'Invalid Data Payload'], 400);
        }

        // 3. CARI TAGIHAN (HANYA YANG PENDING)
        // Kalau udah expired/cancel, webhook ini akan skip (pure logic).
        $billing = SlikBilling::where('virtual_account', $no_va)
                              ->where('status', 'pending')
                              ->first();
        
        if (!$billing) {
            Log::warning("Tagihan tidak ditemukan atau bukan berstatus pending untuk VA: $no_va");
            return response()->json(['status' => 'error', 'message' => 'Billing not found or already processed'], 404);
        }

        // 4. EKSEKUSI UPDATE KE PAID & PERUBAHAN PRODI
        try {
            DB::beginTransaction();

            // Update murni tagihan ini jadi lunas
            $billing->update([
                'status'             => 'paid',
                'payment_ntb'        => $trx_id,
                'datetime_payment'   => now(),
                'slik_response_data' => json_encode($data)
            ]);

            Log::info("✅ Pembayaran PURE SUKSES untuk VA: $no_va");

            // ==========================================================
            // 5. LOGIKA OTOMATIS: UBAH PRODI JIKA INI TAGIHAN SELISIH
            // ==========================================================
            if (str_contains(strtoupper($billing->order_id), 'SELISIH')) {
                
                // A. Update Master Ereg (Jurusan Lulusan)
                MasterEreg::where('user_id', $billing->user_id)->update([
                    'jurusan_lulusan' => $billing->kode_prodi
                ]);

                // B. Update Master Moodle Lulus (Kode Prodi & Nama Prodi)
                $masterUser = MasterUser::where('user_id', $billing->user_id)->first();
                if ($masterUser) {
                    MasterMoodleLulus::where('master_user_id', $masterUser->id)->update([
                        'prodi_lulus'      => $billing->kode_prodi,
                        'nama_prodi_lulus' => $billing->nama_jurusan
                    ]);
                }

                Log::info("🔄 Prodi user_id {$billing->user_id} otomatis diubah ke {$billing->nama_jurusan} karena VA Selisih telah lunas.");
            }
            // ==========================================================
            
            DB::commit();
            return response()->json(['status' => 'success', 'message' => 'Payment updated to paid successfully'], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("💥 Gagal update status webhook: " . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Internal Server Error'], 500);
        }
    }
}