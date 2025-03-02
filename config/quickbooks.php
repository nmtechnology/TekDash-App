<?php

return [
    'client_id' => env('QUICKBOOKS_CLIENT_ID', ''),
    'client_secret' => env('QUICKBOOKS_CLIENT_SECRET', ''),
    'environment' => env('QUICKBOOKS_ENVIRONMENT', 'sandbox'),
    
    // Update this to exactly match what's in QuickBooks Developer Console
    'redirect_uri' => env('QUICKBOOKS_REDIRECT_URI', 'https://developer.intuit.com/v2/OAuth2Playground/RedirectUrl'),
];