<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Groq API Configuration
    |--------------------------------------------------------------------------
    |
    | Here you can configure your Groq API settings. The 'api_key' is required
    | for authenticating requests to the Groq API service.
    |
    */

    'api_key' => env('GROQCLOUD_API_KEY'),
    
    /*
    |--------------------------------------------------------------------------
    | Default Model
    |--------------------------------------------------------------------------
    |
    | The default model to use for Groq API requests.
    |
    */
    'default_model' => env('GROQ_DEFAULT_MODEL', 'llama2-70b-4096'),

    /*
    |--------------------------------------------------------------------------
    | Timeout
    |--------------------------------------------------------------------------
    |
    | The maximum number of seconds to wait for a response from the Groq API.
    |
    */
    'timeout' => env('GROQ_TIMEOUT', 30),
];
