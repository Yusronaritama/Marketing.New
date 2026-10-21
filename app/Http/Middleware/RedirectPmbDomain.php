<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class RedirectPmbDomain
{
    public function handle(Request $request, Closure $next)
    {
        $host = $request->getHost();
        $path = $request->path();
        
        // Domain yang harus di-handle
        $pmbDomains = [
            'pmb.ubpkarawang.ac.id',
            'www.pmb.ubpkarawang.ac.id',
        ];
        
        // Cek jika domain adalah pmb.ubpkarawang.ac.id
        if (in_array($host, $pmbDomains)) {
            // LOGIKA: Hanya redirect jika mengakses root "/" tanpa path
            if ($path === '' || $path === '/' || $path === 'home' || $path === 'welcome') {
                // Log untuk monitoring
                Log::info('PMB Root Redirect', [
                    'from_domain' => $host,
                    'to_domain' => 'penmabar.ubpkarawang.ac.id',
                    'ip_address' => $request->ip(),
                    'path' => $path,
                    'timestamp' => now()->toDateTimeString()
                ]);
                
                // Redirect hanya root ke penmabar.ubpkarawang.ac.id
                return redirect()->away('https://penmabar.ubpkarawang.ac.id', 301);
            }
            
            // Untuk semua path lainnya (login, register, dashboard, dll) -> TIDAK DI-REDIRECT
            // Biarkan user mengakses langsung di domain pmb.ubpkarawang.ac.id
            Log::info('PMB Direct Access Allowed', [
                'domain' => $host,
                'path' => $path,
                'ip_address' => $request->ip(),
                'timestamp' => now()->toDateTimeString()
            ]);
        }
        
        // HAPUS blokir untuk penmabar.ubpkarawang.ac.id/login
        // Biarkan semua akses di penmabar domain
        
        return $next($request);
    }
}