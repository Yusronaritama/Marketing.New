<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class AuthCustom
{
    public function handle($request, Closure $next)
    {
        if (!Auth::check()) {
            return redirect()->away('https://penmabar.ubpkarawang.ac.id/');
        }

        return $next($request);
    }
}
