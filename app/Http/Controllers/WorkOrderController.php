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
    public function show($id)
    {
        $workOrder = WorkOrder::findOrFail($id);
        return view('work_orders.show', compact('workOrder'));
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
        $workOrder = WorkOrder::findOrFail($id);
        $workOrder->delete();

        return response()->json(['message' => 'Work order deleted successfully.']);
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
}