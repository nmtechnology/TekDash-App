<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class StorageCors
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);
        
        if (str_starts_with($request->getPathInfo(), '/storage/')) {
            // Get the request origin
            $origin = $request->headers->get('Origin');
            
            // Set allowed origins - include both localhost and 127.0.0.1 variations
            $allowedOrigins = [
                'http://localhost:8000',
                'http://127.0.0.1:8000',
                'http://localhost',
                'http://127.0.0.1'
            ];
            
            // If origin is in allowed list, set it specifically for better security
            if ($origin && in_array($origin, $allowedOrigins)) {
                $response->headers->set('Access-Control-Allow-Origin', $origin);
            } else {
                // Fallback to allow all origins (less secure but ensures functionality)
                $response->headers->set('Access-Control-Allow-Origin', '*');
            }
            
            $response->headers->set('Access-Control-Allow-Methods', 'GET, OPTIONS');
            $response->headers->set('Access-Control-Allow-Headers', 'Origin, Content-Type, Accept, Authorization');
            $response->headers->set('Access-Control-Allow-Credentials', 'true');
        }
        
        return $response;
    }
}
