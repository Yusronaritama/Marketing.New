<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectRootDomain
{
    /**
     * Handle an incoming request.
     * 
     * Middleware ini HARUS dijalankan PALING AWAL sebelum middleware lain
     * untuk redirect domain pmb.ubpkarawang.ac.id ke penmabar.ubpkarawang.ac.id
     */
    public function handle(Request $request, Closure $next): Response
    {
        // PRIORITAS TERTINGGI: Cek domain dan path
        if ($request->getHost() === 'pmb.ubpkarawang.ac.id' && $request->path() === '/') {
            // Redirect langsung, JANGAN lanjutkan request
            return redirect('https://penmabar.ubpkarawang.ac.id/', 301);
        }
        
        // Jika bukan kondisi di atas, lanjutkan ke middleware berikutnya
        return $next($request);
    }
}