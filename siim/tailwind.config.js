/** @type {import('tailwindcss').Config} */
import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/**/*.blade.php',
        './resources/**/*.js',
        './resources/**/*.vue',
        './app/Livewire/**/*.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Inter', ...defaultTheme.fontFamily.sans],
                serif: ['"Source Serif Pro"', ...defaultTheme.fontFamily.serif],
            },
            colors: {
                brand: {
                    canopy: '#0F4D2A',
                    river:  '#1E7FA8',
                    gold:   '#E0A24A',
                    clay:   '#9B4A2C',
                    mist:   '#F4F1EA',
                },
                ink: {
                    deep: '#1A1F1B',
                    soft: '#5D6A60',
                },
                sentiment: {
                    positive: '#1E7FA8',
                    neutral:  '#B8B0A0',
                    negative: '#9B4A2C',
                },
            },
            boxShadow: {
                brand: '0 1px 3px rgba(15, 77, 42, 0.08)',
                'brand-lg': '0 8px 24px rgba(15, 77, 42, 0.12)',
            },
        },
    },

    plugins: [forms],
};
