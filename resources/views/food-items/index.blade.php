@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-100 dark:bg-gray-900">
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Food Database</h1>
                            <p class="text-gray-600 dark:text-gray-400 mt-2">
                                Browse all food items in the database
                            </p>
                        </div>
                        <a href="{{ route('nutrition') }}" 
                           class="inline-flex items-center px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            Add New Food
                        </a>
                    </div>
                </div>
            </div>

            <!-- Search and Filters -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <form method="GET" action="{{ route('food-items.index') }}" class="flex flex-col md:flex-row gap-4">
                        <div class="flex-1">
                            <label for="search" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Search Foods</label>
                            <input type="text" name="search" id="search" 
                                   value="{{ request('search') }}"
                                   placeholder="Search by name or brand..."
                                   class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        </div>
                        <div class="flex items-end">
                            <button type="submit" 
                                    class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                                Search
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Food Items Grid -->
            @if($foodItems->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($foodItems as $foodItem)
                        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg hover:shadow-md transition-shadow duration-200">
                            <div class="p-6">
                                <!-- Food Image -->
                                @if($foodItem->image_url)
                                    <div class="mb-4">
                                        <img src="{{ $foodItem->image_url }}" alt="{{ $foodItem->name }}" 
                                             class="w-full h-48 object-cover rounded-lg">
                                    </div>
                                @endif

                                <!-- Food Info -->
                                <div class="mb-4">
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-1">
                                        {{ $foodItem->name }}
                                    </h3>
                                    @if($foodItem->brand)
                                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">
                                            {{ $foodItem->brand }}
                                        </p>
                                    @endif
                                    @if($foodItem->serving_size)
                                        <p class="text-xs text-gray-500 dark:text-gray-400">
                                            {{ $foodItem->serving_size }}
                                        </p>
                                    @endif
                                </div>

                                <!-- Quick Nutrition -->
                                <div class="mb-4">
                                    <div class="flex justify-between items-center mb-2">
                                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Calories</span>
                                        <span class="text-sm font-bold text-gray-900 dark:text-white">{{ number_format($foodItem->calories, 0) }}</span>
                                    </div>
                                    <div class="grid grid-cols-3 gap-2 text-xs">
                                        <div class="text-center">
                                            <div class="font-medium text-blue-600 dark:text-blue-400">{{ number_format($foodItem->protein, 1) }}g</div>
                                            <div class="text-gray-500 dark:text-gray-400">Protein</div>
                                        </div>
                                        <div class="text-center">
                                            <div class="font-medium text-green-600 dark:text-green-400">{{ number_format($foodItem->carbohydrates, 1) }}g</div>
                                            <div class="text-gray-500 dark:text-gray-400">Carbs</div>
                                        </div>
                                        <div class="text-center">
                                            <div class="font-medium text-yellow-600 dark:text-yellow-400">{{ number_format($foodItem->fat, 1) }}g</div>
                                            <div class="text-gray-500 dark:text-gray-400">Fat</div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Action Button -->
                                <a href="{{ route('food-items.show', $foodItem) }}" 
                                   class="block w-full text-center px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors duration-200">
                                    View Nutrition Facts
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Pagination -->
                <div class="mt-8">
                    {{ $foodItems->links() }}
                </div>
            @else
                <!-- Empty State -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-12 text-center">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No food items found</h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            @if(request('search'))
                                No food items match your search criteria.
                            @else
                                Get started by adding your first food item.
                            @endif
                        </p>
                        <div class="mt-6">
                            <a href="{{ route('nutrition') }}" 
                               class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                </svg>
                                Add Food Item
                            </a>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection 