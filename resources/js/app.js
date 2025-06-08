import './bootstrap';
import '../css/app.css';

import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import axios from 'axios';

// Ensure axios sends cookies with every request
axios.defaults.withCredentials = true;

// Import Ziggy correctly
import { ZiggyVue } from '@/ziggy';

// CSRF Token Handling - Simplified
const refreshCsrfToken = async () => {
    try {
        // Get token from meta tag (Laravel includes this in every page)
        const metaToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        if (metaToken) {
            axios.defaults.headers.common['X-CSRF-TOKEN'] = metaToken;
            console.log('Set CSRF token from meta tag:', metaToken);
            return metaToken;
        }
        
        // Or fetch a fresh token from the server
        const response = await axios.get('/sanctum/csrf-cookie', {
            withCredentials: true
        });
        
        // Try meta tag again after fetching fresh token
        const refreshedToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        if (refreshedToken) {
            axios.defaults.headers.common['X-CSRF-TOKEN'] = refreshedToken;
            console.log('Set CSRF token after refresh:', refreshedToken);
            return refreshedToken;
        }
    } catch (error) {
        console.error('Failed to refresh CSRF token:', error);
        throw error;
    }
};

// Add request interceptor to handle CSRF token expiry - with better debugging
axios.interceptors.response.use(
    response => response,
    async error => {
        const originalRequest = error.config;
        console.log('Response error:', error.response?.status, error.message);
        
        // If error is CSRF token mismatch (419) or Unauthorized (401) and we haven't retried yet
        if ((error.response?.status === 419 || error.response?.status === 401) && !originalRequest._retry) {
            console.log('Attempting to recover from', error.response.status, 'with fresh CSRF token');
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

// Initialize CSRF token on app start
refreshCsrfToken();

const appName = window.document.getElementsByTagName('title')[0]?.innerText || 'TekDash';

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        return createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue)
            .mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});