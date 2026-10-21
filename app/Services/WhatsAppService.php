<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use App\Jobs\SendWhatsAppMessage;
use Exception;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\TimeoutException;

class WhatsAppService
{
    public  $serverUrl;
    protected $timeout;
    protected $retryAttempts;
    protected $testMode;
    protected $testPhone;
    protected $logOnly;

    public function __construct()
    {
        $this->serverUrl = config('whatsapp.server.url', 'http://103.23.198.160:3009');
        $this->timeout = config('whatsapp.server.timeout', 30);
        $this->retryAttempts = config('whatsapp.server.retry_attempts', 3);
        $this->testMode = config('whatsapp.test.enabled', false);
        $this->testPhone = config('whatsapp.test.test_phone', '');
        $this->logOnly = config('whatsapp.test.log_only', false);
    }

    /**
     * Format nomor telepon untuk WhatsApp
     */
    public function formatPhoneNumber($phoneNumber)
    {
        // Jika sudah diformat dengan @c.us, return langsung
        if (strpos($phoneNumber, '@c.us') !== false || strpos($phoneNumber, '@g.us') !== false) {
            return $phoneNumber;
        }
        
        // Hapus karakter spesial
        $cleanNumber = preg_replace('/[^0-9]/', '', $phoneNumber);
        
        // Jika diawali dengan 0, ganti dengan 62
        if (strpos($cleanNumber, '0') === 0) {
            $cleanNumber = '62' . substr($cleanNumber, 1);
        }
        
        // Jika diawali dengan 62, biarkan
        // Jika diawali dengan 8, tambahkan 62
        if (strpos($cleanNumber, '8') === 0 && strlen($cleanNumber) >= 9) {
            $cleanNumber = '62' . $cleanNumber;
        }
        
        // Hapus + jika ada
        $cleanNumber = ltrim($cleanNumber, '+');
        
        // Validasi panjang
        if (strlen($cleanNumber) < 10 || strlen($cleanNumber) > 15) {
            throw new Exception('Nomor telepon tidak valid: ' . $phoneNumber . ' (Panjang: ' . strlen($cleanNumber) . ')');
        }
        
        return $cleanNumber . '@c.us';
    }

    /**
     * Cek status koneksi WhatsApp
     */
    public function checkStatus()
    {
        try {
            $cacheKey = 'whatsapp_status_' . md5($this->serverUrl);
            $cached = Cache::get($cacheKey);
            
            if ($cached && now()->diffInSeconds($cached['timestamp']) < 30) {
                return $cached['data'];
            }
            
            $response = Http::timeout(10)->get($this->serverUrl . '/api/status');
            
            $data = [
                'success' => $response->successful(),
                'status' => $response->json(),
                'timestamp' => now()
            ];
            
            Cache::put($cacheKey, $data, 30); // Cache 30 detik
            
            return $data;
            
        } catch (Exception $e) {
            Log::error('Error checking WhatsApp status: ' . $e->getMessage());
            
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'timestamp' => now()
            ];
        }
    }
public function sendMessage($phoneNumber, $message, $options = [])
{
    // LOG AWAL YANG SANGAT DETAIL
    Log::channel('daily')->info('═══════════════════════════════════════════');
    Log::channel('daily')->info('📤 START SEND MESSAGE');
    Log::channel('daily')->info('📱 To: ' . $phoneNumber);
    Log::channel('daily')->info('📝 Message preview: ' . substr($message, 0, 100));
    Log::channel('daily')->info('⏰ Time: ' . now()->toDateTimeString());
    Log::channel('daily')->info('═══════════════════════════════════════════');
    
    try {
        // Format nomor telepon untuk API
        $formattedNumber = $this->formatPhoneNumberForApi($phoneNumber);
        
        Log::info('🔵 [WHATSAPP SERVICE] Mengirim pesan', [
            'original_number' => $phoneNumber,
            'formatted_number' => $formattedNumber,
            'message_length' => strlen($message),
            'message_preview' => substr($message, 0, 150),
            'server_url' => $this->serverUrl,
            'timestamp' => now()->toDateTimeString(),
            'options' => $options
        ]);
        
        // Payload untuk Node.js server
        $payload = [
            'to' => $formattedNumber,
            'message' => $message
        ];
        
        if (!empty($options)) {
            $payload = array_merge($payload, $options);
        }
        
        Log::info('📤 Payload ke WhatsApp Server:', $payload);
        
        $endpoint = '/api/send';
        $fullUrl = $this->serverUrl . $endpoint;
        
        Log::info("🔄 Mengirim ke endpoint: {$fullUrl}");
        
        // LOG SEBELUM KIRIM
        Log::channel('daily')->info('📤 Sending to WhatsApp Server:', [
            'url' => $fullUrl,
            'payload' => $payload,
            'timeout' => $this->timeout
        ]);
        
        $response = Http::timeout($this->timeout)
            ->retry(2, 1000, function ($exception, $request) {
                Log::warning('🔄 Retrying WhatsApp request:', [
                    'exception' => $exception->getMessage(),
                    'attempt' => $request->attempts()
                ]);
                return $exception instanceof ConnectionException || 
                       $exception instanceof TimeoutException;
            })
            ->post($fullUrl, $payload);
        
        $success = $response->successful();
        $statusCode = $response->status();
        $responseBody = $response->body();
        
        // LOG RESPONSE DETAIL
        Log::channel('daily')->info('📥 Response dari WhatsApp Server:', [
            'success' => $success,
            'status_code' => $statusCode,
            'body_preview' => substr($responseBody, 0, 500),
            'headers' => $response->headers(),
            'endpoint' => $endpoint
        ]);
        
        Log::info("📥 Response dari WhatsApp Server:", [
            'success' => $success,
            'status_code' => $statusCode,
            'body' => $responseBody,
            'headers' => $response->headers(),
            'endpoint' => $endpoint
        ]);
        
        // Parse response
        $responseData = [];
        try {
            $responseData = $response->json();
            Log::info('✅ Response JSON parsed:', $responseData);
        } catch (\Exception $e) {
            Log::warning('❌ Gagal parse JSON response:', [
                'body' => $responseBody,
                'error' => $e->getMessage()
            ]);
        }
        
        if ($success) {
            Log::channel('daily')->info('✅ WhatsApp - Pesan berhasil dikirim', [
                'to' => $formattedNumber,
                'message_id' => $responseData['data']['messageId'] ?? 'unknown',
                'timestamp' => $responseData['data']['timestamp'] ?? null
            ]);
            
            Log::info('✅ WhatsApp - Pesan berhasil dikirim', [
                'to' => $formattedNumber,
                'response' => $responseData,
                'endpoint' => $endpoint,
                'response_id' => $responseData['data']['messageId'] ?? 'unknown'
            ]);
            
            return [
                'success' => true,
                'data' => $responseData,
                'endpoint_used' => $endpoint,
                'status_code' => $statusCode
            ];
        } else {
            Log::channel('daily')->error('❌ WhatsApp Server response error', [
                'status_code' => $statusCode,
                'body' => $responseBody,
                'phone' => $formattedNumber
            ]);
            
            Log::error('❌ WhatsApp Server response error', [
                'status_code' => $statusCode,
                'body' => $responseBody,
                'phone' => $formattedNumber,
                'response_data' => $responseData
            ]);
            
            return [
                'success' => false,
                'error' => 'WhatsApp server error: ' . $statusCode,
                'response_body' => $responseBody,
                'response_data' => $responseData,
                'status_code' => $statusCode
            ];
        }
        
    } catch (\Exception $e) {
        Log::channel('daily')->error('💥 WhatsApp Service - Exception fatal:', [
            'phone' => $phoneNumber,
            'error' => $e->getMessage(),
            'time' => now()->toDateTimeString()
        ]);
        
        Log::error('💥 WhatsApp Service - Exception fatal:', [
            'phone' => $phoneNumber,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
            'server_url' => $this->serverUrl,
            'timestamp' => now()->toDateTimeString()
        ]);
        
        return [
            'success' => false,
            'error' => 'Exception: ' . $e->getMessage()
        ];
    }
}
/**
 * Test koneksi langsung ke WhatsApp server dengan detail
 */
public function testConnectionDetailed()
{
    try {
        Log::info('🔍 Testing WhatsApp server connection in detail...', [
            'server_url' => $this->serverUrl,
            'endpoints_to_test' => ['/api/health', '/api/status', '/api/send']
        ]);
        
        $results = [];
        
        // Test endpoint health
        try {
            $healthResponse = Http::timeout(10)->get($this->serverUrl . '/api/health');
            $results['health'] = [
                'status' => $healthResponse->status(),
                'success' => $healthResponse->successful(),
                'body' => $healthResponse->json() ?? []
            ];
        } catch (\Exception $e) {
            $results['health'] = ['error' => $e->getMessage()];
        }
        
        // Test endpoint status
        try {
            $statusResponse = Http::timeout(10)->get($this->serverUrl . '/api/status');
            $results['status'] = [
                'status' => $statusResponse->status(),
                'success' => $statusResponse->successful(),
                'body' => $statusResponse->json() ?? []
            ];
        } catch (\Exception $e) {
            $results['status'] = ['error' => $e->getMessage()];
        }
        
        // Test send endpoint (GET untuk test)
        try {
            $sendResponse = Http::timeout(10)->get($this->serverUrl . '/api/send');
            $results['send_get'] = [
                'status' => $sendResponse->status(),
                'success' => $sendResponse->successful(),
                'body' => $sendResponse->body()
            ];
        } catch (\Exception $e) {
            $results['send_get'] = ['error' => $e->getMessage()];
        }
        
        Log::info('📊 WhatsApp Server Detailed Test Results:', $results);
        
        return [
            'success' => ($results['health']['success'] ?? false) && 
                        ($results['status']['success'] ?? false),
            'results' => $results,
            'server_url' => $this->serverUrl,
            'timestamp' => now()->toDateTimeString()
        ];
        
    } catch (\Exception $e) {
        Log::error('🔴 Error testing WhatsApp server:', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        return [
            'success' => false,
            'error' => $e->getMessage()
        ];
    }
}
private function formatPhoneNumberForApi($phoneNumber)
{
    $cleanNumber = preg_replace('/[^0-9]/', '', $phoneNumber);
    
    // Jika diawali dengan 0, ganti dengan 62
    if (strpos($cleanNumber, '0') === 0) {
        $cleanNumber = '62' . substr($cleanNumber, 1);
    }
    
    // Pastikan tidak ada awalan +
    $cleanNumber = ltrim($cleanNumber, '+');
    
    // Validasi panjang minimal
    if (strlen($cleanNumber) < 10) {
        throw new Exception('Nomor telepon terlalu pendek: ' . $cleanNumber);
    }
    
    // JANGAN tambahkan @c.us di sini - biarkan Node.js yang handle
    return $cleanNumber;
}

    public function sendMessageViaQueue($phoneNumber, $message, $options = [], $delay = null)
    {
        if (!config('whatsapp.queue.enabled', true)) {
            return $this->sendMessage($phoneNumber, $message, $options);
        }
        
        $delay = $delay ?? config('whatsapp.queue.delay', 0);
        
        dispatch(new SendWhatsAppMessage($phoneNumber, $message, $options))
            ->onConnection(config('whatsapp.queue.connection', 'database'))
            ->onQueue(config('whatsapp.queue.queue', 'whatsapp'))
            ->delay($delay);
        
        return [
            'success' => true,
            'message' => 'Pesan telah ditambahkan ke queue'
        ];
    }

    /**
     * Kirim pesan dengan template
     */
    public function sendTemplate($phoneNumber, $templateKey, array $data = [], $options = [])
{
    $template = config("whatsapp.templates.{$templateKey}");
    
    if (!$template) {
        throw new Exception("Template '{$templateKey}' tidak ditemukan");
    }
    
    $message = $template['body'];
    
    // Auto-replace common placeholders
    $defaultData = [
        'app_name' => config('app.name'),
        'current_date' => now()->format('d/m/Y'),
        'current_year' => now()->year
    ];
    
    $allData = array_merge($defaultData, $data);
    
    foreach ($allData as $key => $value) {
        $message = str_replace(':' . $key, $value, $message);
    }
    
    return $this->sendMessageViaQueue($phoneNumber, $message, $options);
}
    
    /**
     * Kirim informasi akun (forgot account)
     */
    public function sendAccountInfo($user, $masterUser, $resetUrl)
    {
        $data = [
            'name' => $user->name,
            'trx_id' => $masterUser->trx_id,
            'va_number' => $masterUser->va_number ?? 'Tidak tersedia',
            'reset_url' => $resetUrl,
            'app_name' => config('app.name')
        ];
        
        return $this->sendTemplateMessage(
            $user->phone,
            'forgot_account',
            $data
        );
    }

    /**
     * Kirim notifikasi reset password sukses
     */
    public function sendPasswordResetSuccess($user)
    {
        $data = [
            'name' => $user->name,
            'app_name' => config('app.name')
        ];
        
        return $this->sendTemplateMessage(
            $user->phone,
            'password_reset_success',
            $data
        );
    }

    /**
     * Kirim pengingat pembayaran
     */
    public function sendPaymentReminder($user, $masterUser)
    {
        $data = [
            'name' => $user->name,
            'trx_id' => $masterUser->trx_id,
            'va_number' => $masterUser->va_number ?? 'Tidak tersedia',
            'amount' => 'Rp ' . number_format($masterUser->va_amount ?? 0, 0, ',', '.'),
            'due_date' => $masterUser->va_expired_at 
                ? $masterUser->va_expired_at->format('d M Y H:i')
                : 'Segera',
            'app_name' => config('app.name')
        ];
        
        return $this->sendTemplateMessage(
            $user->phone,
            'payment_reminder',
            $data
        );
    }

    /**
     * Kirim notifikasi registrasi sukses dengan detail username
     * (Menggantikan versi lama)
     */
    public function sendRegistrationSuccess($user, $masterUser): array
    {
        try {
            // Ambil password dari 8 digit terakhir nomor HP
            $phone = preg_replace('/\D/', '', $user->phone);
            $passwordDigits = substr($phone, -8);
            $password = 'PMB' . $passwordDigits;
            
            $username = $masterUser->trx_id ?? 'Tidak tersedia';
            
            $data = [
                'name' => $user->name,
                'trx_id' => $username,
                'username' => $username, // Tambah field username
                'va_number' => $masterUser->va_number ?? 'Tidak tersedia',
                'password' => $password,
                'amount' => 'Rp ' . number_format($masterUser->va_amount ?? 0, 0, ',', '.'),
                'expired_date' => $masterUser->va_expired_at 
                    ? $masterUser->va_expired_at->format('d M Y H:i')
                    : now()->addDays(1)->format('d M Y H:i'),
                'app_name' => config('app.name'),
                'gelombang' => $masterUser->gelombang ?? '1',
                'login_url' => route('login')
            ];
            
            Log::info('WhatsApp Service - Sending registration success', [
                'user_id' => $user->id,
                'username' => $username,
                'trx_id' => $data['trx_id'],
                'phone' => $user->phone
            ]);
            
            return $this->sendTemplateMessage(
                $user->phone,
                'registration_success',
                $data
            );
            
        } catch (Exception $e) {
            Log::error('WhatsApp Service - Error send registration success', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Kirim pesan ke banyak nomor sekaligus
     */
    public function broadcast($phoneNumbers, $message, $options = [])
    {
        $results = [];
        
        foreach ($phoneNumbers as $phoneNumber) {
            $results[$phoneNumber] = $this->sendMessageViaQueue($phoneNumber, $message, $options);
        }
        
        return $results;
    }

    /**
     * Dapatkan QR code
     */
    public function getQrCode()
    {
        try {
            $response = Http::timeout(10)->get($this->serverUrl . '/api/qr');
            
            if ($response->successful()) {
                return $response->json();
            }
            
            return null;
        } catch (Exception $e) {
            Log::error('Error getting QR code: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Restart WhatsApp client
     */
    public function restartClient()
    {
        try {
            $response = Http::timeout(15)->post($this->serverUrl . '/api/restart');
            return $response->successful();
        } catch (Exception $e) {
            Log::error('Error restarting WhatsApp client: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete WhatsApp session
     */
    public function deleteSession()
    {
        try {
            $response = Http::timeout(15)->post($this->serverUrl . '/api/delete-session');
            return $response->successful();
        } catch (Exception $e) {
            Log::error('Error deleting WhatsApp session: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Cek apakah WhatsApp ready
     */
    public function isReady()
    {
        $status = $this->checkStatus();
        
        if (!$status['success']) {
            return false;
        }
        
        $statusData = $status['status']['data'] ?? null;
        
        if (!$statusData) {
            return false;
        }
        
        $isConnected = $statusData['ready'] ?? false;
        $isAuthenticated = $statusData['authenticated'] ?? false;
        
        return $isConnected && $isAuthenticated;
    }
    
    /**
     * Kirim notifikasi reset password
     */
    public function sendPasswordResetNotification($user, $resetUrl)
    {
        $data = [
            'name' => $user->name,
            'reset_url' => $resetUrl,
            'email' => $user->email,
            'app_name' => config('app.name')
        ];
        
        return $this->sendTemplateMessage(
            $user->phone,
            'password_reset',
            $data
        );
    }
    
    /**
     * Kirim username/trx_id ke WhatsApp setelah registrasi
     * Metode tambahan untuk mengirim pesan singkat
     */
    public function sendUsernameToWhatsApp($user, $masterUser): array
    {
        try {
            // Ambil username dari trx_id
            $username = $masterUser->trx_id ?? 'Tidak tersedia';
            
            // Ambil password dari 8 digit terakhir nomor HP
            $phone = preg_replace('/\D/', '', $user->phone);
            $passwordDigits = substr($phone, -8);
            $password = 'PMB' . $passwordDigits;
            
            // Buat pesan yang informatif
            $message = "Halo {$user->name}!\n\n" .
                      "🎉 *PENDAFTARAN BERHASIL* 🎉\n\n" .
                      "Berikut adalah informasi akun Anda:\n" .
                      "• *Username*: `{$username}`\n" .
                      "• *Password*: `{$password}`\n\n" .
                      "📱 *Simpan informasi ini dengan baik!*\n\n" .
                      "Silakan login menggunakan username dan password di atas.\n\n" .
                      "Jika ada pertanyaan, hubungi admin kami.\n\n" .
                      "Terima kasih,\n" .
                      "Tim PMB " . config('app.name');
            
            Log::info('WhatsApp Service - Mengirim username ke WhatsApp', [
                'user_id' => $user->id,
                'username' => $username,
                'password' => $password,
                'phone' => $user->phone
            ]);
            
            // Kirim pesan via queue
            return $this->sendMessageViaQueue(
                $user->phone,
                $message,
                ['delay' => 5] // Delay 5 detik setelah registrasi
            );
            
        } catch (Exception $e) {
            Log::error('WhatsApp Service - Error sending username to WhatsApp', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    public function testConnection()
{
    try {
        $response = Http::timeout(10)->get($this->serverUrl . '/api/health');
        
        Log::info('WhatsApp Server Health Check:', [
            'url' => $this->serverUrl . '/api/health',
            'status' => $response->status(),
            'body' => $response->body()
        ]);
        
        return $response->successful();
        
    } catch (Exception $e) {
        Log::error('WhatsApp Server Connection Error:', [
            'url' => $this->serverUrl,
            'error' => $e->getMessage()
        ]);
        return false;
    }
}

public function sendCompleteLoginInfo($user, $masterUser): array
{
    // LOG AWAL YANG SANGAT DETAIL
    Log::channel('daily')->info('═══════════════════════════════════════════');
    Log::channel('daily')->info('🎯 START: sendCompleteLoginInfo');
    Log::channel('daily')->info('👤 User ID: ' . $user->id);
    Log::channel('daily')->info('📱 Phone: ' . $user->phone);
    Log::channel('daily')->info('🔑 TRX ID: ' . ($masterUser->trx_id ?? 'N/A'));
    Log::channel('daily')->info('⏰ Time: ' . now()->toDateTimeString());
    Log::channel('daily')->info('═══════════════════════════════════════════');
    
    try {
        Log::info('🔄 [sendCompleteLoginInfo] Memulai proses', [
            'user_id' => $user->id,
            'phone' => $user->phone,
            'trx_id' => $masterUser->trx_id,
            'va_number' => $masterUser->va_number,
            'method_called' => 'sendCompleteLoginInfo'
        ]);
        
        // Format phone untuk password
        $phone = preg_replace('/\D/', '', $user->phone);
        $passwordDigits = substr($phone, -8);
        $password = 'PMB' . $passwordDigits;
        
        Log::info('🔐 Password generated:', [
            'original_phone' => $user->phone,
            'clean_phone' => $phone,
            'last_8_digits' => $passwordDigits,
            'password' => $password
        ]);
        
        $username = $masterUser->trx_id;
        $vaNumber = $masterUser->va_number ?? 'Sedang diproses';
        
        // Format amount
        $amount = $masterUser->va_amount ?? 0;
        $formattedAmount = 'Rp ' . number_format($amount, 0, ',', '.');
        
        // Format expired date
        $expiredDate = $masterUser->va_expired_at 
            ? $masterUser->va_expired_at->format('d F Y H:i') 
            : '24 jam setelah pendaftaran';
        
        // Status pembayaran
        $paymentStatus = ($masterUser->va_status === 'paid' || $masterUser->va_status === 'PAID')
            ? '✅ LUNAS' 
            : '⏳ MENUNGGU PEMBAYARAN';
        
        // Buat pesan lengkap
        $message = "🎉 *PENDAFTARAN PMB BERHASIL* 🎉\n\n" .
                  "Halo *{$user->name}*,\n\n" .
                  "Berikut adalah informasi akun dan pembayaran Anda:\n\n" .
                  "📋 *INFORMASI AKUN*\n" .
                  "┌ Username: `{$username}`\n" .
                  "└ Password: `{$password}`\n\n" .
                  "💳 *INFORMASI PEMBAYARAN*\n" .
                  "┌ Virtual Account: `{$vaNumber}`\n" .
                  "├ Jumlah: {$formattedAmount}\n" .
                  "├ Status: {$paymentStatus}\n" .
                  "└ Batas Waktu: {$expiredDate}\n\n" .
                  "🔗 *TAHAP SELANJUTNYA*\n" .
                  "1. Login di: " . route('login') . "\n" .
                  "2. Gunakan username & password di atas\n" .
                  "3. Lakukan pembayaran sebelum {$expiredDate}\n" .
                  "4. Akses dashboard mahasiswa\n\n" .
                  "⚠️ *PENTING*\n" .
                  "- Simpan informasi login ini!\n" .
                  "- Jangan bagikan password Anda!\n" .
                  "- Hubungi admin jika ada kendala.\n\n" .
                  "Terima kasih telah mendaftar,\n" .
                  "Tim PMB " . config('app.name');
        
        Log::info('📝 [sendCompleteLoginInfo] Pesan disiapkan', [
            'message_length' => strlen($message),
            'message_preview' => substr($message, 0, 200),
            'username_included' => $username,
            'password_included' => $password,
            'va_included' => $vaNumber
        ]);
        
        // ✅ GUNAKAN sendMessage LANGSUNG
        Log::info('📤 [sendCompleteLoginInfo] Mengirim pesan via sendMessage...');
        $result = $this->sendMessage($user->phone, $message, [
            'priority' => 'high'
        ]);

        Log::info('📨 [sendCompleteLoginInfo] Hasil pengiriman:', [
            'success' => $result['success'] ?? false,
            'error' => $result['error'] ?? 'No error',
            'message_id' => $result['data']['data']['messageId'] ?? 'N/A',
            'status_code' => $result['status_code'] ?? 'N/A'
        ]);
        
        Log::channel('daily')->info('═══════════════════════════════════════════');
        Log::channel('daily')->info('🎯 END: sendCompleteLoginInfo');
        Log::channel('daily')->info('✅ Success: ' . ($result['success'] ? 'Ya' : 'Tidak'));
        Log::channel('daily')->info('📱 Message ID: ' . ($result['data']['data']['messageId'] ?? 'N/A'));
        Log::channel('daily')->info('═══════════════════════════════════════════');
        
        return $result;
        
    } catch (Exception $e) {
        Log::channel('daily')->error('💥 ERROR in sendCompleteLoginInfo:', [
            'user_id' => $user->id,
            'error' => $e->getMessage(),
            'time' => now()->toDateTimeString()
        ]);
        
        Log::error('💥 [sendCompleteLoginInfo] Error:', [
            'user_id' => $user->id,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        
        return [
            'success' => false,
            'error' => $e->getMessage()
        ];
    }
}
    private function extractPasswordFromUser($user): string
    {
        // Ambil 8 digit terakhir dari nomor HP
        $phone = preg_replace('/\D/', '', $user->phone);
        $last8Digits = substr($phone, -8);
        
        return 'PMB' . $last8Digits;
    }
}