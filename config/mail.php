<?php

return [
    'default' => env('MAIL_MAILER', 'smtp'),
    
    'mailers' => [
        'smtp' => [
            'transport' => 'smtp',
            'url' => env('MAIL_URL'),
            'host' => env('MAIL_HOST', 'smtp.gmail.com'),
            'port' => env('MAIL_PORT', 587),
            'encryption' => env('MAIL_ENCRYPTION', 'tls'),
            'username' => env('MAIL_USERNAME'),
            'password' => env('MAIL_PASSWORD'),
            'timeout' => env('MAIL_TIMEOUT', 60),
            'local_domain' => env('MAIL_EHLO_DOMAIN'),
            'auth_mode' => env('MAIL_AUTH_MODE', null),
            'verify_peer' => env('MAIL_STREAM_SSL_VERIFY_PEER', false),
            'verify_peer_name' => env('MAIL_STREAM_SSL_VERIFY_PEER_NAME', false),
        ],
        
        'ses' => [
            'transport' => 'ses',
        ],
        
        'mailgun' => [
            'transport' => 'mailgun',
        ],
        
        'postmark' => [
            'transport' => 'postmark',
        ],
        
        'sendmail' => [
            'transport' => 'sendmail',
            'path' => env('MAIL_SENDMAIL_PATH', '/usr/sbin/sendmail -bs -i'),
        ],
        
        'log' => [
            'transport' => 'log',
            'channel' => env('MAIL_LOG_CHANNEL'),
        ],
        
        'array' => [
            'transport' => 'array',
        ],
        
        'failover' => [
            'transport' => 'failover',
            'mailers' => [
                'smtp',
                'log',
            ],
        ],
    ],
    
    'from' => [
        'address' => env('MAIL_FROM_ADDRESS', 'marketing@ubpkarawang.ac.id'),
        'name' => env('MAIL_FROM_NAME', 'PMB UBP'),
    ],
    
    'markdown' => [
        'theme' => 'default',
        'paths' => [
            resource_path('views/vendor/mail'),
        ],
    ],
];