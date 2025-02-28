import axios from 'axios';

/**
 * Network Diagnostics Utility
 * Helps diagnose network-related issues with API requests
 */
class NetworkDiagnostics {
  // Store diagnostics results
  results = {
    baseUrl: window.location.origin,
    internetConnected: null,
    apiAccessible: null,
    csrfTokenPresent: null,
    sessionValid: null,
    lastError: null,
    tests: []
  };

  /**
   * Run all diagnostics tests
   */
  async runAll() {
    try {
      this.results.internetConnected = await this.checkInternetConnection();
      this.results.apiAccessible = await this.checkApiAccessibility();
      this.results.csrfTokenPresent = this.checkCsrfToken();
      this.results.sessionValid = await this.checkSession();
      
      return this.results;
    } catch (error) {
      this.results.lastError = error.message;
      return this.results;
    }
  }

  /**
   * Check internet connection by pinging a public service
   */
  async checkInternetConnection() {
    try {
      const testUrl = 'https://www.google.com/generate_204';
      const startTime = performance.now();
      
      const response = await fetch(testUrl, { 
        method: 'HEAD',
        mode: 'no-cors',
        cache: 'no-store',
      });
      
      const endTime = performance.now();
      const pingTime = Math.round(endTime - startTime);
      
      this.results.tests.push({
        name: 'Internet Connection',
        status: 'success',
        message: `Connected (ping: ${pingTime}ms)`
      });
      
      return true;
    } catch (error) {
      this.results.tests.push({
        name: 'Internet Connection',
        status: 'error',
        message: 'Failed to connect to internet'
      });
      
      return false;
    }
  }

  /**
   * Check API accessibility by hitting the server health endpoint
   */
  async checkApiAccessibility() {
    try {
      const startTime = performance.now();
      
      // Try to ping the local API health endpoint
      const response = await axios.get('/api/health', {
        timeout: 5000,
        headers: {
          'Cache-Control': 'no-cache',
          'Pragma': 'no-cache'
        }
      });
      
      const endTime = performance.now();
      const apiTime = Math.round(endTime - startTime);
      
      if (response.status === 200) {
        this.results.tests.push({
          name: 'API Accessibility',
          status: 'success',
          message: `API accessible (${apiTime}ms)`,
          details: response.data
        });
        return true;
      } else {
        this.results.tests.push({
          name: 'API Accessibility',
          status: 'warning',
          message: `API returned unexpected status: ${response.status}`,
          details: response.data
        });
        return false;
      }
    } catch (error) {
      // Try a simpler fetch as fallback if axios fails
      try {
        const response = await fetch('/api/health', { 
          method: 'GET',
          cache: 'no-store',
          headers: {
            'Accept': 'application/json'
          }
        });
        
        if (response.ok) {
          this.results.tests.push({
            name: 'API Accessibility (Fallback)',
            status: 'success',
            message: 'API accessible via fetch fallback'
          });
          return true;
        }
      } catch (fetchError) {
        // Both attempts failed
      }
      
      this.results.tests.push({
        name: 'API Accessibility',
        status: 'error',
        message: `Failed to connect to API: ${error.message}`,
        error: error
      });
      
      return false;
    }
  }

  /**
   * Check if CSRF token is present in the page
   */
  checkCsrfToken() {
    const metaToken = document.querySelector('meta[name="csrf-token"]');
    const tokenValue = metaToken ? metaToken.getAttribute('content') : null;
    
    if (tokenValue) {
      this.results.tests.push({
        name: 'CSRF Token',
        status: 'success',
        message: 'CSRF token is present'
      });
      return true;
    } else {
      this.results.tests.push({
        name: 'CSRF Token',
        status: 'error',
        message: 'CSRF token is missing'
      });
      return false;
    }
  }

  /**
   * Check if the user session is valid
   */
  async checkSession() {
    try {
      // Try to access a simple endpoint that requires authentication
      const response = await axios.get('/api/user', {
        timeout: 5000
      });
      
      if (response.status === 200 && response.data) {
        this.results.tests.push({
          name: 'User Session',
          status: 'success',
          message: 'Session is valid'
        });
        return true;
      } else {
        this.results.tests.push({
          name: 'User Session',
          status: 'warning',
          message: 'Session check returned unexpected response'
        });
        return false;
      }
    } catch (error) {
      // Check if it's an authentication error (401)
      if (error.response && error.response.status === 401) {
        this.results.tests.push({
          name: 'User Session',
          status: 'error',
          message: 'Session is invalid or expired'
        });
      } else {
        this.results.tests.push({
          name: 'User Session',
          status: 'error',
          message: `Failed to check session: ${error.message}`
        });
      }
      return false;
    }
  }
}

export default new NetworkDiagnostics();
