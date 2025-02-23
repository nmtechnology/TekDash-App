<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\WorkOrder;

class GroqController extends Controller
{
    public function query(Request $request)
    {
        $query = $request->input('query');
        
        try {
            
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . config('services.groqcloud.api_key'),
                'Content-Type' => 'application/json',
            ])->post('https://api.groq.com/openai/v1/chat/completions', [
                'model' => 'mixtral-8x7b-32768',
                'messages' => [
                    ['role' => 'system', 'content' => 'You are the ver knowledgable assistant called TekDash, a helpful AI assistant with a knowledge of low voltage.'],
                    ['role' => 'user', 'content' => $query]
                ],
                'temperature' => 0.7,
                'max_tokens' => 1000
            ]);
            // Fetch work orders
            // $workOrders = WorkOrder::all();
            // if ($response->failed()) {
            //     throw new \Exception('Groq API request failed: ' . $response->body());
            // }

            // return response()->json([
            //     'response' => $response->json('choices.0.message.content'),
            //     'work_orders' => $workOrders
            // ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error processing query: ' . $e->getMessage()
            ], 500);
        }
    }
}