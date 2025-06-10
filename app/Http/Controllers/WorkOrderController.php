<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\WorkOrder;
use App\Models\User;
use App\Models\WorkOrderActivity;
use Inertia\Inertia;
use App\Models\Note;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\Customer;

class WorkOrderController extends Controller
{
    // Display a listing of the resource
    public function index()
    {
        return Inertia::render('WorkOrders/Index', [
            'workOrders' => WorkOrder::with('customer')->latest()->paginate(10)
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
            // Log the incoming request data for debugging
            Log::info('Work order creation attempt', [
                'request_data' => $request->all(),
                'customer_id_type' => gettype($request->input('customer_id')),
                'user_id' => $request->input('user_id')
            ]);

            // Get customer_id from the request
            $customerId = $request->input('customer_id');
            
            // Check if the input is numeric (direct ID) or a string (business name)
            if (is_numeric($customerId)) {
                // Find customer by ID
                $customer = Customer::find($customerId);
                if (!$customer) {
                    throw new \Exception("Customer with ID '{$customerId}' not found. Please create the customer first.");
                }
            } else {
                // Find customer by business name
                $customer = Customer::where('business_name', $customerId)->first();
                if (!$customer) {
                    throw new \Exception("Customer with business name '{$customerId}' not found. Please create the customer first.");
                }
            }

            // Validate request with other fields
            $validated = $request->validate([
                'title' => 'required|string',
                'description' => 'required|string',
                'date_time' => 'required|date',
                'end_date' => 'nullable|date',
                'address' => 'required|string',
                'hours' => 'required|numeric|min:0',
                'price' => 'required|numeric|min:0',
                'status' => 'required|string|in:Scheduled,In Progress,Part Needed,Complete,Cancelled',
                'user_id' => 'required|exists:users,id',
                'technician_id' => 'nullable|exists:technicians,id',
            ]);

            // Convert any Part/Return status to Part Needed
            if ($validated['status'] === 'Part/Return') {
                $validated['status'] = 'Part Needed';
            }

            // Make sure we're using the correct customer_id in the validated data
            $validated['customer_id'] = $customer->id;
            Log::info('Using customer ID: ' . $customer->id);

            // Add more detailed debug logging
            Log::info('About to create work order', [
                'validated_data' => $validated,
                'customer' => $customer->toArray(),
                'has_technician_id' => isset($validated['technician_id']),
                'has_hours' => isset($validated['hours']),
                'file_attachments' => $request->hasFile('file_attachments') ? 'yes' : 'no'
            ]);
            
            // Handle file attachments if any
            if ($request->hasFile('file_attachments')) {
                try {
                    $fileAttachments = [];
                    foreach ($request->file('file_attachments') as $file) {
                        $path = $file->store('work_orders', 'public');
                        $fileAttachments[] = $path;
                    }
                    $validated['file_attachments'] = json_encode($fileAttachments);
                    
                    Log::info('File attachments processed', [
                        'count' => count($fileAttachments),
                        'paths' => $fileAttachments
                    ]);
                } catch (\Exception $e) {
                    Log::error('Error processing file attachments', [
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                    // Continue without file attachments rather than failing
                    $validated['file_attachments'] = '[]';
                }
            }
            
            // Create the work order
            $workOrder = WorkOrder::create($validated);
            
            // Log creation activity for each field
            try {
                // Get the authenticated user ID, or use a fallback value
                $userId = auth()->id() ?? $validated['user_id'] ?? 1;
                
                foreach ($validated as $field => $value) {
                    if ($field === 'status' && $value === 'Part/Return') {
                        $value = 'Part Needed';
                    }
                    
                    // Convert value to string for safe storage
                    if (is_array($value)) {
                        $value = json_encode($value);
                    }
                    
                    WorkOrderActivity::create([
                        'work_order_id' => $workOrder->id,
                        'user_id' => $userId,
                        'field_name' => $field,
                        'old_value' => null,
                        'new_value' => $value,
                        'action_type' => 'create',
                        'description' => 'Created work order field: ' . $field
                    ]);
                }

                // Create a general creation activity
                WorkOrderActivity::create([
                    'work_order_id' => $workOrder->id,
                    'user_id' => $userId,
                    'field_name' => 'work_order',
                    'old_value' => null,
                    'new_value' => null,
                    'action_type' => 'create',
                    'description' => 'Work order created'
                ]);
            } catch (\Exception $e) {
                // Log error but continue with response
                Log::error('Error creating work order activity logs', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                // Continue execution - don't let activity logging failure prevent work order creation
            }

            return response()->json([
                'success' => true,
                'message' => 'Work order created successfully.',
                'workOrder' => $workOrder
            ]);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Log validation errors for debugging
            Log::error('Validation failed for work order creation', [
                'errors' => $e->errors(),
                'request_data' => $request->all()
            ]);

            return response()->json([
                'error' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
            
        } catch (\Exception $e) {
            Log::error('Error creating work order', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);

            return response()->json([
                'error' => 'An error occurred while creating the work order.',
                'message' => $e->getMessage(),
                'debug' => config('app.debug') ? $e->getTrace() : null
            ], 500);
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
        $workOrder->load(['user:id,name,email', 'customer', 'technician']);
        
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

            // Log activity for image uploads
            foreach ($newFiles as $file) {
                WorkOrderActivity::create([
                    'work_order_id' => $workOrder->id,
                    'user_id' => auth()->id(),
                    'field_name' => 'images',
                    'old_value' => null,
                    'new_value' => $file,
                    'action_type' => 'update',
                    'description' => 'Added new ' . (str_contains($file, 'documents') ? 'document' : 'image')
                ]);
            }
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

    /**
     * Update a specific field in the work order
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateField(Request $request, $id)
    {
        try {
            // Find the work order
            $workOrder = WorkOrder::findOrFail($id);
            
            // Get the field to update
            $field = array_keys($request->except('_token'))[0];
            $value = $request->input($field);
            
            // Validate based on field
            if ($field === 'hours' || $field === 'hourly_rate' || $field === 'travel_cost') {
                $request->validate([
                    $field => 'numeric|min:0'
                ]);
            } elseif ($field === 'status') {
                $request->validate([
                    $field => 'string|in:Scheduled,In Progress,Part Needed,Complete,Cancelled'
                ]);
            } elseif ($field === 'has_travel') {
                $value = (bool) $value;
            }
            
            // Update the field
            $workOrder->$field = $value;
            $workOrder->save();
            
            // Create activity log
            $workOrder->activities()->create([
                'user_id' => auth()->id(),
                'action' => 'update',
                'description' => "Updated {$field}",
                'details' => json_encode([
                    'field' => $field,
                    'value' => $value
                ])
            ]);
            
            // Return success response
            return response()->json([
                'success' => true,
                'message' => 'Field updated successfully',
                'workOrder' => $workOrder
            ]);
        } catch (\Exception $e) {
            Log::error('Error updating work order field', [
                'error' => $e->getMessage(),
                'work_order_id' => $id,
                'field' => $request->keys()[0] ?? 'unknown'
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to update field: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Upload attachments for a work order
     *
     * @param Request $request
     * @param WorkOrder $workOrder
     * @return \Illuminate\Http\JsonResponse
     */
    public function uploadAttachments(Request $request, $workOrder)
    {
        try {
            // Convert string ID to model if needed
            if (!($workOrder instanceof WorkOrder)) {
                $workOrder = WorkOrder::findOrFail($workOrder);
            }
            
            // Validate the request
            $request->validate([
                'attachments' => 'required|array',
                'attachments.*' => 'file|max:10240', // 10MB max per file
            ]);
            
            $attachments = [];
            
            // Process each uploaded file
            foreach ($request->file('attachments') as $file) {
                // Store the file in the storage/app/public/work-orders directory
                $path = $file->store('work-orders/' . $workOrder->id, 'public');
                
                // Generate a URL for the stored file
                $url = asset('storage/' . $path);
                
                // Store attachment info in the database
                $attachment = $workOrder->attachments()->create([
                    'file_path' => $path,
                    'file_name' => $file->getClientOriginalName(),
                    'file_type' => $file->getClientMimeType(),
                    'file_size' => $file->getSize(),
                    'url' => $url
                ]);
                
                $attachments[] = $url;
            }
            
            // Log the activity
            $workOrder->activities()->create([
                'user_id' => auth()->id() ?? 1,
                'action' => 'upload',
                'description' => count($attachments) . ' files uploaded',
                'details' => json_encode($attachments)
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Files uploaded successfully',
                'attachments' => $workOrder->attachments,
                'workOrder' => $workOrder->load('attachments')
            ]);
        } catch (\Exception $e) {
            Log::error('Error uploading work order attachments', [
                'error' => $e->getMessage(),
                'work_order_id' => $workOrder->id ?? 'unknown'
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to upload files: ' . $e->getMessage()
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

    // Removed duplicate updateHours method as it's now handled by updateField

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

    /**
     * Get a human-readable display name for a field
     */
    private function getFieldDisplayName($field)
    {
        $displayNames = [
            'customer_id' => 'Customer',
            'user_id' => 'Assigned User',
            'title' => 'Title',
            'description' => 'Description',
            'date_time' => 'Date',
            'end_date' => 'End Date',
            'status' => 'Status',
            'price' => 'Price',
            'hours' => 'Hours',
            'address' => 'Address',
            'work_order' => 'Work Order',
            'images' => 'Images',
            'file_attachments' => 'Attachments'
        ];
        
        return $displayNames[$field] ?? ucfirst(str_replace('_', ' ', $field));
    }

    /**
     * Get activities for a work order
     */
    public function getActivities($id)
    {
        try {
            $workOrder = WorkOrder::findOrFail($id);
            $activities = $workOrder->activities()->with('user')->get();
            
            return response()->json([
                'success' => true,
                'activities' => $activities
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function updateGrandTotal(Request $request, $id)
    {
        try {
            // Find the work order
            $workOrder = WorkOrder::findOrFail($id);
            
            // Calculate grand total
            $laborTotal = $workOrder->hourly_rate * $workOrder->hours;
            $travelCost = $workOrder->has_travel ? $workOrder->travel_cost : 0;
            $grandTotal = $laborTotal + $travelCost;
            
            // Update the work order
            $workOrder->grand_total = $grandTotal;
            $workOrder->save();
            
            // Return success response
            return response()->json([
                'success' => true,
                'message' => 'Grand total updated successfully',
                'grand_total' => $grandTotal
            ]);
        } catch (\Exception $e) {
            Log::error('Error updating grand total', [
                'error' => $e->getMessage(),
                'work_order_id' => $id
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to update grand total: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Update all fields of a work order at once
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateAll(Request $request, $id)
    {
        try {
            // Find the work order
            $workOrder = WorkOrder::findOrFail($id);
            
            // Get the original values for activity logging
            $originalValues = $workOrder->only([
                'title', 'description', 'address', 'hours', 
                'hourly_rate', 'travel_cost', 'has_travel', 'status'
            ]);
            
            // Validate the request
            $validatedData = $request->validate([
                'title' => 'string',
                'description' => 'string|nullable',
                'address' => 'string|nullable',
                'hours' => 'numeric|min:0',
                'hourly_rate' => 'numeric|min:0',
                'travel_cost' => 'numeric|min:0|nullable',
                'has_travel' => 'boolean',
                'status' => 'string|in:Scheduled,In Progress,Part Needed,Complete,Cancelled',
            ]);
            
            // Update the work order with validated data
            $workOrder->fill($validatedData);
            
            // Calculate grand total (if price affecting fields were updated)
            $priceAffectingFieldsUpdated = false;
            foreach (['hours', 'hourly_rate', 'travel_cost', 'has_travel'] as $field) {
                if (isset($validatedData[$field]) && $originalValues[$field] != $validatedData[$field]) {
                    $priceAffectingFieldsUpdated = true;
                    break;
                }
            }
            
            if ($priceAffectingFieldsUpdated) {
                $laborTotal = $workOrder->hourly_rate * $workOrder->hours;
                $travelCost = $workOrder->has_travel ? $workOrder->travel_cost : 0;
                $workOrder->grand_total = $laborTotal + $travelCost;
            }
            
            // Save the work order
            $workOrder->save();
            
            // Create activity logs for each changed field
            foreach ($validatedData as $field => $value) {
                if ($originalValues[$field] != $value) {
                    $workOrder->activities()->create([
                        'user_id' => auth()->id(),
                        'action' => 'update',
                        'description' => "Updated {$field}",
                        'details' => json_encode([
                            'field' => $field,
                            'old_value' => $originalValues[$field],
                            'new_value' => $value
                        ])
                    ]);
                }
            }
            
            // Return success response
            return response()->json([
                'success' => true,
                'message' => 'Work order updated successfully',
                'workOrder' => $workOrder
            ]);
        } catch (\Exception $e) {
            Log::error('Error updating work order', [
                'error' => $e->getMessage(),
                'work_order_id' => $id
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to update work order: ' . $e->getMessage()
            ], 500);
        }
    }
}