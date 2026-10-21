<?php

namespace App\Services;

use App\Libraries\Bnienc;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Exception;
use Carbon\Carbon;
use App\Models\User;
use App\Models\MasterUser;

class BNIApiService
{
    protected $clientId;
    protected $secretKey;
    protected $apiUrl;
    protected $vaPrefix;
    protected $lastError;

    public function __construct()
    {
        $this->clientId  = '41625';
        $this->secretKey = 'd2ea3883241ee95f7d0d6fd1de085c41';
        $this->vaPrefix  = '98841625';
        
//     BNI_CLIENT_ID=41625
// BNI_SECRET_KEY=d2ea3883241ee95f7d0d6fd1de085c41
// BNI_API_URL=https://api.bni-ecollection.com/
// BNI_VA_PREFIX=98841625

        $baseUrl = env('BNI_API_URL', 'https://api.bni-ecollection.com/');
        $this->apiUrl = rtrim($baseUrl, '/') . '/api/v1/billing/';
    }

    /**
     * Validate BNI credentials - FIXED VERSION
     */
    public function validateCredentials(): bool
    {
            if (empty($this->clientId) || empty($this->secretKey) || empty($this->apiUrl)) {
                $this->lastError = 'Kredensial BNI tidak lengkap';
                Log::error('❌ ' . $this->lastError, [
                    'client_id_set' => !empty($this->clientId),
                    'secret_key_set' => !empty($this->secretKey),
                    'api_url_set' => !empty($this->apiUrl)
                ]);
                return false;
            }
            Log::info('🔍 Memvalidasi kredensial BNI', [
                'client_id' => substr($this->clientId, 0, 8) . '...',
                'api_url' => $this->apiUrl
            ]);
            $testTrxId = $this->generateTestTrxId();
            $testPayload = [
                'type' => 'createBilling',
                'client_id' => $this->clientId,
                'trx_id' => $testTrxId,
                'trx_amount' => 1000,
                'billing_type' => 'c',
                'datetime_expired' => now()->addDay()->timezone('Asia/Jakarta')->format('Y-m-d H:i:s'),
                'virtual_account' => '9884162526009999', // VA test
                'customer_name' => 'TEST USER',
                'customer_email' => 'test@example.com',
                'customer_phone' => '081234567890',
            ];

            $encryptedPayload = Bnienc::encrypt($testPayload, $this->clientId, $this->secretKey);
            $response = Http::timeout(45)
                ->retry(2, 2000)
                ->asJson()
                ->post($this->apiUrl, [
                    'client_id' => $this->clientId,
                    'data' => $encryptedPayload,
                ]);

            $statusCode = $response->status();
            $body = $response->body();

            if ($response->successful()) {
                $result = $response->json();
                $status = $result['status'] ?? '';
                if (in_array($status, ['000', '108', '107', '105'])) {
                    Log::info('✅ Kredensial BNI valid - API merespon dengan benar', [
                        'status' => $status,
                        'message' => $result['message'] ?? 'No message'
                    ]);
                    return true;
                }
                
                Log::warning('⚠️ API BNI merespon tapi dengan status tidak biasa', [
                    'status' => $status,
                    'message' => $result['message'] ?? 'No message'
                ]);
                
                return true;
            }

            if ($statusCode >= 400 && $statusCode < 500) {
                Log::warning('⚠️ API BNI merespon dengan error client', [
                    'status_code' => $statusCode,
                    'body' => substr($body, 0, 500)
                ]);
                return true;
            }

            $this->lastError = "Tidak dapat terhubung ke API BNI (HTTP {$statusCode})";
            Log::error('❌ ' . $this->lastError, [
                'body_preview' => substr($body, 0, 300)
            ]);
            
            return false;
    }

    /**
     * Get last error message
     */
    public function getLastError(): ?string
    {
        return $this->lastError;
    }

    public function createBilling($user, $amount, $retryCount = 0)
    {
        // dd($this->validateCredentials());
            // ✅ VALIDASI USER DAN DATA
            if (!$user || !$user->id) {
                $this->lastError = 'Data user tidak valid';
                Log::error('❌ ' . $this->lastError);
                return null;
            }

            // ✅ VALIDASI KREDENSIAL
            if (!$this->validateCredentials()) {
                Log::error('❌ Gagal validasi kredensial sebelum createBilling');
                return null;
            }

            // --- 🔹 Generate trx_id ---
            // GUNAKAN generateTrxIdForRetry() JIKA INI PERCOBAAN RETRY
            $trxId = ($retryCount > 0) 
                ? $this->generateTrxIdForRetry($user, $retryCount)
                : $this->generateTrxId($user);
            
            // --- 🔹 Buat VA number ---
            $vaPrefixUtama = '98841625';
            $vaKodeUrut    = '2600';
            $vaAkhir       = str_pad($user->id, 4, '0', STR_PAD_LEFT);
            $virtualAccount = $vaPrefixUtama . $vaKodeUrut . $vaAkhir;

            // Validasi VA 16 digit
            if (strlen($virtualAccount) !== 16) {
                $this->lastError = 'VA Number harus 16 digit';
                Log::error('❌ ' . $this->lastError, ['va' => $virtualAccount]);
                return null;
            }

            // --- 🔹 Format nomor telepon ---
            $phone = preg_replace('/\D/', '', $user->phone);
            $customerPhone = '08123456789'; // Default
            
            if (strlen($phone) >= 10 && strlen($phone) <= 13) {
                if (str_starts_with($phone, '62')) {
                    $customerPhone = '0' . substr($phone, 2);
                } elseif (str_starts_with($phone, '0')) {
                    $customerPhone = $phone;
                } elseif (str_starts_with($phone, '8')) {
                    $customerPhone = '0' . $phone;
                }
            }

            // --- 🔹 Format nama ---
            $sanitizedName = preg_replace('/[^\p{L}\p{N}\s]/u', '', trim($user->name));
            $sanitizedName = substr($sanitizedName, 0, 50);
            if (empty($sanitizedName)) {
                $sanitizedName = 'User ' . $user->id;
            }

            // --- 🔹 Expired date 24 jam ---
            $datetimeExpired = now()->addDay()->timezone('Asia/Jakarta')->format('Y-m-d H:i:s');

            // --- 🔹 Payload ke BNI ---
            $payload = [
                'type'             => 'createBilling',
                'client_id'        => $this->clientId,
                'trx_id'           => $trxId,
                'trx_amount'       => $amount,
                'billing_type'     => 'c',
                'datetime_expired' => $datetimeExpired,
                'virtual_account'  => $virtualAccount,
                'customer_name'    => $sanitizedName,
                'customer_email'   => $user->email,
                'customer_phone'   => $customerPhone,
                'description'      => 'Pendaftaran PMB ' . date('Y'),
            ];
                
            // dd($payload);
            
            Log::info('📤 Mengirim request CREATE BILLING ke BNI', [
                'user_id' => $user->id,
                'trx_id' => $trxId,
                'amount' => $amount,
                'virtual_account' => $virtualAccount,
                'customer_name' => $sanitizedName,
                'retry_count' => $retryCount
            ]);

            $encryptedPayload = Bnienc::encrypt($payload, $this->clientId, $this->secretKey);
            
            // INCREASE TIMEOUT FOR BNI API
            $response = Http::timeout(60)
                ->retry(3, 2000)
                ->asJson()
                ->post($this->apiUrl, [
                    'client_id' => $this->clientId,
                    'data' => $encryptedPayload,
                ]);

            if (!$response->successful()) {
                $this->lastError = 'HTTP Error dari BNI: ' . $response->status();
                Log::error('❌ ' . $this->lastError, [
                    'body' => $response->body(),
                    'user_id' => $user->id
                ]);
                return null;
            }

            $result = $response->json();
            Log::debug('Response BNI:', $result);
            
            $status = $result['status'] ?? '';
            
            if ($status !== '000') {
                $errorMessage = $result['message'] ?? 'Unknown error';
                $this->lastError = 'Gagal membuat VA di BNI: ' . $errorMessage;
                Log::error('❌ ' . $this->lastError, [
                    'response' => $result,
                    'user_id' => $user->id,
                    'retry_count' => $retryCount
                ]);
                
                // ✅ PERBAIKAN: Handle duplicate billing dengan rekursi terkontrol
                if ($status === '105' && $retryCount < 3) {
                    $newRetryCount = $retryCount + 1;
                    Log::warning('🔄 Duplicate Billing ID, retry ke-' . $newRetryCount);
                    
                    // Tunggu sebentar dengan exponential backoff
                    $sleepTime = pow(2, $retryCount) * 100000; // 0.1s, 0.2s, 0.4s
                    usleep($sleepTime);
                    
                    // Panggil ulang createBilling dengan retryCount yang meningkat
                    return $this->createBilling($user, $amount, $newRetryCount);
                }
                
                // ✅ Handle error VA sudah digunakan (status 107) - bisa juga di-retry
                if ($status === '107' && $retryCount < 2) {
                    Log::warning('🔄 VA sudah digunakan, coba dengan VA baru');
                    
                    // Generate VA baru dengan suffix random
                    $randomSuffix = str_pad(rand(10, 99), 2, '0', STR_PAD_LEFT);
                    $newVirtualAccount = substr($virtualAccount, 0, 14) . $randomSuffix;
                    
                    // Update payload dengan VA baru
                    $payload['virtual_account'] = $newVirtualAccount;
                    $virtualAccount = $newVirtualAccount;
                    
                    // Generate trx_id baru juga untuk memastikan keunikan
                    $newTrxId = $this->generateTrxIdForRetry($user, $retryCount + 1);
                    $payload['trx_id'] = $newTrxId;
                    
                    $newRetryCount = $retryCount + 1;
                    $sleepTime = pow(2, $retryCount) * 100000;
                    usleep($sleepTime);
                    
                    // Re-enkripsi payload dengan VA dan trx_id baru
                    $encryptedPayload = Bnienc::encrypt($payload, $this->clientId, $this->secretKey);
                    $response = Http::timeout(60)
                        ->retry(2, 1000)
                        ->asJson()
                        ->post($this->apiUrl, [
                            'client_id' => $this->clientId,
                            'data' => $encryptedPayload,
                        ]);
                        
                    if ($response->successful()) {
                        $result = $response->json();
                        if (($result['status'] ?? '') === '000') {
                            $decrypted = Bnienc::decrypt($result['data'], $this->clientId, $this->secretKey);
                            $vaNumber = $decrypted['virtual_account'] ?? $virtualAccount;
                            return $this->saveVAToDatabase($user, $newTrxId, $vaNumber, $datetimeExpired);
                        }
                    }
                }
                
                return null;
            }

            // --- 🔓 Dekripsi data sukses ---
            $decrypted = Bnienc::decrypt($result['data'], $this->clientId, $this->secretKey);
            $vaNumber = $decrypted['virtual_account'] ?? $virtualAccount;

            // Simpan ke database
            return $this->saveVAToDatabase($user, $trxId, $vaNumber, $datetimeExpired);

        } 
    
    /**
     * Simpan VA ke database
     */
    private function saveVAToDatabase($user, $trxId, $vaNumber, $datetimeExpired)
    {
        try {
            // --- 🔹 Buat password dari nomor HP ---
            $cleanPhoneForPassword = preg_replace('/\D/', '', $user->phone);
            $passwordDigits = substr($cleanPhoneForPassword, -8);
            $plainPassword = 'PMB' . $passwordDigits;
            $hashedPassword = bcrypt($plainPassword);

            // --- 🗂 Simpan ke master_users ---
            DB::table('master_users')->updateOrInsert(
                ['user_id' => $user->id],
                [
                    'password'        => $hashedPassword,
                    'trx_id'          => $trxId,
                    'va_number'       => $vaNumber,
                    'va_status'       => 'pending',
                    'va_expired_at'   => $datetimeExpired,
                    'va_amount'       => 300000, // Default amount
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ]
            );

            Log::info('✅ VA & password berhasil disimpan', [
                'user_id' => $user->id,
                'trx_id'  => $trxId,
                'va'      => $vaNumber,
                'password_source' => 'PMB + 8 digit terakhir HP',
                'expired_at' => $datetimeExpired
            ]);

            // --- Return ke controller ---
            return [
                'status'           => true,
                'virtual_account'  => $vaNumber,
                'trx_id'           => $trxId,
                'password'         => $plainPassword,
                'datetime_expired' => $datetimeExpired,
                'message'          => 'VA berhasil dibuat'
            ];

        } catch (Exception $e) {
            $this->lastError = 'Gagal menyimpan VA ke database: ' . $e->getMessage();
            Log::error('💥 ' . $this->lastError);
            return null;
        }
    }

    /**
     * Generate trx_id UNIK dengan sistem random yang cerdas:
     * Format: 2026HHHHRR (9-10 digit)
     * - 2026: Tahun tetap
     * - HHHH: 4 digit terakhir HP
     * - RR: 2 angka random (00-99) untuk urutan normal
     * - RRR: 3 angka random (000-999) jika sudah mencapai ratusan
     */
    private function generateTrxId($user): string
    {
        $tahun = '2026';
        $cleanPhone = preg_replace('/\D/', '', $user->phone);
        $last4Digits = substr($cleanPhone, -4);
        
        if (strlen($last4Digits) < 4) {
            $last4Digits = str_pad(substr($user->id, -4), 4, '0', STR_PAD_LEFT);
        }
        
        // 1. Cari urutan terakhir dari database untuk pola 2026+HHHH
        $lastTrx = DB::table('master_users')
            ->where('user_id', $user->id)
            ->where('trx_id', 'LIKE', $tahun . $last4Digits . '%')
            ->orderBy('created_at', 'desc')
            ->first();
        
        $urutan = 1;
        if ($lastTrx && !empty($lastTrx->trx_id)) {
            $oldTrxId = $lastTrx->trx_id;
            // Coba ekstrak urutan dari trx_id lama
            $suffix = substr($oldTrxId, 8); // Ambil bagian setelah 2026HHHH
            if (is_numeric($suffix)) {
                $urutan = (int)$suffix + 1;
            }
        }
        
        // 2. TENTUKAN PANJANG RANDOM BERDASARKAN URUTAN
        $randomLength = 2; // Default 2 digit random
        
        // Jika urutan sudah mencapai ratusan (>= 100), gunakan 3 digit random
        if ($urutan >= 100) {
            $randomLength = 3;
        }
        
        // 3. GENERATE RANDOM NUMBER DENGAN PANJANG YANG TELAH DITENTUKAN
        $maxRandom = pow(10, $randomLength) - 1;
        $randomNumber = str_pad(rand(0, $maxRandom), $randomLength, '0', STR_PAD_LEFT);
        
        // 4. GABUNGKAN SEMUA BAGIAN
        $trxId = $tahun . $last4Digits . $randomNumber;
        
        // 5. PASTIKAN trx_id BELUM PERNAH DIGUNAKAN
        $maxAttempts = 10;
        $attempt = 0;
        
        while ($attempt < $maxAttempts) {
            $exists = DB::table('master_users')
                ->where('trx_id', $trxId)
                ->exists();
            
            if (!$exists) {
                // trx_id unik ditemukan
                Log::info('🔑 Generate trx_id UNIK 2026', [
                    'user_id' => $user->id,
                    'phone' => $user->phone,
                    'clean_phone' => $cleanPhone,
                    'tahun_fixed' => '2026',
                    'last_4_digits' => $last4Digits,
                    'urutan' => $urutan,
                    'random_length' => $randomLength,
                    'random_number' => $randomNumber,
                    'trx_id' => $trxId,
                    'format' => '2026HHHHR' . $randomLength,
                    'length' => strlen($trxId),
                    'last_trx_from_db' => $lastTrx->trx_id ?? 'tidak ada',
                    'attempt' => $attempt + 1
                ]);
                return $trxId;
            }
            
            // Jika sudah ada, generate random baru
            $randomNumber = str_pad(rand(0, $maxRandom), $randomLength, '0', STR_PAD_LEFT);
            $trxId = $tahun . $last4Digits . $randomNumber;
            $attempt++;
        }
        
        // Jika masih bentrok setelah 10 percobaan, tambah 1 digit extra
        $extraRandom = str_pad(rand(0, 9), 1, '0', STR_PAD_LEFT);
        $trxId = $tahun . $last4Digits . $randomNumber . $extraRandom;
        
        Log::warning('⚠️ Trx_id masih konflik, tambah 1 digit extra', [
            'user_id' => $user->id,
            'final_trx_id' => $trxId,
            'extra_digit' => $extraRandom
        ]);
        
        return $trxId;
    }

    /**
     * Generate trx_id untuk retry dengan random yang lebih agresif
     * Format: 2026HHHHRRR (10-11 digit)
     * - Selalu gunakan 3 digit random untuk retry
     * - Tambahkan retryCount ke random seed untuk lebih unik
     */
    private function generateTrxIdForRetry($user, $retryCount): string
    {
        $tahun = '2026';
        $cleanPhone = preg_replace('/\D/', '', $user->phone);
        $last4Digits = substr($cleanPhone, -4);
        
        if (strlen($last4Digits) < 4) {
            $last4Digits = str_pad(substr($user->id, -4), 4, '0', STR_PAD_LEFT);
        }
        
        // Untuk retry, selalu gunakan 3 digit random untuk memastikan keunikan
        $randomLength = 3;
        
        // Gunakan microtime dan retryCount sebagai seed untuk random yang lebih unik
        $seed = time() . $retryCount . $user->id;
        srand(crc32($seed));
        
        // Generate 3 digit random
        $randomSuffix = str_pad(rand(0, 999), 3, '0', STR_PAD_LEFT);
        
        // Reset random seed
        srand();
        
        $trxId = $tahun . $last4Digits . $randomSuffix;
        
        // Pastikan tidak ada duplikat di database
        $maxAttempts = 5;
        $attempt = 0;
        
        while ($attempt < $maxAttempts) {
            $exists = DB::table('master_users')
                ->where('trx_id', $trxId)
                ->exists();
            
            if (!$exists) {
                break;
            }
            
            // Generate random baru jika masih ada duplikat
            $randomSuffix = str_pad(rand(0, 999), 3, '0', STR_PAD_LEFT);
            $trxId = $tahun . $last4Digits . $randomSuffix;
            $attempt++;
        }
        
        // Jika masih bentrok, tambah timestamp micro
        if ($attempt >= $maxAttempts) {
            $micro = substr(microtime(), 2, 2);
            $trxId = $tahun . $last4Digits . $randomSuffix . $micro;
        }
        
        Log::info('🔑 Generate trx_id UNIK untuk retry', [
            'user_id' => $user->id,
            'tahun_fixed' => '2026',
            'last_4_digits' => $last4Digits,
            'retry_count' => $retryCount,
            'random_suffix' => $randomSuffix,
            'trx_id' => $trxId,
            'format' => '2026HHHHRRR',
            'length' => strlen($trxId),
            'attempts' => $attempt + 1
        ]);
        
        return $trxId;
    }

    /**
     * Generate test trx_id
     */
    private function generateTestTrxId(): string
    {
        $tahun = '2026';
        $last4Digits = str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);
        $randomNumber = str_pad(rand(0, 99), 2, '0', STR_PAD_LEFT); // 2 digit random untuk test
        
        return $tahun . $last4Digits . $randomNumber;
    }

    /**
     * Helper function untuk menentukan gelombang berdasarkan bulan
     */
    private function determineGelombang(): string
    {
        $currentMonth = Carbon::now()->month;
        
        $gelombangMapping = [
            1  => '01',  // Januari = gelombang 01
            2  => '02',  // Februari = gelombang 02
            3  => '03',  // Maret = gelombang 03
            4  => '04',  // April = gelombang 04
            5  => '05',  // Mei = gelombang 05
            6  => '06',  // Juni = gelombang 06
            7  => '07',  // Juli = gelombang 07
            8  => '08',  // Agustus = gelombang 08
            9  => '09',  // September = gelombang 09
            10 => '10',  // Oktober = gelombang 10
            11 => '10',  // November = gelombang 10 (skip gelombang 11)
            12 => '01',  // Desember = gelombang 01
        ];

        return $gelombangMapping[$currentMonth] ?? '01';
    }
    
    public function regenerateVirtualAccount($user, $amount = null, $promoCode = null)
    {
        try {
            // VALIDASI KREDENSIAL
            if (empty($this->clientId) || empty($this->secretKey)) {
                $this->lastError = 'Kredensial BNI tidak lengkap';
                Log::error('❌ ' . $this->lastError);
                return null;
            }

            // Ambil data master user
            $masterUser = MasterUser::where('user_id', $user->id)->first();
            if (!$masterUser) {
                $this->lastError = 'Data master user tidak ditemukan';
                Log::error('❌ ' . $this->lastError);
                return null;
            }

            // Gunakan amount yang ada jika tidak ditentukan
            $amount = $amount ?? $masterUser->va_amount ?? 300000;

            // 🔴 GUNAKAN TRX_ID YANG SAMA DARI DATABASE
            $existingTrxId = $masterUser->trx_id;
            
            if (empty($existingTrxId)) {
                $this->lastError = 'TRX_ID tidak ditemukan untuk user ini';
                Log::error('❌ ' . $this->lastError, ['user_id' => $user->id]);
                return null;
            }

            Log::info('🔄 Regenerate VA dengan TRX_ID yang sama', [
                'user_id' => $user->id,
                'old_trx_id' => $existingTrxId,
                'old_va' => $masterUser->va_number,
                'va_status' => $masterUser->va_status,
                'operation' => 'updateBilling' // Menggunakan updateBilling bukan createBilling
            ]);

            // --- 🔹 1. TUTUP BILLING LAMA DENGAN UPDATE STATUS ---
            $closeResult = $this->closeOldBilling($existingTrxId, $masterUser->va_number);
            
            if (!$closeResult) {
                Log::warning('⚠️ Gagal menutup billing lama, lanjutkan dengan createBilling baru');
                // Lanjut dengan createBilling dengan VA baru
                return $this->createNewBillingForRegenerate($user, $amount, $masterUser);
            }

            // --- 🔹 2. BUAT VA BARU DENGAN UPDATE BILLING ---
            // Format VA baru: gunakan format yang sama
            $vaPrefixUtama = '98841625';
            $vaKodeUrut    = '2600';
            
            // Format: 98841625 + 2600 + user_id(4 digit)
            $vaAkhir = str_pad($user->id, 4, '0', STR_PAD_LEFT);
            $newVirtualAccount = $vaPrefixUtama . $vaKodeUrut . $vaAkhir;
            
            // Coba dengan VA yang sama dulu
            $newVirtualAccount = $masterUser->va_number;

            // --- 🔹 Data customer ---
            $phone = preg_replace('/\D/', '', $user->phone);
            $customerPhone = (strlen($phone) >= 9 && strlen($phone) <= 13)
                ? (str_starts_with($phone, '62') ? '0' . substr($phone, 2) : $phone)
                : '08123456789';

            $sanitizedName = substr(preg_replace('/[^a-zA-Z0-9\s]/', '', trim($user->name)), 0, 50);
            if (empty($sanitizedName)) $sanitizedName = 'User ' . $user->id;

            // --- 🔹 Expired date 24 jam ---
            $datetimeExpired = now()->addHours(24)->timezone('Asia/Jakarta')->format('Y-m-d H:i:s');

            Log::info('📤 Mengirim UPDATE BILLING ke BNI dengan TRX_ID sama', [
                'user_id' => $user->id,
                'trx_id' => $existingTrxId,
                'old_va' => $masterUser->va_number,
                'new_va' => $newVirtualAccount,
                'amount' => $amount,
                'expired_date' => $datetimeExpired
            ]);

            // --- 🔹 Kirim UPDATE BILLING ke BNI ---
            $payload = [
                'type'             => 'updateBilling', // PASTIKAN INI UPDATE BILLING
                'client_id'        => $this->clientId,
                'trx_id'           => $existingTrxId, // GUNAKAN TRX_ID YANG SAMA
                'trx_amount'       => $amount,
                'datetime_expired' => $datetimeExpired,
                'customer_name'    => $sanitizedName,
                'virtual_account'  => $newVirtualAccount, // Boleh sama atau beda
            ];

            $encryptedPayload = Bnienc::encrypt($payload, $this->clientId, $this->secretKey);
            $response = Http::timeout(45)
                ->retry(3, 1000)
                ->asJson()
                ->post($this->apiUrl, [
                    'client_id' => $this->clientId,
                    'data' => $encryptedPayload,
                ]);

            if (!$response->successful()) {
                $this->lastError = 'HTTP Error dari BNI: ' . $response->status();
                Log::error('❌ ' . $this->lastError, ['body' => $response->body()]);
                return null;
            }

            $result = $response->json();
            $status = $result['status'] ?? '';
            
            if ($status !== '000') {
                $errorMsg = $result['message'] ?? 'Unknown error';
                
                // Jika update gagal, coba dengan createBilling baru
                if (in_array($status, ['105', '108', '113'])) {
                    Log::warning('⚠️ Update billing gagal, coba create billing baru', [
                        'status' => $status,
                        'message' => $errorMsg,
                        'user_id' => $user->id
                    ]);
                    
                    // Coba buat billing baru
                    return $this->createNewBillingForRegenerate($user, $amount, $masterUser);
                }
                
                $this->lastError = 'Gagal update VA di BNI: ' . $errorMsg;
                Log::error('❌ ' . $this->lastError, ['response' => $result]);
                return null;
            }

            // --- 🔓 Dekripsi data sukses ---
            $decrypted = Bnienc::decrypt($result['data'], $this->clientId, $this->secretKey);
            
            // Update database dengan data baru
            $this->updateVADatabase($masterUser, $decrypted, $datetimeExpired);

            Log::info('✅ UPDATE BILLING berhasil di BNI dengan TRX_ID sama', [
                'user_id' => $user->id,
                'trx_id' => $existingTrxId,
                'new_va' => $decrypted['virtual_account'] ?? $newVirtualAccount,
                'expired_at' => $datetimeExpired,
                'status' => 'updated'
            ]);

            return [
                'status'           => true,
                'virtual_account'  => $decrypted['virtual_account'] ?? $newVirtualAccount,
                'trx_id'           => $existingTrxId, // TRX_ID TETAP SAMA
                'datetime_expired' => $datetimeExpired,
                'amount'           => $amount,
                'operation'        => 'updateBilling',
                'message'          => 'VA berhasil diupdate dengan TRX_ID yang sama'
            ];

        } catch (Exception $e) {
            $this->lastError = 'Exception di regenerateVirtualAccount: ' . $e->getMessage();
            Log::error('💥 ' . $this->lastError, [
                'trace' => $e->getTraceAsString()
            ]);
            return null;
        }
    }
    
    /**
     * Update VA database
     */
    private function updateVADatabase($masterUser, $bniData, $datetimeExpired)
    {
        try {
            $updateData = [
                'va_number'       => $bniData['virtual_account'] ?? $masterUser->va_number,
                'va_expired_at'   => $datetimeExpired,
                'va_status'       => 'pending',
                'updated_at'      => now(),
            ];

            // Update master user
            $masterUser->update($updateData);

            Log::info('🗂 Database updated untuk regenerate VA', [
                'user_id' => $masterUser->user_id,
                'va_number' => $updateData['va_number'],
                'expired_at' => $datetimeExpired,
                'status' => 'pending'
            ]);

            return true;

        } catch (Exception $e) {
            Log::error('💥 Error updateVADatabase: ' . $e->getMessage());
            return false;
        }
    }

    private function createNewBillingForRegenerate($user, $amount, $masterUser)
    {
        try {
            // Generate trx_id baru untuk regenerate (dengan random 3 digit)
            $newTrxId = $this->generateTrxIdForRegenerate($user);

            // Generate VA baru
            $vaPrefixUtama = '98841625';
            $vaKodeUrut    = '2600';
            $vaAkhir = str_pad($user->id, 4, '0', STR_PAD_LEFT);
            $virtualAccount = $vaPrefixUtama . $vaKodeUrut . $vaAkhir;

            // Data customer
            $phone = preg_replace('/\D/', '', $user->phone);
            $customerPhone = (strlen($phone) >= 9 && strlen($phone) <= 13)
                ? (str_starts_with($phone, '62') ? '0' . substr($phone, 2) : $phone)
                : '08123456789';

            $sanitizedName = substr(preg_replace('/[^a-zA-Z0-9\s]/', '', trim($user->name)), 0, 50);
            if (empty($sanitizedName)) $sanitizedName = 'User ' . $user->id;

            $datetimeExpired = now()->addHours(24)->timezone('Asia/Jakarta')->format('Y-m-d H:i:s');

            Log::info('📤 CREATE BILLING baru untuk regenerate', [
                'user_id' => $user->id,
                'old_trx_id' => $masterUser->trx_id,
                'new_trx_id' => $newTrxId,
                'va' => $virtualAccount,
                'expired' => $datetimeExpired
            ]);

            $payload = [
                'type'             => 'createBilling',
                'client_id'        => $this->clientId,
                'trx_id'           => $newTrxId, // GUNAKAN TRX_ID BARU
                'trx_amount'       => $amount,
                'billing_type'     => 'c',
                'datetime_expired' => $datetimeExpired,
                'virtual_account'  => $virtualAccount,
                'customer_name'    => $sanitizedName,
                'customer_email'   => $user->email,
                'customer_phone'   => $customerPhone,
                'description'      => 'Regenerate VA ' . date('Y-m-d'),
            ];

            $encryptedPayload = Bnienc::encrypt($payload, $this->clientId, $this->secretKey);
            $response = Http::timeout(45)
                ->retry(3, 1000)
                ->asJson()
                ->post($this->apiUrl, [
                    'client_id' => $this->clientId,
                    'data' => $encryptedPayload,
                ]);

            if (!$response->successful()) {
                $this->lastError = 'HTTP Error dari BNI: ' . $response->status();
                Log::error('❌ ' . $this->lastError, ['body' => $response->body()]);
                return null;
            }

            $result = $response->json();
            $status = $result['status'] ?? '';
            
            if ($status !== '000') {
                $errorMsg = $result['message'] ?? 'Unknown error';
                
                // Handle duplicate trx_id
                if ($status === '105') {
                    // Generate trx_id baru untuk retry
                    $retryTrxId = $this->generateTrxIdForRetry($user, 1);
                    $payload['trx_id'] = $retryTrxId;
                    
                    Log::warning('🔄 Duplicate trx_id, coba dengan trx_id baru', [
                        'old_trx_id' => $newTrxId,
                        'new_trx_id' => $retryTrxId
                    ]);
                    
                    $encryptedPayload = Bnienc::encrypt($payload, $this->clientId, $this->secretKey);
                    $response = Http::timeout(30)
                        ->retry(2, 2000)
                        ->asJson()
                        ->post($this->apiUrl, [
                            'client_id' => $this->clientId,
                            'data' => $encryptedPayload,
                        ]);
                    
                    if ($response->successful()) {
                        $result = $response->json();
                        if (($result['status'] ?? '') === '000') {
                            $newTrxId = $retryTrxId; // Update trx_id
                        }
                    }
                } else {
                    $this->lastError = 'Gagal create VA di BNI: ' . $errorMsg;
                    Log::error('❌ ' . $this->lastError, ['response' => $result]);
                    return null;
                }
            }

            // Dekripsi data sukses
            $decrypted = Bnienc::decrypt($result['data'], $this->clientId, $this->secretKey);
            
            // Update database
            $this->updateVADatabase($masterUser, $decrypted, $datetimeExpired);

            Log::info('✅ CREATE BILLING baru berhasil', [
                'user_id' => $user->id,
                'trx_id' => $newTrxId,
                'va' => $decrypted['virtual_account'] ?? $virtualAccount,
                'expired' => $datetimeExpired
            ]);

            return [
                'status'           => true,
                'virtual_account'  => $decrypted['virtual_account'] ?? $virtualAccount,
                'trx_id'           => $newTrxId, // TRX_ID YANG DIGUNAKAN
                'datetime_expired' => $datetimeExpired,
                'amount'           => $amount,
                'operation'        => 'createBilling',
                'message'          => 'VA baru berhasil dibuat'
            ];

        } catch (Exception $e) {
            Log::error('💥 Error createNewBillingForRegenerate: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Generate trx_id untuk regenerate dengan format: 2026HHHHRRR
     * Format: 2026 + 4 digit HP + 3 digit random
     */
    private function generateTrxIdForRegenerate($user): string
    {
        $tahun = '2026';
        $cleanPhone = preg_replace('/\D/', '', $user->phone);
        $last4Digits = substr($cleanPhone, -4);
        
        if (strlen($last4Digits) < 4) {
            $last4Digits = str_pad(substr($user->id, -4), 4, '0', STR_PAD_LEFT);
        }
        
        // Untuk regenerate, selalu gunakan 3 digit random
        $randomNumber = str_pad(rand(0, 999), 3, '0', STR_PAD_LEFT);
        
        // Gabungkan semua bagian
        $trxId = $tahun . $last4Digits . $randomNumber;
        
        // Pastikan tidak ada duplikat
        $maxAttempts = 5;
        $attempt = 0;
        
        while ($attempt < $maxAttempts) {
            $exists = DB::table('master_users')
                ->where('trx_id', $trxId)
                ->exists();
            
            if (!$exists) {
                break;
            }
            
            $randomNumber = str_pad(rand(0, 999), 3, '0', STR_PAD_LEFT);
            $trxId = $tahun . $last4Digits . $randomNumber;
            $attempt++;
        }
        
        Log::info('🔑 Generate trx_id untuk regenerate', [
            'user_id' => $user->id,
            'tahun' => $tahun,
            'last_4_digits' => $last4Digits,
            'random_number' => $randomNumber,
            'trx_id' => $trxId,
            'format' => '2026HHHHRRR',
            'length' => strlen($trxId),
            'attempts' => $attempt + 1
        ]);
        
        return $trxId;
    }

    public function checkVAAvailability($virtualAccount)
    {
        try {
            $payload = [
                'type' => 'inquiryBilling',
                'client_id' => $this->clientId,
                'virtual_account' => $virtualAccount,
            ];

            $encryptedPayload = Bnienc::encrypt($payload, $this->clientId, $this->secretKey);
            $response = Http::timeout(30)->asJson()->post($this->apiUrl, [
                'client_id' => $this->clientId,
                'data' => $encryptedPayload,
            ]);

            if ($response->successful()) {
                $result = $response->json();
                if (($result['status'] ?? '') === '000') {
                    $decrypted = Bnienc::decrypt($result['data'], $this->clientId, $this->secretKey);
                    return [
                        'exists' => true,
                        'status' => $decrypted['status'] ?? 'unknown',
                        'trx_id' => $decrypted['trx_id'] ?? null,
                        'amount' => $decrypted['trx_amount'] ?? null,
                        'expired' => $decrypted['datetime_expired'] ?? null,
                    ];
                }
            }
            
            return ['exists' => false];
            
        } catch (Exception $e) {
            Log::error('Error checking VA availability: ' . $e->getMessage());
            return ['exists' => false, 'error' => $e->getMessage()];
        }
    }

    public function closeOldBilling($trxId, $virtualAccount)
    {
        try {
            $payload = [
                'type' => 'updateBilling',
                'client_id' => $this->clientId,
                'trx_id' => $trxId,
                'trx_amount' => 1, // Amount minimal
                'datetime_expired' => now()->subDay()->timezone('Asia/Jakarta')->format('Y-m-d H:i:s'), // Set expired kemarin
                'customer_name' => 'VA_CLOSED',
            ];

            Log::info('🔒 Menutup billing lama', [
                'trx_id' => $trxId,
                'va' => $virtualAccount
            ]);

            $encryptedPayload = Bnienc::encrypt($payload, $this->clientId, $this->secretKey);
            $response = Http::timeout(30)
                ->asJson()
                ->post($this->apiUrl, [
                    'client_id' => $this->clientId,
                    'data' => $encryptedPayload,
                ]);

            if ($response->successful()) {
                $result = $response->json();
                Log::info('✅ Billing lama ditutup', [
                    'trx_id' => $trxId,
                    'status' => $result['status'] ?? 'unknown'
                ]);
                return true;
            }
            
            return false;
            
        } catch (Exception $e) {
            Log::error('❌ Gagal menutup billing lama', [
                'error' => $e->getMessage(),
                'trx_id' => $trxId
            ]);
            return false;
        }
    }
    
    public function canRegenerateVA($masterUser): bool
    {
        if (!$masterUser) {
            return false;
        }

        // Cek status VA
        if ($masterUser->va_status === 'paid') {
            return false; // Sudah bayar, tidak bisa regenerate
        }

        // Cek expired date
        if ($masterUser->va_expired_at && now()->gt($masterUser->va_expired_at)) {
            return true; // Sudah expired, bisa regenerate
        }

        // Cek jika VA sudah expired atau akan expired dalam 1 jam
        if ($masterUser->va_expired_at) {
            $oneHourBefore = now()->addHour();
            if (now()->gt($masterUser->va_expired_at) || $oneHourBefore->gt($masterUser->va_expired_at)) {
                return true;
            }
        }

        // Default: tidak bisa regenerate
        return false;
    }
    
    /**
     * Get VA history untuk user
     */
    public function getVAHistory($userId)
    {
        try {
            $history = DB::table('master_users_va_history')
                ->where('user_id', $userId)
                ->orderBy('created_at', 'desc')
                ->get();
                
            return $history;
        } catch (Exception $e) {
            Log::error('Error get VA history: ' . $e->getMessage());
            return collect();
        }
    }
    
    /**
     * Log VA history
     */
    private function logVAHistory($userId, $trxId, $vaNumber, $operation, $oldData = null)
    {
        try {
            DB::table('master_users_va_history')->insert([
                'user_id' => $userId,
                'trx_id' => $trxId,
                'va_number' => $vaNumber,
                'operation' => $operation,
                'old_data' => $oldData ? json_encode($oldData) : null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            Log::info('📝 VA history logged', [
                'user_id' => $userId,
                'trx_id' => $trxId,
                'operation' => $operation
            ]);
            
        } catch (Exception $e) {
            Log::error('Error logging VA history: ' . $e->getMessage());
        }
    }
}