<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use Illuminate\Http\Request;
use QuickBooksOnline\API\DataService\DataService;
use QuickBooksOnline\API\Core\Http\Serialization\XmlObjectSerializer;

class InvoiceController extends Controller
{
    private function InvoiceItem($invoice_id)
    {
        $invoice = Invoice::find($invoice_id);
        $invoice_items = InvoiceItem::where('invoice_id', $invoice_id)->get();
        return view('invoice_item', compact('invoice', 'invoice_items'));
    }

    public function createInvoiceItem($invoice_id)
    {
        return $this->InvoiceItem($invoice_id);
    }

    public function storeInvoiceItem(Request $request)
    {
        $invoice_item = new InvoiceItem();
        $invoice_item->invoice_id = $request->invoice_id;
        $invoice_item->item_name = $request->item_name;
        $invoice_item->item_quantity = $request->item_quantity;
        $invoice_item->item_price = $request->item_price;
        $invoice_item->save();
        
        // Send to QuickBooks after saving locally
        $this->sendToQuickBooks($request->invoice_id);
        
        return $this->InvoiceItem($request->invoice_id);
    }
    
    // Add your existing methods here...
    
    /**
     * Send invoice data to QuickBooks
     */
    public function sendToQuickBooks($invoice_id)
{
    try {
        $invoice = Invoice::findOrFail($invoice_id);
        $invoice_items = InvoiceItem::where('invoice_id', $invoice_id)->get();
        
        // Initialize QuickBooks DataService
        $dataService = $this->getQuickBooksDataService();
        
        // Check if dataService is valid
        if (!$dataService) {
            \Log::error('Failed to initialize QuickBooks DataService');
            return false;
        }
        
        // First check if we need to refresh the token
        if ($this->isTokenExpired()) {
            $this->refreshToken($dataService);
            // Re-initialize with new token
            $dataService = $this->getQuickBooksDataService();
        }
        
        // Prepare line items
        $lineItems = [];
        foreach ($invoice_items as $item) {
            $lineItems[] = [
                "DetailType" => "SalesItemLineDetail", 
                "Amount" => (float) $item->item_price * $item->item_quantity, 
                "Description" => $item->item_name,
                "SalesItemLineDetail" => [
                    "ItemRef" => [
                        "name" => "Services", 
                        "value" => "1"  // Use your actual Item ID from QuickBooks
                    ],
                    "Qty" => (float) $item->item_quantity,
                    "UnitPrice" => (float) $item->item_price
                ]
            ];
        }
        
        // Get or create customer in QuickBooks
        $customerRef = $this->getOrCreateCustomerReference($dataService, $invoice->customer_id);
        
        // Format data according to QuickBooks API requirements
        $invoiceData = [
            "Line" => $lineItems,
            "CustomerRef" => [
                "value" => $customerRef
            ],
            "DocNumber" => $invoice->title ?? ('INV-' . $invoice->id),
            "TxnDate" => date('Y-m-d', strtotime($invoice->date_time)),
            "DueDate" => date('Y-m-d', strtotime($invoice->date_time . ' +30 days')),
            "TotalAmt" => (float) $invoice->price,
            "BillEmail" => [
                "Address" => $invoice->customer_email ?? "customer@example.com"
            ]
        ];
        
        // Create invoice object
        $theInvoiceObj = \QuickBooksOnline\API\Facades\Invoice::create($invoiceData);
        
        // Send invoice to QuickBooks
        $resultingObj = $dataService->Add($theInvoiceObj);
        
        if ($resultingObj) {
            // Update local invoice with QuickBooks ID
            $invoice->quickbooks_id = $resultingObj->Id;
            $invoice->save();
            
            return true;
        }
        
        // Check for errors
        $error = $dataService->getLastError();
        if ($error) {
            \Log::error('QuickBooks API Error: ' . $error->getResponseBody());
        }
        
        return false;
    } catch (\Exception $e) {
        // Log the error
        \Log::error('QuickBooks API Error: ' . $e->getMessage());
        return false;
    }
}

// Helper function to check if token is expired
private function isTokenExpired() {
    // Implementation depends on how you store token expiration
    // This is just a placeholder
    return false; 
}

// Helper function to refresh token
private function refreshToken($dataService) {
    try {
        $oauth2LoginHelper = $dataService->getOAuth2LoginHelper();
        $refreshedAccessTokenObj = $oauth2LoginHelper->refreshToken();
        
        $refreshedAccessToken = $refreshedAccessTokenObj->getAccessToken();
        $refreshedRefreshToken = $refreshedAccessTokenObj->getRefreshToken();
        
        // Update stored tokens
        $this->updateTokens($refreshedAccessToken, $refreshedRefreshToken);
        
        return true;
    } catch (\Exception $e) {
        \Log::error('Failed to refresh token: ' . $e->getMessage());
        return false;
    }
}

// Helper function to get or create a customer in QuickBooks
private function getOrCreateCustomerReference($dataService, $customerId) {
    // This is a simplified example
    // You would typically query your customer database and then
    // check if that customer exists in QuickBooks
    
    // For now, returning a default value
    return "1"; // Replace with actual logic to get/create customer
}
    
    /**
     * Initialize QuickBooks DataService
     */
    private function getQuickBooksDataService()
    {
        // Get your QB credentials from config or environment variables
        $clientID = config('quickbooks.client_id');
        $clientSecret = config('quickbooks.client_secret');
        $realmID = config('quickbooks.realm_id');
        $accessToken = config('quickbooks.access_token');
        $refreshToken = config('quickbooks.refresh_token');
        $environment = config('quickbooks.environment', 'sandbox'); // or 'production'
        
        // Configure DataService
        $config = [
            'auth_mode' => 'oauth2',
            'ClientID' => $clientID,
            'ClientSecret' => $clientSecret,
            'accessTokenKey' => $accessToken,
            'refreshTokenKey' => $refreshToken,
            'QBORealmID' => $realmID,
            'baseUrl' => ($environment == 'sandbox') ? "Development" : "Production"
        ];
        
        return DataService::Configure($config);
    }
}