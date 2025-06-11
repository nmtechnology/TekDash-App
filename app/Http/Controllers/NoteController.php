<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Note;
use App\Models\WorkOrder;
use Illuminate\Support\Facades\Log;

class NoteController extends Controller
{
    /**
     * Display notes for a work order
     */
    public function index($workOrderId)
    {
        try {
            $workOrder = WorkOrder::findOrFail($workOrderId);
            $notes = $workOrder->notes()->with('user:id,name')->get();
            
            // Add user initials to each note for front-end display
            $notes = $notes->map(function ($note) {
                $initials = '';
                if ($note->user) {
                    $nameParts = explode(' ', $note->user->name);
                    if (count($nameParts) >= 2) {
                        $initials = strtoupper(substr($nameParts[0], 0, 1) . substr($nameParts[count($nameParts)-1], 0, 1));
                    } else if (count($nameParts) == 1) {
                        $initials = strtoupper(substr($nameParts[0], 0, 2));
                    }
                }
                $note->user_initials = $initials;
                return $note;
            });
            
            return response()->json($notes);
        } catch (\Exception $e) {
            Log::error('Error fetching notes: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Store a new note
     */
    public function store(Request $request, $workOrderId)
    {
        try {
            $request->validate([
                'text' => 'required|string',
                'user_id' => 'required|exists:users,id',
            ]);

            $workOrder = WorkOrder::findOrFail($workOrderId);
            $user = \App\Models\User::findOrFail($request->user_id);

            $note = new Note([
                'text' => $request->input('text'),
                'user_id' => $user->id,
                'work_order_id' => $workOrderId,
            ]);
            
            $note->save();

            return response()->json([
                'id' => $note->id,
                'text' => $note->text,
                'user_id' => $note->user_id,
                'user_name' => $user->name,
                'created_at' => $note->created_at,
            ], 201);
        } catch (\Exception $e) {
            Log::error('Error storing note: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}