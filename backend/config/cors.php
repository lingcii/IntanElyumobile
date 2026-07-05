<?php

return [
    /*
    |--------------------------------------------------------------------------
    | CORS Configuration
    |--------------------------------------------------------------------------
    |
    | Allows the PHP frontend (http://localhost:8080) to call the Laravel API (http://127.0.0.1:8000).
    |
    | Both frontends run as separate processes in the non-XAMPP setup:
    |   - PHP built-in server:  php -S localhost:8080   (Frontend)
    |   - Laravel:              php artisan serve        (Backend, port 8000)
    |
    */

    'paths' => ['api/*'],

    'allowed_methods' => ['*'],

    'allowed_origins' => [
        // PHP built-in dev server (Frontend)
        'http://localhost:8080',
        'http://127.0.0.1:8080',
        'http://localhost:8081',
        'http://[IP_ADDRESS]',


        // Laravel itself (for same-origin requests / Artisan serve)
        'http://localhost:8000',
        'http://127.0.0.1:8000',

        // Generic localhost (no port) — kept for compatibility
        'http://localhost',
        'http://127.0.0.1',

        // Cloudflare tunnel (remote access / staging)
        'https://boc-cornell-rolled-delicious.trycloudflare.com',
    ],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 86400,

    /*
     * IMPORTANT: credentials must be true so the session cookie is sent
     * with every cross-origin request from the frontend dev servers.
     * The allowed_origins list above must NOT use a wildcard when this
     * is true — each origin must be listed explicitly.
     */
    'supports_credentials' => true,
];
