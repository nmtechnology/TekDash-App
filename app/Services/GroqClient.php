<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class GroqClient
{
    protected $apiKey;
    protected $defaultModel;
    protected $timeout;
    protected $httpClient;
    protected $baseUrl = 'https://api.groq.com/openai/v1';

    public function __construct(array $config = [])
    {
        // TEMPORARY: Hardcode the API key for testing
        $this->apiKey = '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';
        
        $this->defaultModel = $config['default_model'] ?? 'llama2-70b-4096';
        $this->timeout = $config['timeout'] ?? 30;
        
        $this->initializeHttpClient();
        Log::info('Groq client initialized with hardcoded API key');
    }
    
    protected function initializeHttpClient()
    {
        $this->httpClient = new Client([
            'base_uri' => $this->baseUrl,
            'timeout'  => $this->timeout,
            'headers' => [
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ],
        ]);
        Log::info('Groq client initialized successfully with API key: ' . substr($this->apiKey, 0, 10) . '...');
    }
    public function chat($messages, $options = [])
    {
        try {
            $response = $this->httpClient->post('/chat/completions', [
                'json' => [
                    'model' => $options['model'] ?? $this->defaultModel,
                    'messages' => $messages,
                    'temperature' => $options['temperature'] ?? 0.7,
                    'max_tokens' => $options['max_tokens'] ?? 1000,
                ]
            ]);
            
            return json_decode($response->getBody()->getContents(), true);
        } catch (\Exception $e) {
            Log::error('Groq API error: ' . $e->getMessage());
            throw $e;
        }
    }
    
    public function testConnection()
    {
        try {
            $response = $this->httpClient->get('/models');
            return json_decode($response->getBody()->getContents(), true);
        } catch (\Exception $e) {
            Log::error('Groq API connection test failed: ' . $e->getMessage());
            throw $e;
        }
    }
}
    }
    
    public function chat($messages, $options = [])
    {
        try {
            $response = $this->httpClient->post('/chat/completions', [
                'json' => [
                    'model' => $options['model'] ?? $this->defaultModel,
                    'messages' => $messages,
                    'temperature' => $options['temperature'] ?? 0.7,
                    'max_tokens' => $options['max_tokens'] ?? 1000,
                ]
            ]);
            
            return json_decode($response->getBody()->getContents(), true);
        } catch (\Exception $e) {
            Log::error('Groq API error: ' . $e->getMessage());
            throw $e;
        }
    }
    
    public function testConnection()
    {
        try {
            $response = $this->httpClient->get('/models');
            return json_decode($response->getBody()->getContents(), true);
        } catch (\Exception $e) {
            Log::error('Groq API connection test failed: ' . $e->getMessage());
            throw $e;
        }
    }
}
