<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Response;

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
}
