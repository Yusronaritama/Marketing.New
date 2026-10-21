<?php

return [

    /*
     * A policy will determine which CSP headers will be set.
     */
    'policy' => \App\Support\Csp\BasicPolicy::class, // Pastikan ini hanya ditulis satu kali

    'enabled' => env('CSP_ENABLED', true),

    'nonce_generator' => Spatie\Csp\Nonce\RandomString::class,
];
