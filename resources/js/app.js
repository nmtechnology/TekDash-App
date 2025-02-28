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

// Add interceptor for request failures
axios.interceptors.response.use(
    response => response,
    error => {
        // Log detailed error information
        console.error('Axios Request Failed:', {
            config: error.config,
            status: error.response ? error.response.status : 'No response',
            message: error.message
        });

        // Retry network errors up to 3 times
        const config = error.config;
        if (error.code === 'ERR_NETWORK' && !config._retry) {
            config._retry = (config._retry || 0) + 1;
            if (config._retry <= 3) {
                console.log(`Retrying request (attempt ${config._retry})`);
                return new Promise(resolve => {
                    setTimeout(() => resolve(axios(config)), 1000 * config._retry);
                });
            }
        }

        return Promise.reject(error);
    }
);

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