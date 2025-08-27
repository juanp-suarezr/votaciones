import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            fontSize: {
                '8xl': '4.75rem',
                '9xl': '5.5rem',
                'xxl': '7.375rem',
                '2xxl': '8.375rem',
                '4xxl': '9.375rem',
            },
            colors: {
                primary: '#C20E1A', //Títulos, botones estado HOVER
                secondary: '#E20613', //Llamados de acción, botones, texto seleccionado
                option1: '#d3d8f7', //Fondo secciones
                option2: '#F2F2F2', //Fondo secciones 2
                parrafo: '#585857', //Párrafo, fondo dropdowns en HOVER, tab laterales, encabezados tablas
                azul: '#004884',
                naranja: '#FF6200',

            },
        },
    },

    plugins: [forms],
};
