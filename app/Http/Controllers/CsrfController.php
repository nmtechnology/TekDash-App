<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

/**
 * Controller for refreshing CSRF tokens
 */
class CsrfController extends Controller
{
    /**
     * Get a new CSRF token
     */
    public function refresh(Request $request)
    {
        return response()->json([
            'csrfToken' => csrf_token(),
        ]);
    }
}
