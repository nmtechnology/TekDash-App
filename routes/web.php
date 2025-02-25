<?php

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\GroqController;
use App\Http\Controllers\WorkOrderController;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');
});

Route::post('/groq/query', [GroqController::class, 'query'])->name('groq.query');
Route::post('/api/groq/query', [GroqController::class, 'query']);

// Work Orders Routing

Route::resource('work-orders', WorkOrderController::class);
Route::get('/api/work-orders', [WorkOrderController::class, 'index']);
Route::get('/work-orders', [WorkOrderController::class, 'index'])->name('work-orders.index');
Route::post('/work-orders', [WorkOrderController::class, 'store'])->name('work-orders.store');
Route::put('/work-orders/{id}', [WorkOrderController::class, 'update']);
Route::delete('/work-orders/{id}', [WorkOrderController::class, 'destroy']);
Route::post('/work-orders/{id}/duplicate', [WorkOrderController::class, 'duplicate']);
Route::get('/work-orders/{id}/edit', [WorkOrderController::class, 'edit']);
Route::get('/work-orders/create', [WorkOrderController::class, 'create']);
Route::get('/work-orders/{id}', [WorkOrderController::class, 'show']);
    