<?php

namespace App\Providers;

use App\Services\GroqClient;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Log;

class GroqServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register the configuration file if it doesn't exist
        $this->mergeConfigFrom(
            __DIR__.'/../../config/groq.php', 'groq'
        );
        
        $this->app->singleton('groq', function ($app) {
            try {
                $config = $app['config']->get('groq');
                
                // Ensure the API key is available
                if (empty($config['api_key']) && empty(env('GROQCLOUD_API_KEY'))) {
                    Log::error('Groq API key not found in configuration or environment variables');
                    throw new \Exception('Groq API key not configured');
                }
                
                return new GroqClient($config);
            } catch (\Exception $e) {
                Log::error('Failed to initialize Groq client: ' . $e->getMessage());
                throw $e;
            }
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Publish the configuration file
        $this->publishes([
            __DIR__.'/../../config/groq.php' => config_path('groq.php'),
        ], 'groq-config');
        
        // Log the API key status
        if (empty(config('groq.api_key'))) {
            Log::warning('Groq API key is not set in configuration');
        } else {
            Log::info('Groq configuration loaded successfully');
        }
    }
}
