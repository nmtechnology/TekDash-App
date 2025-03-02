<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class VerifyGroqApiKey extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'groq:verify-key';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verify Groq API key is working correctly';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting Groq API key verification...');
        
        // Check if key exists in environment
        $apiKey = env('GROQCLOUD_API_KEY');
        $configKey = config('groq.api_key');
        
        $this->info('ENV key exists: ' . ($apiKey ? 'Yes' : 'No'));
        $this->info('Config key exists: ' . ($configKey ? 'Yes' : 'No'));
        
        if ($apiKey) {
            $this->info('ENV key starts with: ' . substr($apiKey, 0, 5) . '...');
        }
        
        // Try a direct API call
        $this->info('Testing API call...');
        try {
            $client = new Client();
            $response = $client->get('https://api.groq.com/openai/v1/models', [
                'headers' => [
                    'Authorization' => 'Bearer ' . ($apiKey ?: $configKey),
                    'Content-Type' => 'application/json',
                ]
            ]);
            
            $statusCode = $response->getStatusCode();
            $this->info('API call successful! Status code: ' . $statusCode);
            $this->info('Response preview: ' . substr($response->getBody()->getContents(), 0, 100) . '...');
            
            return 0;
        } catch (\Exception $e) {
            $this->error('API call failed: ' . $e->getMessage());
            return 1;
        }
    }
}
