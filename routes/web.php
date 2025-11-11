<?php

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\CsrfController;
use App\Http\Controllers\GroqController;
use App\Http\Controllers\WorkOrderController;
// use App\Http\Controllers\QuickBooksAuthController; // Commented out temporarily
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\PDFController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;

// CSRF and session setup routes
Route::middleware(['web'])->group(function () {
    Route::get('/csrf/refresh', [CsrfController::class, 'refresh'])->name('csrf.refresh');
    Route::post('/csrf/refresh', [CsrfController::class, 'refresh'])->name('csrf.token');
});

// Public routes
Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
        'customers' => \App\Models\Customer::select(['id', 'business_name'])->orderBy('business_name')->get(),
        'users' => \App\Models\User::select(['id', 'name'])->get(),
    ]);
})->name('welcome');

// CSRF Token Routes
Route::get('/csrf/refresh', [CsrfController::class, 'refresh'])
    ->middleware(['web'])
    ->name('csrf.refresh');

// Groq diagnostic routes - no auth required for debugging
Route::get('/groq/diagnose', [App\Http\Controllers\GroqDiagnosticController::class, 'diagnose']);
Route::get('/groq/test-direct', [App\Http\Controllers\GroqDiagnosticController::class, 'testDirectAccessKey']);

// Protected routes
Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified'])->group(function () {
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');
    
    // Groq API
    Route::post('/groq/query', [GroqController::class, 'query'])->name('groq.query');
    Route::post('/api/groq/query', [GroqController::class, 'query']);
    
    // Customer Management
    Route::get('/customers', [App\Http\Controllers\CustomerController::class, 'index'])->name('customers.index');
    Route::get('/customers/{customer}', [App\Http\Controllers\CustomerController::class, 'show'])->name('customers.show');
    Route::post('/customers', [App\Http\Controllers\CustomerController::class, 'store'])->name('customers.store');
    Route::put('/customers/{customer}', [App\Http\Controllers\CustomerController::class, 'update'])->name('customers.update');
    Route::delete('/customers/{customer}', [App\Http\Controllers\CustomerController::class, 'destroy'])->name('customers.destroy');
    Route::get('/customers/{customer}/work-orders', [App\Http\Controllers\CustomerController::class, 'workOrders'])->name('customers.work-orders');
    Route::get('/customers/{customer}/work-orders/{workOrder}', [App\Http\Controllers\CustomerController::class, 'workOrderDetails'])->name('customers.work-order-details');
    Route::post('/customers/{customer}/upload-files', [App\Http\Controllers\CustomerController::class, 'uploadFiles'])->name('customers.upload-files');
    Route::delete('/customers/{customer}/delete-file', [App\Http\Controllers\CustomerController::class, 'deleteFile'])->name('customers.delete-file');
    Route::get('/customers/{customer}/files', [App\Http\Controllers\CustomerController::class, 'getFiles'])->name('customers.get-files');
    Route::post('/customers/{customer}/update-field', [App\Http\Controllers\CustomerController::class, 'updateField'])->name('customers.update-field');
    Route::post('/customers/{customer}/update-images', [App\Http\Controllers\CustomerController::class, 'updateImages'])->name('customers.update-images');
    Route::delete('/customers/{customer}/delete-attachment', [App\Http\Controllers\CustomerController::class, 'deleteAttachment'])->name('customers.delete-attachment');
   
    // Groq test routes
    Route::get('/groq/test', [App\Http\Controllers\GroqTestController::class, 'test']);
    Route::post('/groq/chat', [App\Http\Controllers\GroqTestController::class, 'chat']);
    
    // Work Orders (using resource route)
    Route::resource('work-orders', WorkOrderController::class);
    
    // Additional work order routes
    Route::put('/api/work-orders/{id}', [WorkOrderController::class, 'update']);
    Route::get('/api/work-orders', [WorkOrderController::class, 'index']);
    Route::post('/work-orders/{id}/duplicate', [WorkOrderController::class, 'duplicate']);
    Route::post('/work-orders/{id}/update-field', [WorkOrderController::class, 'updateField'])->name('work-orders.update-field');
    Route::post('/work-orders/{id}/update-all', [WorkOrderController::class, 'updateAll'])->name('work-orders.update-all');
    Route::post('/work-orders/{id}/update-images', [WorkOrderController::class, 'updateImages'])->name('work-orders.update-images');
    Route::post('/work-orders/{id}/update-total', [WorkOrderController::class, 'updateGrandTotal'])->name('work-orders.update-total');
    Route::delete('/work-orders/{id}/attachments', [WorkOrderController::class, 'deleteAttachment'])->name('work-orders.delete-attachment');
    Route::get('/work-orders/{id}/activities', [WorkOrderController::class, 'getActivities'])->name('work-orders.activities');
    
    // Notes
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
        $totalRevenue = \App\Models\WorkOrder::where('status', 'completed')->sum('grand_total');
        
        return [
            'totalWorkOrders' => $totalCount,
            'completedWorkOrders' => $completedCount,
            'totalRevenue' => $totalRevenue,
            'sampleOrders' => \App\Models\WorkOrder::take(5)->get(['id', 'title', 'status', 'grand_total', 'created_at', 'updated_at']),
            'dbColumns' => \Illuminate\Support\Facades\Schema::getColumnListing('work_orders'),
            'availableStatuses' => \App\Models\WorkOrder::distinct()->pluck('status'),
        ];
    });
    
    // Add this route if it doesn't exist
    Route::get('/work-order-stats', function () {
        // This is a simple example. Replace with your actual data fetching logic
        $completedCount = DB::table('work_orders')->where('status', 'Complete')->count();
        $pendingCount = DB::table('work_orders')->whereIn('status', ['Scheduled', 'In Progress', 'Part/Return'])->count();
        $totalRevenue = DB::table('work_orders')->where('status', 'Complete')->sum('grand_total');
        $avgRevenue = $completedCount > 0 
            ? number_format(DB::table('work_orders')->where('status', 'Complete')->avg('grand_total'), 2) 
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
            ->sum('grand_total');
        
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
                'name' => 'Average Revenue', 
                'value' => '$' . $avgRevenue, 
                'change' => '0%', // No comparison yet
                'changeType' => 'neutral'
            ],
        ];
    });

    // Work Order Search Routes - fixed controller references
        Route::get('/work-orders/{id}/details', [WorkOrderController::class, 'getDetails'])
        ->name('work-orders.details');

    // PDF viewer route for secure PDF loading
    Route::get('/pdf-viewer/{path}', [App\Http\Controllers\PdfController::class, 'show'])
        ->where('path', '.*') // Allow slashes in path
        ->name('pdf.show');

    Route::post('/work-orders/{id}/duplicate', [WorkOrderController::class, 'duplicate'])->name('work-orders.duplicate');
    Route::post('/work-orders/{id}/update-field', [WorkOrderController::class, 'updateField'])->name('work-orders.update-field');
    Route::post('/work-orders/{id}/update-images', [WorkOrderController::class, 'updateImages'])->name('work-orders.update-images');

    Route::get('/archived-work-orders', [WorkOrderController::class, 'archived'])->name('archived-work-orders');

    // Add this route with your other routes
    Route::get('/pdf-debug', function () {
        return view('pdf-debug');
    });

    // Add this route to your web routes
    Route::delete('/work-orders/{workOrder}/delete-attachment', [App\Http\Controllers\WorkOrderController::class, 'deleteAttachment'])
        ->middleware(['auth:sanctum', 'verified'])
        ->name('work-orders.delete-attachment');

    // Add this route to handle invoice creation
    Route::post('/work-orders/{workOrder}/invoice', [App\Http\Controllers\WorkOrderController::class, 'createInvoice'])
        ->middleware(['auth', 'verified'])
        ->name('work-orders.create-invoice');

    // Work Order invoice creation
    Route::post('/work-orders/{workOrder}/invoice', [App\Http\Controllers\WorkOrderController::class, 'createInvoice'])
        ->middleware(['auth', 'verified'])
        ->name('work-orders.invoice');

    // Add the document upload route as a web route (with session authentication)
    Route::post('/documents/upload-signed', [PdfController::class, 'uploadSigned'])
        ->middleware(['auth:sanctum', 'verified'])
        ->name('documents.upload.signed');

    Route::post('/work-orders/{workOrder}/attachments', [WorkOrderController::class, 'uploadAttachments'])
        ->name('work-orders.attachments');

    // Add the route for invoice creation if it doesn't already exist
    Route::post('/work-orders/{id}/invoice', [WorkOrderController::class, 'createInvoice'])
        ->middleware(['auth'])
        ->name('work-orders.create-invoice');

    // Add this special route for handling status updates, particularly for Part/Return
    Route::post('/work-orders/{workOrder}/simple-status-update', function (\App\Models\WorkOrder $workOrder, \Illuminate\Http\Request $request) {
        try {
            // Get the status from the request
            $status = $request->input('status');
            
            // Simple direct database update using query builder, which properly escapes the value
            $updated = \Illuminate\Support\Facades\DB::table('work_orders')
                ->where('id', $workOrder->id)
                ->update(['status' => $status]);
            
            if ($updated) {
                // Return a simple JSON response that doesn't need Inertia rendering
                return response()->json([
                    'success' => true,
                    'message' => 'Status updated successfully',
                    'status' => $status,
                    'workOrderId' => $workOrder->id
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Status not updated'
                ]);
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to update work order status: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error updating status',
                'error' => $e->getMessage()
            ], 500);
        }
    })->middleware(['auth']);

    Route::post('/work-orders/{workOrder}/archive', [WorkOrderController::class, 'archive'])
        ->name('work-orders.archive');

    Route::get('/work-orders/{workOrder}/show', [WorkOrderController::class, 'show'])->name('work-orders.show');

    Route::post('/work-orders/{workOrder}/update-hours', [WorkOrderController::class, 'updateHours'])->name('work-orders.update-hours');

    // Document upload routes
    Route::post('/api/documents/upload', [PdfController::class, 'upload'])
        ->name('documents.upload');
    Route::post('/documents/upload', [PdfController::class, 'upload'])
        ->name('documents.upload.web');
});

// Admin only routes
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::put('/work-orders/{workOrder}', [WorkOrderController::class, 'update'])->name('work-orders.update');
    Route::delete('/work-orders/{workOrder}', [WorkOrderController::class, 'destroy'])->name('work-orders.destroy');
    Route::post('/work-orders/{workOrder}/archive', [WorkOrderController::class, 'archive'])->name('work-orders.archive');
    Route::post('/work-orders/{workOrder}/invoice', [WorkOrderController::class, 'invoice'])->name('work-orders.invoice');
});

// Tech and Admin routes
Route::middleware(['auth'])->group(function () {
    Route::get('/work-orders', [WorkOrderController::class, 'index'])->name('work-orders.index');
    Route::post('/work-orders', [WorkOrderController::class, 'store'])->name('work-orders.store');
    Route::get('/work-orders/{workOrder}', [WorkOrderController::class, 'show'])->name('work-orders.show');
    Route::post('/work-orders/{workOrder}/status', [WorkOrderController::class, 'updateStatus'])->name('work-orders.update-status');
    Route::post('/work-orders/{workOrder}/notes', [WorkOrderController::class, 'addNotes'])->name('work-orders.add-notes');
    Route::post('/work-orders/{workOrder}/images', [WorkOrderController::class, 'uploadImages'])->name('work-orders.upload-images');
    Route::post('/work-orders/{workOrder}/signature', [WorkOrderController::class, 'getSignature'])->name('work-orders.get-signature');

    // Technician routes
    Route::get('/technicians', [App\Http\Controllers\TechnicianController::class, 'index'])
        ->name('technicians.index');
    Route::get('/technicians/{technician}', [App\Http\Controllers\TechnicianController::class, 'show'])
        ->name('technicians.show');
    Route::put('/technicians/{technician}', [App\Http\Controllers\TechnicianController::class, 'update'])
        ->name('technicians.update');
});

// Temporarily commenting out QuickBooks routes to fix route list issues
/*
// QuickBooks Integration Routes
Route::middleware(['auth:sanctum', 'verified'])->group(function () {
    Route::get('/quickbooks/authorize', [QuickBooksAuthController::class, 'authorize'])
        ->name('quickbooks.authorize');
    
    Route::post('/work-orders/{workOrder}/delete-attachment', [WorkOrderController::class, 'deleteAttachment'])
        ->name('work-orders.delete-attachment');
    
});

// QuickBooks Routes
Route::prefix('quickbooks')->group(function () {
    Route::get('/connect', 'QuickbooksController@connect')->name('quickbooks.connect');
    Route::get('/callback', 'QuickbooksController@callback')->name('quickbooks.callback');
    Route::get('/connection-status', 'QuickbooksController@connectionStatus')->name('quickbooks.status');
    Route::post('/create-invoice', 'QuickbooksController@createInvoice')->name('quickbooks.create-invoice');
});

// Work Order Routes
Route::post('/work-orders/{workOrder}/mark-invoiced', 'WorkOrderController@markInvoiced')->name('workOrders.markInvoiced');

// QuickBooks OAuth routes
Route::get('/quickbooks/authorize', [App\Http\Controllers\QuickBooksController::class, 'authorize'])
    ->middleware(['auth'])
    ->name('quickbooks.authorize');
    
Route::get('/quickbooks/callback', [QuickBooksAuthController::class, 'callback'])->name('quickbooks.callback');
*/

// Work Order Attachment Routes
Route::middleware(['auth:sanctum', 'verified'])->group(function () {
    Route::post('/work-orders/{workOrder}/delete-attachment', [WorkOrderController::class, 'deleteAttachment'])
        ->name('work-orders.delete-attachment');
});

// Add CSRF token refresh routes
Route::get('/csrf-token', function () {
    return response()->json(['csrf_token' => csrf_token()]);
})->middleware('web');

// Add the CsrfController route for refreshing tokens
Route::post('/csrf/refresh', [App\Http\Controllers\CsrfController::class, 'refresh'])
    ->middleware('web')
    ->name('csrf.refresh');

// Add this route to your existing routes
Route::get('/csrf-token', function () {
    return response()->json(['csrfToken' => csrf_token()]);
});

// Email verification routes
Route::get('/email/verify', function () {
    return view('auth.verify-email');
})->middleware('auth')->name('verification.notice');

Route::get('/email/verify/{id}/{hash}', [App\Http\Controllers\Auth\EmailVerificationController::class, 'verify'])
    ->middleware(['auth', 'signed'])
    ->name('verification.verify');

Route::post('/email/verification-notification', function (Request $request) {
    $request->user()->sendEmailVerificationNotification();

    return back()->with('status', 'verification-link-sent');
})->middleware(['auth', 'throttle:6,1'])->name('verification.send');

// Debug login route
Route::get('/debug/login', function () {
    return Inertia::render('Auth/DebugLogin');
})->middleware('web');

// Serve storage files with CORS headers
Route::middleware(['storage.cors'])->group(function () {
    Route::get('/storage/{path}', function ($path) {
        $fullPath = storage_path('app/public/' . $path);
        if (!file_exists($fullPath)) {
            abort(404);
        }
        return response()->file($fullPath);
    })->where('path', '.*');
});

// Logout Routes
Route::post('/logout', function (Request $request) {
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return redirect('/');
})->name('logout');

// Fallback for GET logout requests to prevent the MethodNotAllowedHttpException
Route::get('/logout', function () {
    return redirect()->route('logout')->with('error', 'Please use the logout button to log out securely.');
});