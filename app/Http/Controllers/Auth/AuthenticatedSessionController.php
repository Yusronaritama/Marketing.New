<?php

namespace App\Http\Controllers\Auth;

use App\Models\MasterUser;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use App\Models\User;
use Illuminate\Support\Facades\Route;


class AuthenticatedSessionController extends Controller
{
    /**
     * Tentukan URL redirect berdasarkan role secara dinamis.
     */
private function getAdminRedirectRoute($user): string
{
    // Role khusus
    if ($user->role === 'admin_farmasi') {
        return url('/admin/farmasi');
    }
if ($user->role === 'admin_profesi') {
        if (Route::has('admin.profesi.dashboard')) {
            return route('admin.profesi.dashboard'); 
        }
        return url('/admin/profesi/dashboard'); 
    }
    if ($user->role === 'admin_keuangan') {
        return url('/admin/mandiri/register');
    }

    if ($user->role === 'admin_akademik') {
        return url('/admin/profile-lengkap');
    }

    // Semua admin_prodi
    if (str_starts_with($user->role, 'admin_')) {
        if (Route::has('admin.prodipage.index')) {
            return route('admin.prodipage.index');
        }
        return url('/admin/prodi'); // fallback aman
    }

    // Super admin
    if (Route::has('admin.dashboard.index')) {
        return route('admin.dashboard.index');
    }

    return url('/admin/dashboard');
}


    public function create(): View
    {
        return view('auth.login');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $username = $request->input('username');
        $password = $request->input('password');

        /**
         * 1 & 2. Cek Admin (By Name atau Email)
         * Kita gabungkan agar lebih ringkas menggunakan OR
         */
        $adminUser = User::where(function($query) use ($username) {
                $query->where('name', $username)
                      ->orWhere('email', $username);
            })
            ->where(function($query) {
                $query->where('role', 'admin')
                      ->orWhere('role', 'like', 'admin_%'); // Menangkap semua role admin_...
            })
            ->first();

        if ($adminUser) {
            if (!Hash::check($password, $adminUser->password)) {
                throw ValidationException::withMessages(['username' => 'Password salah.']);
            }

            Auth::login($adminUser);
            $request->session()->regenerate();
            
            return redirect()->to($this->getAdminRedirectRoute($adminUser))
                ->with('success', 'Selamat datang, ' . $adminUser->name . '!');
        }

        /**
         * 3. Login MAHASISWA BARU (trx_id)
         */
        $masterUser = MasterUser::where('trx_id', $username)->first();

        if ($masterUser && Hash::check($password, $masterUser->password)) {
            $user = User::find($masterUser->user_id);

            if (!$user || $user->role !== 'mahasiswa_baru') {
                throw ValidationException::withMessages([
                    'username' => 'Akses ditolak. Akun tidak valid.',
                ]);
            }

            Auth::login($user);
            $request->session()->regenerate();

            // Cek VA Status (Jika belum bayar)
            if ($masterUser->va_status === 'pending') {
                return redirect()->route('mahasiswa.va.dashboard')
                    ->with('warning', 'Silakan lakukan pembayaran terlebih dahulu.');
            }

            session()->flash('just_logged_in', true);

            // ==============================================================
            // REDIRECT DINAMIS: Cek Profesi
            // Jika user adalah pendaftar Apoteker Farmasi
            // ==============================================================
            if ($user->profesi === 'Apoteker Farmasi') {
                return redirect()->route('profesi.dashboard')
                    ->with('success', 'Selamat datang di Portal Profesi, ' . ($user->name ?: 'Mahasiswa') . '!');
            }

            // Jika user adalah mahasiswa S1/S2 biasa
            return redirect()->route('mahasiswa.dashboard')
                ->with('success', 'Selamat datang, ' . ($user->name ?: 'Mahasiswa') . '!');
        }

        // Jika semua gagal
        throw ValidationException::withMessages([
            'username' => 'Username/TRX ID atau password salah.',
        ]);
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }
}