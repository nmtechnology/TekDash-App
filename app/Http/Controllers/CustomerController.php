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
            
            // Set default values for fields if not present
            $businessName = $request->input('business_name') ?? '';
            $address = $request->input('address') ?? '';
            $pocName = $request->input('poc_name') ?? '';
            $pocEmail = $request->input('poc_email') ?? '';
            $fax = $request->input('fax') ?? '';
            $netTerms = $request->input('net_terms') ?? 'Net 30';
            $payRate = $request->input('pay_rate') ?? 120.00;
            
            // Simplify validation to focus on required fields
            $validated = [
                'business_name' => $businessName,
                'address' => $address,
                'poc_name' => $pocName,
                'poc_email' => $pocEmail,
                'fax' => $fax,
                'net_terms' => $netTerms,
                'pay_rate' => $payRate,
                'attachable_files' => [],
                'is_active' => true
            ];
            
            // Manual validation with informative errors
            if (empty($validated['business_name'])) {
                throw new \Exception('Business name is required');
            }
            if (empty($validated['address'])) {
                throw new \Exception('Address is required');
            }
            if (empty($validated['poc_name'])) {
                throw new \Exception('POC name is required');
            }
            if (empty($validated['poc_email'])) {
                throw new \Exception('POC email is required');
            }
            if (!filter_var($validated['poc_email'], FILTER_VALIDATE_EMAIL)) {
                throw new \Exception('POC email must be a valid email address');
            }
            
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
}
