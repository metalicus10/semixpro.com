/** @type {import('tailwindcss').Config} */
import defaultTheme from 'tailwindcss/defaultTheme'
import { colors } from 'tailwindcss/colors'
export default {
    mode: 'jit',
    safelist: [
        { pattern: /w-\[.*?\]/ },
        { pattern: /cursor-.*/ },
        // padding
        'pt-16', 'pt-20', 'pt-24', 'pt-32',
        'sm:pt-16', 'sm:pt-20', 'sm:pt-24', 'sm:pt-32',
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
            sans: ['Lufga', 'ui-sans-serif', 'system-ui'],
            serif: ['Merriweather', 'serif'],
        },
        extend: {
            colors: {
                background: {
                    DEFAULT: '#0B1019',     // или твой
                    90: '#0B1019E6',        // hex с альфой E6 = 90%
                    100: '#0B1019'          // fallback
                },

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

                primary: '#00F073',
                dark: '#0B1019',
                mid: '#111827',
                border: '#1F2937',
                text: '#F1F5F9',
                soft: '#94A3B8',
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
            },
        },
        container: {
            center: true,
            padding: '1rem',
        },
    },
    future: {
        hoverOnlyWhenSupported: true,
        respectDefaultRingColorOpacity: true,
        disableColorOpacityUtilitiesByDefault: true,
        relativeContentPathsByDefault: true,
    },
    plugins: [
        require('@tailwindcss/forms'),
        require('@tailwindcss/typography'),
        require("tailwindcss-animate"),
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
