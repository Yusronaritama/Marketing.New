<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfNotLoggedIn
{
    public function handle(Request $request, Closure $next): Response
    {
        // Jika user belum login dan mengakses root domain "/"
        if (!auth()->check() && $request->is('/')) {
            return redirect()->away('https://penmabar.ubpkarawang.ac.id');
        }

        return $next($request);
    }
}
