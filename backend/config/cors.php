<?php

return [
    /*
    |--------------------------------------------------------------------------
    | CORS Configuration
    |--------------------------------------------------------------------------
    |
    | Allows frontend clients to call the Laravel API.
    |
    */

    'paths' => ['api/*'],

    'allowed_methods' => ['*'],

    'allowed_origins' => [
        // Local development servers
        'http://localhost:8080',
        'http://127.0.0.1:8080',
        'http://localhost:8081',
        'http://localhost:8000',
        'http://127.0.0.1:8000',
        'http://localhost:3000', // Mobile Frontend
        'http://localhost:4000', // Admin Website
        'http://localhost',
        'http://127.0.0.1',
        'capacitor://localhost',

        // Production — Custom Domain (Named Cloudflare Tunnel)
        'https://app.intan-elyu.online',
        'https://api.intan-elyu.online',

        // Production — Railway Cloud Backend
        'https://intanelyu-production.up.railway.app',
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
