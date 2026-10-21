<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Mail;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Str;
use App\Mail\PasswordResetEmail;

class CustomPasswordResetController extends Controller
{
    /**
     * Tampilkan form reset password
     */
    public function create(Request $request)
    {
        return view('auth.reset-password', ['token' => $request->token, 'email' => $request->email]);
    }

    /**
     * Proses reset password
     */
    public function store(Request $request)
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'min:8', 'confirmed'],
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user) use ($request) {
                // 🔹 Update password di tabel default Laravel (jika ada)
                $user->forceFill([
                    'password' => Hash::make($request->password),
                ])->setRememberToken(Str::random(60));

                $user->save();

                // 🔹 Update juga ke tabel master_users
                DB::table('master_users')
                    ->where('user_id', $user->id)
                    ->orWhere('email', $request->email)
                    ->update([
                        'plain_password' => $request->password,
                        'password' => Hash::make($request->password),
                        'updated_at' => now(),
                    ]);

                // 🔹 Kirim email notifikasi
                try {
                    Mail::to($request->email)->send(new PasswordResetEmail([
                        'email' => $request->email,
                        'password' => $request->password
                    ]));
                } catch (\Exception $e) {
                    \Log::error('Gagal mengirim email reset password: ' . $e->getMessage());
                }

                event(new PasswordReset($user));
            }
        );

        return $status == Password::PASSWORD_RESET
            ? redirect()->route('login')->with('status', __($status))
            : back()->withErrors(['email' => [__($status)]]);
    }
}