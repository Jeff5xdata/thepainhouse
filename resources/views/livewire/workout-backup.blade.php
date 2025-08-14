<div class="py-6">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-6">Backup & Restore</h2>

                <div class="grid gap-6 mb-8">
                    <!-- Create Backup Section -->
                    <div>
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Create Backup</h3>
                        <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                            <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                                Create a comprehensive backup of all your workout data including:
                            </p>
                            <ul class="text-sm text-gray-600 dark:text-gray-400 mb-4 space-y-1">
                                <li>• Workout plans with exercises and schedules</li>
                                <li>• Workout sessions and exercise sets</li>
                                <li>• Workout settings and preferences</li>
                                <li>• Food tracker data and nutrition logs</li>
                                <li>• Body weight and measurement tracking</li>
                                @if(auth()->user()->is_trainer)
                                    <li>• Client workout plans and progress data</li>
                                @endif
                            </ul>
                            <button type="button"
                                wire:click="createBackup"
                                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 dark:focus:ring-offset-gray-800">
                                <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                Download Backup
                            </button>
                        </div>
                    </div>

                    <!-- Restore Backup Section -->
                    <div>
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Restore Backup</h3>
                        <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                            <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                                Upload a backup file to restore your workout data. This will overwrite existing data based on your settings.
                            </p>
                            
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Select Backup File
                                    </label>
                                    <input type="file" 
                                        wire:model="backupFile"
                                        accept=".json"
                                        class="block w-full text-sm text-gray-500 dark:text-gray-400
                                               file:mr-4 file:py-2 file:px-4
                                               file:rounded-full file:border-0
                                               file:text-sm file:font-semibold
                                               file:bg-indigo-50 file:text-indigo-700
                                               hover:file:bg-indigo-100
                                               dark:file:bg-gray-600 dark:file:text-gray-300
                                               dark:hover:file:bg-gray-500">
                                    @error('backupFile')
                                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <button type="button"
                                    wire:click="previewRestore"
                                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800">
                                    <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                                    </svg>
                                    Preview & Restore
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Restore Modal -->
                @if($showRestoreModal)
                <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" id="restore-modal">
                    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white dark:bg-gray-800">
                        <div class="mt-3">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Restore Backup</h3>
                            
                            <!-- Backup Preview -->
                            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4 mb-4">
                                <h4 class="font-medium text-gray-900 dark:text-gray-100 mb-2">Backup Details</h4>
                                <div class="space-y-2 text-sm text-gray-600 dark:text-gray-400">
                                    <div><strong>Version:</strong> {{ $restorePreview['version'] ?? 'Unknown' }}</div>
                                    <div><strong>Created:</strong> {{ $restorePreview['created_at'] ?? 'Unknown' }}</div>
                                    <div><strong>User:</strong> {{ $restorePreview['user']['name'] ?? 'Unknown' }}</div>
                                    <div><strong>Workout Plans:</strong> {{ $restorePreview['workout_plans_count'] ?? 0 }}</div>
                                    <div><strong>Workout Sessions:</strong> {{ $restorePreview['workout_sessions_count'] ?? 0 }}</div>
                                    <div><strong>Exercise Sets:</strong> {{ $restorePreview['exercise_sets_count'] ?? 0 }}</div>
                                    <div><strong>Settings:</strong> {{ $restorePreview['has_settings'] ? 'Yes' : 'No' }}</div>
                                    
                                    @if($restorePreview['has_food_tracker'])
                                        <div class="border-t pt-2 mt-2">
                                            <div><strong>Food Logs:</strong> {{ $restorePreview['food_logs_count'] ?? 0 }}</div>
                                            <div><strong>Food Items:</strong> {{ $restorePreview['food_items_count'] ?? 0 }}</div>
                                        </div>
                                    @endif
                                    
                                    @if($restorePreview['has_body_tracking'])
                                        <div class="border-t pt-2 mt-2">
                                            <div><strong>Weight Measurements:</strong> {{ $restorePreview['weight_measurements_count'] ?? 0 }}</div>
                                            <div><strong>Body Measurements:</strong> {{ $restorePreview['body_measurements_count'] ?? 0 }}</div>
                                        </div>
                                    @endif
                                    
                                    @if($restorePreview['is_trainer'] && $restorePreview['has_client_data'])
                                        <div class="border-t pt-2 mt-2">
                                            <div><strong>Clients:</strong> {{ $restorePreview['clients_count'] ?? 0 }}</div>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <!-- Restore Options -->
                            <div class="space-y-3 mb-4">
                                <h4 class="font-medium text-gray-900 dark:text-gray-100">Restore Options</h4>
                                
                                <label class="flex items-center">
                                    <input type="checkbox" 
                                        wire:model="restoreOptions.overwrite_existing"
                                        class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                    <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Overwrite existing workout plans</span>
                                </label>
                                
                                <label class="flex items-center">
                                    <input type="checkbox" 
                                        wire:model="restoreOptions.include_settings"
                                        class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                    <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Include workout settings</span>
                                </label>
                                
                                <label class="flex items-center">
                                    <input type="checkbox" 
                                        wire:model="restoreOptions.include_history"
                                        class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                    <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Include workout history</span>
                                </label>
                                
                                @if($restorePreview['has_food_tracker'])
                                    <label class="flex items-center">
                                        <input type="checkbox" 
                                            wire:model="restoreOptions.include_food_tracker"
                                            class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                        <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Include food tracker data</span>
                                    </label>
                                @endif
                                
                                @if($restorePreview['has_body_tracking'])
                                    <label class="flex items-center">
                                        <input type="checkbox" 
                                            wire:model="restoreOptions.include_body_tracking"
                                            class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                        <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Include body tracking data</span>
                                    </label>
                                @endif
                                
                                @if($restorePreview['is_trainer'] && $restorePreview['has_client_data'])
                                    <label class="flex items-center">
                                        <input type="checkbox" 
                                            wire:model="restoreOptions.include_client_data"
                                            class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                        <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Include client data</span>
                                    </label>
                                @endif
                            </div>

                            <!-- Warning -->
                            <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-3 mb-4">
                                <div class="flex">
                                    <svg class="h-5 w-5 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z" />
                                    </svg>
                                    <div class="ml-3">
                                        <p class="text-sm text-yellow-800 dark:text-yellow-200">
                                            <strong>Warning:</strong> This will overwrite existing data. Make sure you have a current backup before proceeding.
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="flex space-x-3">
                                <button type="button"
                                    wire:click="restoreBackup"
                                    class="flex-1 inline-flex justify-center items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                    <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                                    </svg>
                                    Restore Backup
                                </button>
                                <button type="button"
                                    wire:click="cancelRestore"
                                    class="flex-1 inline-flex justify-center items-center px-4 py-2 border border-gray-300 dark:border-gray-600 text-sm font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    Cancel
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div> 