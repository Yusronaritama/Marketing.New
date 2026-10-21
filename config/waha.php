<?php

return [
    'waha' => [
        'base_url' => env('WAHA_BASE_URL', 'http://localhost:3000'),
        'api_key' => env('WAHA_API_KEY', ''),
        'webhook_url' => env('WAHA_WEBHOOK_URL', ''),
        'ip_address' => env('WAHA_IP_ADDRESS', '127.0.0.1'),
        'port' => env('WAHA_PORT', '3000'),
    ],
];