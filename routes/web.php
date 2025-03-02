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
Route::middleware(['auth:sanctum', 'verified'])->group(function () {
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');
    
    // Groq API
    Route::post('/groq/query', [GroqController::class, 'query'])->name('groq.query');
    Route::post('/api/groq/query', [GroqController::class, 'query']);
    
    // Work Orders (using resource route)
    Route::resource('work-orders', WorkOrderController::class);
    
    // Additional work order routes
    Route::put('/api/work-orders/{id}', [WorkOrderController::class, 'update']);

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
    
    // Add this route if it doesn't exist
    Route::get('/work-order-stats', function () {
        // This is a simple example. Replace with your actual data fetching logic
        $completedCount = DB::table('work_orders')->where('status', 'Complete')->count();
        $pendingCount = DB::table('work_orders')->whereIn('status', ['Scheduled', 'In Progress', 'Part/Return'])->count();
        $totalRevenue = DB::table('work_orders')->where('status', 'Complete')->sum('price');
        $avgPrice = $completedCount > 0 
            ? number_format(DB::table('work_orders')->where('status', 'Complete')->avg('price'), 2) 
            : 0;
        
        // Get last month's data for comparison
        $lastMonth = now()->subMonth();
        $lastMonthCompletedCount = DB::table('work_orders')
            ->where('status', 'Complete')
            ->whereMonth('created_at', $lastMonth->month)
            ->whereYear('created_at', $lastMonth->year)
            ->count();
        
        $lastMonthRevenue = DB::table('work_orders')
            ->where('status', 'Complete')
            ->whereMonth('created_at', $lastMonth->month)
            ->whereYear('created_at', $lastMonth->year)
            ->sum('price');
        
        // Calculate changes
        $revenueChange = $lastMonthRevenue > 0 
            ? round((($totalRevenue - $lastMonthRevenue) / $lastMonthRevenue) * 100) 
            : 0;
        $completedChange = $lastMonthCompletedCount > 0 
            ? round((($completedCount - $lastMonthCompletedCount) / $lastMonthCompletedCount) * 100) 
            : 0;
        
        return [
            [
                'name' => 'Total Revenue', 
                'value' => '$' . number_format($totalRevenue, 2), 
                'change' => $revenueChange . '%', 
                'changeType' => $revenueChange > 0 ? 'increase' : ($revenueChange < 0 ? 'decrease' : 'neutral')
            ],
            [
                'name' => 'Completed Orders', 
                'value' => $completedCount, 
                'change' => $completedChange . '%', 
                'changeType' => $completedChange > 0 ? 'increase' : ($completedChange < 0 ? 'decrease' : 'neutral')
            ],
            [
                'name' => 'Pending Orders', 
                'value' => $pendingCount, 
                'change' => '0%', // No comparison yet
                'changeType' => 'neutral'
            ],
            [
                'name' => 'Average Price', 
                'value' => '$' . $avgPrice, 
                'change' => '0%', // No comparison yet
                'changeType' => 'neutral'
            ],
        ];
    });

    // Work Order Search Routes - fixed controller references
    Route::get('/search-work-orders', [WorkOrderController::class, 'search'])
        ->name('search.work-orders');

    Route::get('/work-orders/{id}/details', [WorkOrderController::class, 'details'])
        ->name('work-orders.details');

    // PDF viewer route for secure PDF loading
    Route::get('/pdf-viewer/{path}', [App\Http\Controllers\PdfController::class, 'show'])
        ->where('path', '.*') // Allow slashes in path
        ->name('pdf.show');

    Route::post('/work-orders/{id}/duplicate', [WorkOrderController::class, 'duplicate'])->name('work-orders.duplicate');
    Route::post('/work-orders/{id}/update-field', [WorkOrderController::class, 'updateField'])->name('work-orders.update-field');
});

// QuickBooks Integration Routes
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/quickbooks/connect', [App\Http\Controllers\QuickBooksController::class, 'connect'])->name('quickbooks.connect');
    Route::get('/quickbooks/callback', [App\Http\Controllers\QuickBooksController::class, 'callback'])->name('quickbooks.callback');
    Route::get('/quickbooks/disconnect', [App\Http\Controllers\QuickBooksController::class, 'disconnect'])->name('quickbooks.disconnect');
    Route::post('/work-orders/{id}/duplicate', [WorkOrderController::class, 'duplicate'])->name('work-orders.duplicate');
});

// QuickBooks OAuth routes
Route::get('/quickbooks/authorize', [App\Http\Controllers\QuickBooksController::class, 'authorize'])
    ->middleware(['auth'])
    ->name('quickbooks.authorize');
    
Route::get('/quickbooks/callback', [App\Http\Controllers\QuickBooksController::class, 'callback'])
    ->middleware(['auth'])
    ->name('quickbooks.callback');

// Add a CSRF token refresh route
Route::get('/csrf-token', function () {
    return response()->json(['csrf_token' => csrf_token()]);
})->middleware('web');