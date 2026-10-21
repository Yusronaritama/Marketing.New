<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\MasterUser;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Services\WhatsAppService;

class ForgotAccountController extends Controller
{
    private $whatsappServerUrl = 'http://103.23.198.160:3009';
    public function __construct(WhatsAppService $whatsappService)
    {
        $this->whatsappService = $whatsappService;
    }

    public function showForm()
    {
        return view('auth.forgot-account');
    }

    /**
     * Kirim pesan WhatsApp
     */
    private function sendWhatsAppMessage($phoneNumber, $message)
    {
        try {
            // Format nomor telepon (hapus awalan 0 atau 62 jika ada)
            $formattedPhone = $phoneNumber;
            
            // Jika nomor diawali dengan 0, ganti dengan 62
            if (strpos($formattedPhone, '0') === 0) {
                $formattedPhone = '62' . substr($formattedPhone, 1);
            }
            
            // Jika nomor diawali dengan 62, biarkan
            // Jika nomor diawali dengan +62, hapus +
            $formattedPhone = ltrim($formattedPhone, '+');
            
            // Pastikan nomor minimal 10 digit
            if (strlen($formattedPhone) < 10) {
                throw new \Exception('Nomor telepon terlalu pendek');
            }
            
            Log::info('Mengirim WhatsApp ke nomor:', [
                'original' => $phoneNumber,
                'formatted' => $formattedPhone
            ]);
            
            // Kirim ke server WhatsApp
            $response = Http::timeout(30)->post($this->whatsappServerUrl . '/api/send-message', [
                'number' => $formattedPhone,
                'message' => $message
            ]);
            
            Log::info('Response dari server WhatsApp:', [
                'success' => $response->successful(),
                'status' => $response->status(),
                'body' => $response->json()
            ]);
            
            return $response->successful();
            
        } catch (\Exception $e) {
            Log::error('Gagal mengirim WhatsApp: ' . $e->getMessage(), [
                'phone' => $phoneNumber,
                'error' => $e->getTraceAsString()
            ]);
            return false;
        }
    }
     /**
     * Proses pencarian akun dan kirim informasi via Email & WhatsApp
     */
    public function submitForm(Request $request)
    {
        $request->validate([
            'identifier' => 'required|string',
        ]);

        $input = $request->input('identifier');

        // Cari user berdasarkan email atau nomor telepon
        $user = User::where('email', $input)
            ->orWhere('phone', $input)
            ->first();

        if (! $user) {
            return back()->withErrors([
                'identifier' => 'Akun tidak ditemukan berdasarkan email atau nomor telepon yang Anda masukkan.',
            ])->withInput();
        }

        // Ambil data MasterUser
        $masterUser = MasterUser::where('user_id', $user->id)->first();

        if (! $masterUser) {
            return back()->withErrors([
                'identifier' => 'Data akun ditemukan, namun tidak terhubung dengan sistem MasterUser.',
            ]);
        }

        // Generate token untuk reset password
        $token = Str::random(60);
        
        DB::beginTransaction();
        
        try {
            $masterUser->reset_password_token = $token;
            $masterUser->reset_password_expires = now()->addHours(24);
            $masterUser->save();

            DB::commit();

            // Verifikasi data tersimpan
            $freshMasterUser = MasterUser::find($masterUser->id);
            
            if ($freshMasterUser->reset_password_token !== $token) {
                throw new \Exception('Token tidak tersimpan dengan benar di database');
            }

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal menyimpan token reset password', [
                'error' => $e->getMessage(),
                'master_user_id' => $masterUser->id,
            ]);

            return back()->withErrors([
                'identifier' => 'Gagal membuat token reset. Silakan coba lagi.',
            ]);
        }

        // Generate URL reset
        try {
            $resetUrl = URL::temporarySignedRoute(
                'password.reset.master',
                now()->addHours(24),
                ['token' => $token]
            );
            
            // Pastikan URL memiliki protocol (http:// atau https://)
            if (!preg_match('/^https?:\/\//', $resetUrl)) {
                $resetUrl = url($resetUrl);
            }
            
        } catch (\Exception $e) {
            $resetUrl = url('/account-reset/' . $token);
        }

        // KIRIM EMAIL
        $emailSent = false;
        $whatsappSent = false;
        $messages = [];

        try {
            Mail::send('auth.emails.forgot-account', [
                'name' => $user->name,
                'trx_id' => $masterUser->trx_id,
                'resetUrl' => $resetUrl,
                'va_number' => $masterUser->va_number,
            ], function ($message) use ($user) {
                $message->to($user->email)
                    ->subject('Informasi Akun dan Reset Password - ' . config('app.name'));
            });

            $emailSent = true;
            Log::info('Email lupa akun dikirim', [
                'user_id' => $user->id,
                'email' => $user->email,
            ]);

        } catch (\Exception $e) {
            Log::error('Gagal mengirim email lupa akun', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
            $messages[] = 'Email gagal dikirim.';
        }

        // KIRIM WHATSAPP JIKA ADA NOMOR TELEPON
        if (!empty($user->phone)) {
            // Format pesan WhatsApp dengan URL yang dapat diklik
            $whatsappMessage = "Halo {$user->name},\n\n";
            $whatsappMessage .= "Berikut informasi akun Anda:\n";
            $whatsappMessage .= "Username : {$masterUser->trx_id}\n";
            
            if (!empty($masterUser->va_number)) {
                $whatsappMessage .= "Virtual Account: {$masterUser->va_number}\n";
            }
            
            $whatsappMessage .= "\nUntuk reset password, klik link berikut:\n";
            $whatsappMessage .= "\n" . $resetUrl . "\n\n";
            $whatsappMessage .= "Link berlaku 24 jam.\n";
            $whatsappMessage .= "Jika Anda tidak meminta ini, abaikan pesan ini.\n\n";
            $whatsappMessage .= "Terima kasih,\n" . config('app.name');

            // Debug log - SETELAH $whatsappMessage didefinisikan
            Log::info('📱 Mencoba mengirim WhatsApp', [
                'user_id' => $user->id,
                'phone' => $user->phone,
                'message_length' => strlen($whatsappMessage),
                'url_in_message' => str_contains($whatsappMessage, 'http')
            ]);

            // Gunakan WhatsApp service langsung (tanpa queue untuk testing)
            $whatsappResult = $this->whatsappService->sendMessage($user->phone, $whatsappMessage);
            
            Log::info('📱 Hasil pengiriman WhatsApp', [
                'success' => $whatsappResult['success'] ?? false,
                'error' => $whatsappResult['error'] ?? null,
                'phone' => $user->phone
            ]);
            
            $whatsappSent = $whatsappResult['success'] ?? false;
            
            if ($whatsappSent) {
                Log::info('WhatsApp berhasil dikirim', [
                    'user_id' => $user->id,
                    'phone' => $user->phone
                ]);
            } else {
                $messages[] = 'WhatsApp gagal dikirim.';
                Log::warning('WhatsApp gagal dikirim', [
                    'user_id' => $user->id,
                    'phone' => $user->phone,
                    'error' => $whatsappResult['error'] ?? 'Unknown error'
                ]);
            }
        }

        // Tentukan pesan sukses berdasarkan hasil pengiriman
        if ($emailSent || $whatsappSent) {
            $successMessage = 'Akun ditemukan! ';
            
            if ($emailSent) {
                $successMessage .= "Informasi akun telah dikirim ke email: {$user->email}. ";
            }
            
            if ($whatsappSent && !empty($user->phone)) {
                $successMessage .= "Informasi juga dikirim via WhatsApp ke nomor: {$user->phone}. ";
            }
            
            if (!empty($messages)) {
                $successMessage .= implode(' ', $messages);
            }
            
            return back()->with('success', trim($successMessage));
        } else {
            return back()->withErrors([
                'identifier' => 'Gagal mengirim informasi akun. Silakan hubungi administrator.',
            ]);
        }
    }
    public function showResetForm(Request $request, $token)
    {
        $masterUser = MasterUser::where('reset_password_token', $token)
            ->where('reset_password_expires', '>', now())
            ->first();

        if (! $masterUser) {
            return redirect()->route('forgot.account')
                ->with('error', 'Token reset password tidak valid atau sudah kadaluarsa.');
        }

        $user = $masterUser->user;
        if (!$user) {
            return redirect()->route('forgot.account')
                ->with('error', 'Data user tidak ditemukan.');
        }

        return view('auth.reset-password-akun-master', [
            'token' => $token,
            'user' => $user
        ]);
    }

     public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'password' => 'required|confirmed|min:8',
        ]);

        Log::info('Memproses reset password', [
            'token' => $request->token,
            'has_password' => !empty($request->password)
        ]);

        // Cek token di database
        $masterUser = MasterUser::where('reset_password_token', $request->token)
            ->where('reset_password_expires', '>', now())
            ->first();

        if (! $masterUser) {
            Log::warning('Token tidak valid saat reset password', [
                'token' => $request->token
            ]);
            
            return back()->withErrors([
                'password' => 'Token reset password tidak valid atau sudah kadaluarsa.',
            ]);
        }

        // Update password di master_users
        $masterUser->update([
            'password' => Hash::make($request->password),
            'reset_password_token' => null,
            'reset_password_expires' => null,
        ]);

        // Update password di users table juga
        $user = User::find($masterUser->user_id);
        
        if ($user) {
            $user->update([
                'password' => Hash::make($request->password)
            ]);
            
            // 1. Kirim notifikasi WhatsApp (Kode Lama)
            if (!empty($user->phone)) {
                $whatsappMessage = "Halo {$user->name},\n\n";
                $whatsappMessage .= "Password akun Anda berhasil direset.\n";
                $whatsappMessage .= "Sekarang Anda bisa login dengan password baru.\n\n";
                $whatsappMessage .= "Jika Anda tidak melakukan reset ini, segera hubungi administrator.\n\n";
                $whatsappMessage .= "Terima kasih,\n" . config('app.name');
                
                // Gunakan WhatsApp service
                $this->whatsappService->sendMessageViaQueue($user->phone, $whatsappMessage);
            }

            // 2. Kirim notifikasi Email (--- TAMBAHAN EMAIL ---)
            if (!empty($user->email)) {
                try {
                    Mail::send('auth.emails.password-reset-success', [
                        'name' => $user->name,
                        'appName' => config('app.name')
                    ], function ($message) use ($user) {
                        $message->to($user->email)
                                ->subject('Password Berhasil Diubah - ' . config('app.name'));
                    });

                    Log::info('Email notifikasi sukses reset password dikirim', [
                        'email' => $user->email
                    ]);
                } catch (\Exception $e) {
                    // Kita gunakan try-catch agar jika email gagal, proses reset password tetap dianggap sukses
                    Log::error('Gagal mengirim email notifikasi reset password', [
                        'error' => $e->getMessage(),
                        'email' => $user->email
                    ]);
                }
            }
        }

        Log::info('Password berhasil direset', [
            'user_id' => $masterUser->user_id,
            'master_user_id' => $masterUser->id,
            'email' => $user->email ?? 'Tidak diketahui'
        ]);

        return redirect()->route('login')
            ->with('success', 'Password berhasil direset! Silakan login dengan password baru Anda.');
    }
}