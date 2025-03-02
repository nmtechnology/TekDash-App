<?php

namespace App\Http\Controllers;

use App\Facades\Groq;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class GroqTestController extends Controller
{
    public function test()
    {
        try {
            // Display the first few characters of the API key (for debugging)
            $apiKey = config('groq.api_key') ?: env('GROQCLOUD_API_KEY');
            $maskedKey = $apiKey ? (substr($apiKey, 0, 10) . '...') : 'Not configured';
            
            Log::info("Testing Groq connection with API key: {$maskedKey}");
            
            // Test the connection by retrieving available models
            $result = Groq::testConnection();
            
            return response()->json([
                'status' => 'success',
                'message' => 'Groq API connection successful',
                'api_key_configured' => !empty($apiKey),
                'models' => $result['data'] ?? [],
            ]);
        } catch (\Exception $e) {
            Log::error('Groq test failed: ' . $e->getMessage());
            
            return response()->json([
                'status' => 'error',
                'message' => 'Groq API connection failed: ' . $e->getMessage(),
                'env_api_key_set' => !empty(env('GROQCLOUD_API_KEY')),
                'config_api_key_set' => !empty(config('groq.api_key')),
            ], 500);
        }
    }
    
    public function chat(Request $request)
    {
        try {
            $messages = $request->input('messages', [
                ['role' => 'system', 'content' => 'You are a helpful assistant.'],
                ['role' => 'user', 'content' => 'Hello, how are you?']
            ]);
            
            $result = Groq::chat($messages);
            
            return response()->json([
                'status' => 'success',
                'response' => $result,
            ]);
        } catch (\Exception $e) {
            Log::error('Groq chat failed: ' . $e->getMessage());
            
            return response()->json([
                'status' => 'error',
                'message' => 'Groq chat failed: ' . $e->getMessage(),
            ], 500);
        }
    }
}
