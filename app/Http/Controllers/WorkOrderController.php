<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\WorkOrder;
use App\Models\User;
use Inertia\Inertia;
use App\Models\Note;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class WorkOrderController extends Controller
{
    // Display a listing of the resource
    public function index()
    {
        return Inertia::render('WorkOrders/Index', [
            'workOrders' => WorkOrder::latest()->paginate(10)
        ]);
    }

    public function archived()
    {
        $archivedOrders = WorkOrder::where('archived', true)
            ->orderBy('archived_at', 'desc')
            ->get();
        
        return response()->json($archivedOrders);
    }

    // Show the form for creating a new resource
    public function create()
    {
        return Inertia::render('WorkOrders/Create');
    }

    // Store a newly created resource in storage
    public function store(Request $request)
    {
        try {
            // Validate request
            $validated = $request->validate([
                'customer_id' => 'required|string',
                'title' => 'required|string',
                'description' => 'required|string',
                'date_time' => 'required|date',
                'end_date' => 'nullable|date',
                'address' => 'required|string',
                'hours' => 'required|numeric|min:0',
                'price' => 'required|numeric|min:0',
                'status' => 'required|string|in:Scheduled,In Progress,Part/Return,Complete,Cancelled',
                'user_id' => 'required|exists:users,id',
            ]);

            // Create the work order
            $workOrder = WorkOrder::create($validated);

            return response()->json(['success' => true, 'message' => 'Work order created successfully.', 'workOrder' => $workOrder], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Log validation errors for debugging
            \Log::error('Validation failed for work order creation:', $e->errors());

            return response()->json(['success' => false, 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            \Log::error('Error creating work order:', $e->getMessage());

            return response()->json(['success' => false, 'message' => 'An error occurred while creating the work order.'], 500);
        }
    }

    // Display the specified resource
// app/Http/Controllers/WorkOrderController.php
// Make sure work orders are loaded with their notes when shown

public function show($id)
{
    $workOrder = WorkOrder::with(['notes.user'])->findOrFail($id);
    $users = User::all();
    
    return Inertia::render('WorkOrders/Show', [
        'workOrder' => $workOrder,
        'users' => $users,
    ]);
}

/**
 * Get complete details of a work order for the modal
 */
public function getDetails($id)
{
    try {
        // Try to find the work order
        $workOrder = WorkOrder::find($id);
        
        // If not found, return a 404 with a clear message
        if (!$workOrder) {
            return response()->json([
                'error' => 'Work order not found',
                'message' => "No work order exists with ID {$id}"
            ], 404);
        }
        
        // Load relationships safely
        $workOrder->load(['user:id,name,email']);
        
        // Format dates if needed
        if ($workOrder->date_time) {
            $workOrder->formatted_date = \Carbon\Carbon::parse($workOrder->date_time)->format('Y-m-d\TH:i');
        }
        
        return response()->json($workOrder);
    } catch (\Exception $e) {
        \Log::error('Error retrieving work order: ' . $e->getMessage());
        return response()->json(['error' => $e->getMessage()], 500);
    }
}


    // Show the form for editing the specified resource
    public function edit($id)
    {
        $workOrder = WorkOrder::findOrFail($id);
        return view('work_orders.edit', compact('workOrder'));
    }

    // Update the specified resource in storage
    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'user_id' => 'nullable|exists:users,id',
            'customer_id' => 'required|string|max:255',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'date_time' => 'required|date',
            'price' => 'required|numeric',
            'status' => 'required|string|in:Scheduled,In Progress,Part Needed,Complete,Cancelled',
            'file_attachments.*' => 'nullable|file|mimes:pdf,jpg|max:2048',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        $workOrder = WorkOrder::findOrFail($id);
        $workOrder->user_id = $validatedData['user_id'];
        $workOrder->customer_id = $validatedData['customer_id'];
        $workOrder->title = $validatedData['title'];
        $workOrder->description = $validatedData['description'];
        $workOrder->date_time = $validatedData['date_time'];
        $workOrder->price = $validatedData['price'];
        $workOrder->status = $validatedData['status'];

        if ($request->hasFile('file_attachments')) {
            $fileAttachments = [];
            foreach ($request->file('file_attachments') as $file) {
                $path = $file->store('work_orders', 'public');
                $fileAttachments[] = $path;
            }
            $workOrder->file_attachments = json_encode($fileAttachments);
        }

        if ($request->hasFile('images')) {
            $images = [];
            foreach ($request->file('images') as $image) {
                $path = $image->store('work_orders', 'public');
                $images[] = $path;
            }
            $workOrder->images = json_encode($images);
        }

        $workOrder->save();

        $userName = auth()->user()->name;
        return redirect()->route('dashboard')->with('message', "Work order updated successfully by $userName");
    }

    // Remove the specified resource from storage
    public function destroy($id)
{
    try {
        $workOrder = WorkOrder::findOrFail($id);
        
        // Optional: Add authorization check
        // if (auth()->id() !== $workOrder->user_id) {
        //     return response()->json(['error' => 'Unauthorized'], 403);
        // }
        
        // Delete associated records if needed
        // For example, if work orders have notes:
        if (method_exists($workOrder, 'notes')) {
            $workOrder->notes()->delete();
        }
        
        // Delete the work order
        $workOrder->delete();
        
        return response()->json(['success' => true]);
    } catch (\Exception $e) {
        \Log::error('Error deleting work order: ' . $e->getMessage());
        return response()->json(['error' => $e->getMessage()], 500);
    }
}

    // Duplicate the specified resource
    /**
     * Duplicate the specified work order
     * 
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function duplicate($id)
    {
        try {
            $workOrder = WorkOrder::findOrFail($id);
            $newWorkOrder = $workOrder->replicate();
            $newWorkOrder->user_id = auth()->id();
            
            // Fix the pattern to search for duplicates
            $baseTitle = preg_replace('/ -\d+ \(Return\)$/', '', $workOrder->title);
            $latestDuplicate = WorkOrder::where('title', 'like', $baseTitle . ' -%')->orderBy('id', 'desc')->first();
            
            if ($latestDuplicate) {
                // Extract the number from the latest duplicate
                preg_match('/ -(\d+) \(Return\)$/', $latestDuplicate->title, $matches);
                $copyNumber = isset($matches[1]) ? (int)$matches[1] + 1 : 2;
            } else {
                $copyNumber = 2;
            }
            
            $newWorkOrder->title = $baseTitle . ' -' . str_pad($copyNumber, 2, '0', STR_PAD_LEFT) . ' (ReturnTrip)';

            // Handle file attachments
            if ($workOrder->file_attachments) {
                $fileAttachments = json_decode($workOrder->file_attachments, true);
                $newFileAttachments = [];
                foreach ($fileAttachments as $file) {
                    $newPath = 'work_orders/' . basename($file);
                    \Storage::disk('public')->copy($file, $newPath);
                    $newFileAttachments[] = $newPath;
                }
                $newWorkOrder->file_attachments = json_encode($newFileAttachments);
            }
            
            // Copy images if they exist
            if ($workOrder->images) {
                $images = json_decode($workOrder->images, true);
                $newImages = [];
                foreach ($images as $image) {
                    $newPath = 'work_orders/' . basename($image);
                    \Storage::disk('public')->copy($image, $newPath);
                    $newImages[] = $newPath;
                }
                $newWorkOrder->images = json_encode($newImages);
            }

            $newWorkOrder->save();

            // Modified to return a JSON response for API use
            return response()->json([
                'success' => true,
                'message' => 'Work order duplicated successfully',
                'workOrder' => $newWorkOrder,
                'redirect' => route('dashboard')
            ]);
        } catch (\Exception $e) {
            // Log the error
            Log::error('Error duplicating work order: ' . $e->getMessage());
            
            // Return error response
            return response()->json([
                'success' => false,
                'message' => 'Failed to duplicate work order',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function addNote(Request $request, $workOrderId)
{
    $request->validate([
        'text' => 'required|string',
    ]);

    $workOrder = WorkOrder::findOrFail($workOrderId);
    $user = auth()->user();

    $note = new Note([
        'text' => $request->input('text'),
        'user_id' => $user->id,
        'work_order_id' => $workOrderId
    ]);

    $note->save();

    // Return note with user info for the frontend
    return response()->json([
        'id' => $note->id,
        'text' => $note->text,
        'user_id' => $user->id,
        'user_name' => $user->name,
        'created_at' => $note->created_at,
    ], 201);
}

// Removed duplicate getWorkOrdersForCalendar method

public function getWorkOrdersForDashboard()
{
    $workOrders = WorkOrder::with('user')->orderBy('date_time', 'desc')->get();
    
    return response()->json($workOrders);
    
}

public function getWorkOrder($id)
{
    $workOrder = WorkOrder::with('user')->findOrFail($id);
    
    return response()->json($workOrder);
}

public function updateStatus(Request $request, $id)
{
    $validatedData = $request->validate([
        'status' => 'required|string|in:Scheduled,In Progress,Part/Return,Complete,Cancelled',
    ]);

    $workOrder = WorkOrder::findOrFail($id);
    $workOrder->status = $validatedData['status'];
    $workOrder->save();

    return response()->json(['message' => 'Work order status updated successfully.']);
}

public function getWorkOrdersForCalendar()
{
    $workOrders = WorkOrder::select(
        'id', 
        'title', 
        'description', 
        'date_time as start', 
        'status', 
        'user_id', 
        'customer_id'
    )->get();
    
    // Get users for resources 
    $users = User::select('id', 'name')->get()->map(function ($user) {
        return [
            'id' => 'user-' . $user->id,
            'title' => $user->name
        ];
    });
    
    // Assign each work order to its user as a resource
    $workOrders = $workOrders->map(function ($workOrder) {
        $data = $workOrder->toArray();
        $data['resourceId'] = 'user-' . $workOrder->user_id;
        return $data;
    });
    
    return response()->json([
        'events' => $workOrders,
        'resources' => $users
    ]);
}

/**
 * Update images and files for the work order
 */
public function updateImages(Request $request, $id)
{
    $request->validate([
        'files.*' => 'file|max:10240|mimes:jpeg,png,jpg,gif,pdf',  // Allow PDFs and increased max size to 10MB
    ]);
    
    $workOrder = WorkOrder::findOrFail($id);
    
    try {
        $newFiles = [];
        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                // Store in the appropriate directory based on file type
                $isPdf = $file->getClientMimeType() === 'application/pdf';
                $subDir = $isPdf ? 'documents' : 'images';
                
                // Store the file and get the path
                $path = $file->store("work_orders/{$subDir}", 'public');
                $newFiles[] = $path;
            }
        }
        
        // Check if the images column exists in the database
        $hasImagesColumn = \Schema::hasColumn('work_orders', 'images');
        
        if ($hasImagesColumn) {
            // Standard approach when images column exists
            // If work order already has files/images, merge them
            $existingFiles = $workOrder->images ? json_decode($workOrder->images, true) : [];
            if (!is_array($existingFiles)) {
                $existingFiles = [];
            }
            
            $allFiles = array_merge($existingFiles, $newFiles);
            $workOrder->images = json_encode($allFiles);
        } else {
            // Fallback - use file_attachments column if images doesn't exist
            $existingFiles = $workOrder->file_attachments ? json_decode($workOrder->file_attachments, true) : [];
            if (!is_array($existingFiles)) {
                $existingFiles = [];
            }
            
            $allFiles = array_merge($existingFiles, $newFiles);
            $workOrder->file_attachments = json_encode($allFiles);
            
            // Let the frontend know we used file_attachments instead
            \Log::info('Images column not found, using file_attachments instead.');
        }
        
        $workOrder->save();
        
        return response()->json([
            'success' => true,
            'message' => 'Files updated successfully',
            'images' => $allFiles
        ]);
    } catch (\Exception $e) {
        \Log::error('Error uploading files: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'error' => $e->getMessage()
        ], 500);
    }
}

public function updateField(Request $request, $id)
{
    $workOrder = WorkOrder::findOrFail($id);
    $field = key($request->all());
    $value = $request->input($field);
    
    // Validate field name to prevent mass assignment vulnerabilities
    $allowedFields = ['customer_id', 'user_id', 'title', 'description', 'date_time', 'status', 'price'];
    
    if (!in_array($field, $allowedFields)) {
        return response()->json([
            'success' => false,
            'error' => 'Invalid field name'
        ], 422);
    }
    
    try {
        $workOrder->{$field} = $value;
        $workOrder->save();
        
        return response()->json([
            'success' => true,
            'message' => 'Field updated successfully',
            'field' => $field,
            'value' => $value
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage()
        ], 500);
    }
}

public function calendarEvents()
    {
        try {
            $workOrders = WorkOrder::select('id', 'title', 'description', 'date_time as start', 'status', 'user_id', 'customer_id')
                ->orderBy('date_time', 'asc')
                ->get();
                
            return response()->json($workOrders);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getStats()
    {
        try {
            // Get current month's data
            $currentMonthOrders = WorkOrder::whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->get();
                
            // Get previous month's data for comparison
            $previousMonthOrders = WorkOrder::whereMonth('created_at', now()->subMonth()->month)
                ->whereYear('created_at', now()->subMonth()->year)
                ->get();
                
            // Calculate totals for current month - using archived instead of completed
            $currentTotalRevenue = $currentMonthOrders->where('archived', true)->sum('price');
            $currentArchivedCount = $currentMonthOrders->where('archived', true)->count();
            $currentPendingCount = $currentMonthOrders->where('archived', false)->count();
            $currentAvgPrice = $currentArchivedCount > 0 
                ? $currentTotalRevenue / $currentArchivedCount
                : 0;
                
            // Calculate totals for previous month - using archived instead of completed
            $previousTotalRevenue = $previousMonthOrders->where('archived', true)->sum('price');
            $previousArchivedCount = $previousMonthOrders->where('archived', true)->count();
            $previousPendingCount = $previousMonthOrders->where('archived', false)->count();
            $previousAvgPrice = $previousArchivedCount > 0 
                ? $previousTotalRevenue / $previousArchivedCount
                : 0;
                
            // Calculate percentage changes
            $revenueChange = $previousTotalRevenue > 0 
                ? (($currentTotalRevenue - $previousTotalRevenue) / $previousTotalRevenue) * 100 
                : 0;
                
            $archivedChange = $previousArchivedCount > 0 
                ? (($currentArchivedCount - $previousArchivedCount) / $previousArchivedCount) * 100 
                : 0;
                
            $pendingChange = $previousPendingCount > 0 
                ? (($currentPendingCount - $previousPendingCount) / $previousPendingCount) * 100 
                : 0;
                
            $avgPriceChange = $previousAvgPrice > 0 
                ? (($currentAvgPrice - $previousAvgPrice) / $previousAvgPrice) * 100 
                : 0;
                
            // Format the stats array with updated labels
            $stats = [
                [
                    'name' => 'Total Revenue (Archived)',
                    'value' => '$' . number_format($currentTotalRevenue, 2),
                    'change' => ($revenueChange >= 0 ? '+' : '') . number_format($revenueChange, 2) . '%',
                    'changeType' => $revenueChange >= 0 ? 'positive' : 'negative'
                ],
                [
                    'name' => 'Archived Orders',
                    'value' => $currentArchivedCount,
                    'change' => ($archivedChange >= 0 ? '+' : '') . number_format($archivedChange, 2) . '%',
                    'changeType' => $archivedChange >= 0 ? 'positive' : 'negative'
                ],
                [
                    'name' => 'Active Orders',
                    'value' => $currentPendingCount,
                    'change' => ($pendingChange >= 0 ? '+' : '') . number_format($pendingChange, 2) . '%',
                    'changeType' => $pendingChange <= 0 ? 'positive' : 'negative' // Less pending is positive
                ],
                [
                    'name' => 'Average Price (Archived)',
                    'value' => '$' . number_format($currentAvgPrice, 2),
                    'change' => ($avgPriceChange >= 0 ? '+' : '') . number_format($avgPriceChange, 2) . '%',
                    'changeType' => $avgPriceChange >= 0 ? 'positive' : 'negative'
                ]
            ];
            
            return response()->json($stats);
        } catch (\Exception $e) {
            \Log::error('Error calculating work order stats: ' . $e->getMessage());
            return response()->json([
                ['name' => 'Error', 'value' => 'Could not load stats', 'change' => '', 'changeType' => 'neutral']
            ], 500);
        }
    }

/**
 * Search for work orders based on the query
 *
 * @param  \Illuminate\Http\Request  $request
 * @return \Illuminate\Http\Response
 */
public function search(Request $request)
{
    try {
        $query = $request->input('query');
        
        if (empty($query) || strlen($query) < 2) {
            return response()->json([]);
        }

        // Check if our search columns exist in the database
        $workOrderColumns = Schema::getColumnListing('work_orders');
        
        // Start building the query
        $searchQuery = WorkOrder::query();
        
        // Add OR conditions for all searchable fields that exist in the database
        $searchQuery->where(function($q) use ($query, $workOrderColumns) {
            if (in_array('title', $workOrderColumns)) {
                $q->orWhere('title', 'like', "%{$query}%");
            }
            
            if (in_array('description', $workOrderColumns)) {
                $q->orWhere('description', 'like', "%{$query}%");
            }
            
            if (in_array('customer_id', $workOrderColumns)) {
                $q->orWhere('customer_id', 'like', "%{$query}%");
            }
            
            if (in_array('customer_name', $workOrderColumns)) {
                $q->orWhere('customer_name', 'like', "%{$query}%");
            }
            
            // Also match on direct ID if it's numeric
            if (is_numeric($query)) {
                $q->orWhere('id', $query);
            }
        });
        
        // Select only the fields we need for the search results list
        $selectFields = ['id'];
        foreach(['title', 'status', 'customer_id', 'customer_name', 'created_at'] as $field) {
            if (in_array($field, $workOrderColumns)) {
                $selectFields[] = $field;
            }
        }
        
        $workOrders = $searchQuery->select($selectFields)
            ->limit(10)
            ->get();
            
        // Add fallback values for fields that may not exist
        $workOrders = $workOrders->map(function($order) {
            // Make sure these key properties are defined even if null
            if (!isset($order->title)) $order->title = 'Work Order #' . $order->id;
            if (!isset($order->status)) $order->status = 'Unknown';
            if (!isset($order->customer_id) && !isset($order->customer_name)) {
                $order->customer_id = 'Unknown';
            }
            return $order;
        });
        
        return response()->json($workOrders);
        
    } catch (\Exception $e) {
        Log::error('Error in search method: ' . $e->getMessage());
        return response()->json([
            'error' => 'An error occurred while searching',
            'message' => config('app.debug') ? $e->getMessage() : 'Search failed. Please try again later.'
        ], 500);
    }
}

/**
 * Get details for a specific work order
 *
 * @param  int  $id
 * @return \Illuminate\Http\Response
 */
public function details($id)
{
    try {
        $workOrder = WorkOrder::findOrFail($id);
        
        // Make sure essential properties are present
        $responseData = $workOrder->toArray();
        
        // Add any necessary computed fields or formatting
        if (isset($responseData['created_at'])) {
            $responseData['formatted_date'] = date('Y-m-d H:i:s', strtotime($responseData['created_at']));
        }
        
        return response()->json($responseData);
        
    } catch (\Exception $e) {
        Log::error('Error in details method: ' . $e->getMessage());
        
        // If the work order doesn't exist
        if ($e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) {
            return response()->json([
                'error' => 'Work order not found',
                'message' => "No work order exists with ID {$id}"
            ], 404);
        }
        
        // For any other error
        return response()->json([
            'error' => 'An error occurred',
            'message' => config('app.debug') ? $e->getMessage() : 'Failed to retrieve work order details.'
        ], 500);
    }
}

/**
 * Delete a specific attachment from a work order
 *
 * @param Request $request
 * @param int $id
 * @return \Illuminate\Http\JsonResponse
 */

public function deleteAttachment(Request $request, WorkOrder $workOrder)
{
    try {
        if (!$request->user()->can('delete', $workOrder)) {
            return back()->with('error', 'You are not authorized to delete attachments.');
        }

        $attachmentPath = $request->input('attachment_path');
        
        if (!Storage::disk('public')->exists($attachmentPath)) {
            return back()->with('error', 'File not found.');
        }
        
        Storage::disk('public')->delete($attachmentPath);
        
        // Remove the attachment from both images and file_attachments arrays
        $images = is_array($workOrder->images) ? $workOrder->images : [];
        $fileAttachments = is_array($workOrder->file_attachments) ? $workOrder->file_attachments : [];
        
        $images = array_filter($images, fn($img) => $img !== $attachmentPath);
        $fileAttachments = array_filter($fileAttachments, fn($file) => $file !== $attachmentPath);
        
        $workOrder->images = $images;
        $workOrder->file_attachments = $fileAttachments;
        $workOrder->save();

        return back()->with('success', 'Attachment deleted successfully.');
        
    } catch (\Exception $e) {
        \Log::error('Error deleting attachment: ' . $e->getMessage());
        return back()->with('error', 'Failed to delete attachment.');
    }
}

public function uploadAttachments(Request $request, WorkOrder $workOrder)
{
    $request->validate([
        'attachments.*' => 'required|file|mimes:jpeg,jpg,png,gif,pdf,heic,docx|max:10240',
        // Make sure 'jpeg' is explicitly listed in mimes
    ]);
    
    // Add some debug logging
    \Log::info('File upload attempt', [
        'files' => $request->file('attachments'),
        'mimetypes' => array_map(function($file) {
            return $file->getMimeType();
        }, $request->file('attachments') ?? [])
    ]);

    try {
        $attachments = [];
        
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $filename = time() . '_' . $file->getClientOriginalName();
                $path = $file->storeAs('work-orders/' . $workOrder->id, $filename, 'public');
                $attachments[] = $path;
            }

            // Get existing attachments as arrays
            $existingImages = is_array($workOrder->images) ? $workOrder->images : 
                            (is_string($workOrder->images) ? json_decode($workOrder->images, true) : []);
            $existingFiles = is_array($workOrder->file_attachments) ? $workOrder->file_attachments : 
                            (is_string($workOrder->file_attachments) ? json_decode($workOrder->file_attachments, true) : []);

            // Ensure arrays are not null
            $existingImages = $existingImages ?? [];
            $existingFiles = $existingFiles ?? [];

            // Update the work order with merged arrays
            $workOrder->images = array_values(array_merge($existingImages, $attachments));
            $workOrder->file_attachments = array_values(array_merge($existingFiles, $attachments));
            $workOrder->save();

            return response()->json([
                'success' => true,
                'message' => 'Files uploaded successfully',
                'attachments' => $attachments
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'No files were uploaded'
        ], 400);
    } catch (\Exception $e) {
        \Log::error('Error uploading attachments: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'error' => $e->getMessage()
        ], 500);
    }
}

/**
 * Create an invoice in QuickBooks
 * 
 * @param WorkOrder $workOrder
 * @return string Invoice ID
 */
private function createQuickBooksInvoice($workOrder)
{
    // This is a placeholder method - replace with your actual QuickBooks integration
    // For example, using QuickBooks SDK or API
    
    try {
        // Implement your QuickBooks invoice creation logic here
        // Examples might include:
        // 1. Preparing customer data
        // 2. Setting up line items based on work order
        // 3. Making API calls to QuickBooks
        
        // For demonstration purposes, returning a dummy invoice ID
        return 'INV-' . time() . '-' . $workOrder->id;
        
    } catch (\Exception $e) {
        \Log::error('QuickBooks invoice creation failed: ' . $e->getMessage());
        throw $e;
    }
}

/**
 * Check if a work order with the given work order number exists
 *
 * @param  \Illuminate\Http\Request  $request
 * @return \Illuminate\Http\JsonResponse
 */
public function checkWorkOrderExists(Request $request)
{
    $workOrderNumber = $request->input('workOrderNumber');
    
    if (empty($workOrderNumber)) {
        return response()->json(['exists' => false]);
    }

    // Search for the work order number within the title field
    // Using LIKE with wildcards to find it anywhere in the title
    $exists = WorkOrder::where('title', 'like', '%' . $workOrderNumber . '%')->exists();

    return response()->json(['exists' => $exists]);
}

public function archive(WorkOrder $workOrder)
{
    try {
        $workOrder->archived = true;
        $workOrder->archived_at = now();
        $workOrder->save();

        return response()->json([
            'success' => true,
            'message' => 'Work order has been archived successfully.'
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Failed to archive work order: ' . $e->getMessage()
        ], 500);
    }
}

/**
 * Update the hours for the specified work order.
 *
 * @param  \Illuminate\Http\Request  $request
 * @param  int  $id
 * @return \Illuminate\Http\Response
 */
public function updateHours(Request $request, $id)
{
    $workOrder = WorkOrder::findOrFail($id);
    
    $request->validate([
        'hours' => 'nullable|numeric|min:0',
    ]);
    
    $workOrder->hours = $request->hours;
    $workOrder->save();
    
    return response()->json([
        'success' => true,
        'message' => 'Hours updated successfully',
        'hours' => $workOrder->hours
    ]);
}

/**
 * Update the address for the specified work order.
 *
 * @param  \Illuminate\Http\Request  $request
 * @param  int  $id
 * @return \Illuminate\Http\Response
 */
public function updateAddress(Request $request, $id)
{
    $workOrder = WorkOrder::findOrFail($id);
    
    $request->validate([
        'address' => 'nullable|string|max:255',
    ]);
    
    $workOrder->address = $request->address;
    $workOrder->save();
    
    return response()->json([
        'success' => true,
        'message' => 'Address updated successfully',
        'address' => $workOrder->address
    ]);
}

public function createInvoice($id)
{
    $workOrder = WorkOrder::find($id);

    if (!$workOrder) {
        return response()->json(['message' => 'Work order not found'], 404);
    }

    // Logic to create an invoice
    $invoice = Invoice::create([
        'work_order_id' => $workOrder->id,
        'amount' => $workOrder->price,
        // Add other necessary fields
    ]);

    return response()->json([
        'success' => true,
        'invoiceId' => $invoice->id,
        'message' => 'Invoice created successfully'
    ]);
}

}