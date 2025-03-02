<?php

namespace App\Services;

use App\Models\WorkOrder;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use QuickBooksOnline\API\DataService\DataService;

// Prep Data Services
$dataService = DataService::Configure(array(
   'auth_mode' => 'oauth2',
   'ClientID' => "Client ID from the app's keys tab",
   'ClientSecret' => "Client Secret from the app's keys tab",
   'RedirectURI' => "The redirect URI provided on the Redirect URIs part under keys tab",
   'scope' => "com.intuit.quickbooks.accounting or com.intuit.quickbooks.payment",
   'baseUrl' => "Development/Production"
));

class QuickBooksService
{
    protected $apiBase;
    protected $token;

    public function __construct()
    {
        $this->apiBase = config('services.quickbooks.api_url');
        $this->token = $this->getAccessToken();
    }

    /**
     * Get the access token for QuickBooks API
     * 
     * @return string
     */
    protected function getAccessToken()
    {
        // Replace with your actual OAuth token retrieval logic
        // This is just a placeholder
        return cache()->remember('quickbooks_token', 3500, function() {
            // Your OAuth token retrieval logic here
            return 'your-token-here';
        });
    }

    /**
     * Create an invoice in QuickBooks from a work order
     * 
     * @param WorkOrder $workOrder
     * @return array
     */
    public function createInvoiceFromWorkOrder(WorkOrder $workOrder)
    {
        try {
            // Format the work order data for QuickBooks
            $invoiceData = $this->formatWorkOrderForQuickBooks($workOrder);
            
            // Make the API request to QuickBooks
            $response = Http::withToken($this->token)
                ->withHeaders(['Accept' => 'application/json'])
                ->post("{$this->apiBase}/invoice", $invoiceData);
            
            // Handle the response
            if ($response->successful()) {
                Log::info('Invoice created successfully', [
                    'work_order_id' => $workOrder->id,
                    'quickbooks_invoice_id' => $response->json('Id')
                ]);
                
                return [
                    'success' => true,
                    'invoice_id' => $response->json('Id'),
                    'data' => $response->json()
                ];
            }
            
            // Log and throw error for unsuccessful response
            Log::error('QuickBooks API error', [
                'work_order_id' => $workOrder->id,
                'status' => $response->status(),
                'response' => $response->json()
            ]);
            
            throw new \Exception('QuickBooks API error: ' . ($response->json('Fault.Error.Message') ?? 'Unknown error'));
        } catch (\Exception $e) {
            Log::error('QuickBooks invoice creation exception', [
                'work_order_id' => $workOrder->id,
                'error' => $e->getMessage()
            ]);
            
            throw $e;
        }
    }

    /**
     * Format work order data for QuickBooks
     * 
     * @param WorkOrder $workOrder
     * @return array
     */
    protected function formatWorkOrderForQuickBooks(WorkOrder $workOrder)
    {
        // Replace this with your actual QuickBooks data mapping logic
        return [
            'Line' => [
                [
                    'Amount' => $workOrder->price,
                    'DetailType' => 'SalesItemLineDetail',
                    'SalesItemLineDetail' => [
                        'ItemRef' => [
                            'value' => '1', // Replace with your item ID
                            'name' => 'Services'
                        ],
                        'Qty' => 1,
                        'UnitPrice' => $workOrder->price
                    ],
                    'Description' => $workOrder->title . ' - ' . $workOrder->description
                ]
            ],
            'CustomerRef' => [
                'value' => $this->getQuickBooksCustomerId($workOrder->customer_id)
            ]
        ];
    }

    /**
     * Get QuickBooks customer ID from your customer name/ID
     * 
     * @param string $customerName
     * @return string
     */
    protected function getQuickBooksCustomerId($customerName)
    {
        // Replace with your customer mapping logic
        // This should return the QuickBooks customer ID that corresponds to your customer
        $customerMap = [
            'Advanced Project Solutions' => 'qb-1',
            'Barrister Global Service Network' => 'qb-2',
            'DarAlIslam' => 'qb-3',
            'Field Nation' => 'qb-4',
            'Navco' => 'qb-5',
            'NEW CUSTOMER' => 'qb-6',
            'NuTech National' => 'qb-7',
            'Telaid' => 'qb-8',
        ];
        
        return $customerMap[$customerName] ?? 'qb-6'; // Default to NEW CUSTOMER
    }

    /**
     * Create an invoice in QuickBooks from a work order
     *
     * @param WorkOrder $workOrder
     * @return array
     */
    public function createInvoice(WorkOrder $workOrder)
    {
        try {
            // Log the attempt
            Log::info('Attempting to create QuickBooks invoice for work order #' . $workOrder->id);
            
            // This is a placeholder implementation
            // In a real implementation, you would:
            // 1. Connect to QuickBooks API
            // 2. Format the work order data for QuickBooks
            // 3. Create the invoice via the QuickBooks API
            // 4. Return the result with the QuickBooks invoice ID
            
            // Simulate successful invoice creation
            $invoiceId = 'QB-' . time() . '-' . $workOrder->id;
            
            return [
                'success' => true,
                'invoice_id' => $invoiceId,
                'message' => 'Invoice created successfully in QuickBooks'
            ];
            
        } catch (\Exception $e) {
            Log::error('QuickBooks invoice creation failed: ' . $e->getMessage());
            
            return [
                'success' => false,
                'message' => 'Failed to create invoice in QuickBooks: ' . $e->getMessage()
            ];
        }
    }
}
