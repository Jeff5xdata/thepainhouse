<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Exercise Library</h1>
        @if(auth()->user()->can('create', \App\Models\Exercise::class))
        <button wire:click="$set('showCreateModal', true)" class="primary-button">
            <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Add Exercise
        </button>
        @endif
    </div>

    <div class="mb-6">
        <div class="relative">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </div>
            <input 
                type="text" 
                wire:model.live="search" 
                placeholder="Search exercises..." 
                class="input-field pl-10"
                autocomplete="off"
            >
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($exercises as $exercise)
            <div class="content-card">
                <div class="flex justify-between items-start">
                    <div>
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">{{ $exercise->name }}</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ $exercise->description }}</p>
                        <div class="mt-2 flex items-center space-x-2">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800 dark:bg-indigo-800 dark:text-indigo-100">
                                {{ $categories[$exercise->category] ?? ucfirst($exercise->category) }}
                            </span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-100">
                                {{ $equipment_types[$exercise->equipment] ?? ucfirst($exercise->equipment) }}
                            </span>
                        </div>
                    </div>
                    <div class="flex space-x-2">
                        @if(auth()->user()->can('update', $exercise))
                        <button wire:click="editExercise({{ $exercise->id }})" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                        </button>
                        @endif
                        @if(auth()->user()->can('delete', $exercise))
                        <button wire:click="deleteExercise({{ $exercise->id }})" wire:confirm="Are you sure you want to delete this exercise?" class="text-red-600 dark:text-red-400 hover:text-red-900 dark:hover:text-red-300">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                        </button>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    @if($showCreateModal)
        <div class="fixed z-10 inset-0 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 dark:bg-opacity-90 transition-opacity" aria-hidden="true"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <form wire:submit="{{ $editingExercise ? 'updateExercise' : 'createExercise' }}">
                        <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <div class="mb-4">
                                <label for="name" class="input-label">Name</label>
                                <input type="text" wire:model="name" id="name" class="input-field mt-1">
                                @error('name') <span class="input-error">{{ $message }}</span> @enderror
                            </div>

                            <div class="mb-4">
                                <label for="description" class="input-label">Description</label>
                                <textarea wire:model="description" id="description" rows="3" class="input-field mt-1"></textarea>
                                @error('description') <span class="input-error">{{ $message }}</span> @enderror
                            </div>

                            <div class="mb-4">
                                <label for="category" class="input-label">Category</label>
                                <select wire:model="category" id="category" class="input-field mt-1">
                                    <option value="">Select a category</option>
                                    @foreach($categories as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('category') <span class="input-error">{{ $message }}</span> @enderror
                            </div>

                            <div class="mb-4">
                                <label for="equipment" class="input-label">Equipment</label>
                                <select wire:model="equipment" id="equipment" class="input-field mt-1">
                                    <option value="">Select equipment type</option>
                                    @foreach($equipment_types as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('equipment') <span class="input-error">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                            <button type="submit" class="primary-button sm:ml-3 sm:w-auto sm:text-sm">
                                {{ $editingExercise ? 'Update' : 'Create' }} Exercise
                            </button>
                            <button type="button" wire:click="$set('showCreateModal', false)" class="secondary-button mt-3 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
