import '../css/app.css';
import './bootstrap';

import { createInertiaApp } from '@inertiajs/vue3';
import { createApp, h } from 'vue';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';
import AppLayout from './Layouts/AppLayout.vue';
import axios from 'axios';

// Configure axios defaults
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
axios.defaults.withCredentials = true;
axios.defaults.timeout = 30000; // 30 seconds timeout

// Function to retrieve CSRF token from multiple possible sources
const getCsrfToken = () => {
  // Try to get it from the meta tag (most common)
  const metaToken = document.head.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
  if (metaToken) return metaToken;
  
  // Try to get it from a form input (sometimes used in Laravel forms)
  const inputToken = document.querySelector('input[name="_token"]')?.value;
  if (inputToken) return inputToken;

  // Try to get it from cookies (Laravel sets this)
  const cookies = document.cookie.split(';').map(cookie => cookie.trim());
  const xsrfCookie = cookies.find(cookie => cookie.startsWith('XSRF-TOKEN='));
  if (xsrfCookie) {
    const token = xsrfCookie.split('=')[1];
    // Laravel stores it URL-encoded in the cookie
    return decodeURIComponent(token);
  }

  // Try to get it from inertia shared props if using Inertia.js
  if (window.Inertia && window.Inertia.shared && window.Inertia.shared.csrf) {
    return window.Inertia.shared.csrf;
  }
  
  console.error('CSRF token not found from any source');
  return '';
};

// Initial setup of CSRF token
const initialToken = getCsrfToken();
if (initialToken) {
  axios.defaults.headers.common['X-CSRF-TOKEN'] = initialToken;
  axios.defaults.headers.common['X-XSRF-TOKEN'] = initialToken;
} else {
  console.error('Initial CSRF token not found - API calls may fail!');
}

// Add request interceptor to ensure fresh token on every request
axios.interceptors.request.use(config => {
  // Get fresh token before each request
  const token = getCsrfToken();
  if (token) {
    config.headers['X-CSRF-TOKEN'] = token;
    config.headers['X-XSRF-TOKEN'] = token;
  }
  return config;
});

// Add response interceptor for handling errors
axios.interceptors.response.use(
  response => response,
  async error => {
    // Log detailed error information (but not for logout CSRF issues - those are expected sometimes)
    const isLogoutRequest = error.config && error.config.url && error.config.url.includes('/logout');
    if (!isLogoutRequest || error.response?.status !== 419) {
      console.error('Axios Request Failed:', {
        config: error.config,
        status: error.response ? error.response.status : 'No response',
        message: error.message
      });
    }

    // Special handling for CSRF token mismatches (419 errors)
    if (error.response && error.response.status === 419) {
      console.warn('CSRF token mismatch detected (419). Attempting to fetch fresh token...');
      
      // For logout specifically, redirect to login page instead of retrying
      if (isLogoutRequest) {
        console.info('Logout CSRF issue - redirecting to login page');
        window.location.href = '/login';
        return Promise.reject(error);
      }
      
      try {
        // First approach: try to get a new token from the current page
        let freshToken = getCsrfToken();
        
        // If that fails, try to make a GET request to refresh the token
        if (!freshToken) {
          console.info('Fetching fresh CSRF token from server...');
          try {
            const response = await fetch('/csrf-token', {
              method: 'GET',
              credentials: 'same-origin',
              headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
              }
            });
            
            if (response.ok) {
              const data = await response.json();
              if (data.csrf_token) {
                freshToken = data.csrf_token;
              }
            }
          } catch (fetchError) {
            console.error('Error fetching CSRF token:', fetchError);
          }
        }
        
        if (freshToken) {
          console.info('Got fresh token, retrying request');
          // Update the stored token
          axios.defaults.headers.common['X-CSRF-TOKEN'] = freshToken;
          axios.defaults.headers.common['X-XSRF-TOKEN'] = freshToken;
          
          // Update the failed request's token
          error.config.headers['X-CSRF-TOKEN'] = freshToken;
          error.config.headers['X-XSRF-TOKEN'] = freshToken;
          
          // Don't keep retrying the same request with a 419 error
          if (!error.config._retryCount) {
            error.config._retryCount = 1;
            return axios(error.config);
          }
        } else {
          console.error('Unable to obtain a fresh CSRF token');
        }
      } catch (e) {
        console.error('Error while trying to refresh CSRF token:', e);
      }
    }

    // Retry network errors up to 3 times
    const config = error.config;
    if (error.code === 'ERR_NETWORK' && (!config._retry || config._retry < 3)) {
      config._retry = (config._retry || 0) + 1;
      console.log(`Retrying network request (attempt ${config._retry})`);
      return new Promise(resolve => {
        setTimeout(() => resolve(axios(config)), 1000 * config._retry);
      });
    }

    return Promise.reject(error);
  }
);

// Create a simple route for refreshing CSRF token (you'll need to add this in Laravel routes)
// This assumes you have a route like Route::get('/csrf-token', function () { return response()->json(['csrf_token' => csrf_token()]); });

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
  resolve: name => {
    const pages = import.meta.glob('./Pages/**/*.vue', { eager: true });
    const page = pages[`./Pages/${name}.vue`];

    if (!page) {
      throw new Error(`Page not found: ${name}`);
    }

    return { ...page, layout: page.layout || AppLayout };
  },
  setup({ el, App, props, plugin }) {
    try {
      createApp({ render: () => h(App, props) })
        .use(plugin)
        .use(ZiggyVue)
        .mount(el);
    } catch (error) {
      console.error('Error during app setup:', error);
    }
  },
  progress: {
    delay: 250,
    color: '#90ff00',
    includeCSS: true,
    showSpinner: false,
  },
});