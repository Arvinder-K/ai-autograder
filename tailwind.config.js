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
                    primary: '#6366F1', // Indigo
                    'primary-hover': '#4F46E5',
                    'primary-active': '#4338CA',
                    accent: '#10B981', // Emerald
                    'accent-hover': '#059669',
                    violet: '#8B5CF6',
                    blue: '#3B82F6',
                    gold: '#F59E0B',
                    neutral: '#1E293B',
                },
                surface: {
                    page: '#F8FAFC', // Slate 50
                    panel: '#FFFFFF', // White
                    'panel-muted': '#F1F5F9', // Slate 100
                    inverse: '#0F172A', // Slate 900
                },
                txt: {
                    primary: '#0F172A', // Slate 900
                    secondary: '#475569', // Slate 600
                    muted: '#94A3B8', // Slate 400
                    inverse: '#FFFFFF',
                },
                border: {
                    DEFAULT: '#E2E8F0', // Slate 200
                    strong: '#CBD5E1', // Slate 300
                    subtle: '#F1F5F9', // Slate 100
                },
                status: {
                    'success-bg': 'rgba(16, 185, 129, 0.1)',
                    'success-text': '#34D399',
                    'warning-bg': 'rgba(245, 158, 11, 0.1)',
                    'warning-text': '#FBBF24',
                    'danger-bg': 'rgba(239, 68, 68, 0.1)',
                    'danger-text': '#F87171',
                    'info-bg': 'rgba(59, 130, 246, 0.1)',
                    'info-text': '#60A5FA',
                },
            },
            fontFamily: {
                heading: ['Outfit', 'Inter', ...defaultTheme.fontFamily.sans],
                base: ['Inter', ...defaultTheme.fontFamily.sans],
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
                'layout-topbar': '4.5rem',
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
                '2xl': '1.5rem',
            },
            boxShadow: {
                sm: '0 1px 2px rgba(0, 0, 0, 0.2)',
                md: '0 4px 12px rgba(0, 0, 0, 0.3)',
                lg: '0 12px 28px rgba(0, 0, 0, 0.4)',
                glow: '0 0 15px rgba(99, 102, 241, 0.5)',
            },
            transitionDuration: {
                fast: '150ms',
                base: '250ms',
                slow: '350ms',
            },
        },
    },

    plugins: [forms],
};
