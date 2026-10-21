<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  ...$roles
     * @return mixed
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = $request->user();
        
        if (!$user) {
            return redirect()->away('https://penmabar.ubpkarawang.ac.id');

        }
        
        // Check if user has any of the required roles
        if (in_array($user->role, $roles)) {
            return $next($request);
        }
        
        // If user is admin trying to access mahasiswa routes or vice versa
        if ($user->role === 'admin' || $user->role === 'admin_akademik' || $user->role === 'admin_farmasi') {
            return redirect()->route('admin.dashboard.index');
        }
        
        if ($user->role === 'mahasiswa_baru') {
            return redirect()->route('mahasiswa.dashboard');
        }
        
        // Default redirect for other roles
        return redirect()->route('dashboard');
    }
}