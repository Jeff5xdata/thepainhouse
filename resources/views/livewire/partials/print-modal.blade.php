<div class="fixed z-10 inset-0 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-middle bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:max-w-4xl w-full">
            <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6">
                <div class="sm:flex sm:items-start">
                    <div class="w-full">
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                                {{ $workoutPlan->name }}
                            </h3>
                            <button onclick="window.print()" class="print:hidden inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                                <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                                </svg>
                                Print
                            </button>
                        </div>
                        @if($workoutPlan->description)
                        <p class="text-gray-600 dark:text-gray-400 mb-6">{{ $workoutPlan->description }}</p>
                        @endif
                        <div class="space-y-8">
                            @php
                                $currentWeek = \Carbon\Carbon::now()->isoWeek();
                                $startWeek = $currentWeek;
                            @endphp
                            @for($week = $startWeek; $week < $startWeek + $workoutPlan->weeks_duration; $week++)
                                <div class="border dark:border-gray-700 rounded-lg p-4">
                                    <h4 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">ISO Week {{ $week }}</h4>
                                    <div class="space-y-6">
                                        @foreach($daysOfWeek as $day => $dayName)
                                            @if(isset($weekSchedule[$day]) && count($weekSchedule[$day]) > 0)
                                                <div>
                                                    <h5 class="text-md font-medium text-gray-800 dark:text-gray-200 mb-2">{{ $dayName }}</h5>
                                                    <div class="space-y-3">
                                                        @foreach(collect($weekSchedule[$day])->groupBy('exercise_id') as $exerciseId => $items)
                                                            <div class="pl-4 border-l-2 border-indigo-500 dark:border-indigo-400">
                                                                <div class="font-medium text-gray-900 dark:text-gray-100">
                                                                    {{ $items->first()->exercise->name }}
                                                                </div>
                                                                <div class="text-sm text-gray-600 dark:text-gray-400">
                                                                    @foreach($items->sortBy('order_in_day') as $scheduleItem)
                                                                        @php
                                                                            $setDetails = $scheduleItem->formatted_set_details;
                                                                            $warmupSets = collect($setDetails)->where('is_warmup', true);
                                                                            $workingSets = collect($setDetails)->where('is_warmup', false);
                                                                        @endphp
                                                                        <div class="mb-1">
                                                                            @if($warmupSets->count() > 0)
                                                                                <div class="text-yellow-600 dark:text-yellow-400">
                                                                                    Warmup: {{ $warmupSets->count() }}×{{ $warmupSets->first()['reps'] ?? 0 }} reps
                                                                                </div>
                                                                            @endif
                                                                            <div>
                                                                                {{ $workingSets->count() }} sets × {{ $workingSets->first()['reps'] ?? 0 }} reps
                                                                            </div>
                                                                        </div>
                                                                    @endforeach
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            @endif
                                        @endforeach
                                    </div>
                                </div>
                            @endfor
                        </div>
                    </div>
                </div>
            </div>
            <div class="print:hidden bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button type="button" wire:click="togglePrintModal"
                    class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 dark:border-gray-600 shadow-sm px-4 py-2 bg-white dark:bg-gray-700 text-base font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800 sm:mt-0 sm:w-auto sm:text-sm">
                    Close
                </button>
            </div>
        </div>
    </div>
</div> 