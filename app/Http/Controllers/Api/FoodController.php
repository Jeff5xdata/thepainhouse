<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FoodItem;
use App\Services\ChompApiService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class FoodController extends Controller
{
    public function searchByName(Request $request): JsonResponse
    {
        $request->validate([
            'query' => 'required|string|min:2|max:100',
        ]);

        $chompService = app(ChompApiService::class);
        $results = $chompService->searchByName($request->query);

        return response()->json([
            'success' => true,
            'data' => $results,
        ]);
    }

    public function searchByBarcode(Request $request): JsonResponse
    {
        $request->validate([
            'barcode' => 'required|string|min:8|max:20',
        ]);

        $chompService = app(ChompApiService::class);
        $foodData = $chompService->searchByBarcode($request->barcode);

        if (!$foodData) {
            return response()->json([
                'success' => false,
                'message' => 'Food item not found for this barcode.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $foodData,
        ]);
    }

    public function storeFoodItem(Request $request): JsonResponse
    {
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
            'chomp_id' => 'nullable|string|max:100',
        ]);

        $foodItem = FoodItem::create($request->all());

        return response()->json([
            'success' => true,
            'data' => $foodItem,
        ], 201);
    }
}
