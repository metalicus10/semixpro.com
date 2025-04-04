import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import "html5-qrcode";
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/css/lufga-webfont/style.css','resources/js/app.js'],
            refresh: true,
        }),
        tailwindcss(),
    ],
});
