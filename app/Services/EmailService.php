<?php

namespace App\Services;

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class EmailService
{
    public function sendForgotAccountEmail($user, $masterUser, $resetUrl)
    {
        try {
            // Gunakan mailer yang lebih spesifik jika perlu
            Mail::mailer('smtp')->send('auth.emails.forgot-account', [
                'name' => $user->name,
                'trx_id' => $masterUser->trx_id,
                'resetUrl' => $resetUrl,
                'va_number' => $masterUser->va_number,
            ], function ($message) use ($user) {
                $message->to($user->email)
                    ->subject('Reset Password - ' . config('app.name'));
            });

            Log::info('Email berhasil dikirim ke: ' . $user->email);
            return ['success' => true, 'message' => 'Email sent successfully'];

        } catch (\Exception $e) {
            Log::error('Gagal kirim email: ' . $e->getMessage());
            
            // Cek jika error karena credentials
            if (strpos($e->getMessage(), '535-5.7.8') !== false) {
                return [
                    'success' => false,
                    'message' => 'Email server authentication failed. Please check email credentials.',
                    'error' => 'SMTP Authentication Error'
                ];
            }
            
            return [
                'success' => false,
                'message' => 'Failed to send email: ' . $e->getMessage()
            ];
        }
    }
}