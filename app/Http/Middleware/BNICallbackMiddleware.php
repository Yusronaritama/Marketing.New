<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class BNICallbackMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        // Log semua request ke callback
        Log::info('🔔 Request ke callback endpoint', [
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

        // Validasi IP BNI (jika diperlukan)
        $allowedIPs = [
            '103.247.9.226', // IP BNI Production
            '103.247.9.227', // IP BNI Production alternatif
            '127.0.0.1',     // Localhost untuk testing
        ];

        if (!app()->environment('local', 'testing') && !in_array($request->ip(), $allowedIPs)) {
            Log::warning('🚫 IP tidak diizinkan mengakses callback', [
                'ip' => $request->ip(),
                'allowed_ips' => $allowedIPs
            ]);
            
            return response()->json([
                'status' => '999',
                'message' => 'Access denied'
            ], 403);
        }

        // Validasi content-type
        if (!$request->isJson()) {
            Log::warning('🚫 Request bukan JSON', [
                'content_type' => $request->header('Content-Type')
            ]);
            
            return response()->json([
                'status' => '999',
                'message' => 'Invalid content type'
            ], 400);
        }

        return $next($request);
    }
}