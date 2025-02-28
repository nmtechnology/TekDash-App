<?php

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\GroqController;
use App\Http\Controllers\WorkOrderController;
use App\Http\Controllers\QuickBooksAuthController;
use App\Http\Controllers\InvoiceController;

// Public routes
Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

// Protected routes
Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');
    
    // Groq API
    Route::post('/groq/query', [GroqController::class, 'query'])->name('groq.query');
    Route::post('/api/groq/query', [GroqController::class, 'query']);
    
    // Work Orders (using resource route)
    Route::resource('work-orders', WorkOrderController::class);
    
    // Additional work order routes
    Route::get('/api/work-orders', [WorkOrderController::class, 'index']);
    Route::post('/work-orders/{id}/duplicate', [WorkOrderController::class, 'duplicate']);
    Route::post('/work-orders/{id}/update-field', [WorkOrderController::class, 'updateField'])->name('work-orders.update-field');
    Route::post('/work-orders/{id}/update-images', [WorkOrderController::class, 'updateImages'])->name('work-orders.update-images');
    
    // Notes
    Route::post('/work-orders/{workOrder}/notes', [WorkOrderController::class, 'addNote']);
    Route::get('/work-orders/{workOrderId}/notes', [\App\Http\Controllers\NoteController::class, 'index'])->name('work-order.notes.index');
    Route::post('/work-orders/{workOrderId}/notes', [\App\Http\Controllers\NoteController::class, 'store'])->name('work-order.notes.store');
    
    // Calendar
    Route::get('/calendar-data', [WorkOrderController::class, 'calendarEvents'])->name('calendar.data');
    Route::get('/calendar-events', [WorkOrderController::class, 'calendarEvents'])->name('calendar.events');
    
    // Users list
    Route::get('/users-list', function () {
        return response()->json(App\Models\User::select('id', 'name', 'email')->get());
    });
    
    // Work order details for calendar
    Route::get('/work-orders/{id}/details', [WorkOrderController::class, 'getDetails'])->name('work-orders.details');
    
    // Dashboard stats
    Route::get('/work-order-stats', [WorkOrderController::class, 'getStats'])->name('work-orders.stats');
    
    // Messenger
    Route::get('/messenger', function () {
        return Inertia::render('Messenger');
    })->name('messenger');
    
    // QuickBooks integration
    Route::get('/quickbooks/connect', [QuickBooksAuthController::class, 'connect'])->name('quickbooks.connect');
    Route::get('/quickbooks/callback', [QuickBooksAuthController::class, 'callback'])->name('quickbooks.callback');
    
    // Revenue stats
    Route::get('/revenue-stats', [\App\Http\Controllers\RevenueController::class, 'getStats']);
    
    // Add debugging routes
    Route::get('/debug/work-orders', function() {
        $workOrders = \App\Models\WorkOrder::all();
        $totalCount = $workOrders->count();
        $completedCount = \App\Models\WorkOrder::where('status', 'completed')->count();
        $totalRevenue = \App\Models\WorkOrder::where('status', 'completed')->sum('price');
        
        return [
            'totalWorkOrders' => $totalCount,
            'completedWorkOrders' => $completedCount,
            'totalRevenue' => $totalRevenue,
            'sampleOrders' => \App\Models\WorkOrder::take(5)->get(['id', 'title', 'status', 'price', 'created_at', 'updated_at']),
            'dbColumns' => \Illuminate\Support\Facades\Schema::getColumnListing('work_orders'),
            'availableStatuses' => \App\Models\WorkOrder::distinct()->pluck('status'),
        ];
    });
});