<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Libraries\Bnienc;

class BNICalllbackController extends Controller
{
    public function callback(Request $request)
    {
        $clientId  = '41625';
        $secretKey = 'd2ea3883241ee95f7d0d6fd1de085c41';

        Log::info('📥 Callback BNI diterima', ['payload' => $request->all()]);

        $dataJson = $request->json()->all();

        if (!$dataJson || !isset($dataJson['client_id'])) {
            return response()->json(['status' => '999', 'message' => 'Payload tidak valid'], 400);
        }

        if ($dataJson['client_id'] !== $clientId) {
            return response()->json(['status' => '999', 'message' => 'Client ID tidak sesuai'], 403);
        }
        $decrypted = Bnienc::decrypt($dataJson['data'], $clientId, $secretKey);

        if (!$decrypted) {
            return response()->json([
                'status' => '999',
                'message' => 'Dekripsi gagal, secret key salah atau waktu server tidak sinkron.'
            ], 400);
        }

        Log::info('🔓 Data callback terdekripsi', ['data' => $decrypted]);

        try {
            $vaNumber         = $decrypted['virtual_account'] ?? null;
            $trxId            = $decrypted['trx_id'] ?? null;
            $paymentAmount    = $decrypted['payment_amount'] ?? null;
            $paymentDateTime  = $decrypted['datetime_payment'] ?? now();
            DB::table('master_users')
                ->where('va_number', $vaNumber)
                ->update([
                    'va_status'       => 'paid',
                    'payment_amount'  => $paymentAmount,
                    'payment_date'    => $paymentDateTime,
                    'updated_at'      => now(),
                ]);

            Log::info('✅ Status pembayaran diperbarui', [
                'va_number' => $vaNumber,
                'trx_id' => $trxId,
                'status' => 'paid'
            ]);

            // Ambil data user untuk ditampilkan di dashboard
            $dataUser = DB::table('master_users')
                ->join('users', 'users.id', '=', 'master_users.user_id')
                ->select('users.name', 'users.email', 'master_users.*')
                ->where('master_users.va_number', $vaNumber)
                ->first();

            // Tampilkan halaman dashboard mahasiswa
            return view('mahasiswa.va.dashboard', [
                'user' => $dataUser,
                'message' => 'Pembayaran berhasil diverifikasi',
            ]);
        } catch (\Exception $e) {
            Log::error('💥 Error saat memproses callback', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['status' => '999', 'message' => 'Gagal memproses callback'], 500);
        }
    }
}
