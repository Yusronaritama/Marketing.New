<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],
    
    // Konfigurasi MOODLE (Dihapus duplikasi)
    'moodle' => [
        'url' => env('MOODLE_API_URL'),
        'token' => env('MOODLE_API_TOKEN'),
        // Menambahkan konfigurasi lama yang menggunakan kunci MOODLE_URL dan MOODLE_TOKEN
        'base_url' => env('MOODLE_URL'), 
        'base_token' => env('MOODLE_TOKEN'),
    ],
    'node' => [
        'server_url' => env('NODE_SERVER_URL'),
    ],

    'ocr' => [
        'space_key' => env('OCR_SPACE_KEY'),
    ],
     'bni' => [
        'client_id' => env('BNI_CLIENT_ID', '41625'),
        'secret_key' => env('BNI_SECRET_KEY', 'd2ea3883241ee95f7d0d6fd1de085c41'),
        'api_url' => env('BNI_API_URL', 'https://api.bni-ecollection.com/'),
        'va_prefix' => env('BNI_VA_PREFIX', '98841625'),
    ],
    'sipt' => [
        'url'              => env('SIPT_API_URL', 'https://api-gateway.ubpkarawang.ac.id'),
        'token'            => env('SIPT_API_TOKEN', '4qd0a94x9xfhq039s927caAjda'),
        'default_password' => env('SIPT_DEFAULT_PASSWORD', 'ubpk2026'),
    ],

];
