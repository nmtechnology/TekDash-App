<?php

namespace App\Http\Controllers;

use App\Models\Technician;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TechnicianController extends Controller
{
    public function index(Request $request)
    {
        $technicians = Technician::orderBy('last_name')
            ->orderBy('first_name')
            ->get();

        // Return JSON for API requests, Inertia for web
        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json($technicians);
        }

        return Inertia::render('Technicians/Index', [
            'technicians' => $technicians
        ]);
    }

    public function show(Technician $technician)
    {
        // Get the technician's recent work orders
        $recentWorkOrders = \App\Models\WorkOrder::where('technician_id', $technician->id)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->with(['customer']) // Include customer relationship
            ->get();

        return Inertia::render('Technicians/Show', [
            'technician' => $technician,
            'recentWorkOrders' => $recentWorkOrders
        ]);
    }

    public function update(Request $request, Technician $technician)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:technicians,email,' . $technician->id,
            'phone_number' => 'required|string|max:20',
            'address' => 'nullable|string|max:255',
            'employee_id' => 'required|string|max:255|unique:technicians,employee_id,' . $technician->id,
            'hire_date' => 'required|date',
            'certifications' => 'nullable|array',
            'specializations' => 'nullable|array',
            'is_active' => 'boolean',
            'pay_rate' => 'nullable|numeric|min:0',
        ]);

        $technician->update($validated);

        return redirect()->back()->with('success', 'Technician updated successfully.');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:technicians,email',
            'phone_number' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'employee_id' => 'nullable|string|max:255|unique:technicians,employee_id',
            'hire_date' => 'nullable|date',
            'certifications' => 'nullable|array',
            'specializations' => 'nullable|array',
            'is_active' => 'boolean',
            'pay_rate' => 'nullable|numeric|min:0',
        ]);

        // Auto-generate employee_id if not provided
        if (empty($validated['employee_id'])) {
            $validated['employee_id'] = 'EMP-' . strtoupper(substr($validated['first_name'],0,1)) . strtoupper(substr($validated['last_name'],0,1)) . '-' . rand(1000,9999);
        }
        // Auto-set hire_date if not provided
        if (empty($validated['hire_date'])) {
            $validated['hire_date'] = now();
        }
        if (!isset($validated['is_active'])) {
            $validated['is_active'] = true;
        }

        $technician = Technician::create($validated);
        return response()->json($technician, 201);
    }

    public function getActiveTechnicians()
    {
        $technicians = Technician::where('is_active', true)
            ->select('id', 'first_name', 'last_name', 'employee_id')
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get()
            ->map(function($tech) {
                return [
                    'id' => $tech->id,
                    'name' => $tech->first_name . ' ' . $tech->last_name . ' (' . $tech->employee_id . ')'
                ];
            });

        return response()->json($technicians);
    }
}
