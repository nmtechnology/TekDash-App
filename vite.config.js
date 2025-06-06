import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import { resolve } from 'path';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
            ],
            refresh: true,
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
            compilerOptions: {
                isCustomElement: (tag) => ['calendar-date', 'calendar-month'].includes(tag),
            },
        }),
    ],
    optimizeDeps: {
        include: ['pdfjs-dist'],
    },
    resolve: {
        alias: {
            '@': '/resources/js',
        },
    },
});
