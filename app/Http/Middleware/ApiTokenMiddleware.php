<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ApiTokenMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $validToken = env('API_TOKEN', 'pmbubpkarawang');
        $requestToken = $request->header('Authorization');
        
        // Remove "Bearer " prefix jika ada
        if (str_starts_with($requestToken, 'Bearer ')) {
            $requestToken = substr($requestToken, 7);
        }
        
        if ($requestToken !== $validToken) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized: Invalid API token'
            ], 401);
        }
        
        return $next($request);
    }
}