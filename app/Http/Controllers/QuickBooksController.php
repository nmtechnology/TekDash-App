<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Inertia\Inertia;

class QuickBooksController extends Controller
{
    protected $clientId;
    protected $clientSecret;
    protected $redirectUri;
    protected $scope;
    
    public function __construct()
    {
        $this->clientId = config('services.quickbooks.client_id');
        $this->clientSecret = config('services.quickbooks.client_secret');
        $this->redirectUri = config('app.url') . '/quickbooks/callback';
        $this->scope = 'com.intuit.quickbooks.accounting';
    }

    public function authorize()
    {
        // Generate and store CSRF token
        $state = Str::random(40);
        Session::put('quickbooks_state', $state);

        $authorizationUrl = 'https://appcenter.intuit.com/connect/oauth2';
        $queryParams = http_build_query([
            'client_id' => $this->clientId,
            'response_type' => 'code',
            'scope' => $this->scope,
            'redirect_uri' => $this->redirectUri,
            'state' => $state,
        ]);

        return redirect($authorizationUrl . '?' . $queryParams);
    }

    public function callback(Request $request)
    {
        // Verify state to prevent CSRF
        $state = Session::pull('quickbooks_state');
        
        if ($state !== $request->state) {
            return redirect('/dashboard')->with('error', 'Invalid state parameter. Authorization failed.');
        }

        if ($request->has('error')) {
            return redirect('/dashboard')->with('error', 'QuickBooks authorization failed: ' . $request->error_description);
        }

        // Exchange authorization code for access token
        try {
            $response = Http::asForm()->post('https://oauth.platform.intuit.com/oauth2/v1/tokens/bearer', [
                'grant_type' => 'authorization_code',
                'code' => $request->code,
                'redirect_uri' => $this->redirectUri,
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
            ]);

            if ($response->successful()) {
                $tokenData = $response->json();
                
                // Store token data in session or database
                Session::put('quickbooks_access_token', $tokenData['access_token']);
                Session::put('quickbooks_refresh_token', $tokenData['refresh_token']);
                Session::put('quickbooks_token_expires', now()->addSeconds($tokenData['expires_in']));
                Session::put('quickbooks_realm_id', $request->realmId);
                
                return redirect('/dashboard')->with('success', 'Successfully connected to QuickBooks!');
            } else {
                Log::error('QuickBooks token exchange failed', [
                    'status' => $response->status(),
                    'response' => $response->json(),
                ]);
                return redirect('/dashboard')->with('error', 'Failed to connect to QuickBooks. Please try again later.');
            }
        } catch (\Exception $e) {
            Log::error('Exception during QuickBooks authorization', [
                'exception' => $e->getMessage(),
            ]);
            return redirect('/dashboard')->with('error', 'An error occurred connecting to QuickBooks: ' . $e->getMessage());
        }
    }
    
    public function disconnect()
    {
        // Clear the tokens from storage
        Session::forget([
            'quickbooks_access_token',
            'quickbooks_refresh_token',
            'quickbooks_token_expiry',
            'quickbooks_connected',
            'quickbooks_state'
        ]);
        
        Log::info('QuickBooks disconnected', ['user_id' => Auth::id()]);
        
        return redirect()->route('dashboard')->with('success', 'Successfully disconnected from QuickBooks.');
    }
}
