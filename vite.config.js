import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

const codespace = process.env.CODESPACE_NAME;

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
    ],
    server: {
        host: '0.0.0.0',
        port: 5173,
        ...(codespace && {
            hmr: {
                host: `${codespace}-5173.app.github.dev`,
                protocol: 'wss',
                clientPort: 443,
            },
        }),
    },
});
