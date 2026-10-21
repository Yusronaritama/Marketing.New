<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class BlockDirectLogin
{
    public function handle(Request $request, Closure $next)
    {
        // Cek jika user mencoba mengakses /login atau /login/
        $path = $request->path();
        
        if (in_array($path, ['login', 'login/'])) {
            // Redirect ke halaman utama dengan pesan
            return redirect('/')->with('error', 'Akses langsung ke halaman login tidak diizinkan. Silakan gunakan tombol login yang tersedia.');
        }
        
        return $next($request);
    }
}