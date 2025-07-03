// Import Vite configuration utilities and Laravel plugin
import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";

/**
 * Vite Configuration
 *
 * This file configures Vite, the build tool used for frontend assets in Laravel.
 * Vite handles CSS and JavaScript compilation, bundling, and hot module replacement.
 */
export default defineConfig({
    // Configure Vite plugins
    plugins: [
        // Laravel Vite plugin for seamless integration with Laravel
        laravel({
            // Define the main entry points for CSS and JavaScript
            input: [
                "resources/css/app.css", // Main CSS file with Tailwind imports
                "resources/js/app.js", // Main JavaScript file with Alpine.js
            ],
            // Enable hot module replacement for development
            refresh: true,
        }),
    ],
});
