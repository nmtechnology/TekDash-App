<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Response;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Force HTTPS in production
        if (config('app.env') === 'production') {
            URL::forceScheme('https');
        }
        
        // Add PDF response macro for better handling of PDFs
        Response::macro('pdf', function ($content, $filename = null) {
            $headers = [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => $filename ? 'inline; filename="'.$filename.'"' : 'inline',
                'Accept-Ranges' => 'bytes',
                'X-Content-Type-Options' => 'nosniff',
            ];
            
            return Response::make($content, 200, $headers);
        });
    }
}
