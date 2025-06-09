<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class CustomerController extends Controller
{
    public function index()
    {
        return Inertia::render('Customers/Index', [
            'customers' => Customer::where('is_active', true)
                ->orderBy('business_name')
                ->paginate(10)
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
        return response()->json($customer->load('workOrders'));
    }

    public function update(Request $request, Customer $customer)
    {
        try {
            $validated = $request->validate([
                'business_name' => 'required|string|max:255|unique:customers,business_name,' . $customer->id,
                'address' => 'required|string|max:255',
                'poc_name' => 'required|string|max:255',
                'poc_email' => 'required|email|max:255',
                'fax' => 'nullable|string|max:20',
                'net_terms' => 'required|in:Net 7,Net 15,Net 30,Net 60',
                'pay_rate' => 'nullable|numeric|min:0',
                'attachable_files' => 'nullable|array',
                'is_active' => 'boolean'
            ]);

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
}
