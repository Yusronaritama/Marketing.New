<?php

return [
    /*
    |--------------------------------------------------------------------------
    | WhatsApp Configuration
    |--------------------------------------------------------------------------
    |
    | Konfigurasi untuk koneksi ke server WhatsApp Node.js
    |
    */

    'server' => [
        'url' => env('WHATSAPP_SERVER_URL', 'http://103.23.198.160:3009'),
        'timeout' => env('WHATSAPP_TIMEOUT', 30),
        'retry_attempts' => env('WHATSAPP_RETRY_ATTEMPTS', 3),
    ],

    'admin_numbers' => explode(',', env('WHATSAPP_ADMIN_NUMBERS', '6281234567890')),
    /*
    |--------------------------------------------------------------------------
    | Message Templates
    |--------------------------------------------------------------------------
    |
    | Template pesan WhatsApp yang dapat digunakan
    |
    */
    'templates' => [
        'forgot_account' => [
            'subject' => 'Informasi Akun Anda',
            'body' => "Halo :name,\n\nBerikut informasi akun Anda:\nUsername: :trx_id\nVirtual Account: :va_number\n\nUntuk reset password, klik link berikut:\n:reset_url\n\nLink berlaku 24 jam.\n\nTerima kasih,\n:app_name"
        ],
        'password_reset_success' => [
            'subject' => 'Password Berhasil Direset',
            'body' => "Halo :name,\n\nPassword akun Anda berhasil direset.\nSekarang Anda bisa login dengan password baru.\n\nJika Anda tidak melakukan reset ini, segera hubungi administrator.\n\nTerima kasih,\n:app_name"
        ],
        'payment_reminder' => [
            'subject' => 'Pengingat Pembayaran',
            'body' => "Halo :name,\n\nIni adalah pengingat untuk pembayaran Anda:\nUsername: :trx_id\nVirtual Account: :va_number\nJumlah: :amount\n\nSilakan lakukan pembayaran sebelum :due_date.\n\nTerima kasih,\n:app_name"
        ],
        'registration_success' => [
            'subject' => 'Registrasi Berhasil',
            'body' => "Halo :name,\n\nRegistrasi Anda berhasil!\nUsername: :trx_id\nVirtual Account: :va_number\n\nSilakan lakukan pembayaran untuk melanjutkan.\n\nTerima kasih,\n:app_name"
        ],
        'test_message' => [
            'subject' => 'Test Message',
            'body' => "Halo,\n\nIni adalah pesan test dari sistem :app_name.\n\nWaktu: :timestamp\n\nTerima kasih."
        ],
        'password_reset' => [
            'subject' => 'Reset Password',
            'body' => "Halo :name,\n\nAnda menerima email ini karena kami menerima permintaan reset password untuk akun Anda.\n\nUntuk reset password, klik link berikut:\n:reset_url\n\nLink reset password akan kadaluarsa dalam 24 jam.\n\nJika Anda tidak meminta reset password, abaikan pesan ini.\nTautan reset password juga telah dikirim ke email: :email\n\nTerima kasih,\n:app_name"
        ],
        'registration_success' => [
            'body' => "Halo :name!\n\n" .
                     "🎉 *PENDAFTARAN BERHASIL* 🎉\n\n" .
                     "Berikut adalah informasi akun Anda:\n" .
                     "• *Username*: `:trx_id`\n" .
                     "📱 *Simpan informasi ini dengan baik!*\n\n" .
                     "Login: :login_url\n\n" .
                     "Virtual Account: :va_number\n" .
                     "Amount: :amount\n" .
                     "Expired: :expired_date\n" .
                     "Gelombang: :gelombang\n\n" .
                     "Jika ada pertanyaan, hubungi admin kami.\n\n" .
                     "Terima kasih,\n" .
                     "Tim PMB :app_name",
        ],
        ],

    /*
    |--------------------------------------------------------------------------
    | Queue Configuration
    |--------------------------------------------------------------------------
    |
    | Konfigurasi queue untuk pengiriman WhatsApp
    |
    */
    'queue' => [
        'enabled' => env('WHATSAPP_QUEUE_ENABLED', true),
        'connection' => env('WHATSAPP_QUEUE_CONNECTION', 'database'),
        'queue' => env('WHATSAPP_QUEUE_NAME', 'whatsapp'),
        'delay' => env('WHATSAPP_QUEUE_DELAY', 0),
    ],

    /*
    |--------------------------------------------------------------------------
    | Logging
    |--------------------------------------------------------------------------
    |
    | Konfigurasi logging untuk WhatsApp
    |
    */
    'logging' => [
        'enabled' => env('WHATSAPP_LOGGING_ENABLED', true),
        'channel' => env('WHATSAPP_LOG_CHANNEL', 'stack'),
        'level' => env('WHATSAPP_LOG_LEVEL', 'info'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Fallback Options
    |--------------------------------------------------------------------------
    |
    | Opsi fallback jika WhatsApp gagal
    |
    */
    'fallback' => [
        'email' => env('WHATSAPP_FALLBACK_EMAIL', true),
        'sms' => env('WHATSAPP_FALLBACK_SMS', false),
        'notification' => env('WHATSAPP_FALLBACK_NOTIFICATION', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Test Configuration
    |--------------------------------------------------------------------------
    |
    | Konfigurasi untuk testing
    |
    */
    'test' => [
        'enabled' => env('WHATSAPP_TEST_MODE', false),
        'test_phone' => env('WHATSAPP_TEST_PHONE', ''),
        'log_only' => env('WHATSAPP_LOG_ONLY', false),
    ],
];