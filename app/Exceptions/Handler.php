<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
        
        // Add JSON response for API requests
        $this->renderable(function (Throwable $e, Request $request) {
            if ($request->expectsJson() || $request->is('api/*') || $request->ajax()) {
                $status = $this->isHttpException($e) ? $e->getStatusCode() : 500;
                
                // Log detailed error information for server errors
                if ($status >= 500) {
                    Log::error('API Error', [
                        'exception' => get_class($e),
                        'message' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                        'url' => $request->fullUrl(),
                        'method' => $request->method(),
                        'headers' => $request->headers->all(),
                    ]);
                }
                
                return response()->json([
                    'message' => $status >= 500 ? 'Server error occurred.' : $e->getMessage(),
                    'error' => $e->getMessage(),
                    'status' => $status,
                    'code' => $e->getCode(),
                    'debug' => config('app.debug') ? [
                        'file' => $e->getFile(),
                        'line' => $e->getLine(),
                    ] : null,
                ], $status, [
                    'Content-Type' => 'application/json',
                    'X-Response-Time' => round(microtime(true) - LARAVEL_START, 3),
                ]);
            }
        });
    }
}
