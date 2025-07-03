// Import Tailwind CSS default theme for extending
const defaultTheme = require("tailwindcss/defaultTheme");

/**
 * Tailwind CSS Configuration
 *
 * This file configures Tailwind CSS, the utility-first CSS framework used in this application.
 * It defines content paths, theme customizations, and plugins.
 */
/** @type {import('tailwindcss').Config} */
export default {
    // Enable dark mode using class strategy (add 'dark' class to html element)
    darkMode: "class",

    // Define content paths where Tailwind should look for classes to include in the build
    content: [
        // Laravel pagination views
        "./vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php",
        // Laravel compiled views
        "./storage/framework/views/*.php",
        // Application views
        "./resources/views/**/*.blade.php",
    ],

    // Theme customization
    theme: {
        extend: {
            // Customize font families
            fontFamily: {
                // Use Figtree as the primary sans-serif font, fallback to default fonts
                sans: ["Figtree", ...defaultTheme.fontFamily.sans],
            },
        },
    },

    // Tailwind CSS plugins for additional functionality
    plugins: [
        // Forms plugin for better form styling
        require("@tailwindcss/forms"),
    ],
};
