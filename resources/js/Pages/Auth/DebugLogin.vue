<script setup>
import { ref, onMounted } from 'vue';
import { Head } from '@inertiajs/vue3';
import axios from 'axios';

const email = ref('');
const password = ref('');
const remember = ref(false);
const status = ref('');
const error = ref('');
const csrfToken = ref('');
const tokenSource = ref('');
const debugInfo = ref({});
const loading = ref(false);

onMounted(async () => {
    await checkCsrfToken();
});

async function checkCsrfToken() {
    try {
        // Check various sources for CSRF token
        const metaToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        const cookieToken = document.cookie.split(';')
            .map(cookie => cookie.trim())
            .find(cookie => cookie.startsWith('XSRF-TOKEN='))
            ?.split('=')[1];
        const decodedCookieToken = cookieToken ? decodeURIComponent(cookieToken) : null;
        
        debugInfo.value = {
            metaToken: metaToken || 'Not found',
            cookieToken: decodedCookieToken || 'Not found',
            inertiaToken: window?.$page?.props?.csrf_token || 'Not found',
            headers: axios.defaults.headers.common || 'Not set'
        };

        // Try to get token from different sources
        csrfToken.value = metaToken || decodedCookieToken || window?.$page?.props?.csrf_token;
        
        if (csrfToken.value) {
            tokenSource.value = metaToken ? 'Meta tag' : 
                              decodedCookieToken ? 'Cookie' : 
                              'Inertia props';
            status.value = 'CSRF token found from ' + tokenSource.value;
        } else {
            status.value = 'No CSRF token found, attempting to refresh...';
            await refreshToken();
        }
    } catch (err) {
        error.value = 'Error checking CSRF token: ' + err.message;
        console.error('CSRF check error:', err);
    }
}

async function refreshToken() {
    try {
        loading.value = true;
        status.value = 'Refreshing CSRF token...';
        
        // First try to get a fresh cookie
        await axios.get('/sanctum/csrf-cookie');
        
        // Then get the token from our debug endpoint
        const response = await axios.get('/csrf/status');
        
        if (response.data?.csrf_token) {
            csrfToken.value = response.data.csrf_token;
            status.value = 'Token refreshed successfully';
            
            // Update meta tag
            let meta = document.querySelector('meta[name="csrf-token"]');
            if (!meta) {
                meta = document.createElement('meta');
                meta.name = 'csrf-token';
                document.head.appendChild(meta);
            }
            meta.setAttribute('content', csrfToken.value);
            
            // Update axios defaults
            axios.defaults.headers.common['X-CSRF-TOKEN'] = csrfToken.value;
            axios.defaults.headers.common['X-XSRF-TOKEN'] = csrfToken.value;
            
            await checkCsrfToken(); // Refresh debug info
        } else {
            error.value = 'Failed to get new token';
        }
    } catch (err) {
        error.value = 'Error refreshing token: ' + err.message;
        console.error('Token refresh error:', err);
    } finally {
        loading.value = false;
    }
}

async function login() {
    try {
        loading.value = true;
        error.value = '';
        status.value = 'Attempting login...';
        
        // Create form data
        const formData = new FormData();
        formData.append('email', email.value);
        formData.append('password', password.value);
        formData.append('remember', remember.value ? 'on' : '');
        formData.append('_token', csrfToken.value);
        
        // Log request details (for debugging)
        console.log('Login attempt with token:', csrfToken.value);
        
        const response = await axios.post('/login', formData, {
            headers: {
                'X-CSRF-TOKEN': csrfToken.value,
                'X-Requested-With': 'XMLHttpRequest',
                'Content-Type': 'multipart/form-data'
            },
            withCredentials: true
        });
        
        status.value = 'Login successful! Redirecting...';
        setTimeout(() => {
            window.location.href = '/dashboard';
        }, 1000);
    } catch (err) {
        error.value = err.response?.data?.message || err.message;
        status.value = '';
        
        if (err.response?.status === 419) {
            status.value = 'CSRF token mismatch. Attempting to refresh...';
            await refreshToken();
        }
    } finally {
        loading.value = false;
    }
}
</script>

<template>
    <Head title="Debug Login" />

    <div class="min-h-screen flex flex-col items-center pt-6 sm:pt-0 bg-gray-900">
        <div class="w-full sm:max-w-md mt-6 px-6 py-4 bg-gray-800 shadow-md overflow-hidden sm:rounded-lg">
            <h2 class="text-2xl font-bold mb-4 text-green-400">Debug Login</h2>
            
            <!-- Status and Error Messages -->
            <div v-if="status" class="mb-4 p-2 bg-blue-900/50 text-blue-300 rounded">
                Status: {{ status }}
            </div>
            <div v-if="error" class="mb-4 p-2 bg-red-900/50 text-red-300 rounded">
                Error: {{ error }}
            </div>
            
            <!-- CSRF Debug Info -->
            <div class="mb-4 p-2 bg-gray-900/50 rounded">
                <h3 class="font-bold text-green-400">CSRF Debug Info:</h3>
                <pre class="text-xs overflow-auto text-gray-300">{{ JSON.stringify(debugInfo, null, 2) }}</pre>
            </div>
            
            <!-- Token Refresh Button -->
            <button 
                @click="refreshToken"
                :disabled="loading"
                class="mb-4 px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 disabled:opacity-50"
            >
                {{ loading ? 'Refreshing...' : 'Refresh CSRF Token' }}
            </button>
            
            <!-- Login Form -->
            <form @submit.prevent="login" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-300">Email</label>
                    <input
                        v-model="email"
                        type="email"
                        required
                        class="mt-1 block w-full rounded-md border-gray-600 bg-gray-700 text-white shadow-sm focus:border-green-500 focus:ring focus:ring-green-500/50"
                    />
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-300">Password</label>
                    <input
                        v-model="password"
                        type="password"
                        required
                        class="mt-1 block w-full rounded-md border-gray-600 bg-gray-700 text-white shadow-sm focus:border-green-500 focus:ring focus:ring-green-500/50"
                    />
                </div>
                
                <div class="flex items-center">
                    <input
                        v-model="remember"
                        type="checkbox"
                        class="rounded border-gray-600 bg-gray-700 text-green-600 shadow-sm focus:border-green-500 focus:ring focus:ring-green-500/50"
                    />
                    <label class="ml-2 block text-sm text-gray-300">Remember me</label>
                </div>
                
                <div>
                    <button
                        type="submit"
                        :disabled="loading"
                        class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 disabled:opacity-50"
                    >
                        {{ loading ? 'Processing...' : 'Log in' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>