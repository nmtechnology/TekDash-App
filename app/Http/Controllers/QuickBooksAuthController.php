<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use QuickBooksOnline\API\DataService\DataService;

class QuickBooksAuthController extends Controller
{
    public function connect()
    {
        try {
            $dataService = DataService::Configure([
                'auth_mode' => 'oauth2',
                'ClientID' => config('quickbooks.client_id'),
                'ClientSecret' => config('quickbooks.client_secret'),
                
                // Use the exact same string that's in the QuickBooks Developer Console
                'RedirectURI' => config('quickbooks.redirect_uri'),
                
                'scope' => 'com.intuit.quickbooks.accounting',
                'baseUrl' => config('quickbooks.environment') == 'sandbox' ? "Development" : "Production"
            ]);
            
            $authUrl = $dataService->getOAuth2LoginHelper()->getAuthorizationCodeURL();
            
            return redirect($authUrl);
        } catch (\Exception $e) {
            \Log::error('QuickBooks Connect Error: ' . $e->getMessage());
            return redirect()->route('dashboard')->with('error', 'Error connecting to QuickBooks: ' . $e->getMessage());
        }
    }
    
    public function callback(Request $request)
    {
        if ($request->has('code')) {
            $dataService = DataService::Configure([
                'auth_mode' => 'oauth2',
                'ClientID' => config('quickbooks.client_id'),
                'ClientSecret' => config('quickbooks.client_secret'),
                
                // Use the exact same string that's in the QuickBooks Developer Console
                'RedirectURI' => config('quickbooks.redirect_uri'),
                
                'baseUrl' => config('quickbooks.environment') == 'sandbox' ? "Development" : "Production"
            ]);
            
            $oauth2LoginHelper = $dataService->getOAuth2LoginHelper();
            $accessTokenObj = $oauth2LoginHelper->exchangeAuthorizationCodeForToken($request->code);
            
            // Store these tokens in your database or .env file
            $accessToken = $accessTokenObj->getAccessToken();
            $refreshToken = $accessTokenObj->getRefreshToken();
            $realmID = $request->realmId;
            
            // Update your environment variables or save to database
            // This is just an example - implement proper secure storage
            $this->updateEnvVariables([
                'QUICKBOOKS_ACCESS_TOKEN' => $accessToken,
                'QUICKBOOKS_REFRESH_TOKEN' => $refreshToken,
                'QUICKBOOKS_REALM_ID' => $realmID,
            ]);
            
            return redirect()->route('home')->with('success', 'QuickBooks connected successfully');
        }
        
        return redirect()->route('home')->with('error', 'Failed to connect to QuickBooks');
    }
    
    private function updateEnvVariables(array $values)
    {
        $envFile = app()->environmentFilePath();
        $str = file_get_contents($envFile);
        
        foreach ($values as $key => $value) {
            $str = preg_replace("/\b{$key}=.*\b/", "{$key}={$value}", $str);
        }
        
        file_put_contents($envFile, $str);
    }
}