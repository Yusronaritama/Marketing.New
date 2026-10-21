<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\MasterUser;

class CheckVAStatus
{
    public function handle(Request $request, Closure $next)
    {
        // Skip middleware untuk route VA sendiri
        if ($request->routeIs('mahasiswa.va.*')) {
            return $next($request);
        }
        
        if (Auth::check()) {
            $user = Auth::user();
            $master = MasterUser::where('user_id', $user->id)->first();
            
            // Jika belum bayar dan VA sudah expired, redirect ke VA dashboard
            if ($master && 
                $master->va_status === 'pending' && 
                $master->va_expired_at && 
                now()->gt($master->va_expired_at)) {
                
                return redirect()->route('mahasiswa.va.dashboard')
                    ->with('warning', 'Virtual Account Anda sudah expired. Silakan buat VA baru untuk melanjutkan.');
            }
        }
        
        return $next($request);
    }
}