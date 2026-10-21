<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminCheck
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return redirect()->away('https://penmabar.ubpkarawang.ac.id');
        }

        // Cek jika user memiliki role admin
        if (auth()->user()->role !== 'admin') {
            abort(403, 'Unauthorized access.');
        }

        return $next($request);
    }
}