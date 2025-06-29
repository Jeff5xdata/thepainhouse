<x-layouts.navigation>
    <div class="offline-container">
        <div class="offline-card">
            <div class="text-center">
                <h2 class="section-title mt-6">
                    You're Offline
                </h2>
                <p class="section-description">
                    Please check your internet connection and try again.
                </p>
            </div>
            <div class="mt-8 space-y-6">
                <button onclick="window.location.reload()" class="primary-button group relative w-full">
                    <span class="absolute left-0 inset-y-0 flex items-center pl-3">
                        <svg class="h-5 w-5 text-indigo-500 group-hover:text-indigo-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v4a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd" />
                        </svg>
                    </span>
                    Retry
                </button>
            </div>
        </div>
    </div>
</x-layouts.navigation> 