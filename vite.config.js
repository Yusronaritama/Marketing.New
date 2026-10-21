// vite.config.js
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

import vue from '@vitejs/plugin-vue';

export default defineConfig({
    plugins: [
        vue(),
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/js/register-step.js',
                'resources/js/register-loader.js'
            ],
            refresh: true,
        }),
    ],
    build: {
        rollupOptions: {
            output: {
                manualChunks: undefined,
            }
        }
    }
});