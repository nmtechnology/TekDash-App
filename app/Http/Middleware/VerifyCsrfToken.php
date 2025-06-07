<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    protected $except = [
        'public/*', // Only exclude truly public routes
        'sanctum/csrf-cookie', // Allow CSRF cookie endpoint
        'csrf/refresh', // Allow CSRF refresh endpoint
    ];
}
