<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PreventMahasiswaAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Cek jika user terautentikasi dan role-nya adalah mahasiswa_baru
        if (auth()->check() && auth()->user()->role === 'mahasiswa_baru') {
            // Redirect ke halaman mahasiswa dashboard dengan pesan error
            return redirect()->route('mahasiswa.dashboard')
                ->with('error', 'Anda tidak memiliki akses ke halaman tersebut.');
        }

        return $next($request);
    }
}