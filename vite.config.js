import { defineConfig } from 'vite'
import tailwindcss from '@tailwindcss/vite'
import laravel from 'laravel-vite-plugin';
import "html5-qrcode";

export default defineConfig({
    plugins: [
        tailwindcss(),
        laravel({
            input: ['resources/css/app.css', 'resources/css/lufga-webfont/style.css','resources/js/app.js'],
            refresh: true,
        }),
    ],
});
