import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    server: {
        host: '0.0.0.0', // allows LAN access to Vite dev server
        port: 5173,
        strictPort: true,
        cors: {
            origin: 'http://192.168.5.146',
            credentials: true,
        },
        hmr: {
            host: '192.168.5.146', // YOUR local IP here
        },
    },
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
    ],
});
