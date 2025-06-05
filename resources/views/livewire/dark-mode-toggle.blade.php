<button
    wire:click="toggleDarkMode"
    type="button"
    class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:ring-offset-2 dark:focus:ring-offset-gray-900"
    role="switch"
    :class="{ 'bg-gray-200': !$wire.darkMode, 'bg-indigo-600': $wire.darkMode }"
    :aria-checked="$wire.darkMode"
>
    <span class="sr-only">Toggle dark mode</span>
    <span
        :class="{ 'translate-x-0': !$wire.darkMode, 'translate-x-5': $wire.darkMode }"
        class="pointer-events-none relative inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"
    >
        <span
            :class="{ 'opacity-0 duration-100 ease-out': $wire.darkMode, 'opacity-100 duration-200 ease-in': !$wire.darkMode }"
            class="absolute inset-0 flex h-full w-full items-center justify-center transition-opacity"
            aria-hidden="true"
        >
            <!-- Sun icon -->
            <svg class="h-3 w-3 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
            </svg>
        </span>
        <span
            :class="{ 'opacity-100 duration-200 ease-in': $wire.darkMode, 'opacity-0 duration-100 ease-out': !$wire.darkMode }"
            class="absolute inset-0 flex h-full w-full items-center justify-center transition-opacity"
            aria-hidden="true"
        >
            <!-- Moon icon -->
            <svg class="h-3 w-3 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
            </svg>
        </span>
    </span>
</button> 