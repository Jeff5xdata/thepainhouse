<div class="flex flex-col min-h-full">
    <nav class="bg-white dark:bg-gray-800 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex">
                    <div class="flex-shrink-0 flex items-center">
                        <span class="text-xl font-bold text-indigo-600 dark:text-indigo-400">The Pain House</span>
                    </div>
                </div>
                <div class="flex items-center">
                    <!-- Dark Mode Toggle -->
                    <div class="mr-4">
                        <livewire:dark-mode-toggle />
                    </div>
                    
                    @auth
                        <a href="{{ url('/dashboard') }}" class="text-sm text-gray-700 dark:text-gray-300 hover:text-indigo-600 dark:hover:text-indigo-400">Dashboard</a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    <main class="flex-1">
        <div class="py-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Hero Section -->
                <div class="text-center">
                    <h1 class="text-4xl tracking-tight font-extrabold text-gray-900 dark:text-white sm:text-5xl md:text-6xl">
                        <span class="block">Welcome to</span>
                        <span class="block text-indigo-600 dark:text-indigo-400">The Pain House</span>
                    </h1>
                    <p class="mt-3 max-w-md mx-auto text-base text-gray-500 dark:text-gray-400 sm:text-lg md:mt-5 md:text-xl md:max-w-3xl">
                        Track your lifts, monitor progress, and achieve your strength goals with our comprehensive weightlifting platform.
                    </p>
                    <div class="mt-5 max-w-md mx-auto sm:flex sm:justify-center md:mt-8">
                        @guest
                            <div class="rounded-md shadow">
                                <a href="{{ route('register') }}" class="w-full flex items-center justify-center px-8 py-3 border border-transparent text-base font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 dark:bg-indigo-500 dark:hover:bg-indigo-600 md:py-4 md:text-lg md:px-10">
                                    Get Started
                                </a>
                            </div>
                            <div class="mt-3 sm:mt-0 sm:ml-3">
                                <a href="{{ route('login') }}" class="w-full flex items-center justify-center px-8 py-3 border border-transparent text-base font-medium rounded-md text-indigo-600 dark:text-indigo-400 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 md:py-4 md:text-lg md:px-10">
                                    Sign In
                                </a>
                            </div>
                        @else
                            <div class="rounded-md shadow">
                                <a href="{{ route('dashboard') }}" class="w-full flex items-center justify-center px-8 py-3 border border-transparent text-base font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 dark:bg-indigo-500 dark:hover:bg-indigo-600 md:py-4 md:text-lg md:px-10">
                                    Go to Dashboard
                                </a>
                            </div>
                        @endguest
                    </div>
                </div>

                <!-- Feature Section -->
                <div class="mt-24">
                    <div class="grid grid-cols-1 gap-8 sm:grid-cols-2 lg:grid-cols-3">
                        <div class="pt-6">
                            <div class="flow-root bg-white dark:bg-gray-800 rounded-lg px-6 pb-8">
                                <div class="-mt-6">
                                    <div>
                                        <span class="inline-flex items-center justify-center p-3 bg-indigo-500 rounded-md shadow-lg">
                                            <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                            </svg>
                                        </span>
                                    </div>
                                    <h3 class="mt-8 text-lg font-medium text-gray-900 dark:text-white tracking-tight">Create Custom Workouts</h3>
                                    <p class="mt-5 text-base text-gray-500 dark:text-gray-400">
                                        Design personalized workout plans tailored to your specific goals and preferences.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="pt-6">
                            <div class="flow-root bg-white dark:bg-gray-800 rounded-lg px-6 pb-8">
                                <div class="-mt-6">
                                    <div>
                                        <span class="inline-flex items-center justify-center p-3 bg-indigo-500 rounded-md shadow-lg">
                                            <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                            </svg>
                                        </span>
                                    </div>
                                    <h3 class="mt-8 text-lg font-medium text-gray-900 dark:text-white tracking-tight">Track Progress</h3>
                                    <p class="mt-5 text-base text-gray-500 dark:text-gray-400">
                                        Monitor your performance with detailed progress tracking and analytics.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="pt-6">
                            <div class="flow-root bg-white dark:bg-gray-800 rounded-lg px-6 pb-8">
                                <div class="-mt-6">
                                    <div>
                                        <span class="inline-flex items-center justify-center p-3 bg-indigo-500 rounded-md shadow-lg">
                                            <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                            </svg>
                                        </span>
                                    </div>
                                    <h3 class="mt-8 text-lg font-medium text-gray-900 dark:text-white tracking-tight">Exercise Library</h3>
                                    <p class="mt-5 text-base text-gray-500 dark:text-gray-400">
                                        Access a comprehensive library of exercises with detailed instructions.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <footer class="bg-white dark:bg-gray-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="md:mt-0">
                <p class="text-center text-base text-gray-400 dark:text-gray-500">
                    &copy; {{ date('Y') }} The Pain House. All rights reserved.
                </p>
            </div>
        </div>
    </footer>
</div> 