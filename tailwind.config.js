/** @type {import('tailwindcss').Config} */
import { colors } from 'tailwindcss/colors'
export default {
    mode: 'jit',
    safelist: [
        { pattern: /w-\[.*?\]/ },
        { pattern: /cursor-.*/ },
    ],
    content: [
        "./resources/**/*.blade.php",
        "./resources/**/*.js",
        "./resources/**/*.vue",
        "./node_modules/flowbite/**/*.js",
    ],
    theme: {
        screens: {
            'sm': '640px',
            'md': '768px',
            'lg': '1024px',
            'xl': '1280px',
            '2xl': '1536px',
        },
        fontFamily: {
            sans: ['Lufga', 'sans-serif'],
            serif: ['Merriweather', 'serif'],
        },
        extend: {
            colors: {
                ...colors, // ⬅️ Важно: чтобы работали встроенные цвета Tailwind

                // ✅ Брендовые цвета (flat-схема)
                'brand-light': '#F4F5EF',        // Light shades (GREEN WHITE)
                'brand-accent': '#31d66e',       // Light accent (MOUNTAIN MEADOW)
                'brand-primary': '#FFE37C',      // Main brand color (KOURNIKOVA)
                'brand-dark': '#FF7C4F',         // Dark accent (CORAL)
                'brand-darker': '#000000',       // Dark shades (BLACK)

                // ✅ Дополнительные брендовые цвета
                'brand-red': '#d81313',
                'brand-first': '#003d88',
                'brand-second': '#007fbf',
                'brand-font-main': '#444655',
                'brand-border-grey': '#a8aabc',
            },
            spacing: {
                '128': '32rem',
                '144': '36rem',
            },
            borderRadius: {
                '4xl': '2rem',
            },
            flexBasis: {
                "1/9": "11.11%",
            }
        },
        container: {
            center: true,
        },
    },

    plugins: [
        require('@tailwindcss/forms'),
        require('@tailwindcss/typography'),
        require('@tailwindcss/aspect-ratio'),
        require('flowbite/plugin')({
            charts: true,
            forms: true,
            tooltips: true,
            datatables: true,
        }),
    ],
    darkMode: "class",
};
