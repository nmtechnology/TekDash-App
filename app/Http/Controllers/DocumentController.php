<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class DocumentController extends Controller
{
    public function uploadSignedDocument(Request $request)
    {
        try {
            $request->validate([
                'file' => 'required|file|max:10240', // 10MB max
                'workOrderId' => 'required|exists:work_orders,id'
            ]);

            $file = $request->file('file');
            $path = $file->store('signed_documents/' . $request->workOrderId, 'public');

            return response()->json([
                'success' => true,
                'path' => $path,
                'url' => Storage::disk('public')->url($path)
            ]);
        } catch (\Exception $e) {
            Log::error('Error uploading signed document: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to upload signed document: ' . $e->getMessage()
            ], 500);
        }
    }

    public function uploadSigned(Request $request)
    {
        return $this->uploadSignedDocument($request);
    }
}