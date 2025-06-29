<div class="fixed z-10 inset-0 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-middle bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:max-w-lg w-full">
            <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6">
                <div class="sm:flex sm:items-start">
                    <div class="w-full">
                        <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-blue-100 dark:bg-blue-900 mb-4">
                            <svg class="h-6 w-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <div class="text-center sm:text-left">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-100 mb-4">
                                Copy Workout
                            </h3>
                            <div class="space-y-4">
                                <div>
                                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-2">
                                        Copy workouts from <strong>{{ $daysOfWeek[$sourceDay] ?? $sourceDay }}</strong> to:
                                    </p>
                                </div>
                                
                                @if($isTrainer && count($clients) > 0)
                                    <div>
                                        <label for="copyTarget" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                            Copy to:
                                        </label>
                                        <div class="space-y-2">
                                            <label class="flex items-center">
                                                <input type="radio" wire:model="selectedClientId" value="" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 dark:border-gray-600">
                                                <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">My workout plan</span>
                                            </label>
                                            @foreach($clients as $client)
                                                <label class="flex items-center">
                                                    <input type="radio" wire:model="selectedClientId" value="{{ $client->id }}" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 dark:border-gray-600">
                                                    <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">{{ $client->name }}</span>
                                                </label>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                                
                                <div>
                                    <label for="targetWeek" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Target Week
                                    </label>
                                    <select wire:model="targetWeek" id="targetWeek" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                        @for($week = 1; $week <= $workoutPlan->weeks_duration; $week++)
                                            <option value="{{ $week }}">Week {{ $week }}</option>
                                        @endfor
                                    </select>
                                </div>
                                
                                <div>
                                    <label for="targetDay" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Target Day
                                    </label>
                                    <select wire:model="targetDay" id="targetDay" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                        @foreach($daysOfWeek as $day => $dayName)
                                            <option value="{{ $day }}">{{ $dayName }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                
                                <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-md p-3">
                                    <div class="flex">
                                        <div class="flex-shrink-0">
                                            <svg class="h-5 w-5 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                            </svg>
                                        </div>
                                        <div class="ml-3">
                                            <p class="text-sm text-yellow-700 dark:text-yellow-300">
                                                @if($selectedClientId)
                                                    This will add the workouts to your client's plan. Any existing workouts will be preserved. The client will be notified.
                                                @else
                                                    This will add the workouts to the target day. Any existing workouts will be preserved.
                                                @endif
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button type="button" wire:click="copyWorkout"
                    class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm dark:bg-blue-500 dark:hover:bg-blue-400"
                    wire:loading.class="opacity-50"
                    wire:loading.attr="disabled">
                    <svg wire:loading class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span wire:loading.remove>
                        @if($selectedClientId)
                            Copy to Client
                        @else
                            Copy Workout
                        @endif
                    </span>
                    <span wire:loading>Copying...</span>
                </button>
                <button type="button" wire:click="toggleCopyModal"
                    class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 dark:border-gray-600 shadow-sm px-4 py-2 bg-white dark:bg-gray-700 text-base font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800 sm:mt-0 sm:w-auto sm:text-sm">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div> 