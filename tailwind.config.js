import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.js',
    ],

    theme: {
        extend: {
            colors: {
                brand: {
                    primary: '#193E6B',
                    'primary-hover': '#122d4f',
                    'primary-active': '#0d2038',
                    accent: '#B3A125',
                    'accent-hover': '#8f8120',
                    violet: '#7F3F98',
                    blue: '#448E9D',
                    gold: '#E9AC53',
                    neutral: '#EEE7E0',
                },
                surface: {
                    page: '#EEE7E0',
                    panel: '#FFFFFF',
                    'panel-muted': '#F5F2EE',
                    inverse: '#193E6B',
                },
                txt: {
                    primary: '#111111',
                    secondary: '#3D3D3D',
                    muted: '#6B6860',
                    inverse: '#FFFFFF',
                },
                border: {
                    DEFAULT: '#D4CEC7',
                    strong: '#B0A99F',
                    subtle: '#E8E4DF',
                },
                status: {
                    'success-bg': '#E6F4EA',
                    'success-text': '#1E5C30',
                    'warning-bg': '#FDF3DC',
                    'warning-text': '#7A4F0A',
                    'danger-bg': '#FDECEA',
                    'danger-text': '#8B1C1C',
                    'info-bg': '#E0EFF2',
                    'info-text': '#1E5F6B',
                },
            },
            fontFamily: {
                heading: ['Montserrat', ...defaultTheme.fontFamily.sans],
                base: ['Source Sans 3', ...defaultTheme.fontFamily.sans],
                special: ['Vollkorn', ...defaultTheme.fontFamily.serif],
                multilingual: ['Noto Sans', ...defaultTheme.fontFamily.sans],
            },
            fontSize: {
                caption: '0.75rem',
                'body-sm': '0.875rem',
                body: '1rem',
                'body-lg': '1.125rem',
                'title-sm': '1.375rem',
                title: '1.5rem',
                'title-lg': '2rem',
                display: '2.5rem',
                'display-lg': '3rem',
                'display-xl': '4rem',
            },
            spacing: {
                'layout-sidebar': '16rem',
                'layout-sidebar-collapsed': '4.5rem',
                'layout-topbar': '4rem',
            },
            maxWidth: {
                'layout': '90rem',
                'content': '72rem',
            },
            borderRadius: {
                sm: '0.375rem',
                md: '0.5rem',
                lg: '0.75rem',
                xl: '1rem',
                pill: '9999px',
            },
            boxShadow: {
                sm: '0 1px 2px rgba(25, 62, 107, 0.06)',
                md: '0 4px 12px rgba(25, 62, 107, 0.10)',
                lg: '0 12px 28px rgba(25, 62, 107, 0.16)',
            },
            transitionDuration: {
                fast: '120ms',
                base: '180ms',
                slow: '260ms',
            },
        },
    },

    plugins: [forms],
};
