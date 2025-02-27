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
    

// Messenger ROuting

Route::get('/messenger', function () {
    return Inertia::render('Messenger');
})->name('messenger');

// Notes Routing

Route::post('/work-orders/{workOrder}/notes', [WorkOrderController::class, 'addNote'])->middleware('auth');

// In your web.php routes file
Route::post('/work-orders/{id}/update-field', [WorkOrderController::class, 'updateField'])->name('work-orders.update-field');
Route::post('/work-orders/{id}/update-images', [WorkOrderController::class, 'updateImages'])->name('work-orders.update-images');

// Work order field and image updates
Route::put('/work-orders/{id}/update-field', [WorkOrderController::class, 'updateField'])
    ->name('work-orders.update-field')
    ->middleware(['auth']);
    
Route::post('/work-orders/{id}/update-images', [WorkOrderController::class, 'updateImages'])
    ->name('work-orders.update-images')
    ->middleware(['auth']);

    // Note routes
    Route::get('/work-orders/{workOrderId}/notes', [\App\Http\Controllers\NoteController::class, 'index'])
    ->name('work-order.notes.index')
    ->middleware(['auth']);

Route::post('/work-orders/{workOrderId}/notes', [\App\Http\Controllers\NoteController::class, 'store'])
    ->name('work-order.notes.store')
    ->middleware(['auth']);

    Route::get('/users-list', function () {
        return response()->json(App\Models\User::select('id', 'name', 'email')->get());
    })->middleware(['auth']);

    Route::get('/calendar-data', [WorkOrderController::class, 'calendarEvents'])
    ->middleware(['auth'])
    ->name('calendar.data');

    Route::get('/calendar-events', [WorkOrderController::class, 'calendarEvents'])
    ->middleware(['auth'])
    ->name('calendar.events');

    Route::get('/work-orders/{id}', [WorkOrderController::class, 'show'])
    ->middleware(['auth'])
    ->name('work-orders.show');


// Get detailed work order for calendar clicks
Route::get('/work-orders/{id}/details', [WorkOrderController::class, 'getDetails'])
    ->middleware(['auth'])
    ->name('work-orders.details');

    Route::delete('/work-orders/{id}', [WorkOrderController::class, 'destroy'])
    ->middleware(['auth'])
    ->name('work-orders.destroy');

    // Get work order statistics for dashboard
Route::get('/work-order-stats', [WorkOrderController::class, 'getStats'])
->middleware(['auth'])
->name('work-orders.stats');

Route::delete('/work-orders/{id}', [WorkOrderController::class, 'destroy'])
    ->middleware(['auth'])
    ->name('work-orders.destroy');