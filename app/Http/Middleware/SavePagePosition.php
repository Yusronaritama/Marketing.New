<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SavePagePosition
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Cek jika route adalah profile-lengkap.index
        if ($request->routeIs('admin.profile-lengkap.index')) {
            // Reset session jika ada parameter reset
            if ($request->has('reset') && $request->get('reset') === 'true') {
                session()->forget('profile_lengkap_page');
            }
            
            // Simpan filter ke session
            if ($request->hasAny(['search', 'kelulusan', 'verifikasi', 'per_page'])) {
                session(['profile_lengkap_filters' => $request->only(['search', 'kelulusan', 'verifikasi', 'per_page'])]);
            }
        }
        
        return $next($request);
    }
}