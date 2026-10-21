<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    protected $except = [
        'admin/webhook/message',
        '/admin/whatsapp/api/*',
        'payment/callback',
        'whatsapp/webhook/message',
        'slik/callback/payment',
        'api/whatsapp/webhook/message',
        'api/*',
        'api/whatsapp-status/*',
        'whatsapp/status',
        'api/registrasi_pendaftar',
        'api/whatsapp/status',
        'api/callback/mandiri',
        'admin/show-all/tren-tahun-ke-tahun/ai-analyze',
        'presensi/*',
        'presensi',
    ];
}
