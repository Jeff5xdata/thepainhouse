<div class="py-4 sm:py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-4 sm:p-6">
                <h2 class="text-xl sm:text-2xl font-bold text-gray-900 dark:text-gray-100 mb-4 sm:mb-6">Workout Settings</h2>

                <!-- Tab Navigation -->
                <div class="border-b border-gray-200 dark:border-gray-700 mb-4 sm:mb-6">
                    <nav class="-mb-px flex space-x-4 sm:space-x-8 overflow-x-auto" aria-label="Tabs">
                        <button type="button" 
                            onclick="showTab('settings')"
                            class="tab-button border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm active flex-shrink-0"
                            data-tab="settings">
                            Settings
                        </button>
                        <button type="button" 
                            onclick="showTab('backup')"
                            class="tab-button border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm flex-shrink-0"
                            data-tab="backup">
                            Backup & Restore
                        </button>
                    </nav>
                </div>

                <!-- Settings Tab -->
                <div id="settings-tab" class="tab-content">
                    <div class="grid gap-4 sm:gap-6 mb-6 sm:mb-8">
                        <!-- Rest Timer Settings -->
                        <div>
                            <h3 class="text-base sm:text-lg font-medium text-gray-900 dark:text-gray-100 mb-3 sm:mb-4">Rest Timer</h3>
                            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-3 sm:p-4">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Default Rest Time (seconds)
                                </label>
                                <div class="mt-1">
                                    <input type="number" 
                                        wire:model.live="defaultRestTimer"
                                        min="10"
                                        max="300"
                                        class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    >
                                </div>
                                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                                    Set the default rest time between sets (10-300 seconds)
                                </p>
                                @error('defaultRestTimer')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Warm-up Settings -->
                        <div>
                            <h3 class="text-base sm:text-lg font-medium text-gray-900 dark:text-gray-100 mb-3 sm:mb-4">Warm-up Defaults</h3>
                            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-3 sm:p-4 grid gap-4 grid-cols-1 sm:grid-cols-2">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Default Warm-up Sets
                                    </label>
                                    <div class="mt-1">
                                        <input type="number"
                                            wire:model.live="defaultWarmupSets"
                                            min="0"
                                            max="5"
                                            class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                        >
                                    </div>
                                    @error('defaultWarmupSets')
                                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Default Warm-up Reps
                                    </label>
                                    <div class="mt-1">
                                        <input type="number"
                                            wire:model.live="defaultWarmupReps"
                                            min="1"
                                            max="30"
                                            class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                        >
                                    </div>
                                    @error('defaultWarmupReps')
                                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div class="sm:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Default Warm-up Weight (% of Working Weight)
                                    </label>
                                    <input type="number" wire:model="defaultWarmupWeightPercentage" min="10" max="90"
                                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    @error('defaultWarmupWeightPercentage') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Working Sets Settings -->
                        <div>
                            <h3 class="text-base sm:text-lg font-medium text-gray-900 dark:text-gray-100 mb-3 sm:mb-4">Working Sets Defaults</h3>
                            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-3 sm:p-4 grid gap-4 grid-cols-1 sm:grid-cols-2">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Default Working Sets
                                    </label>
                                    <div class="mt-1">
                                        <input type="number"
                                            wire:model.live="defaultWorkSets"
                                            min="1"
                                            max="10"
                                            class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                        >
                                    </div>
                                    @error('defaultWorkSets')
                                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Default Working Reps
                                    </label>
                                    <div class="mt-1">
                                        <input type="number"
                                            wire:model.live="defaultWorkReps"
                                            min="1"
                                            max="30"
                                            class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                        >
                                    </div>
                                    @error('defaultWorkReps')
                                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Save Button -->
                    <div class="flex justify-end">
                        <button type="button"
                            wire:click="saveSettings"
                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800 w-full sm:w-auto">
                            <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            Save Settings
                        </button>
                    </div>
                </div>

                <!-- Backup & Restore Tab -->
                <div id="backup-tab" class="tab-content hidden">
                    @livewire('workout-backup')
                </div>

                <script>
                    function showTab(tabName) {
                        // Hide all tab contents
                        document.querySelectorAll('.tab-content').forEach(content => {
                            content.classList.add('hidden');
                        });
                        
                        // Remove active class from all tab buttons
                        document.querySelectorAll('.tab-button').forEach(button => {
                            button.classList.remove('border-indigo-500', 'text-indigo-600');
                            button.classList.add('border-transparent', 'text-gray-500');
                        });
                        
                        // Show selected tab content
                        document.getElementById(tabName + '-tab').classList.remove('hidden');
                        
                        // Add active class to selected tab button
                        event.target.classList.remove('border-transparent', 'text-gray-500');
                        event.target.classList.add('border-indigo-500', 'text-indigo-600');
                    }

                    document.addEventListener('livewire:initialized', () => {
                        @this.on('settings-saved', () => {
                            // Show success notification
                            window.dispatchEvent(new CustomEvent('notify', { 
                                detail: { 
                                    type: 'success',
                                    message: 'Settings saved successfully!'
                                }
                            }));
                        });
                    });
                </script>
            </div>
        </div>
    </div>
</div> 