import axios from 'axios';

// Ensure credentials are sent with every request (for cookies)
axios.defaults.withCredentials = true;

// Function to refresh CSRF token
const refreshCsrfToken = async () => {
  try {
    // First try getting token from meta tag
    const metaToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    if (metaToken) {
      axios.defaults.headers.common['X-CSRF-TOKEN'] = metaToken;
      return metaToken;
    }

    // If no meta tag token, fetch a fresh token from the server
    const response = await axios.get('/sanctum/csrf-cookie', { withCredentials: true });
    const refreshedToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    if (refreshedToken) {
      axios.defaults.headers.common['X-CSRF-TOKEN'] = refreshedToken;
      return refreshedToken;
    }
  } catch (error) {
    console.error('Failed to refresh CSRF token:', error);
    throw error;
  }
};

// Set initial CSRF token
const initialToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
if (initialToken) {
  axios.defaults.headers.common['X-CSRF-TOKEN'] = initialToken;
}

// Add response interceptor for error handling with retries
axios.interceptors.response.use(
  response => response,
  async error => {
    const originalRequest = error.config;
    
    // Handle CSRF token issues (419) or other auth issues (401)
    if ((error.response?.status === 419 || error.response?.status === 401) && !originalRequest._retry) {
      originalRequest._retry = true;
      try {
        await refreshCsrfToken();
        return axios(originalRequest);
      } catch (refreshError) {
        console.error('CSRF recovery failed:', refreshError);
        return Promise.reject(refreshError);
      }
    }
    
    return Promise.reject(error);
  }
);

export default axios;
