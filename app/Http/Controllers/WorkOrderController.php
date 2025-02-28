<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\WorkOrder;
use App\Models\User;
use Inertia\Inertia;
use App\Models\Note;

class WorkOrderController extends Controller
{
    // Display a listing of the resource
    public function index()
    {
        $workOrders = WorkOrder::all();
        $users = User::all();
        return Inertia::render('WorkOrders/Index', [
            'workOrders' => $workOrders,
            'users' => $users,
        ]);
    }

    // Show the form for creating a new resource
    public function create()
    {
        return Inertia::render('WorkOrders/Create');
    }

    // Store a newly created resource in storage
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'user_id' => 'required|exists:users,id',
            'customer_id' => 'required|string|max:255',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'date_time' => 'required|date',
            'price' => 'required|numeric',
            'status' => 'required|string|in:Scheduled,In Progress,Part/Return,Complete,Cancelled',
            'file_attachments.*' => 'nullable|file|mimes:pdf,jpg|max:2048',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        $workOrder = new WorkOrder();
        $workOrder->user_id = $validatedData['user_id'];
        $workOrder->customer_id = $validatedData['customer_id'];
        $latestWorkOrder = WorkOrder::where('title', 'like', $validatedData['title'] . '-%')->orderBy('id', 'desc')->first();
        if ($latestWorkOrder) {
            preg_match('/\-(\d+)$/', $latestWorkOrder->title, $matches);
            $copyNumber = isset($matches[1]) ? (int)$matches[1] + 1 : 1;
        } else {
            $copyNumber = 1;
        }
        $workOrder->title = $validatedData['title'] . ' -' . str_pad($copyNumber, 2, '0', STR_PAD_LEFT);
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

        return redirect()->route('dashboard')->with('success', 'Work order added successfully.');
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
            'status' => 'required|string|in:Scheduled,In Progress,Part/Return,Complete,Cancelled',
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
    public function duplicate($id)
    {
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
        
        $newWorkOrder->title = $baseTitle . ' -' . str_pad($copyNumber, 2, '0', STR_PAD_LEFT) . ' (Return)';

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

        $userName = auth()->user()->name;
        return redirect()->route('dashboard')->with('message', "Work order duplicated successfully by $userName");
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

public function updateImages(Request $request, $id)
{
    $request->validate([
        'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048'
    ]);
    
    $workOrder = WorkOrder::findOrFail($id);
    
    try {
        $newImages = [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('work_orders', 'public');
                $newImages[] = $path;
            }
        }
        
        // If work order already has images, merge them
        $existingImages = $workOrder->images ? json_decode($workOrder->images, true) : [];
        if (!is_array($existingImages)) {
            $existingImages = [];
        }
        
        $allImages = array_merge($existingImages, $newImages);
        $workOrder->images = json_encode($allImages);
        $workOrder->save();
        
        return response()->json([
            'success' => true,
            'message' => 'Images updated successfully',
            'images' => $allImages
        ]);
    } catch (\Exception $e) {
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
            
        // Calculate totals for current month
        $currentTotalRevenue = $currentMonthOrders->sum('price');
        $currentCompletedCount = $currentMonthOrders->where('status', 'Complete')->count();
        $currentPendingCount = $currentMonthOrders->whereIn('status', ['Scheduled', 'In Progress'])->count();
        $currentAvgPrice = $currentMonthOrders->count() > 0 
            ? $currentTotalRevenue / $currentMonthOrders->count() 
            : 0;
            
        // Calculate totals for previous month
        $previousTotalRevenue = $previousMonthOrders->sum('price');
        $previousCompletedCount = $previousMonthOrders->where('status', 'Complete')->count();
        $previousPendingCount = $previousMonthOrders->whereIn('status', ['Scheduled', 'In Progress'])->count();
        $previousAvgPrice = $previousMonthOrders->count() > 0 
            ? $previousTotalRevenue / $previousMonthOrders->count() 
            : 0;
            
        // Calculate percentage changes
        $revenueChange = $previousTotalRevenue > 0 
            ? (($currentTotalRevenue - $previousTotalRevenue) / $previousTotalRevenue) * 100 
            : 0;
            
        $completedChange = $previousCompletedCount > 0 
            ? (($currentCompletedCount - $previousCompletedCount) / $previousCompletedCount) * 100 
            : 0;
            
        $pendingChange = $previousPendingCount > 0 
            ? (($currentPendingCount - $previousPendingCount) / $previousPendingCount) * 100 
            : 0;
            
        $avgPriceChange = $previousAvgPrice > 0 
            ? (($currentAvgPrice - $previousAvgPrice) / $previousAvgPrice) * 100 
            : 0;
            
        // Format the stats array
        $stats = [
            [
                'name' => 'Total Revenue',
                'value' => '$' . number_format($currentTotalRevenue, 2),
                'change' => ($revenueChange >= 0 ? '+' : '') . number_format($revenueChange, 2) . '%',
                'changeType' => $revenueChange >= 0 ? 'positive' : 'negative'
            ],
            [
                'name' => 'Completed Orders',
                'value' => $currentCompletedCount,
                'change' => ($completedChange >= 0 ? '+' : '') . number_format($completedChange, 2) . '%',
                'changeType' => $completedChange >= 0 ? 'positive' : 'negative'
            ],
            [
                'name' => 'Pending Orders',
                'value' => $currentPendingCount,
                'change' => ($pendingChange >= 0 ? '+' : '') . number_format($pendingChange, 2) . '%',
                'changeType' => $pendingChange <= 0 ? 'positive' : 'negative' // Less pending is positive
            ],
            [
                'name' => 'Average Price',
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
 * Search for work orders
 */
public function search(Request $request)
{
    $query = $request->input('query');
    
    if (empty($query)) {
        return response()->json([]);
    }
    
    $workOrders = WorkOrder::where('title', 'like', "%{$query}%")
        ->orWhere('customer_id', 'like', "%{$query}%")
        ->orWhere('description', 'like', "%{$query}%")
        ->orderBy('created_at', 'desc')
        ->limit(10)
        ->get(['id', 'title', 'status', 'customer_id']);
        
    return response()->json($workOrders);
}

}