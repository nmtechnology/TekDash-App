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
use App\Models\WorkOrder;
use App\Services\QuickBooksService;

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
        $this->redirectUri = config('services.quickbooks.redirect_uri');
        $this->scope = 'com.intuit.quickbooks.accounting';
    }

    public function authorize(Request $request)
    {
        // Store CSRF token in session for validation in callback
        $state = Str::random(40);
        session(['quickbooks_state' => $state]);

        $authUrl = 'https://appcenter.intuit.com/connect/oauth2';
        $params = [
            'client_id' => $this->clientId,
            'response_type' => 'code',
            'scope' => $this->scope,
            'redirect_uri' => $this->redirectUri,
            'state' => $state
        ];

        return redirect($authUrl . '?' . http_build_query($params));
    }

    public function callback(Request $request)
    {
        try {
            // Validate state to prevent CSRF
            if ($request->state !== session('quickbooks_state')) {
                throw new \Exception('Invalid state parameter');
            }

            if ($request->has('error')) {
                throw new \Exception("QuickBooks OAuth error: " . $request->get('error'));
            }

            $code = $request->get('code');
            
            // Exchange authorization code for tokens
            $response = Http::withBasicAuth($this->clientId, $this->clientSecret)
                ->asForm()
                ->post('https://oauth.platform.intuit.com/oauth2/v1/tokens/bearer', [
                    'grant_type' => 'authorization_code',
                    'code' => $code,
                    'redirect_uri' => $this->redirectUri
                ]);

            if (!$response->successful()) {
                Log::error('QuickBooks token exchange failed', [
                    'response' => $response->json(),
                    'status' => $response->status()
                ]);
                throw new \Exception("Failed to exchange authorization code for tokens");
            }

            $tokens = $response->json();

            // Store the tokens securely
            // You should encrypt these before storing
            session([
                'quickbooks_access_token' => $tokens['access_token'],
                'quickbooks_refresh_token' => $tokens['refresh_token'],
                'quickbooks_realm_id' => $request->get('realmId')
            ]);

            return redirect()->route('dashboard')
                ->with('success', 'Successfully connected to QuickBooks!');

        } catch (\Exception $e) {
            Log::error('QuickBooks OAuth error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->route('dashboard')
                ->with('error', 'Failed to connect to QuickBooks: ' . $e->getMessage());
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

    /**
     * Create a new invoice in QuickBooks
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function createInvoice(Request $request)
    {
        try {
            // Validate request
            $validatedData = $request->validate([
                'workOrderId' => 'required|exists:work_orders,id',
                'invoiceData' => 'required|array',
            ]);
            
            $workOrder = WorkOrder::findOrFail($validatedData['workOrderId']);
            $invoiceData = $validatedData['invoiceData'];
            
            if (!Session::has('quickbooks_access_token')) {
                throw new \Exception('QuickBooks is not connected. Please connect to QuickBooks first.');
            }
            
            // Use QuickBooks service to create the invoice
            $quickbooksService = new QuickBooksService();
            $response = $quickbooksService->createInvoice($workOrder, $invoiceData);
            
            if (!$response) {
                throw new \Exception('Failed to create invoice in QuickBooks');
            }
            
            // Update the work order with invoice reference if needed
            if (isset($response['Id']) || isset($response['id']) || isset($response['invoiceId'])) {
                $invoiceId = $response['Id'] ?? $response['id'] ?? $response['invoiceId'];
                $workOrder->update([
                    'invoice_id' => $invoiceId,
                    'invoice_status' => 'created',
                    'invoice_created_at' => now(),
                ]);
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Invoice created successfully',
                'invoiceId' => $response['Id'] ?? $response['id'] ?? null,
                'invoice' => $response,
            ]);
        } catch (\Exception $e) {
            Log::error('QuickBooks invoice creation error: ' . $e->getMessage(), [
                'exception' => $e,
                'request' => $request->all(),
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to create invoice: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Connect to QuickBooks
     */
    public function connect()
    {
        // This would typically integrate with a QuickBooks SDK or service
        // For now, return a view with a message
        return view('quickbooks.connect', [
            'title' => 'Connect to QuickBooks',
            'message' => 'This would redirect to QuickBooks for authentication.'
        ]);
    }
    
    /**
     * Check connection status
     */
    public function connectionStatus()
    {
        // For demonstration, we'll assume connected
        // In a real implementation, check token validity
        return response()->json([
            'connected' => true,
            'company' => 'Your QuickBooks Company',
            'expires_at' => now()->addHours(1)->toDateTimeString()
        ]);
    }
}
