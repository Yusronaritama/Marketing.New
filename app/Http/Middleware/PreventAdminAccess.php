<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PreventAdminAccess
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Cek jika user adalah admin dan mencoba akses route mahasiswa
        if (auth()->check() && in_array(auth()->user()->role, ['admin', 'admin_akademik', 'admin_farmasi'])) {
            
            // Redirect ke admin dashboard dengan pesan error
            return redirect()->route('admin.dashboard.index')
                ->with('error', 'Anda tidak memiliki akses ke halaman mahasiswa.');
        }

        return $next($request);
    }
}