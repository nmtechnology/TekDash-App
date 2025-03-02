<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Client;

class GroqDiagnosticController extends Controller
{
    public function diagnose()
    {
        // Collect diagnostic information
        $diagnosticData = [
            'env_api_key_exists' => !empty(env('GROQCLOUD_API_KEY')),
            'env_api_key_first_chars' => env('GROQCLOUD_API_KEY') ? substr(env('GROQCLOUD_API_KEY'), 0, 5) . '...' : 'Not found',
            'config_api_key_exists' => !empty(config('groq.api_key')),
            'config_api_key_first_chars' => config('groq.api_key') ? substr(config('groq.api_key'), 0, 5) . '...' : 'Not found',
            'artisan_env' => $this->getArtisanEnv(),
            'dotenv_loaded' => function_exists('env'),
            'laravel_version' => app()->version(),
            'php_version' => PHP_VERSION,
            'config_cached' => app()->configurationIsCached(),
            'env_file_exists' => file_exists(base_path('.env')),
        ];

        // Test direct API call without using the service
        try {
            $client = new Client();
            $headers = [
                'Authorization' => 'Bearer ' . env('GROQCLOUD_API_KEY'),
                'Content-Type' => 'application/json',
            ];

            $response = $client->get('https://api.groq.com/openai/v1/models', [
                'headers' => $headers
            ]);

            $diagnosticData['direct_api_call'] = [
                'success' => true,
                'status_code' => $response->getStatusCode(),
                'response_preview' => substr($response->getBody()->getContents(), 0, 100) . '...'
            ];
        } catch (\Exception $e) {
            $diagnosticData['direct_api_call'] = [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }

        // Log the diagnostic information
        Log::info('Groq API Diagnostics', $diagnosticData);
        
        return response()->json($diagnosticData);
    }
    
    private function getArtisanEnv()
    {
        try {
            $output = [];
            exec('cd ' . base_path() . ' && php artisan env', $output);
            return implode("\n", $output);
        } catch (\Exception $e) {
            return 'Error getting artisan env: ' . $e->getMessage();
        }
    }
    
    public function testDirectAccessKey()
    {
        // Bypass all framework methods and try to read the .env file directly
        $envPath = base_path('.env');
        $envContents = file_exists($envPath) ? file_get_contents($envPath) : null;
        
        // Simple regex to extract the API key
        preg_match('/GROQCLOUD_API_KEY=([^\s]+)/', $envContents, $matches);
        $extractedKey = $matches[1] ?? null;
        
        $isKeyFound = !empty($extractedKey);
        $keyFirstChars = $isKeyFound ? substr($extractedKey, 0, 5) . '...' : 'Not found';
        
        return response()->json([
            'env_file_exists' => file_exists($envPath),
            'env_file_readable' => is_readable($envPath),
            'key_found_in_env' => $isKeyFound,
            'key_preview' => $keyFirstChars,
            // If we found the key, try a direct API call with it
            'direct_test' => $isKeyFound ? $this->testDirectCall($extractedKey) : null,
        ]);
    }
    
    private function testDirectCall($apiKey)
    {
        try {
            $client = new Client();
            $response = $client->get('https://api.groq.com/openai/v1/models', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $apiKey,
                    'Content-Type' => 'application/json',
                ]
            ]);
            
            return [
                'success' => true,
                'status' => $response->getStatusCode(),
                'message' => 'API call successful',
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }
}
