<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CsrfController;

// CSRF Token Routes with web middleware
Route::middleware(['web'])->group(function () {
    // The main token refresh endpoint supporting both GET and POST
    Route::match(['GET', 'POST'], '/csrf/refresh', [CsrfController::class, 'refresh'])
        ->name('csrf.refresh');

    // Separate endpoints for sanctum
    Route::get('/sanctum/csrf-cookie', function () {
        return response()
            ->json(['token' => csrf_token()])
            ->withHeaders([
                'X-CSRF-TOKEN' => csrf_token(),
                'X-XSRF-TOKEN' => csrf_token(),
            ]);
    })->name('sanctum.csrf-cookie');

    // Backup token endpoint
    Route::get('/csrf-token', function () {
        return response()
            ->json(['token' => csrf_token()])
            ->withHeaders([
                'X-CSRF-TOKEN' => csrf_token(),
                'X-XSRF-TOKEN' => csrf_token(),
            ]);
    })->name('csrf.token');
});
