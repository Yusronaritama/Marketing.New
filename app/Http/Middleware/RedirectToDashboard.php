<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectToDashboard
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // JANGAN redirect jika belum login (biarkan guest access)
        if (!auth()->check()) {
            return $next($request);
        }
        
        // Hanya redirect jika sudah login dan mengakses root
        if (auth()->check() && $request->is('/')) {
            $user = auth()->user();
            
            switch ($user->role) {
                case 'admin':
                case 'admin_akademik':
                case 'admin_farmasi':
                    return redirect()->route('admin.dashboard');
                case 'mahasiswa_baru':
                    return redirect()->route('mahasiswa.dashboard');
                case 'user':
                    return redirect()->route('dashboard');
            }
        }
        
        return $next($request);
    }
}