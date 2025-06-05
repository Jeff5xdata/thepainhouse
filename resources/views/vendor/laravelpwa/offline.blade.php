<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full dark">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ config('app.name', 'The Pain House') }} - Offline</title>

        <!-- PWA -->
        @laravelPWA

        <!-- Styles -->
        @vite(['resources/css/app.css'])
    </head>
    <body class="h-full bg-gray-100 dark:bg-gray-900">
        <div class="min-h-screen flex items-center justify-center">
            <div class="text-center">
                <h1 class="text-4xl font-bold text-gray-900 dark:text-white mb-4">You're Offline</h1>
                <p class="text-lg text-gray-600 dark:text-gray-400 mb-8">Please check your internet connection and try again.</p>
                <button onclick="window.location.reload()" class="bg-indigo-600 dark:bg-indigo-500 text-white px-6 py-3 rounded-lg hover:bg-indigo-700 dark:hover:bg-indigo-600 transition-colors">
                    Try Again
                </button>
            </div>
        </div>
    </body>
</html>