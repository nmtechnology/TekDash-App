<?php
// filepath: /Users/nmtechnology/Herd/nmtis-dash/app/Http/Controllers/NoteController.php

namespace App\Http\Controllers;

use App\Models\Note;
use App\Models\WorkOrder;
use Illuminate\Http\Request;

class NoteController extends Controller
{
    /**
     * Store a newly created note in storage.
     */
    public function store(Request $request, $workOrderId)
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

        return response()->json([
            'id' => $note->id,
            'text' => $note->text,
            'user_id' => $user->id,
            'user_name' => $user->name,
            'created_at' => $note->created_at,
        ], 201);
    }
    
    /**
     * Update the specified note in storage.
     */
    public function update(Request $request, Note $note)
    {
        $request->validate([
            'text' => 'required|string',
        ]);

        $note->update([
            'text' => $request->input('text'),
        ]);

        return response()->json([
            'id' => $note->id,
            'text' => $note->text,
            'user_id' => $note->user_id,
            'user_name' => $note->user->name,
            'created_at' => $note->created_at,
        ]);
    }

    /**
     * Remove the specified note from storage.
     */
    public function destroy(Note $note)
    {
        $note->delete();

        return response()->json(['message' => 'Note deleted successfully']);
    }
}