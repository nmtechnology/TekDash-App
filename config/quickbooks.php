<?php

return [
    'client_id' => env('QUICKBOOKS_CLIENT_ID', ''),
    'client_secret' => env('QUICKBOOKS_CLIENT_SECRET', ''),
    'realm_id' => env('QUICKBOOKS_REALM_ID', ''),
    'access_token' => env('QUICKBOOKS_ACCESS_TOKEN', ''),
    'refresh_token' => env('QUICKBOOKS_REFRESH_TOKEN', ''),
    'environment' => env('QUICKBOOKS_ENVIRONMENT', 'sandbox'),
];