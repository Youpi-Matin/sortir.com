const defaultTheme = require('tailwindcss/defaultTheme');

/** @type {import('tailwindcss').Config} */
module.exports = {
    content: [
        "./templates/*.html.twig",
        "./assets/controllers/*.js",
    ],
    safelist: [
    ],
    theme: {
        container: {
            center: true,
            padding: '1rem'
        },
        extend: {
        },
    },
    plugins: [
    ],
}
