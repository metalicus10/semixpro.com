/** @type {import('tailwindcss').Config} */
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
            sans: ['Graphik', 'sans-serif'],
            serif: ['Merriweather', 'serif'],
        },
        extend: {
            colors: {
                'red': '#db1313',
                'first': '#003d88',
                'second': '#007fbf',
                'accent-first': '#80102c',
                'accent-second': '#bfa5a6',
                'font-main': '#444655',
                'border-grey': '#a8aabc',
                'brand': {
                    light: '#F3E8E9',    // Soft Peach
                    accent: '#B48B74',    // Sandal
                    primary: '#EC4C00',   // Fire (Main brand color)
                    dark: '#957B78',      // Hemp
                    darker: '#1E2939'     // Mirage
                }
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
