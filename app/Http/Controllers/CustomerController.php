<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $customers = Customer::where('is_active', true)
            ->orderBy('business_name')
            ->paginate(10);
        
        // Get the ids of all customers in the current page
        $customerIds = $customers->pluck('id');
        
        // Get recent work orders for each customer in the current page
        $recentWorkOrders = \App\Models\WorkOrder::whereIn('customer_id', $customerIds)
            ->orderBy('created_at', 'desc')
            ->get()
            ->groupBy('customer_id');

        // Add recent work orders to each customer
        $customers->getCollection()->transform(function ($customer) use ($recentWorkOrders) {
            if (isset($recentWorkOrders[$customer->id])) {
                $customer->recent_work_orders = $recentWorkOrders[$customer->id]
                    ->take(3) // Limit to 3 most recent
                    ->values()
                    ->toArray();
            } else {
                $customer->recent_work_orders = [];
            }
            return $customer;
        });

        // Return JSON for API requests, Inertia for web
        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json($customers);
        }

        return Inertia::render('Customers/Index', [
            'customers' => $customers
        ]);
    }

    public function store(Request $request)
    {
        try {
            // Debug: Log the incoming request data with extra details
            Log::info('Customer creation request data:', [
                'all_data' => $request->all(),
                'has_poc_name' => $request->has('poc_name'),
                'poc_name_value' => $request->input('poc_name'),
                'content_type' => $request->header('Content-Type'),
                'files' => $request->hasFile('attachable_files') ? 'has files' : 'no files',
                'files_count' => $request->hasFile('attachable_files') ? count($request->file('attachable_files')) : 0,
                'request_keys' => array_keys($request->all())
            ]);
            
            // Validate the request
            $validator = \Validator::make($request->all(), [
                'business_name' => 'required|string|max:255',
                'address' => 'required|string|max:255',
                'poc_name' => 'required|string|max:255',
                'poc_email' => 'required|email|max:255',
                'fax' => 'nullable|string|max:20',
                'net_terms' => 'required|in:Net 7,Net 15,Net 30,Net 60',
                'pay_rate' => 'required|numeric|min:0',
                'attachable_files.*' => 'nullable|file|max:10240', // 10MB max file size
            ]);
            
            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }
            
            // Set data from validated input
            $validated = $validator->validated();
            $validated['is_active'] = true;
            $validated['attachable_files'] = [];
            
            // Make sure business name is unique
            if (Customer::where('business_name', $validated['business_name'])->exists()) {
                Log::warning('Duplicate business name attempt: ' . $validated['business_name']);
                return response()->json([
                    'message' => 'Validation failed',
                    'errors' => ['business_name' => ['Business name already exists']]
                ], 422); // Return 422 Unprocessable Entity for validation errors
            }

            // Process file uploads if present
            $filePaths = [];
            if ($request->hasFile('attachable_files')) {
                foreach ($request->file('attachable_files') as $file) {
                    $filePaths[] = $file->store('customer_files', 'public');
                }
            }
            $validated['attachable_files'] = $filePaths;

            // Create the customer record
            $customer = Customer::create($validated);

            return response()->json([
                'message' => 'Customer created successfully',
                'customer' => $customer
            ], 201);
        } catch (\Exception $e) {
            Log::error('Error creating customer: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error creating customer',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show(Customer $customer)
    {
        // Get the customer's recent work orders
        $recentWorkOrders = \App\Models\WorkOrder::where('customer_id', $customer->id)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
            
        return response()->json([
            'customer' => $customer,
            'recentWorkOrders' => $recentWorkOrders
        ]);
    }

    public function update(Request $request, Customer $customer)
    {
        try {
            // Get only the fields that are being updated
            $updateData = $request->all();
            $validationRules = [];
            
            // Only validate the fields that are present in the request
            if (array_key_exists('business_name', $updateData)) {
                $validationRules['business_name'] = 'required|string|max:255|unique:customers,business_name,' . $customer->id;
            }
            
            if (array_key_exists('address', $updateData)) {
                $validationRules['address'] = 'required|string|max:255';
            }
            
            if (array_key_exists('poc_name', $updateData)) {
                $validationRules['poc_name'] = 'required|string|max:255';
            }
            
            if (array_key_exists('poc_email', $updateData)) {
                $validationRules['poc_email'] = 'required|email|max:255';
            }
            
            if (array_key_exists('fax', $updateData)) {
                $validationRules['fax'] = 'nullable|string|max:20';
            }
            
            if (array_key_exists('net_terms', $updateData)) {
                $validationRules['net_terms'] = 'required|in:Net 7,Net 15,Net 30,Net 60';
            }
            
            if (array_key_exists('pay_rate', $updateData)) {
                $validationRules['pay_rate'] = 'nullable|numeric|min:0';
            }
            
            if (array_key_exists('attachable_files', $updateData)) {
                $validationRules['attachable_files'] = 'nullable|array';
            }
            
            if (array_key_exists('is_active', $updateData)) {
                $validationRules['is_active'] = 'boolean';
            }
            
            // If we have no validation rules, that means no valid fields to update
            if (empty($validationRules)) {
                return response()->json([
                    'message' => 'No valid fields to update',
                ], 400);
            }
            
            // Validate the fields being updated
            $validated = $request->validate($validationRules);
            
            // Update only the validated fields
            $customer->update($validated);
            
            return response()->json([
                'message' => 'Customer updated successfully',
                'customer' => $customer
            ]);
        } catch (\Exception $e) {
            Log::error('Error updating customer: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error updating customer',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Customer $customer)
    {
        try {
            // Soft delete by marking as inactive instead of actually deleting
            $customer->update(['is_active' => false]);
            return response()->json(['message' => 'Customer deactivated successfully']);
        } catch (\Exception $e) {
            Log::error('Error deactivating customer: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error deactivating customer',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function search(Request $request)
    {
        $query = $request->get('query');
        
        if (!$query) {
            return response()->json([]);
        }

        $customers = Customer::where('is_active', true)
            ->where(function($q) use ($query) {
                $q->where('business_name', 'like', "%{$query}%")
                  ->orWhere('poc_name', 'like', "%{$query}%")
                  ->orWhere('poc_email', 'like', "%{$query}%");
            })
            ->orderBy('business_name')
            ->get();

        return response()->json($customers);
    }

    public function workOrders(Customer $customer)
    {
        $workOrders = \App\Models\WorkOrder::where('customer_id', $customer->id)
            ->orderBy('created_at', 'desc')
            ->paginate(10);
        
        return Inertia::render('Customers/WorkOrders', [
            'customer' => $customer,
            'workOrders' => $workOrders
        ]);
    }

    // Check if a business name already exists
    public function checkBusinessNameExists(Request $request)
    {
        $businessName = $request->input('business_name');
        $exists = Customer::where('business_name', $businessName)->exists();
        
        return response()->json([
            'exists' => $exists
        ]);
    }
}
