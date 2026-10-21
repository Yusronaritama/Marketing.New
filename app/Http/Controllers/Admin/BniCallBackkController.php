<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Libraries\Bnienc;

class BniCallBackkController extends Controller
{
    /**
     * Method ini untuk menangani error "undefined method index()"
     * Route: GET /bni/callback
     */
    public function index()
    {
        return response()->json([
            'status' => 'success',
            'message' => 'BNI Callback Endpoint is Ready (GET Method).'
        ]);
    }

    /**
     * Method tambahan jika route showAll dipanggil
     * Route: GET /bni/callbacks
     */
    public function showAll()
    {
        return response()->json(['message' => 'History callback feature coming soon.']);
    }

    /**
     * Method UTAMA untuk memproses pembayaran dari BNI
     * Route: POST /bni/callback
     * Pastikan di web.php route-nya mengarah ke [BniCallBackkController::class, 'handleCallback']
     */
    public function handleCallback(Request $request)
    {
        // Konfigurasi (Sebaiknya gunakan env(), tapi hardcode dulu untuk debug)
        $clientId  = '41625';
        $secretKey = 'd2ea3883241ee95f7d0d6fd1de085c41';

        Log::info('📥 Callback BNI diterima', ['payload' => $request->all()]);

        try {
            $dataJson = $request->json()->all();

            // 1. Validasi Payload
            if (!$dataJson || !isset($dataJson['client_id']) || !isset($dataJson['data'])) {
                Log::warning('⚠️ Payload tidak valid');
                return response()->json(['status' => '999', 'message' => 'Invalid Payload'], 400);
            }

            if ($dataJson['client_id'] !== $clientId) {
                return response()->json(['status' => '999', 'message' => 'Invalid Client ID'], 403);
            }

            // 2. Dekripsi Data
            $decrypted = Bnienc::decrypt($dataJson['data'], $clientId, $secretKey);

            if (!$decrypted) {
                Log::error('❌ Gagal dekripsi data BNI');
                return response()->json(['status' => '999', 'message' => 'Decryption Failed'], 400);
            }

            Log::info('🔓 Data callback terdekripsi', ['data' => $decrypted]);

            $vaNumber         = $decrypted['virtual_account'] ?? null;
            $trxId            = $decrypted['trx_id'] ?? null;
            $paymentAmount    = $decrypted['payment_amount'] ?? null;
            $paymentDateTime  = $decrypted['datetime_payment'] ?? now();

            // 3. Cek apakah sudah lunas sebelumnya (Mencegah double update)
            $cekStatus = DB::table('master_users')
                ->where('va_number', $vaNumber)
                ->where('va_status', 'paid')
                ->first();

            if ($cekStatus) {
                // Jika sudah paid, langsung return sukses ke BNI agar tidak hit ulang
                return response()->json(['status' => '000', 'message' => 'Already Paid'], 200);
            }

            // 4. Update Database
            DB::table('master_users')
                ->where('va_number', $vaNumber)
                ->update([
                    'va_status'       => 'paid',
                    'payment_amount'  => $paymentAmount,
                    'payment_date'    => $paymentDateTime,
                    'updated_at'      => now(),
                ]);

            Log::info('✅ Pembayaran Berhasil', ['va' => $vaNumber]);

            // 5. RETURN JSON (PENTING!)
            // Jangan return view() di sini karena yang akses adalah SERVER BNI.
            // BNI mewajibkan return JSON dengan status '000'.
            return response()->json([
                'status' => '000',
                'message' => 'Success'
            ], 200);

        } catch (\Exception $e) {
            Log::error('💥 Error Callback', ['msg' => $e->getMessage()]);
            return response()->json(['status' => '999', 'message' => 'Server Error'], 500);
        }
    }
}