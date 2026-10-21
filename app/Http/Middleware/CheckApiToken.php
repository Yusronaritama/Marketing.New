<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckApiToken
{
    public function handle(Request $request, Closure $next)
    {
        $token = $request->bearerToken();
        $validToken = env('LARAVEL_API_TOKEN', 'pmbubpkarawang');
        
        if (!$token || $token !== $validToken) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized. Invalid or missing API token.'
            ], 401);
        }
        
        return $next($request);
    }
}