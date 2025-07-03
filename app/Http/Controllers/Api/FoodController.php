<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FoodItem;
use App\Services\FatSecretApiService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

/**
 * FoodController handles API endpoints for food-related operations
 * including searching by name, barcode, and storing food items
 */
class FoodController extends Controller
{
    /**
     * Search for food items by name using the FatSecret API
     * 
     * @param Request $request The incoming HTTP request
     * @return JsonResponse JSON response with search results
     */
    public function searchByName(Request $request): JsonResponse
    {
        // Validate the request parameters
        // Query must be a string between 2 and 100 characters
        $request->validate([
            'query' => 'required|string|min:2|max:100',
        ]);

        // Get the FatSecret API service from the service container
        $fatSecretService = app(FatSecretApiService::class);
        
        // Search for food items by name using the FatSecret API
        $results = $fatSecretService->searchByName($request->query);

        // Return successful response with search results
        return response()->json([
            'success' => true,
            'data' => $results,
        ]);
    }

    /**
     * Search for food items by barcode using the FatSecret API
     * First checks local database, then falls back to external API
     * 
     * @param Request $request The incoming HTTP request
     * @return JsonResponse JSON response with food item data
     */
    public function searchByBarcode(Request $request): JsonResponse
    {
        // Validate the request parameters
        // Barcode must be a string between 8 and 20 characters
        $request->validate([
            'barcode' => 'required|string|min:8|max:20',
        ]);

        // // Add leading zero to barcode if it doesn't have 13 digits
        // // This is a common practice for UPC barcodes that need to be 13 digits
        // if (strlen($request->barcode) < 13) {
        //     $request->barcode = '0' . $request->barcode;
        // }

        // Check if the food item already exists in our local database
        // This helps avoid unnecessary API calls and improves performance
        $foodItem = FoodItem::where('barcode', $request->barcode)->first();
        if ($foodItem) {
            // Return the existing food item from database
            return response()->json([
                'success' => true,
                'data' => $foodItem,
            ]);
        }

        // If not found in database, search using the FatSecret API
        $fatSecretService = app(FatSecretApiService::class);
        $foodData = $fatSecretService->searchByBarcode($request->barcode);

        // If no food item found via API, return 404 error
        if (!$foodData) {
            return response()->json([
                'success' => false,
                'message' => 'Food item not found for this barcode.',
            ], 404);
        }

        // Return successful response with food data from API
        return response()->json([
            'success' => true,
            'data' => $foodData,
        ]);
    }

    /**
     * Store a new food item in the database
     * Validates all required nutritional information
     * 
     * @param Request $request The incoming HTTP request with food item data
     * @return JsonResponse JSON response with created food item
     */
    public function storeFoodItem(Request $request): JsonResponse
    {
        // Validate all the food item fields
        // Required fields: name, calories, protein, carbohydrates, fat
        // Optional fields: brand, barcode, serving_size, fiber, sugar, sodium, cholesterol, image_url, fatsecret_id
        $request->validate([
            'name' => 'required|string|max:255',
            'brand' => 'nullable|string|max:255',
            'barcode' => 'nullable|string|max:50|unique:food_items,barcode',
            'serving_size' => 'nullable|string|max:100',
            'calories' => 'required|numeric|min:0',
            'protein' => 'required|numeric|min:0',
            'carbohydrates' => 'required|numeric|min:0',
            'fat' => 'required|numeric|min:0',
            'fiber' => 'nullable|numeric|min:0',
            'sugar' => 'nullable|numeric|min:0',
            'sodium' => 'nullable|numeric|min:0',
            'cholesterol' => 'nullable|numeric|min:0',
            'image_url' => 'nullable|url|max:500',
            'fatsecret_id' => 'nullable|string|max:100',
        ]);

        // Create a new FoodItem record in the database
        // Uses mass assignment with all validated request data
        $foodItem = FoodItem::create($request->all());

        // Return successful response with the created food item
        // HTTP status 201 indicates successful creation
        return response()->json([
            'success' => true,
            'data' => $foodItem,
        ], 201);
    }
}
