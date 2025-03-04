<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class DocumentController extends Controller
{
    /**
     * Upload a signed document
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function uploadSignedDocument(Request $request)
    {
        Log::info('Document upload request received', [
            'has_file' => $request->hasFile('file'),
            'content_type' => $request->header('Content-Type'),
            'user_agent' => $request->header('User-Agent'),
        ]);
        
        // Validate the request
        $request->validate([
            'file' => 'required|file|mimes:pdf|max:10240', // 10MB max size
            'firstName' => 'nullable|string|max:100',
            'lastName' => 'nullable|string|max:100',
        ]);

        try {
            // Store the uploaded file
            $path = $request->file('file')->store('uploads/work_orders', 'public');
            
            // Get the full URL to the file
            $url = Storage::disk('public')->url($path);
            
            Log::info('Document uploaded successfully', [
                'path' => $path,
                'url' => $url,
                'filename' => $request->file('file')->getClientOriginalName(),
            ]);
            
            return response()->json([
                'success' => true,
                'url' => $url,
                'path' => $path,
                'message' => 'Signed document uploaded successfully',
            ]);

        } catch (\Exception $e) {
            // Log the error
            Log::error('Document upload failed: ' . $e->getMessage(), [
                'exception' => $e,
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to upload document: ' . $e->getMessage()
            ], 500);
        }
    }
}
