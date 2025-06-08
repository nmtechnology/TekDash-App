<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class RegisterController extends Controller
{
    public function __construct()
    {
        $this->middleware('guest');
    }

    public function store(Request $request)
    {
        // Validate CSRF token manually
        if ($request->header('X-CSRF-TOKEN') !== csrf_token() && 
            $request->header('X-XSRF-TOKEN') !== csrf_token()) {
            // Refresh the token and send it back
            $token = csrf_token();
            return response()->json([
                'message' => 'CSRF token mismatch. New token provided.',
                'token' => $token
            ], 419)->withHeaders([
                'X-CSRF-TOKEN' => $token,
                'X-XSRF-TOKEN' => $token
            ])->cookie('XSRF-TOKEN', $token, 60, '/', null, config('session.secure'), true);
        }

        return app(CreatesNewUsers::class)->create($request->all());
    }
}
