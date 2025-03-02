<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GroqController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WorkOrderController;
use App\Models\WorkOrder;
use Carbon\Carbon;

// Public routes
Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'timestamp' => now()->toIso8601String(),
        'server_time' => now()->format('Y-m-d H:i:s'),
        'environment' => config('app.env'),
        'php_version' => PHP_VERSION
    ]);
});

Route::middleware('auth:sanctum')->group(function () {
    // User routes
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    
    Route::get('/users', function () {
        return App\Models\User::select('id', 'name', 'email')->get();
    });
    
    // Work order routes
    Route::get('/work-orders', function () {
        return WorkOrder::select('id', 'title', 'description', 'date_time as start', 'status', 'user_id', 'customer_id')
                   ->orderBy('date_time', 'asc')
                   ->get();
    });
    
    // Work order controller routes
    Route::get('/work-orders/{id}', [WorkOrderController::class, 'getWorkOrder']);
    Route::post('/work-orders', [WorkOrderController::class, 'createWorkOrder']);
    Route::put('/work-orders/{id}', [WorkOrderController::class, 'updateWorkOrder']);
    Route::delete('/work-orders/{id}', [WorkOrderController::class, 'deleteWorkOrder']);
    
    // Work order field updates
    Route::post('/work-orders/{workOrder}/update-field', [WorkOrderController::class, 'updateField']);
    Route::post('/work-orders/{workOrder}/update-images', [WorkOrderController::class, 'updateImages']);
    Route::post('/work-orders/{workOrder}/duplicate', [WorkOrderController::class, 'duplicate']);
    
    // Notes and attachments
    Route::get('/work-orders/{id}/notes', [WorkOrderController::class, 'getNotes']);
    Route::post('/work-orders/{id}/notes', [WorkOrderController::class, 'addNote']);
    Route::get('/work-orders/{id}/images', [WorkOrderController::class, 'getImages']);
    Route::post('/work-orders/{id}/images', [WorkOrderController::class, 'addImage']);
    Route::delete('/work-orders/{id}/images/{imageId}', [WorkOrderController::class, 'deleteImage']);
    Route::get('/work-orders/{id}/file-attachments', [WorkOrderController::class, 'getFileAttachments']);
    Route::post('/work-orders/{id}/file-attachments', [WorkOrderController::class, 'addFileAttachment']);
    Route::delete('/work-orders/{id}/file-attachments/{fileAttachmentId}', [WorkOrderController::class, 'deleteFileAttachment']);
    
    // Search and details
    Route::get('/search-work-orders', [WorkOrderController::class, 'search']);
    Route::get('/work-orders/{id}/details', [WorkOrderController::class, 'getDetails']);
    
    // Calendar and stats
    Route::get('/work-orders-calendar', [WorkOrderController::class, 'getWorkOrdersForCalendar']);
    
    // Revenue statistics
    Route::get('/revenue-stats', function (Request $request) {
        // Get current date ranges
        $now = Carbon::now();
        $weekStart = $now->copy()->startOfWeek();
        $weekEnd = $now->copy()->endOfWeek();
        $monthStart = $now->copy()->startOfMonth();
        $monthEnd = $now->copy()->endOfMonth();
        
        // Previous periods for comparison
        $lastWeekStart = $weekStart->copy()->subWeek();
        $lastWeekEnd = $weekEnd->copy()->subWeek();
        $lastMonthStart = $monthStart->copy()->subMonth();
        $lastMonthEnd = $monthEnd->copy()->subMonth();
        
        // Current week statistics
        $weeklyWorkOrders = WorkOrder::whereBetween('date_time', [$weekStart, $weekEnd])->get();
        $weeklyRevenue = $weeklyWorkOrders->sum('price');
        $weeklyCompleted = $weeklyWorkOrders->where('status', 'Complete')->count();
        $weeklyCompletionRate = $weeklyWorkOrders->count() > 0 
                               ? round(($weeklyCompleted / $weeklyWorkOrders->count()) * 100) 
                               : 0;
        $weeklyAveragePrice = $weeklyWorkOrders->count() > 0 
                             ? $weeklyRevenue / $weeklyWorkOrders->count() 
                             : 0;
        
        // Previous week for comparison
        $lastWeekWorkOrders = WorkOrder::whereBetween('date_time', [$lastWeekStart, $lastWeekEnd])->get();
        $lastWeekRevenue = $lastWeekWorkOrders->sum('price');
        $weeklyChangePercent = $lastWeekRevenue > 0 
                              ? round((($weeklyRevenue - $lastWeekRevenue) / $lastWeekRevenue) * 100, 1) . '%' 
                              : '0%';
        
        // Current month statistics
        $monthlyWorkOrders = WorkOrder::whereBetween('date_time', [$monthStart, $monthEnd])->get();
        $monthlyRevenue = $monthlyWorkOrders->sum('price');
        $monthlyCompleted = $monthlyWorkOrders->where('status', 'Complete')->count();
        $monthlyCompletionRate = $monthlyWorkOrders->count() > 0 
                                ? round(($monthlyCompleted / $monthlyWorkOrders->count()) * 100) 
                                : 0;
        $monthlyAveragePrice = $monthlyWorkOrders->count() > 0 
                              ? $monthlyRevenue / $monthlyWorkOrders->count() 
                              : 0;
        
        // Previous month for comparison
        $lastMonthWorkOrders = WorkOrder::whereBetween('date_time', [$lastMonthStart, $lastMonthEnd])->get();
        $lastMonthRevenue = $lastMonthWorkOrders->sum('price');
        $monthlyChangePercent = $lastMonthRevenue > 0 
                               ? round((($monthlyRevenue - $lastMonthRevenue) / $lastMonthRevenue) * 100, 1) . '%' 
                               : '0%';
        
        return [
            'weekly' => [
                'revenue' => $weeklyRevenue,
                'workOrders' => $weeklyWorkOrders->count(),
                'completionRate' => $weeklyCompletionRate,
                'averagePrice' => $weeklyAveragePrice,
                'changePercent' => $weeklyChangePercent
            ],
            'monthly' => [
                'revenue' => $monthlyRevenue,
                'workOrders' => $monthlyWorkOrders->count(),
                'completionRate' => $monthlyCompletionRate,
                'averagePrice' => $monthlyAveragePrice,
                'changePercent' => $monthlyChangePercent
            ]
        ];
    });
    
    // Additional API routes
    Route::post('/api/groq/query', [GroqController::class, 'query']);
    Route::post('groq/query', [GroqController::class, 'query'])->name('groq.query');
    
    // Updated routes
    Route::post('work-orders/{id}/duplicate', [WorkOrderController::class, 'duplicate']);
    Route::post('work-orders/{id}/update-field', [WorkOrderController::class, 'updateField']);
    Route::put('work-orders/{id}', [WorkOrderController::class, 'update']);
    Route::post('/work-orders/{workOrder}/invoice', [WorkOrderController::class, 'invoice'])
        ->name('work-orders.invoice');
    Route::post('work-orders/{workOrder}/invoice', [WorkOrderController::class, 'createInvoice'])->name('api.work-orders.create-invoice');
    
    // Archived work orders
    Route::get('/work-orders/archived', [WorkOrderController::class, 'getArchived']);
    
    Route::middleware(['auth:sanctum'])->group(function () {
        Route::post('work-orders/{workOrder}/invoice', [WorkOrderController::class, 'createInvoice']);
    });
});

// Quickbooks Routes (if needed)
Route::get('/quickbooks/connect', [QuickBooksAuthController::class, 'connect'])->name('quickbooks.connect');
Route::get('/quickbooks/callback', [QuickBooksAuthController::class, 'callback'])->name('quickbooks.callback');