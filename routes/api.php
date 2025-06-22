<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GroqController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WorkOrderController;
use App\Http\Controllers\CustomerController;
use App\Models\WorkOrder;
use Carbon\Carbon;
use App\Http\Controllers\DocumentController; // Add this import
use App\Http\Controllers\PdfController;

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

// Public route for uploading signed documents - no need for auth:sanctum middleware
Route::post('/documents/upload-signed', [DocumentController::class, 'uploadSignedDocument']);

// Make this route completely public with no auth requirements
Route::post('/public/documents/upload-signed', [DocumentController::class, 'uploadSignedDocument']);

Route::prefix('public')->group(function () {
    Route::post('/documents/upload-signed', [DocumentController::class, 'uploadSigned']);
});

// User routes
Route::get('/user', function (Request $request) {
    return $request->user();
});

// Customer routes (public for testing, remove in production)
Route::get('/customers', [CustomerController::class, 'index'])->name('api.customers.index');
Route::get('/customers/check-name', [CustomerController::class, 'checkBusinessNameExists'])->name('api.customers.check-name');
Route::post('/customers', [CustomerController::class, 'store'])->name('api.customers.store');
Route::get('/customers/search', [CustomerController::class, 'search'])->name('api.customers.search');
Route::get('/customers/{customer}', [CustomerController::class, 'show'])->name('api.customers.show');
Route::put('/customers/{customer}', [CustomerController::class, 'update'])->name('api.customers.update');
Route::delete('/customers/{customer}', [CustomerController::class, 'destroy'])->name('api.customers.destroy');

// Work order routes (public for testing, remove in production)
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
Route::get('/work-orders/{workOrderId}/notes', [\App\Http\Controllers\NoteController::class, 'index'])->name('work-order.notes.index');
Route::post('/work-orders/{workOrderId}/notes', [\App\Http\Controllers\NoteController::class, 'store'])->name('work-order.notes.store');
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
    // ...existing code for revenue stats...
    $now = Carbon::now();
    $weekStart = $now->copy()->startOfWeek();
    $weekEnd = $now->copy()->endOfWeek();
    $monthStart = $now->copy()->startOfMonth();
    $monthEnd = $now->copy()->endOfMonth();
    $lastWeekStart = $weekStart->copy()->subWeek();
    $lastWeekEnd = $weekEnd->copy()->subWeek();
    $lastMonthStart = $monthStart->copy()->subMonth();
    $lastMonthEnd = $monthEnd->copy()->subMonth();
    $weeklyWorkOrders = WorkOrder::whereBetween('date_time', [$weekStart, $weekEnd])->get();
    $weeklyRevenue = $weeklyWorkOrders->sum('grand_total');
    $weeklyCompleted = $weeklyWorkOrders->where('status', 'Complete')->count();
    $weeklyCompletionRate = $weeklyWorkOrders->count() > 0 
                           ? round(($weeklyCompleted / $weeklyWorkOrders->count()) * 100) 
                           : 0;
    $weeklyAverageRevenue = $weeklyWorkOrders->count() > 0 
                         ? $weeklyRevenue / $weeklyWorkOrders->count() 
                         : 0;
    $lastWeekWorkOrders = WorkOrder::whereBetween('date_time', [$lastWeekStart, $lastWeekEnd])->get();
    $lastWeekRevenue = $lastWeekWorkOrders->sum('grand_total');
    $weeklyChangePercent = $lastWeekRevenue > 0 
                          ? round((($weeklyRevenue - $lastWeekRevenue) / $lastWeekRevenue) * 100, 1) . '%' 
                          : '0%';
    $monthlyWorkOrders = WorkOrder::whereBetween('date_time', [$monthStart, $monthEnd])->get();
    $monthlyRevenue = $monthlyWorkOrders->sum('grand_total');
    $monthlyCompleted = $monthlyWorkOrders->where('status', 'Complete')->count();
    $monthlyCompletionRate = $monthlyWorkOrders->count() > 0 
                            ? round(($monthlyCompleted / $monthlyWorkOrders->count()) * 100) 
                            : 0;
    $monthlyAverageRevenue = $monthlyWorkOrders->count() > 0 
                          ? $monthlyRevenue / $monthlyWorkOrders->count() 
                          : 0;
    $lastMonthWorkOrders = WorkOrder::whereBetween('date_time', [$lastMonthStart, $lastMonthEnd])->get();
    $lastMonthRevenue = $lastMonthWorkOrders->sum('grand_total');
    $monthlyChangePercent = $lastMonthRevenue > 0 
                           ? round((($monthlyRevenue - $lastMonthRevenue) / $lastMonthRevenue) * 100, 1) . '%' 
                           : '0%';
    return [
        'weekly' => [
            'revenue' => $weeklyRevenue,
            'workOrders' => $weeklyWorkOrders->count(),
            'completionRate' => $weeklyCompletionRate,
            'averageRevenue' => $weeklyAverageRevenue,
            'changePercent' => $weeklyChangePercent
        ],
        'monthly' => [
            'revenue' => $monthlyRevenue,
            'workOrders' => $monthlyWorkOrders->count(),
            'completionRate' => $monthlyCompletionRate,
            'averageRevenue' => $monthlyAverageRevenue,
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
Route::post('work-orders/{workOrder}/invoice', [WorkOrderController::class, 'createInvoice']);
Route::post('work-orders/{id}/invoice', [WorkOrderController::class, 'createInvoice']);

// Fix the document upload endpoint (remove duplicate /api)
Route::post('/documents/upload-signed', [DocumentController::class, 'uploadSignedDocument']);

// Quickbooks Routes (if needed)
Route::get('/quickbooks/connect', [QuickBooksAuthController::class, 'connect'])->name('quickbooks.connect');
Route::get('/quickbooks/callback', [QuickBooksAuthController::class, 'callback'])->name('quickbooks.callback');
Route::get('/quickbooks/disconnect', [QuickBooksAuthController::class, 'disconnect'])->name('quickbooks.disconnect');
Route::get('/quickbooks/refresh', [QuickBooksAuthController::class, 'refresh'])->name('quickbooks.refresh');

// Add POST route for creating invoices
Route::post('/quickbooks/invoices', [QuickBooksController::class, 'createInvoice'])->name('quickbooks.create-invoice');
// Existing GET route for invoices
Route::get('/quickbooks/invoices', [QuickBooksController::class, 'getInvoices'])->name('quickbooks.invoices');

Route::get('/quickbooks/invoice/{id}', [QuickBooksController::class, 'getInvoice'])->name('quickbooks.invoice');
Route::get('/quickbooks/disconnect', [QuickBooksAuthController::class, 'disconnect'])->name('quickbooks.disconnect');
Route::get('/quickbooks/refresh', [QuickBooksAuthController::class, 'refresh'])->name('quickbooks.refresh');
Route::get('/quickbooks/invoices', [QuickBooksController::class, 'getInvoices'])->name('quickbooks.invoices');
Route::get('/quickbooks/invoice/{id}', [QuickBooksController::class, 'getInvoice'])->name('quickbooks.invoice');
Route::get('/quickbooks/customers', [QuickBooksController::class, 'getCustomers'])->name('quickbooks.customers');
Route::get('/quickbooks/customer/{id}', [QuickBooksController::class, 'getCustomer'])->name('quickbooks.customer');
Route::get('/quickbooks/items', [QuickBooksController::class, 'getItems'])->name('quickbooks.items');
Route::get('/quickbooks/item/{id}', [QuickBooksController::class, 'getItem'])->name('quickbooks.item');
Route::get('/quickbooks/estimate/{id}', [QuickBooksController::class, 'getEstimate'])->name('quickbooks.estimate');
Route::get('/quickbooks/estimates', [QuickBooksController::class, 'getEstimates'])->name('quickbooks.estimates');
Route::get('/quickbooks/estimate/{id}/pdf', [QuickBooksController::class, 'getEstimatePdf'])->name('quickbooks.estimate.pdf');
Route::get('/quickbooks/invoice/{id}/pdf', [QuickBooksController::class, 'getInvoicePdf'])->name('quickbooks.invoice.pdf');
Route::get('/quickbooks/invoice/{id}/send', [QuickBooksController::class, 'sendInvoice'])->name('quickbooks.invoice.send');
Route::get('/quickbooks/invoice/{id}/payment', [QuickBooksController::class, 'createPayment'])->name('quickbooks.invoice.payment');
Route::get('/quickbooks/invoice/{id}/payments', [QuickBooksController::class, 'getPayments'])->name('quickbooks.invoice.payments');
Route::get('/quickbooks/invoice/{id}/payment/{paymentId}', [QuickBooksController::class, 'getPayment'])->name('quickbooks.invoice.payment');
Route::get('/quickbooks/invoice/{id}/payment/{paymentId}/pdf', [QuickBooksController::class, 'getPaymentPdf'])->name('quickbooks.invoice.payment.pdf');
Route::get('/quickbooks/invoice/{id}/payment/{paymentId}/send', [QuickBooksController::class, 'sendPayment'])->name('quickbooks.invoice.payment.send');
Route::get('/quickbooks/invoice/{id}/payment/{paymentId}/refund', [QuickBooksController::class, 'refundPayment'])->name('quickbooks.invoice.payment.refund');
Route::get('/quickbooks/invoice/{id}/payment/{paymentId}/refund/pdf', [QuickBooksController::class, 'getRefundPdf'])->name('quickbooks.invoice.payment.refund.pdf');


// uplaodSignedDocument
Route::post('/upload-signed-document', [WorkOrderController::class, 'uploadSignedDocument']);

// Check if work order exists
Route::get('/check-work-order-exists', [WorkOrderController::class, 'checkWorkOrderExists']);

// Add the missing route for creating invoices
Route::post('/work-orders/{id}/create-invoice', [WorkOrderController::class, 'createInvoice']);

// Add the route for the PDF upload endpoint
Route::post('/documents/upload', [PdfController::class, 'upload']);

// Customer routes

// Route::middleware('auth:sanctum')->group(function () {
//     Route::get('/customers', [CustomerController::class, 'index']);
//     Route::get('/customers/{id}', [CustomerController::class, 'show']);
//     Route::post('/customers', [CustomerController::class, 'store']);
//     Route::put('/customers/{id}', [CustomerController::class, 'update']);
//     Route::delete('/customers/{id}', [CustomerController::class, 'destroy']);
// });

// Add the API route for getting all technicians (for index page)
Route::get('/technicians', [App\Http\Controllers\TechnicianController::class, 'index'])->name('api.technicians.index');

// Add the API route for getting active technicians
Route::get('/technicians/active', [App\Http\Controllers\TechnicianController::class, 'getActiveTechnicians'])->name('api.technicians.active');

// Add the API route for creating a technician
Route::post('/technicians', [App\Http\Controllers\TechnicianController::class, 'store'])->name('api.technicians.store');

// Add route for getting work order activities
Route::get('/work-orders/{id}/activities', [WorkOrderController::class, 'getActivities']);
