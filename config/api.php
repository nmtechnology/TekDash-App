<?php

return [
    /*
    |--------------------------------------------------------------------------
    | API Keys
    |--------------------------------------------------------------------------
    |
    | This file contains the API keys used throughout the application.
    |
    */

    'groqcloud' => [
        'key' => env('GROQCLOUD_API_KEY'),
    ],
    
    'quickbooks' => [
        'client_id' => env('QUICKBOOKS_CLIENT_ID'),
        'client_secret' => env('QUICKBOOKS_CLIENT_SECRET'),
        'redirect_uri' => env('QUICKBOOKS_REDIRECT_URI'),
    ],
    
    // Add other API configurations as needed
];
