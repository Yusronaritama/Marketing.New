<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Libraries\Bnienc;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class BniCallbackController extends Controller
{
    protected $clientId;
    protected $secretKey;

    public function __construct()
    {
        $this->clientId = '41625';
        $this->secretKey = 'd2ea3883241ee95f7d0d6fd1de085c41';
    }

    /**
     * TAMPILAN BLADE: Menampilkan Log Callback
     */
    public function showLogs()
    {
        $logs = DB::table('bni_logs')->orderBy('created_at', 'desc')->get();
        
        $logs = $logs->map(function($log) {
            $log->details = json_decode($log->decrypted_data, true);
            return $log;
        });

        return view('admin.bni.index', compact('logs'));
    }

    /**
     * CALLBACK: Menerima notifikasi dari BNI (Anti Lunas Palsu)
     */
    public function handle(Request $request)
    {
        $data = $request->all();
        Log::info("BNI CALLBACK RECEIVED: ", $data);

        try {
            $bniEnc = new Bnienc();
            $decryptedData = $bniEnc->decrypt($data['data'], $this->clientId, $this->secretKey);

            if ($decryptedData) {
                Log::info("BNI CALLBACK DECRYPTED: ", $decryptedData);

                // VALIDASI NTB & NOMINAL
                $ntb = $decryptedData['payment_ntb'] ?? null;
                $amountPaid = (float)($decryptedData['payment_amount'] ?? 0);

                if (empty($ntb) || $amountPaid <= 0) {
                    Log::warning("BNI CALLBACK INVALID: NTB Kosong atau Nominal 0.");
                    return response()->json(['status' => '999']);
                }

                // Simpan ke Log DB
                DB::table('bni_logs')->insert([
                    'va_number' => $decryptedData['virtual_account'] ?? null,
                    'raw_data' => json_encode($data),
                    'decrypted_data' => json_encode($decryptedData),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // Update Status ke User & Master User
                DB::transaction(function() use ($decryptedData, $amountPaid) {
                    $va = $decryptedData['virtual_account'];
                    
                    DB::table('master_users')->where('va_number', $va)->update([
                        'va_status' => 'paid',
                        'payment_amount' => $amountPaid,
                        'paid_at' => now(),
                        'updated_at' => now()
                    ]);

                    User::where('va_number', $va)->update(['va_status' => 'PAID']);
                });

                return response()->json(['status' => '000']);
            }
        } catch (\Exception $e) {
            Log::error("BNI CALLBACK ERROR: " . $e->getMessage());
        }
        return response()->json(['status' => '999']);
    }

    /**
     * SINKRONISASI: Inquiry ke Server BNI (Anti Lunas Palsu)
     */
    public function syncAll()
    {
        $pendingPayments = DB::table('master_users')
            ->whereNotNull('va_number')
            ->where('va_status', '=', 'pending')
            ->get();

        if ($pendingPayments->isEmpty()) {
            return redirect()->back()->with('info', 'Tidak ada antrian pending.');
        }

        $bniEnc = new Bnienc();
        $apiUrl = 'https://api.bni-ecollection.com/';
        $countUpdated = 0;

        foreach ($pendingPayments as $row) {
            $dataToEncrypt = [
                'client_id'  => $this->clientId,
                'type'       => 'inquirybilling',
                'trx_id'     => (string)$row->trx_id, 
            ];

            $encrypted = $bniEnc->encrypt($dataToEncrypt, $this->clientId, $this->secretKey);

            try {
                $response = Http::withOptions(['verify' => false])->post($apiUrl, [
                    'client_id' => $this->clientId,
                    'data'      => $encrypted
                ]);

                $result = $response->json();

                if (isset($result['status']) && $result['status'] === '000') {
                    $dec = $bniEnc->decrypt($result['data'], $this->clientId, $this->secretKey);
                    
                    // VALIDASI KETAT: Status 2 DAN NTB Ada
                    if (($dec['va_status'] ?? null) == '2' && !empty($dec['payment_ntb'])) {
                        DB::transaction(function() use ($row, $dec) {
                            DB::table('master_users')->where('id', $row->id)->update([
                                'va_status' => 'paid',
                                'paid_at' => now(),
                                'updated_at' => now()
                            ]);
                            DB::table('users')->where('id', $row->user_id)->update(['va_status' => 'PAID']);
                        });
                        $countUpdated++;
                    }
                }
            } catch (\Exception $e) {
                Log::error("SYNC ERROR: " . $e->getMessage());
            }
        }

        return redirect()->back()->with('success', "Berhasil sinkron $countUpdated data.");
    }
}