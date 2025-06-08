<?php

return [
    'paths' => ['*'],
    'allowed_methods' => ['*'],
    'allowed_origins' => [
        env('APP_URL', 'http://localhost:8000'),
        'http://localhost',
        'http://127.0.0.1:8000',
        'http://localhost:3000'
    ],
    'allowed_origins_patterns' => [],
    'allowed_headers' => [
        '*',
        'X-CSRF-TOKEN',
        'X-XSRF-TOKEN',
        'X-Requested-With',
        'Content-Type',
        'Accept',
        'Authorization'
    ],
    'exposed_headers' => [
        'X-CSRF-TOKEN',
        'X-XSRF-TOKEN'
    ],
    'max_age' => 0,
    'supports_credentials' => true,
];
