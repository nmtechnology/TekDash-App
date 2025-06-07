<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

/**
 * Controller for refreshing CSRF tokens
 */
class CsrfController extends Controller
{
    /**
     * Refresh the CSRF token.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh(Request $request)
    {
        return response()->json([
            'csrfToken' => csrf_token(),
        ]);
    }
}
