<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full" x-data="{ darkMode: $persist(false).as('darkMode') }" :class="{ 'dark': darkMode }">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ config('app.name', 'The Pain House') }}</title>

        <!-- PWA -->
        @laravelPWA

        <!-- Favicon -->
        <link rel="icon" type="image/png" href="{{ asset('images/hl.png') }}">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,600&display=swap" rel="stylesheet" />

        <!-- Styles -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        
        @livewireStyles

        <!-- Dark mode initialization -->
        <script>
            // On page load or when changing themes, best to add inline in `head` to avoid FOUC
            if (localStorage.darkMode === 'true' || (!('darkMode' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }
        </script>
    </head>
    <body class="antialiased h-full bg-gray-100 dark:bg-gray-900">
        <div class="min-h-full flex flex-col">
            <x-welcome />
        </div>
        
        @livewireScripts
        <script>
            document.addEventListener('livewire:initialized', () => {
                Livewire.on('dark-mode-toggled', (event) => {
                    document.documentElement.__x.$data.darkMode = event.darkMode;
                });
            });
        </script>
    </body>
</html>
