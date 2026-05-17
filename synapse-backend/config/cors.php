<?php

return [

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    // Baca dari .env — tinggal ganti APP_FRONTEND_URL saat deploy
    'allowed_origins' => array_filter(
        explode(',', env('APP_FRONTEND_URL', 'http://127.0.0.1:8001,http://localhost:8001'))
    ),

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => false,

];