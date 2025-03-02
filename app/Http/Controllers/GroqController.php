<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\WorkOrder;

class GroqController extends Controller
{
    public function query(Request $request)
    {
        try {
            $apiKey = config('services.groq.api_key');
            $model = config('services.groq.model');
            
            if (!$apiKey) {
                Log::error('Groq API key not configured');
                return response()->json(['error' => 'API key not configured'], 500);
            }

            $response = Http::withHeaders([
                'Authorization' => "Bearer $apiKey",
                'Content-Type' => 'application/json'
            ])->timeout(30)->post('https://api.groq.com/openai/v1/chat/completions', [
                'model' => $model ?? 'mixtral-8x7b-32768',
                'messages' => [
                    ['role' => 'user', 'content' => $request->input('query')],
                ],
                'temperature' => 0.7,
                'max_tokens' => 1000,
                'top_p' => 1.0,
                'frequency_penalty' => 0.0,
                'presence_penalty' => 0.0
            ]);
            

            Log::debug('Groq API Response', [
                'status' => $response->status(),
                'body' => $response->json()
            ]);

            if (!$response->successful()) {
                return response()->json([
                    'error' => 'Groq API error: ' . ($response->json()['error']['message'] ?? 'Unknown error'),
                    'debug_data' => $response->json()
                ], 500);
            }

            return response()->json([
                'response' => $response->json()['choices'][0]['message']['content']
            ]);

        } catch (\Exception $e) {
            Log::error('Groq API Exception', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'error' => 'Internal server error: ' . $e->getMessage()
            ], 500);
        }
    }
}