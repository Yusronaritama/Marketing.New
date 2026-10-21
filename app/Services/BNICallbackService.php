<?php

namespace App\Services;

use App\Libraries\Bnienc;
use App\Models\MasterUser;
use App\Models\User;
use App\Models\PaymentLog;
use App\Models\BNICallbackLog;
use App\Services\WhatsAppService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Exception;

class BNICallbackService
{
    protected $clientId;
    protected $secretKey;
    protected $whatsappService;

    public function __construct()
    {
        $this->clientId = env('BNI_CLIENT_ID', '41625');
        $this->secretKey = env('BNI_SECRET_KEY', 'd2ea3883241ee95f7d0d6fd1de085c41');
        $this->whatsappService = app(WhatsAppService::class);
    }

    /**
     * Process callback dari BNI
     */
    public function processCallback(string $clientId, string $encryptedData): array
    {
        DB::beginTransaction();

        try {
            // Validasi client_id
            if ($clientId !== $this->clientId) {
                throw new Exception('Invalid client_id');
            }

            // Dekripsi data
            Log::info('🔓 Mendekripsi data callback', [
                'client_id' => substr($clientId, 0, 8) . '...',
                'data_length' => strlen($encryptedData)
            ]);

            $decryptedData = Bnienc::decrypt($encryptedData, $this->clientId, $this->secretKey);
            
            Log::info('📊 Data callback terdekripsi', $decryptedData);

            // Validasi data yang diperlukan
            $requiredFields = ['virtual_account', 'trx_id', 'trx_amount', 'payment_amount'];
            foreach ($requiredFields as $field) {
                if (!isset($decryptedData[$field])) {
                    throw new Exception("Missing required field: {$field}");
                }
            }

            // Simpan log callback
            $callbackLog = BNICallbackLog::create([
                'client_id' => $clientId,
                'raw_data' => $encryptedData,
                'decrypted_data' => json_encode($decryptedData),
                'virtual_account' => $decryptedData['virtual_account'],
                'trx_id' => $decryptedData['trx_id'],
                'trx_amount' => $decryptedData['trx_amount'],
                'payment_amount' => $decryptedData['payment_amount'],
                'datetime_payment' => isset($decryptedData['datetime_payment']) ? $decryptedData['datetime_payment'] : null,
                'payment_ntb' => isset($decryptedData['payment_ntb']) ? $decryptedData['payment_ntb'] : null,
                'status' => 'received',
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent()
            ]);

            // Cari user berdasarkan virtual_account
            $masterUser = MasterUser::where('va_number', $decryptedData['virtual_account'])
                ->orWhere('trx_id', $decryptedData['trx_id'])
                ->first();

            if (!$masterUser) {
                throw new Exception("Virtual account not found: " . $decryptedData['virtual_account']);
            }

            // Update callback log dengan user_id
            $callbackLog->update([
                'user_id' => $masterUser->user_id,
                'master_user_id' => $masterUser->id
            ]);

            // Proses pembayaran
            $paymentResult = $this->processPayment($masterUser, $decryptedData);

            if ($paymentResult['success']) {
                DB::commit();
                
                $callbackLog->update(['status' => 'processed']);
                
                Log::info('✅ Pembayaran berhasil diproses', [
                    'user_id' => $masterUser->user_id,
                    'virtual_account' => $decryptedData['virtual_account'],
                    'amount' => $decryptedData['payment_amount'],
                    'status' => $paymentResult['status']
                ]);

                return [
                    'success' => true,
                    'virtual_account' => $decryptedData['virtual_account'],
                    'status' => $paymentResult['status'],
                    'trx_id' => $decryptedData['trx_id'],
                    'message' => 'Payment processed successfully'
                ];
            } else {
                throw new Exception(isset($paymentResult['error']) ? $paymentResult['error'] : 'Payment processing failed');
            }

        } catch (Exception $e) {
            DB::rollBack();
            
            if (isset($callbackLog)) {
                $callbackLog->update([
                    'status' => 'failed',
                    'error_message' => $e->getMessage()
                ]);
            }

            Log::error('❌ Gagal memproses callback', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Proses pembayaran
     */
    private function processPayment(MasterUser $masterUser, array $paymentData): array
    {
        try {
            $virtualAccount = $paymentData['virtual_account'];
            $trxAmount = (int) $paymentData['trx_amount'];
            $paymentAmount = (int) $paymentData['payment_amount'];
            $cumulativePayment = isset($paymentData['cumulative_payment_amount']) ? (int) $paymentData['cumulative_payment_amount'] : $paymentAmount;
            
            Log::info('💰 Memproses pembayaran', [
                'user_id' => $masterUser->user_id,
                'virtual_account' => $virtualAccount,
                'trx_amount' => $trxAmount,
                'payment_amount' => $paymentAmount,
                'cumulative' => $cumulativePayment
            ]);

            // Ambil data user
            $user = User::find($masterUser->user_id);
            
            if (!$user) {
                throw new Exception("User tidak ditemukan");
            }

            // Cek jika sudah dibayar
            if ($masterUser->va_status === 'paid') {
                Log::warning('⚠️ VA sudah dibayar sebelumnya', [
                    'user_id' => $masterUser->user_id,
                    'va_status' => $masterUser->va_status
                ]);
                
                $this->savePaymentLog($masterUser, $paymentData, 'duplicate');
                
                // Kirim notifikasi duplicate payment ke user
                $this->sendWhatsAppNotification($user, $paymentAmount, 'duplicate');
                
                return [
                    'success' => true,
                    'status' => 'already_paid',
                    'message' => 'Payment already processed'
                ];
            }

            // Cek jika pembayaran kurang
            if ($paymentAmount < $trxAmount) {
                $shortage = $trxAmount - $paymentAmount;
                
                Log::warning('⚠️ Pembayaran kurang dari yang seharusnya', [
                    'required' => $trxAmount,
                    'paid' => $paymentAmount,
                    'shortage' => $shortage
                ]);

                // Ambil nilai datetime_payment dan payment_ntb dengan cara yang kompatibel PHP 7.x
                $datetimePayment = isset($paymentData['datetime_payment']) ? $paymentData['datetime_payment'] : now()->format('Y-m-d H:i:s');
                $paymentNtb = isset($paymentData['payment_ntb']) ? $paymentData['payment_ntb'] : null;

                // Update status
                $masterUser->update([
                    'va_status' => 'partial',
                    'payment_amount' => $paymentAmount,
                    'payment_shortage' => $shortage,
                    'paid_at' => Carbon::parse($datetimePayment),
                    'payment_method' => 'BNI',
                    'payment_ntb' => $paymentNtb,
                    'datetime_payment' => $datetimePayment,
                ]);

                // Kirim notifikasi partial payment
                $this->sendWhatsAppNotification($user, $paymentAmount, 'partial', $shortage);
                
                // Simpan log
                $this->savePaymentLog($masterUser, $paymentData, 'partial');

                return [
                    'success' => true,
                    'status' => 'partial',
                    'message' => 'Partial payment received'
                ];
            }

            // Pembayaran lunas
            Log::info('🎉 Pembayaran lunas diterima', [
                'user_id' => $masterUser->user_id,
                'amount' => $paymentAmount
            ]);

            // Ambil nilai datetime_payment dan payment_ntb dengan cara yang kompatibel PHP 7.x
            $datetimePayment = isset($paymentData['datetime_payment']) ? $paymentData['datetime_payment'] : now()->format('Y-m-d H:i:s');
            $paymentNtb = isset($paymentData['payment_ntb']) ? $paymentData['payment_ntb'] : null;

            // Update status
            $masterUser->update([
                'va_status' => 'paid',
                'payment_amount' => $paymentAmount,
                'paid_at' => Carbon::parse($datetimePayment),
                'payment_method' => 'BNI',
                'payment_ntb' => $paymentNtb,
                'datetime_payment' => $datetimePayment,
                'va_expired_at' => null,
            ]);

            // Update user
            $user->update([
                'va_status' => 'PAID',
                'paid_at' => Carbon::parse($datetimePayment),
                'payment_method' => 'BNI'
            ]);
            
            // Kirim notifikasi sukses ke user
            $this->sendWhatsAppNotification($user, $paymentAmount, 'paid');
            
            // Kirim notifikasi ke admin
            $this->sendAdminNotification($user, $masterUser, $paymentAmount);

            // Simpan log
            $this->savePaymentLog($masterUser, $paymentData, 'paid');

            return [
                'success' => true,
                'status' => 'paid',
                'message' => 'Payment completed successfully'
            ];

        } catch (Exception $e) {
            Log::error('❌ Gagal memproses pembayaran', [
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Kirim notifikasi WhatsApp ke user
     */
    private function sendWhatsAppNotification(User $user, int $amount, string $status, int $shortage = null): void
    {
        try {
            // Format phone number
            $phone = $this->formatPhoneForWhatsApp($user->phone);
            if (!$phone) {
                Log::warning('❌ Nomor telepon tidak valid untuk WhatsApp', [
                    'user_id' => $user->id,
                    'phone' => $user->phone
                ]);
                return;
            }

            // Format amount
            $formattedAmount = 'Rp ' . number_format($amount, 0, ',', '.');
            $formattedShortage = $shortage ? 'Rp ' . number_format($shortage, 0, ',', '.') : null;

            // Pesan berdasarkan status
            $message = $this->generateWhatsAppMessage($user, $formattedAmount, $status, $formattedShortage);

            // Kirim via WhatsApp Service
            $result = $this->whatsappService->sendMessage($phone, $message);

            if ($result) {
                Log::info('✅ Notifikasi WhatsApp terkirim ke user', [
                    'user_id' => $user->id,
                    'phone' => $phone,
                    'status' => $status,
                    'amount' => $formattedAmount
                ]);
            } else {
                Log::warning('⚠️ Gagal mengirim notifikasi WhatsApp ke user', [
                    'user_id' => $user->id,
                    'phone' => $phone
                ]);
            }

        } catch (Exception $e) {
            Log::error('❌ Error mengirim notifikasi WhatsApp', [
                'error' => $e->getMessage(),
                'user_id' => $user->id
            ]);
        }
    }

    /**
     * Kirim notifikasi ke admin
     */
    private function sendAdminNotification(User $user, MasterUser $masterUser, int $amount): void
    {
        try {
            // Dapatkan nomor admin dari config atau env
            $adminPhones = env('WHATSAPP_ADMIN_PHONES', '6281234567890');
            $adminPhones = explode(',', $adminPhones);
            
            if (empty($adminPhones)) {
                $adminPhones = ['6281234567890']; // Default admin number
            }

            // Format amount
            $formattedAmount = 'Rp ' . number_format($amount, 0, ',', '.');

            // Format phone untuk display
            $displayPhone = $this->formatPhoneForDisplay($user->phone);

            // Buat pesan untuk admin
            $message = "📢 *NOTIFIKASI PEMBAYARAN PMB*\n\n"
                     . "✅ *Pembayaran Baru Diterima!*\n\n"
                     . "👤 *Nama:* {$user->name}\n"
                     . "📧 *Email:* {$user->email}\n"
                     . "📞 *Telepon:* {$displayPhone}\n"
                     . "💰 *Jumlah:* {$formattedAmount}\n"
                     . "🏦 *VA Number:* {$masterUser->va_number}\n"
                     . "🆔 *TRX ID:* {$masterUser->trx_id}\n"
                     . "⏰ *Waktu:* " . now()->format('d/m/Y H:i:s') . "\n\n"
                     . "🎉 Status: *LUNAS*";

            // Kirim ke semua admin
            foreach ($adminPhones as $adminPhone) {
                $formattedPhone = $this->formatPhoneForWhatsApp($adminPhone);
                if ($formattedPhone) {
                    $this->whatsappService->sendMessage($formattedPhone, $message);
                    Log::info('✅ Notifikasi admin terkirim', [
                        'admin_phone' => $formattedPhone,
                        'user_id' => $user->id
                    ]);
                }
            }

        } catch (Exception $e) {
            Log::error('❌ Error mengirim notifikasi ke admin', [
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Kirim notifikasi error ke admin
     */
    private function sendErrorNotification(Exception $e, array $paymentData): void
    {
        try {
            $adminPhones = env('WHATSAPP_ADMIN_PHONES', '6281234567890');
            $adminPhones = explode(',', $adminPhones);

            $virtualAccount = isset($paymentData['virtual_account']) ? $paymentData['virtual_account'] : 'N/A';
            $trxId = isset($paymentData['trx_id']) ? $paymentData['trx_id'] : 'N/A';
            $trxAmount = isset($paymentData['trx_amount']) ? $paymentData['trx_amount'] : 0;
            $formattedAmount = 'Rp ' . number_format($trxAmount, 0, ',', '.');
            $errorMessage = substr($e->getMessage(), 0, 100);

            $message = "🚨 *ERROR CALLBACK BNI*\n\n"
                     . "❌ Terjadi kesalahan saat memproses callback!\n\n"
                     . "🏦 *VA Number:* {$virtualAccount}\n"
                     . "🆔 *TRX ID:* {$trxId}\n"
                     . "💰 *Amount:* {$formattedAmount}\n"
                     . "⏰ *Waktu:* " . now()->format('d/m/Y H:i:s') . "\n"
                     . "🔧 *Error:* " . $errorMessage . "\n\n"
                     . "📝 Silakan cek log untuk detail lebih lanjut.";

            foreach ($adminPhones as $adminPhone) {
                $formattedPhone = $this->formatPhoneForWhatsApp($adminPhone);
                if ($formattedPhone) {
                    $this->whatsappService->sendMessage($formattedPhone, $message);
                }
            }

        } catch (Exception $error) {
            Log::error('❌ Error mengirim notifikasi error', [
                'error' => $error->getMessage()
            ]);
        }
    }

    /**
     * Generate pesan WhatsApp berdasarkan status
     */
    private function generateWhatsAppMessage(User $user, string $formattedAmount, string $status, $formattedShortage = null): string
    {
        $currentTime = now()->format('d/m/Y H:i:s');
        
        switch ($status) {
            case 'paid':
                return "🎉 *PEMBAYARAN BERHASIL!*\n\n"
                     . "Halo {$user->name},\n\n"
                     . "✅ Pembayaran Anda telah *BERHASIL* diproses.\n\n"
                     . "📋 *Detail Pembayaran:*\n"
                     . "💰 *Jumlah:* {$formattedAmount}\n"
                     . "⏰ *Waktu:* {$currentTime}\n"
                     . "🏦 *Metode:* Transfer Bank BNI\n\n"
                     . "🎓 *Status Pendaftaran:*\n"
                     . "✓ Pendaftaran PMB *AKTIF*\n"
                     . "✓ Akses dashboard *TERBUKA*\n"
                     . "✓ Data Anda *TERVERIFIKASI*\n\n"
                     . "🔗 *Login ke Dashboard:*\n"
                     . "http://pmb.ubpkarawang.ac.id/dashboard\n\n"
                     . "📞 *Butuh Bantuan?*\n"
                     . "Hubungi admin: 0896-6111-6600\n\n"
                     . "Terima kasih telah bergabung dengan UBP Karawang! 🎓";

            case 'partial':
                $shortageText = $formattedShortage ? $formattedShortage : 'Rp 0';
                return "⚠️ *PEMBAYARAN SEBAGIAN*\n\n"
                     . "Halo {$user->name},\n\n"
                     . "Kami menerima pembayaran Anda, namun *MASIH KURANG*.\n\n"
                     . "📋 *Detail Pembayaran:*\n"
                     . "💰 *Dibayar:* {$formattedAmount}\n"
                     . "❌ *Kurang:* {$shortageText}\n"
                     . "⏰ *Waktu:* {$currentTime}\n\n"
                     . "🎯 *Tindakan yang diperlukan:*\n"
                     . "1. Lakukan pelunasan sisa pembayaran\n"
                     . "2. Gunakan VA yang sama: *{$user->va_number}*\n"
                     . "3. Status akan otomatis update setelah lunas\n\n"
                     . "📞 *Butuh Bantuan?*\n"
                     . "Hubungi admin: 0896-6111-6600";

            case 'duplicate':
                return "ℹ️ *PEMBAYARAN GANDA*\n\n"
                     . "Halo {$user->name},\n\n"
                     . "Kami mendeteksi pembayaran ganda untuk transaksi yang sama.\n\n"
                     . "📋 *Detail:*\n"
                     . "💰 *Jumlah:* {$formattedAmount}\n"
                     . "⏰ *Waktu:* {$currentTime}\n"
                     . "🏦 *Status:* Sudah dibayar sebelumnya\n\n"
                     . "✅ *Status Akun Anda:*\n"
                     . "✓ Pendaftaran PMB *SUDAH AKTIF*\n"
                     . "✓ Tidak perlu pembayaran ulang\n\n"
                     . "🔗 *Login ke Dashboard:*\n"
                     . "http://pmb.ubpkarawang.ac.id/dashboard\n\n"
                     . "📞 Jika ada pertanyaan, hubungi admin.";

            default:
                return "📢 *NOTIFIKASI PMB*\n\n"
                     . "Halo {$user->name},\n\n"
                     . "Status pembayaran Anda telah diperbarui.\n"
                     . "💰 *Jumlah:* {$formattedAmount}\n"
                     . "⏰ *Waktu:* {$currentTime}\n"
                     . "🔗 Login ke dashboard untuk info lengkap.";
        }
    }

    /**
     * Format nomor telepon untuk WhatsApp
     */
    private function formatPhoneForWhatsApp(string $phone): ?string
    {
        // Hapus semua karakter non-digit
        $cleanPhone = preg_replace('/\D/', '', $phone);
        
        // Cek panjang minimal
        if (strlen($cleanPhone) < 10) {
            return null;
        }

        // Format ke 62xxx
        if (substr($cleanPhone, 0, 1) === '0') {
            return '62' . substr($cleanPhone, 1);
        } elseif (substr($cleanPhone, 0, 2) === '62') {
            return $cleanPhone;
        } elseif (substr($cleanPhone, 0, 1) === '8') {
            return '62' . $cleanPhone;
        }

        return null;
    }

    /**
     * Format nomor telepon untuk display
     */
    private function formatPhoneForDisplay(string $phone): string
    {
        $cleanPhone = preg_replace('/\D/', '', $phone);
        
        if (substr($cleanPhone, 0, 2) === '62') {
            return '0' . substr($cleanPhone, 2);
        }
        
        return $cleanPhone;
    }

    /**
     * Method untuk testing WhatsApp notification
     */
    public function testWhatsAppNotification($userId, $type = 'paid')
    {
        try {
            $user = User::find($userId);
            if (!$user) {
                return [
                    'success' => false,
                    'message' => 'User tidak ditemukan'
                ];
            }

            $masterUser = MasterUser::where('user_id', $userId)->first();
            if (!$masterUser) {
                return [
                    'success' => false,
                    'message' => 'Master user tidak ditemukan'
                ];
            }

            $testData = [
                'payment_amount' => 300000,
                'datetime_payment' => now()->format('Y-m-d H:i:s'),
                'payment_ntb' => 'NTB' . rand(100000, 999999),
                'virtual_account' => $masterUser->va_number,
                'trx_id' => $masterUser->trx_id
            ];

            // Kirim notifikasi
            $this->sendWhatsAppNotification($user, $testData['payment_amount'], $type);

            return [
                'success' => true,
                'message' => 'Test notifikasi WhatsApp berhasil dikirim',
                'data' => [
                    'user_id' => $userId,
                    'phone' => $user->phone,
                    'type' => $type,
                    'virtual_account' => $masterUser->va_number
                ]
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Simpan log pembayaran
     */
    private function savePaymentLog(MasterUser $masterUser, array $paymentData, string $status): void
    {
        try {
            PaymentLog::create([
                'user_id' => $masterUser->user_id,
                'master_user_id' => $masterUser->id,
                'virtual_account' => $paymentData['virtual_account'],
                'trx_id' => $paymentData['trx_id'],
                'trx_amount' => $paymentData['trx_amount'],
                'payment_amount' => $paymentData['payment_amount'],
                'cumulative_payment_amount' => isset($paymentData['cumulative_payment_amount']) ? $paymentData['cumulative_payment_amount'] : $paymentData['payment_amount'],
                'payment_ntb' => isset($paymentData['payment_ntb']) ? $paymentData['payment_ntb'] : null,
                'datetime_payment' => isset($paymentData['datetime_payment']) ? $paymentData['datetime_payment'] : null,
                'datetime_payment_iso8601' => isset($paymentData['datetime_payment_iso8601']) ? $paymentData['datetime_payment_iso8601'] : null,
                'status' => $status,
                'payment_data' => json_encode($paymentData),
                'created_at' => now(),
                'updated_at' => now()
            ]);

            Log::info('📝 Log pembayaran disimpan', [
                'user_id' => $masterUser->user_id,
                'status' => $status,
                'amount' => $paymentData['payment_amount']
            ]);

        } catch (Exception $e) {
            Log::error('❌ Gagal menyimpan log pembayaran', [
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Cek status pembayaran manual
     */
    public function checkPayment(string $virtualAccount, string $trxId): array
    {
        try {
            $masterUser = MasterUser::where('va_number', $virtualAccount)
                ->orWhere('trx_id', $trxId)
                ->first();

            if (!$masterUser) {
                return [
                    'success' => false,
                    'error' => 'Virtual account tidak ditemukan'
                ];
            }

            $paymentLogs = PaymentLog::where('virtual_account', $virtualAccount)
                ->orWhere('trx_id', $trxId)
                ->orderBy('created_at', 'desc')
                ->get();

            $user = User::find($masterUser->user_id);

            return [
                'success' => true,
                'data' => [
                    'virtual_account' => $masterUser->va_number,
                    'trx_id' => $masterUser->trx_id,
                    'va_status' => $masterUser->va_status,
                    'va_amount' => $masterUser->va_amount,
                    'payment_amount' => $masterUser->payment_amount,
                    'paid_at' => $masterUser->paid_at,
                    'va_expired_at' => $masterUser->va_expired_at,
                    'payment_logs' => $paymentLogs,
                    'user' => $user ? [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email
                    ] : null
                ]
            ];

        } catch (Exception $e) {
            Log::error('❌ Gagal cek status pembayaran', [
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    public function processManualPayment(MasterUser $masterUser, array $paymentData): array
{
    try {
        Log::info('🔄 Processing manual payment sync', [
            'user_id' => $masterUser->user_id,
            'virtual_account' => $masterUser->va_number,
            'payment_amount' => $paymentData['payment_amount'],
        ]);

        return $this->processPayment($masterUser, $paymentData);
        
    } catch (Exception $e) {
        Log::error('❌ Manual payment sync failed', [
            'error' => $e->getMessage(),
            'virtual_account' => $masterUser->va_number,
        ]);
        
        return [
            'success' => false,
            'error' => $e->getMessage()
        ];
    }
}

/**
 * Send notification for bulk paid
 */
public function sendBulkPaidNotification(User $user, int $amount, string $paymentMethod, Carbon $paymentDate): void
{
    try {
        $phone = $this->formatPhoneForWhatsApp($user->phone);
        if (!$phone) {
            Log::warning('Nomor telepon tidak valid untuk bulk paid notification', [
                'user_id' => $user->id,
                'phone' => $user->phone,
            ]);
            return;
        }

        $formattedAmount = 'Rp ' . number_format($amount, 0, ',', '.');
        $formattedDate = $paymentDate->format('d/m/Y H:i:s');
        
        $message = "🎉 *PEMBAYARAN DIPROSES ADMIN*\n\n"
                 . "Halo {$user->name},\n\n"
                 . "✅ Pembayaran Anda telah diproses oleh administrator.\n\n"
                 . "📋 *Detail Pembayaran:*\n"
                 . "💰 *Jumlah:* {$formattedAmount}\n"
                 . "🏦 *Metode:* {$paymentMethod}\n"
                 . "⏰ *Waktu:* {$formattedDate}\n\n"
                 . "🎓 *Status Pendaftaran:*\n"
                 . "✓ Pendaftaran PMB *AKTIF*\n"
                 . "✓ Akses dashboard *TERBUKA*\n"
                 . "✓ Data Anda *TERVERIFIKASI*\n\n"
                 . "🔗 *Login ke Dashboard:*\n"
                 . "http://pmb.ubpkarawang.ac.id/dashboard\n\n"
                 . "📞 *Butuh Bantuan?*\n"
                 . "Hubungi admin: 0896-6111-6600\n\n"
                 . "Terima kasih telah bergabung dengan UBP Karawang! 🎓";

        $result = $this->whatsappService->sendMessage($phone, $message);
        
        if ($result) {
            Log::info('Bulk paid notification sent', [
                'user_id' => $user->id,
                'phone' => $phone,
                'amount' => $amount,
            ]);
        }

    } catch (Exception $e) {
        Log::error('Failed to send bulk paid notification', [
            'error' => $e->getMessage(),
            'user_id' => $user->id,
        ]);
    }
}

/**
 * Send notification for force paid
 */
public function sendForcePaidNotification(User $user, int $amount, string $paymentMethod, Carbon $paymentDate): void
{
    try {
        $phone = $this->formatPhoneForWhatsApp($user->phone);
        if (!$phone) {
            Log::warning('Nomor telepon tidak valid untuk force paid notification', [
                'user_id' => $user->id,
                'phone' => $user->phone,
            ]);
            return;
        }

        $formattedAmount = 'Rp ' . number_format($amount, 0, ',', '.');
        $formattedDate = $paymentDate->format('d/m/Y H:i:s');
        
        $message = "🎉 *PEMBAYARAN DIPROSES ADMIN*\n\n"
                 . "Halo {$user->name},\n\n"
                 . "✅ Status pembayaran Anda telah diupdate menjadi *LUNAS*.\n\n"
                 . "📋 *Detail Pembayaran:*\n"
                 . "💰 *Jumlah:* {$formattedAmount}\n"
                 . "🏦 *Metode:* {$paymentMethod}\n"
                 . "⏰ *Waktu:* {$formattedDate}\n\n"
                 . "🎓 *Status Pendaftaran:*\n"
                 . "✓ Pendaftaran PMB *AKTIF*\n"
                 . "✓ Akses dashboard *TERBUKA*\n"
                 . "✓ Data Anda *TERVERIFIKASI*\n\n"
                 . "🔗 *Login ke Dashboard:*\n"
                 . "http://pmb.ubpkarawang.ac.id/dashboard\n\n"
                 . "📞 *Butuh Bantuan?*\n"
                 . "Hubungi admin: 0896-6111-6600";

        $result = $this->whatsappService->sendMessage($phone, $message);
        
        if ($result) {
            Log::info('Force paid notification sent', [
                'user_id' => $user->id,
                'phone' => $phone,
            ]);
        }

    } catch (Exception $e) {
        Log::error('Failed to send force paid notification', [
            'error' => $e->getMessage(),
            'user_id' => $user->id,
        ]);
    }
}}