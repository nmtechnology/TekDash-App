<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Response;
use Barryvdh\DomPDF\Facade\Pdf;

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

    public function uploadSignedDocument(Request $request, $id)
    {
        // Validate the request
        $request->validate([
            'file' => 'required|file|mimes:pdf|max:2048'
        ]);
        
        // Get the file
        $file = $request->file('file');
        
        // Store the file
        $path = $file->store('work_orders/documents', 'public');
        
        // Update the work order
        $workOrder = WorkOrder::find($id);
        $workOrder->signed_document = $path;
        $workOrder->save();
        
        // Return the updated work order
        return $workOrder;
    }
}
