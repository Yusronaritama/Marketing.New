<?php

namespace App\Http\Middleware;
use Illuminate\Http\Request;
use Closure;

class CheckRole
{
    public function handle($request, Closure $next, ...$roles)
{
    $user = auth()->user();
    
    if (!$user) {
            return redirect()->away('https://penmabar.ubpkarawang.ac.id');
    }

    if (in_array($user->role, $roles)) {
        return $next($request);
    }

    abort(403, 'Unauthorized action.');
}
}