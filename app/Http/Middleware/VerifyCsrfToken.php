<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Facades\Log;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    protected $except = [
        'sanctum/csrf-cookie',
        'csrf/refresh',
    ];

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     *
     * @throws \Illuminate\Session\TokenMismatchException
     */
    public function handle($request, Closure $next)
    {
        try {
            // Check if the route should be excluded
            if ($this->isReading($request) || $this->inExceptArray($request) || 
                $this->tokensMatch($request)) {
                return $next($request);
            }

            $token = csrf_token();
            
            // Log token mismatch for debugging
            Log::debug('CSRF Token Mismatch', [
                'url' => $request->url(),
                'method' => $request->method(),
                'headers' => [
                    'X-CSRF-TOKEN' => $request->header('X-CSRF-TOKEN'),
                    'X-XSRF-TOKEN' => $request->header('X-XSRF-TOKEN'),
                ],
                'has_session' => $request->hasSession(),
                'session_token' => $request->session()->token(),
                'new_token' => $token,
            ]);

            // For AJAX requests, return JSON response with new token
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'message' => 'CSRF token mismatch',
                    'token' => $token
                ], 419)->withHeaders([
                    'X-CSRF-TOKEN' => $token,
                    'X-XSRF-TOKEN' => $token
                ])->cookie('XSRF-TOKEN', $token, 60, '/', null, config('session.secure'), true);
            }

            throw new TokenMismatchException('CSRF token mismatch');
        } catch (\Exception $e) {
            Log::error('CSRF Verification Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }
}
