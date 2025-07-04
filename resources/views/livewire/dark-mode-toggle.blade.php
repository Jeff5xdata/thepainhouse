<button
    x-data="{ darkMode: $persist(false).as('darkMode') }"
    x-init="
        $wire.darkMode = darkMode;
        $watch('darkMode', value => {
            if (value) {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }
        });
        $wire.on('dark-mode-toggled', ({ darkMode: newDarkMode }) => {
            darkMode = newDarkMode;
        });"
    wire:click="toggleDarkMode"
    type="button"
    class="relative inline-flex h-8 w-14 sm:h-6 sm:w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:ring-offset-2 dark:focus:ring-offset-gray-900"
    role="switch"
    :class="{ 'bg-gray-200': !darkMode, 'bg-indigo-600': darkMode }"
    :aria-checked="darkMode"
>
    <span class="sr-only">Toggle dark mode</span>
    <span
        :class="{ 'translate-x-0': !darkMode, 'translate-x-6 sm:translate-x-5': darkMode }"
        class="pointer-events-none relative inline-block h-7 w-7 sm:h-5 sm:w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"
    >
        <span
            :class="{ 'opacity-0 duration-100 ease-out': darkMode, 'opacity-100 duration-200 ease-in': !darkMode }"
            class="absolute inset-0 flex h-full w-full items-center justify-center transition-opacity"
            aria-hidden="true"
        >
            <!-- Sun icon -->
            <svg class="h-4 w-4 sm:h-3 sm:w-3 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
            </svg>
        </span>
        <span
            :class="{ 'opacity-100 duration-200 ease-in': darkMode, 'opacity-0 duration-100 ease-out': !darkMode }"
            class="absolute inset-0 flex h-full w-full items-center justify-center transition-opacity"
            aria-hidden="true"
        >
            <!-- Moon icon -->
            <svg class="h-4 w-4 sm:h-3 sm:w-3 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
            </svg>
        </span>
    </span>
</button> 