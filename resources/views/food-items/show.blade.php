@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-100 dark:bg-gray-900">
    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <!-- Back Button -->
            <div class="mb-6">
                <a href="{{ url()->previous() }}" class="inline-flex items-center px-4 py-2 bg-gray-600 text-white text-sm font-medium rounded-md hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Back
                </a>
            </div>

            <!-- Food Item Header -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">
                                {{ $foodItem->name }}
                            </h1>
                            @if($foodItem->brand)
                                <p class="text-lg text-gray-600 dark:text-gray-400 mb-2">
                                    {{ $foodItem->brand }}
                                </p>
                            @endif
                            @if($foodItem->serving_size)
                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                    Serving Size: {{ $foodItem->serving_size }}
                                </p>
                            @endif
                        </div>
                        @if($foodItem->image_url)
                            <div class="ml-6">
                                <img src="{{ $foodItem->image_url }}" alt="{{ $foodItem->name }}" 
                                     class="w-24 h-24 object-cover rounded-lg shadow-md">
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Nutrition Facts Panel -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6 border-b border-gray-200 dark:border-gray-700 pb-2">
                        Nutrition Facts
                    </h2>

                    <!-- Calories -->
                    <div class="mb-6">
                        <div class="flex justify-between items-center py-2 border-b border-gray-200 dark:border-gray-700">
                            <span class="text-lg font-semibold text-gray-900 dark:text-white">Calories</span>
                            <span class="text-lg font-bold text-gray-900 dark:text-white">{{ number_format($foodItem->calories, 0) }}</span>
                        </div>
                    </div>

                    <!-- Macronutrients -->
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">Macronutrients</h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="bg-blue-50 dark:bg-blue-900/20 p-4 rounded-lg">
                                <div class="text-center">
                                    <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ number_format($foodItem->protein, 1) }}g</div>
                                    <div class="text-sm text-gray-600 dark:text-gray-400">Protein</div>
                                </div>
                            </div>
                            <div class="bg-green-50 dark:bg-green-900/20 p-4 rounded-lg">
                                <div class="text-center">
                                    <div class="text-2xl font-bold text-green-600 dark:text-green-400">{{ number_format($foodItem->carbohydrates, 1) }}g</div>
                                    <div class="text-sm text-gray-600 dark:text-gray-400">Carbohydrates</div>
                                </div>
                            </div>
                            <div class="bg-yellow-50 dark:bg-yellow-900/20 p-4 rounded-lg">
                                <div class="text-center">
                                    <div class="text-2xl font-bold text-yellow-600 dark:text-yellow-400">{{ number_format($foodItem->fat, 1) }}g</div>
                                    <div class="text-sm text-gray-600 dark:text-gray-400">Fat</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Detailed Nutrition -->
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">Detailed Nutrition</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="space-y-3">
                                <div class="flex justify-between items-center py-2 border-b border-gray-100 dark:border-gray-700">
                                    <span class="text-gray-700 dark:text-gray-300">Fiber</span>
                                    <span class="font-medium text-gray-900 dark:text-white">{{ number_format($foodItem->fiber, 1) }}g</span>
                                </div>
                                <div class="flex justify-between items-center py-2 border-b border-gray-100 dark:border-gray-700">
                                    <span class="text-gray-700 dark:text-gray-300">Sugar</span>
                                    <span class="font-medium text-gray-900 dark:text-white">{{ number_format($foodItem->sugar, 1) }}g</span>
                                </div>
                                <div class="flex justify-between items-center py-2 border-b border-gray-100 dark:border-gray-700">
                                    <span class="text-gray-700 dark:text-gray-300">Sodium</span>
                                    <span class="font-medium text-gray-900 dark:text-white">{{ number_format($foodItem->sodium, 0) }}mg</span>
                                </div>
                                <div class="flex justify-between items-center py-2 border-b border-gray-100 dark:border-gray-700">
                                    <span class="text-gray-700 dark:text-gray-300">Cholesterol</span>
                                    <span class="font-medium text-gray-900 dark:text-white">{{ number_format($foodItem->cholesterol, 0) }}mg</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Daily Values (if available) -->
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">Daily Values (Based on 2,000 calorie diet)</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="space-y-3">
                                <div class="flex justify-between items-center py-2 border-b border-gray-100 dark:border-gray-700">
                                    <span class="text-gray-700 dark:text-gray-300">Calories</span>
                                    <span class="font-medium text-gray-900 dark:text-white">{{ number_format(($foodItem->calories / 2000) * 100, 1) }}%</span>
                                </div>
                                <div class="flex justify-between items-center py-2 border-b border-gray-100 dark:border-gray-700">
                                    <span class="text-gray-700 dark:text-gray-300">Protein (50g)</span>
                                    <span class="font-medium text-gray-900 dark:text-white">{{ number_format(($foodItem->protein / 50) * 100, 1) }}%</span>
                                </div>
                                <div class="flex justify-between items-center py-2 border-b border-gray-100 dark:border-gray-700">
                                    <span class="text-gray-700 dark:text-gray-300">Carbohydrates (275g)</span>
                                    <span class="font-medium text-gray-900 dark:text-white">{{ number_format(($foodItem->carbohydrates / 275) * 100, 1) }}%</span>
                                </div>
                                <div class="flex justify-between items-center py-2 border-b border-gray-100 dark:border-gray-700">
                                    <span class="text-gray-700 dark:text-gray-300">Fat (55g)</span>
                                    <span class="font-medium text-gray-900 dark:text-white">{{ number_format(($foodItem->fat / 55) * 100, 1) }}%</span>
                                </div>
                            </div>
                            <div class="space-y-3">
                                <div class="flex justify-between items-center py-2 border-b border-gray-100 dark:border-gray-700">
                                    <span class="text-gray-700 dark:text-gray-300">Fiber (28g)</span>
                                    <span class="font-medium text-gray-900 dark:text-white">{{ number_format(($foodItem->fiber / 28) * 100, 1) }}%</span>
                                </div>
                                <div class="flex justify-between items-center py-2 border-b border-gray-100 dark:border-gray-700">
                                    <span class="text-gray-700 dark:text-gray-300">Sodium (2,300mg)</span>
                                    <span class="font-medium text-gray-900 dark:text-white">{{ number_format(($foodItem->sodium / 2300) * 100, 1) }}%</span>
                                </div>
                                <div class="flex justify-between items-center py-2 border-b border-gray-100 dark:border-gray-700">
                                    <span class="text-gray-700 dark:text-gray-300">Cholesterol (300mg)</span>
                                    <span class="font-medium text-gray-900 dark:text-white">{{ number_format(($foodItem->cholesterol / 300) * 100, 1) }}%</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Serving Size Calculator -->
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">Serving Size Calculator</h3>
                        <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                            <div class="flex items-center space-x-4 mb-4">
                                <label for="quantity" class="text-sm font-medium text-gray-700 dark:text-gray-300">Quantity:</label>
                                <input type="number" id="quantity" value="1" min="0.1" step="0.1" 
                                       class="w-20 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-600 dark:border-gray-500 dark:text-white"
                                       onchange="updateNutrition(this.value)">
                                <span class="text-sm text-gray-600 dark:text-gray-400">servings</span>
                            </div>
                            
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                                <div class="text-center">
                                    <div class="font-bold text-gray-900 dark:text-white" id="calc-calories">{{ number_format($foodItem->calories, 0) }}</div>
                                    <div class="text-gray-600 dark:text-gray-400">Calories</div>
                                </div>
                                <div class="text-center">
                                    <div class="font-bold text-blue-600 dark:text-blue-400" id="calc-protein">{{ number_format($foodItem->protein, 1) }}g</div>
                                    <div class="text-gray-600 dark:text-gray-400">Protein</div>
                                </div>
                                <div class="text-center">
                                    <div class="font-bold text-green-600 dark:text-green-400" id="calc-carbs">{{ number_format($foodItem->carbohydrates, 1) }}g</div>
                                    <div class="text-gray-600 dark:text-gray-400">Carbs</div>
                                </div>
                                <div class="text-center">
                                    <div class="font-bold text-yellow-600 dark:text-yellow-400" id="calc-fat">{{ number_format($foodItem->fat, 1) }}g</div>
                                    <div class="text-gray-600 dark:text-gray-400">Fat</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Additional Information -->
                    <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">Additional Information</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-gray-600 dark:text-gray-400">
                            @if($foodItem->barcode)
                                <div>
                                    <span class="font-medium">Barcode:</span> {{ $foodItem->barcode }}
                                </div>
                            @endif
                            @if($foodItem->fatsecret_id)
                                <div>
                                    <span class="font-medium">FatSecret ID:</span> {{ $foodItem->fatsecret_id }}
                                </div>
                            @endif
                            <div>
                                <span class="font-medium">Added:</span> {{ $foodItem->created_at->format('M j, Y') }}
                            </div>
                            @if($foodItem->updated_at != $foodItem->created_at)
                                <div>
                                    <span class="font-medium">Updated:</span> {{ $foodItem->updated_at->format('M j, Y') }}
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="mt-6 flex space-x-4">
                <a href="{{ route('food-items.index') }}" 
                   class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    View All Foods
                </a>
                <a href="{{ route('nutrition') }}" 
                   class="inline-flex items-center px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                    Back to Food Tracker
                </a>
            </div>
        </div>
    </div>
</div>

<script>
function updateNutrition(quantity) {
    const baseValues = {
        calories: {{ $foodItem->calories }},
        protein: {{ $foodItem->protein }},
        carbohydrates: {{ $foodItem->carbohydrates }},
        fat: {{ $foodItem->fat }},
        fiber: {{ $foodItem->fiber }},
        sugar: {{ $foodItem->sugar }},
        sodium: {{ $foodItem->sodium }},
        cholesterol: {{ $foodItem->cholesterol }}
    };
    
    document.getElementById('calc-calories').textContent = Math.round(baseValues.calories * quantity);
    document.getElementById('calc-protein').textContent = (baseValues.protein * quantity).toFixed(1) + 'g';
    document.getElementById('calc-carbs').textContent = (baseValues.carbohydrates * quantity).toFixed(1) + 'g';
    document.getElementById('calc-fat').textContent = (baseValues.fat * quantity).toFixed(1) + 'g';
}
</script>
@endsection 