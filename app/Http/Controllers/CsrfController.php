<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;

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
        // Regenerate CSRF token
        $token = csrf_token();
        
        return response()->json([
            'token' => $token,
            'header' => 'X-CSRF-TOKEN'
        ]);
    }
}
