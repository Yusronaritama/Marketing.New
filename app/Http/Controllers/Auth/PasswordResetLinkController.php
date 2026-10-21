<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Services\WhatsAppService;
use Illuminate\Support\Facades\URL;

class PasswordResetLinkController extends Controller
{
    protected $whatsappService;

    public function __construct(WhatsAppService $whatsappService)
    {
        $this->whatsappService = $whatsappService;
    }

    /**
     * Display the password reset link request view.
     */
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * Handle an incoming password reset link request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        // Cari user berdasarkan email
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return back()->withInput($request->only('email'))
                ->withErrors(['email' => __('Kami tidak dapat menemukan pengguna dengan alamat email tersebut.')]);
        }

        // Generate reset token menggunakan Laravel Password Broker
        $token = Password::createToken($user);
        
        // Generate reset URL
        $resetUrl = URL::temporarySignedRoute(
            'password.reset',
            now()->addHours(24),
            ['token' => $token, 'email' => $user->email]
        );

        // Log untuk debugging
        Log::info('Password reset requested', [
            'user_id' => $user->id,
            'email' => $user->email,
            'phone' => $user->phone,
            'has_phone' => !empty($user->phone)
        ]);

        // KIRIM EMAIL (default Laravel)
        $emailStatus = Password::sendResetLink(
            $request->only('email')
        );

        $emailSent = $emailStatus === Password::RESET_LINK_SENT;
        
        // KIRIM WHATSAPP JIKA ADA NOMOR TELEPON
        $whatsappSent = false;
        
        if (!empty($user->phone)) {
            $whatsappMessage = "Halo {$user->name},\n\n";
            $whatsappMessage .= "Anda menerima email ini karena kami menerima permintaan reset password untuk akun Anda.\n\n";
            $whatsappMessage .= "Untuk reset password, klik link berikut:\n";
            $whatsappMessage .= $resetUrl . "\n\n";
            $whatsappMessage .= "Link reset password akan kadaluarsa dalam 24 jam.\n\n";
            $whatsappMessage .= "Jika Anda tidak meminta reset password, abaikan pesan ini.\n";
            $whatsappMessage .= "Tautan reset password juga telah dikirim ke email: {$user->email}\n\n";
            $whatsappMessage .= "Terima kasih,\n" . config('app.name');

            try {
                // Gunakan WhatsApp service
                $whatsappResult = $this->whatsappService->sendMessage($user->phone, $whatsappMessage);
                
                $whatsappSent = $whatsappResult['success'] ?? false;
                
                Log::info('WhatsApp password reset sent', [
                    'user_id' => $user->id,
                    'phone' => $user->phone,
                    'success' => $whatsappSent,
                    'error' => $whatsappResult['error'] ?? null
                ]);
                
            } catch (\Exception $e) {
                Log::error('Failed to send WhatsApp password reset', [
                    'user_id' => $user->id,
                    'phone' => $user->phone,
                    'error' => $e->getMessage()
                ]);
            }
        }

        // Prepare success message based on delivery methods
        if ($emailSent || $whatsappSent) {
            $successMessage = 'Tautan reset password telah dikirim! ';
            
            if ($emailSent) {
                $successMessage .= "Silakan cek email Anda di: {$user->email}. ";
            }
            
            if ($whatsappSent) {
                $successMessage .= "Tautan reset juga dikirim via WhatsApp ke nomor: {$user->phone}. ";
            }
            
            $successMessage .= "Link berlaku selama 24 jam.";
            
            return back()->with('status', trim($successMessage));
        } else {
            return back()->withInput($request->only('email'))
                ->withErrors(['email' => __('Gagal mengirim tautan reset password.')]);
        }
    }

    /**
     * Custom method to test WhatsApp integration
     */
    public function testWhatsApp(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return back()->with('error', 'User tidak ditemukan');
        }

        if (empty($user->phone)) {
            return back()->with('error', 'User tidak memiliki nomor WhatsApp');
        }

        $testMessage = "Halo {$user->name},\n\n";
        $testMessage .= "Ini adalah test notifikasi WhatsApp dari sistem " . config('app.name') . ".\n";
        $testMessage .= "Email: {$user->email}\n";
        $testMessage .= "Waktu: " . now()->format('d M Y H:i:s') . "\n\n";
        $testMessage .= "Jika Anda menerima pesan ini, berarti integrasi WhatsApp berfungsi dengan baik.\n\n";
        $testMessage .= "Terima kasih.";

        $result = $this->whatsappService->sendMessage($user->phone, $testMessage);

        if ($result['success'] ?? false) {
            return back()->with('success', 'Test WhatsApp berhasil dikirim ke ' . $user->phone);
        } else {
            return back()->with('error', 'Gagal mengirim test WhatsApp: ' . ($result['error'] ?? 'Unknown error'));
        }
    }
}