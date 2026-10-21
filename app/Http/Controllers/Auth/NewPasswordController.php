<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use App\Models\User;
use App\Models\MasterUser;
use App\Models\AkunPendaftarDefault; // ✅ Tambahkan ini
use Illuminate\Support\Facades\Password;

class NewPasswordController extends Controller
{
    /**
     * Tampilkan halaman reset password.
     */
    public function create(Request $request)
    {
        $token = $request->route('token');
        $email = $request->query('email');

        return view('auth.reset-password', [
            'token' => $token,
            'email' => $email,
        ]);
    }

    /**
     * Simpan password baru dan update tabel terkait.
     */
    public function store(Request $request)
    {
        // 1. Validasi form input
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        // 2. Cek token reset password
        $resetData = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->first();

        if (!$resetData) {
            return back()->withErrors(['email' => 'Token tidak valid atau sudah kedaluwarsa.']);
        }

        // 3. Cari user berdasarkan email
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return back()->withErrors(['email' => 'Akun tidak ditemukan di sistem.']);
        }

        DB::beginTransaction();
        try {
            // 4. Update password di tabel 'users' (Hashed)
            $user->update([
                'password' => Hash::make($request->password),
                'updated_at' => now(),
            ]);

            // 5. Update password di tabel 'master_users'
            $masterUser = MasterUser::where('user_id', $user->id)->first();
            if ($masterUser) {
                $masterUser->update([
                    'password' => Hash::make($request->password),
                    'plain_password' => $request->password, // Opsional jika tabel ini punya kolom plain
                    'updated_at' => now(),
                ]);
            }

            // 6. Update password di tabel 'akun_pendaftar_default' (Plain Text) ✅
            // Ini adalah tabel yang Anda gunakan untuk log admin/excel
            $pendaftarDefault = AkunPendaftarDefault::where('email', $user->email)->first();
            if ($pendaftarDefault) {
                $pendaftarDefault->update([
                    'password_plain' => $request->password, // Password teks biasa
                    'updated_at' => now(),
                ]);
            }

            // 7. Hapus token setelah berhasil reset
            DB::table('password_reset_tokens')
                ->where('email', $request->email)
                ->delete();

            DB::commit();

            // Redirect ke login
            return redirect()->route('login')->with('status', 'Password berhasil direset. Silakan login kembali.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['email' => 'Terjadi kesalahan saat menyimpan password baru.']);
        }
    }
}