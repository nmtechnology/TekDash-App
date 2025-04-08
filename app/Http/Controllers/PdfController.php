<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Str;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;

class PdfController extends Controller
{
    /**
     * Display a PDF file from storage
     *
     * @param Request $request
     * @param string $path
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $path)
    {
        // Check if file exists
        $fullPath = 'work_orders/documents/' . $path;
        if (!Storage::disk('public')->exists($fullPath)) {
            abort(404);
        }
        
        // Get the file content
        $file = Storage::disk('public')->get($fullPath);
        
        // Return the file as a PDF response
        return Response::pdf($file, basename($path));
    }

    public function generatePDF (Request $request) {
        $data = [
            'title' => 'Welcome to NM Technology',
            'date' => date('m/d/Y'),
            'name' => 'John Doe',
            'amount' => 5000,
            'items' => [
                ['item' => 'Item 1', 'price' => 1000],
                ['item' => 'Item 2', 'price' => 2000],
                ['item' => 'Item 3', 'price' => 3000],
            ],
            'total' => 6000,
            'tax' => 600,
            'discount' => 100,
            'grand_total' => 5500,
            'footer' => 'Thank you for your business, please visit us at nmtechnology.us'
        ];
        $pdf = PDF::loadView('pdf');
        return $pdf->download('invoice.pdf');
    }

    public function uploadSignedDocument(Request $request)
    {
        try {
            $request->validate([
                'pdf_url' => 'required|string',
                'signature_data' => 'required|string',
                'work_order_id' => 'required|integer'
            ]);

            // Get original PDF content
            $pdfUrl = $request->input('pdf_url');
            $pdfPath = parse_url($pdfUrl, PHP_URL_PATH);
            $pdfPath = basename($pdfPath);
            $originalPdf = Storage::disk('public')->get('work_orders/documents/' . $pdfPath);

            // Decode base64 signature data
            $signatureData = $request->input('signature_data');
            $signatureData = substr($signatureData, strpos($signatureData, ',') + 1);
            $signatureData = base64_decode($signatureData);

            // Merge PDF and signature (you might need additional PDF manipulation logic here)
            $mergedContent = $originalPdf . $signatureData;

            // Generate unique filename
            $filename = time() . '_signed_' . Str::random(8) . '.pdf';
            $path = 'work_orders/documents/' . $request->work_order_id . '/' . $filename;

            // Store the file
            if (!Storage::disk('public')->put($path, $mergedContent)) {
                throw new \Exception('Failed to save signed document');
            }

            return response()->json([
                'success' => true,
                'message' => 'Signed document uploaded successfully',
                'path' => $path,
                'url' => Storage::disk('public')->url($path)
            ]);

        } catch (\Exception $e) {
            \Log::error('Error uploading signed document: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to upload signed document: ' . $e->getMessage()
            ], 500);
        }
    }

    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:pdf|max:10240', // 10MB max
            'metadata' => 'required|json'
        ]);

        try {
            $file = $request->file('file');
            $metadata = json_decode($request->metadata, true);
            
            // Generate a unique filename
            $filename = Str::uuid() . '.pdf';
            
            // Store in the signed-documents directory
            $path = $file->storeAs('public/signed-documents', $filename);
            
            // Convert storage path to public URL
            $publicPath = Storage::url($path);

            return response()->json([
                'success' => true,
                'path' => $publicPath,
                'filename' => $filename,
                'metadata' => $metadata
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to upload document: ' . $e->getMessage()
            ], 500);
        }
    }

    public function uploadSigned(Request $request)
    {
        try {
            $request->validate([
                'document' => 'required|file|mimes:pdf|max:10240', // 10MB max, PDF only
                'workOrderId' => 'required|exists:work_orders,id'
            ]);

            $file = $request->file('document');
            $path = $file->store('signed_documents/' . $request->workOrderId, 'public');

            return response()->json([
                'success' => true,
                'path' => $path,
                'url' => Storage::url($path)
            ]);
        } catch (\Exception $e) {
            Log::error('Signed PDF upload failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to upload signed PDF'
            ], 500);
        }
    }
}
