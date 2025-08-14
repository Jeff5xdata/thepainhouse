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
                        <button type="button" 
                            onclick="showTab('quit-smoking')"
                            class="tab-button border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm flex-shrink-0"
                            data-tab="quit-smoking">
                            Quit Smoking
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

                        <!-- Weight Unit Preferences -->
                        <div>
                            <h3 class="text-base sm:text-lg font-medium text-gray-900 dark:text-gray-100 mb-3 sm:mb-4">Weight Unit Preferences</h3>
                            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-3 sm:p-4">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Preferred Weight Unit
                                </label>
                                <div class="mt-1">
                                    <select wire:model.live="weightUnitPreference" 
                                        class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                        <option value="kg">Kilograms (kg)</option>
                                        <option value="lbs">Pounds (lbs)</option>
                                    </select>
                                </div>
                                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                                    This will be used as the default unit for weight tracking and workout sessions
                                </p>
                                @error('weightUnitPreference')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
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

                <!-- Quit Smoking Settings Tab -->
                <div id="quit-smoking-tab" class="tab-content hidden">
                    <div class="grid gap-4 sm:gap-6 mb-6 sm:mb-8">
                        <!-- Quit Date Settings -->
                        <div>
                            <h3 class="text-base sm:text-lg font-medium text-gray-900 dark:text-gray-100 mb-3 sm:mb-4">Quit Date</h3>
                            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-3 sm:p-4">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Quit Date
                                </label>
                                <div class="mt-1">
                                    <input type="date" 
                                        wire:model.live="quitDate"
                                        class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    >
                                </div>
                                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                                    Set the date when you quit smoking
                                </p>
                                @error('quitDate')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Smoking Cost Settings -->
                        <div>
                            <h3 class="text-base sm:text-lg font-medium text-gray-900 dark:text-gray-100 mb-3 sm:mb-4">Cost Settings</h3>
                            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-3 sm:p-4 grid gap-4 grid-cols-1 sm:grid-cols-2">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Pack Price ($)
                                    </label>
                                    <div class="mt-1">
                                        <input type="number"
                                            wire:model.live="packPrice"
                                            min="0"
                                            step="0.01"
                                            class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                        >
                                    </div>
                                    @error('packPrice')
                                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Cigarettes per Pack
                                    </label>
                                    <div class="mt-1">
                                        <input type="number"
                                            wire:model.live="cigarettesPerPack"
                                            min="1"
                                            max="50"
                                            class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                        >
                                    </div>
                                    @error('cigarettesPerPack')
                                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Daily Smoking Limit -->
                        <div>
                            <h3 class="text-base sm:text-lg font-medium text-gray-900 dark:text-gray-100 mb-3 sm:mb-4">Daily Smoking Limit</h3>
                            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-3 sm:p-4">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Maximum Cigarettes per Day
                                </label>
                                <div class="mt-1">
                                    <input type="number"
                                        wire:model.live="maxCigarettesPerDay"
                                        min="0"
                                        max="100"
                                        class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    >
                                </div>
                                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                                    Set your daily smoking limit (0 for complete quit)
                                </p>
                                @error('maxCigarettesPerDay')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Reduction Plan Settings -->
                        <div>
                            <h3 class="text-base sm:text-lg font-medium text-gray-900 dark:text-gray-100 mb-3 sm:mb-4">Reduction Plan</h3>
                            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-3 sm:p-4">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Enable 30-Day Reduction Plan
                                </label>
                                <div class="mt-1">
                                    <label class="inline-flex items-center">
                                        <input type="checkbox"
                                            wire:model.live="enableReductionPlan"
                                            class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        >
                                        <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Gradually reduce smoking over 30 days</span>
                                    </label>
                                </div>
                                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                                    This will create a gradual reduction plan to help you quit smoking
                                </p>
                                @error('enableReductionPlan')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Push Notification Settings -->
                        <div>
                            <h3 class="text-base sm:text-lg font-medium text-gray-900 dark:text-gray-100 mb-3 sm:mb-4">Push Notifications</h3>
                            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-3 sm:p-4">
                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Notification Permission
                                    </label>
                                    <div class="flex items-center space-x-4">
                                        <button type="button" 
                                            onclick="requestNotificationPermission()"
                                            class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5zM4 19h6a2 2 0 002-2V7a2 2 0 00-2-2H4a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                            </svg>
                                            Enable Notifications
                                        </button>
                                        <span id="notification-status" class="text-sm text-gray-500 dark:text-gray-400">
                                            Checking permission...
                                        </span>
                                    </div>
                                </div>
                                
                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Notification Types
                                    </label>
                                    <div class="space-y-2">
                                        <label class="inline-flex items-center">
                                            <input type="checkbox" 
                                                wire:model.live="enableSmokeReminders"
                                                class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                            >
                                            <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Smoke time reminders</span>
                                        </label>
                                        <br>
                                        <label class="inline-flex items-center">
                                            <input type="checkbox" 
                                                wire:model.live="enableDailyProgress"
                                                class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                            >
                                            <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Daily progress updates</span>
                                        </label>
                                        <br>
                                        <label class="inline-flex items-center">
                                            <input type="checkbox" 
                                                wire:model.live="enableMilestoneCelebrations"
                                                class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                            >
                                            <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Milestone celebrations</span>
                                        </label>
                                    </div>
                                </div>
                                
                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                    Push notifications will be sent to your device when it's time to smoke or when you reach important milestones.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Save Button -->
                    <div class="flex justify-end">
                        <button type="button"
                            wire:click="saveQuitSmokingSettings"
                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 dark:focus:ring-offset-gray-800 w-full sm:w-auto">
                            <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            Save Quit Smoking Settings
                        </button>
                    </div>
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

                    // Notification permission handling
                    function requestNotificationPermission() {
                        if ('Notification' in window) {
                            Notification.requestPermission().then(function(permission) {
                                updateNotificationStatus(permission);
                                
                                if (permission === 'granted') {
                                    // Subscribe to push notifications
                                    subscribeToPushNotifications();
                                }
                            });
                        } else {
                            updateNotificationStatus('not-supported');
                        }
                    }

                    function updateNotificationStatus(permission) {
                        const statusElement = document.getElementById('notification-status');
                        if (statusElement) {
                            switch(permission) {
                                case 'granted':
                                    statusElement.textContent = '✅ Notifications enabled';
                                    statusElement.className = 'text-sm text-green-600 dark:text-green-400';
                                    break;
                                case 'denied':
                                    statusElement.textContent = '❌ Notifications blocked';
                                    statusElement.className = 'text-sm text-red-600 dark:text-red-400';
                                    break;
                                case 'not-supported':
                                    statusElement.textContent = '❌ Notifications not supported';
                                    statusElement.className = 'text-sm text-red-600 dark:text-red-400';
                                    break;
                                default:
                                    statusElement.textContent = '⏳ Permission not set';
                                    statusElement.className = 'text-sm text-gray-500 dark:text-gray-400';
                            }
                        }
                    }

                    function subscribeToPushNotifications() {
                        if ('serviceWorker' in navigator) {
                            navigator.serviceWorker.ready.then(function(registration) {
                                registration.pushManager.subscribe({
                                    userVisibleOnly: true,
                                    applicationServerKey: '{{ config("app.vapid_public_key", "BEl62iUYgUivxIkv69yViEuiBIa1ORoFJVmrkgU8KtctQjBxqFhTQ9O9gCgZLN5aPwzKv7T1Og9fVrsUowNkr0") }}'
                                }).then(function(subscription) {
                                    console.log('Push notification subscription:', subscription);
                                    
                                    // Send subscription to server
                                    fetch('/api/push-subscription', {
                                        method: 'POST',
                                        headers: {
                                            'Content-Type': 'application/json',
                                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                                        },
                                        body: JSON.stringify(subscription)
                                    }).then(response => response.json())
                                    .then(data => {
                                        console.log('Subscription saved:', data);
                                    })
                                    .catch(error => {
                                        console.error('Failed to save subscription:', error);
                                    });
                                }).catch(function(error) {
                                    console.log('Failed to subscribe to push notifications:', error);
                                });
                            });
                        }
                    }

                    // Check notification status on page load
                    document.addEventListener('DOMContentLoaded', function() {
                        if ('Notification' in window) {
                            updateNotificationStatus(Notification.permission);
                        } else {
                            updateNotificationStatus('not-supported');
                        }
                    });
                </script>
            </div>
        </div>
    </div>
</div> 