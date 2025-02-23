import '../css/app.css';
import './bootstrap';

import { createInertiaApp } from '@inertiajs/vue3';
import { createApp, h } from 'vue';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';
import AppLayout from './Layouts/AppLayout.vue';

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