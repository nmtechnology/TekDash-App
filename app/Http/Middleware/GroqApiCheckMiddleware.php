<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class GroqApiCheckMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $apiKey = config('groq.api_key') ?: env('GROQCLOUD_API_KEY');
        
        if (empty($apiKey) && $request->is('groq/*')) {
            Log::warning('Groq API request attempted without API key configured');
            return response()->json([
                'error' => 'API key not configured',
                'details' => 'The Groq API key is not configured. Please check your .env file and configuration.',
            ], 500);
        }
        
        return $next($request);
    }
}
