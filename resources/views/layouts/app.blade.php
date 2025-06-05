<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
    <head></head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'The Pain House') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Styles -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
    </head>
    <body 
        class="font-sans antialiased h-full transition-colors duration-200"
        x-data="{ 
            darkMode: localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches),
            toggleDarkMode() {
                this.darkMode = !this.darkMode;
                localStorage.theme = this.darkMode ? 'dark' : 'light';
                if (this.darkMode) {
                    document.documentElement.classList.add('dark');
                } else {
                    document.documentElement.classList.remove('dark');
                }
            }
        }"
        x-init="$watch('darkMode', val => {
            if (val) {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }
        });
        if (darkMode) {
            document.documentElement.classList.add('dark');
        }"
        :class="{ 'bg-gray-100': !darkMode, 'dark bg-gray-900': darkMode }"
    >
        <div class="min-h-screen">
            <nav class="bg-white dark:bg-gray-800 border-b border-gray-100 dark:border-gray-700">
                <!-- Primary Navigation Menu -->
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex justify-between h-16">
                        <div class="flex">
                            <!-- Logo -->
                            <div class="shrink-0 flex items-center">
                                <a href="{{ route('dashboard') }}" class="text-xl font-bold text-indigo-600 dark:text-indigo-400">
                                    The Pain House
                                </a>
                            </div>

                            <!-- Navigation Links -->
                            <div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex">
                                <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')" class="text-gray-900 dark:text-gray-100">
                                    {{ __('Dashboard') }}
                                </x-nav-link>
                                <x-nav-link :href="route('workout.planner')" :active="request()->routeIs('workout.planner')" class="text-gray-900 dark:text-gray-100">
                                    {{ __('Workout Plan') }}
                                </x-nav-link>
                                <x-nav-link :href="route('exercises.index')" :active="request()->routeIs('exercises.index')" class="text-gray-900 dark:text-gray-100">
                                    {{ __('Exercises') }}
                                </x-nav-link>
                                <x-nav-link :href="route('progress')" :active="request()->routeIs('progress')" class="text-gray-900 dark:text-gray-100">
                                    {{ __('Progress') }}
                                </x-nav-link>
                            </div>
                        </div>

                        <div class="flex items-center">
                            <!-- Dark mode toggle -->
                            <div class="mr-4">
                                <button 
                                    @click="toggleDarkMode()" 
                                    class="p-2 text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 focus:outline-none"
                                >
                                    <svg x-show="!darkMode" class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                                    </svg>
                                    <svg x-show="darkMode" class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                                    </svg>
                                </button>
                            </div>

                            <!-- Settings Dropdown -->
                            <div class="hidden sm:flex sm:items-center sm:ml-6">
                                <x-dropdown align="right" width="48">
                                    <x-slot name="trigger">
                                        <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-800 hover:text-gray-700 dark:hover:text-gray-300 focus:outline-none transition ease-in-out duration-150">
                                            <div>{{ Auth::user()->name }}</div>

                                            <div class="ml-1">
                                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                                </svg>
                                            </div>
                                        </button>
                                    </x-slot>

                                    <x-slot name="content">
                                        <x-dropdown-link :href="route('profile.edit')" class="text-gray-700 dark:text-gray-300">
                                            {{ __('Profile') }}
                                        </x-dropdown-link>

                                        <!-- Authentication -->
                                        <form method="POST" action="{{ route('logout') }}">
                                            @csrf

                                            <x-dropdown-link :href="route('logout')"
                                                    onclick="event.preventDefault();
                                                                this.closest('form').submit();" class="text-gray-700 dark:text-gray-300">
                                                {{ __('Log Out') }}
                                            </x-dropdown-link>
                                        </form>
                                    </x-slot>
                                </x-dropdown>
                            </div>
                        </div>
                    </div>
                </div>
            </nav>

            <!-- Page Heading -->
            @if (isset($header))
                <header class="bg-white dark:bg-gray-800 shadow">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endif

            <!-- Page Content -->
            <main class="py-12 bg-white dark:bg-gray-900 shadow">
                {{ $slot }}
            </main>
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
