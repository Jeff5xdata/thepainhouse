<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * ChompApiService - Food nutrition data service
 * 
 * Uses the Chomp API v2 endpoints:
 * - Name search: https://chompthis.com/api/v2/food/branded/name.php?api_key=API_KEY&name=NAME
 * - Barcode search: https://chompthis.com/api/v2/food/branded/barcode.php?api_key=API_KEY&code=CODE
 * 
 * NOTE: You need a valid API key from https://chompthis.com/api/ for this service to work.
 */
class ChompApiService
{
    private string $baseUrl = 'https://chompthis.com/api/v2';
    private ?string $apiKey;
    private ?string $apiUser;

    public function __construct()
    {
        $this->apiKey = config('services.chomp.api_key');
        $this->apiUser = config('services.chomp.api_user');
    }

    /**
     * Search for food products by barcode
     */
    public function searchByBarcode(string $barcode): ?array
    {
        if (empty($this->apiKey)) {
            Log::error('Chomp API key not configured');
            return null;
        }

        if (empty($this->apiUser)) {
            Log::error('Chomp API user not configured');
            return null;
        }

        try {
            $response = Http::get("{$this->baseUrl}/food/branded/barcode.php", [
                'api_key' => $this->apiKey,
                'code' => $barcode,
                'user_id' => $this->apiUser
            ]);

            if ($response->successful()) {
                return $response->json();
            }

            Log::warning('Chomp API barcode search failed', [
                'barcode' => $barcode,
                'status' => $response->status(),
                'response' => $response->body()
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('Chomp API barcode search error', [
                'barcode' => $barcode,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Search for food products by name
     */
    public function searchByName(string $query, int $limit = 10): array
    {
        if (empty($this->apiKey)) {
            Log::error('Chomp API key not configured');
            return [];
        }

        try {
            $response = Http::get("{$this->baseUrl}/food/branded/name.php", [
                'api_key' => $this->apiKey,
                'name' => $query
            ]);

            if ($response->successful()) {
                $data = $response->json();
                // The API might return a single item or an array, normalize it
                if (isset($data['items'])) {
                    return array_slice($data['items'], 0, $limit);
                } elseif (is_array($data)) {
                    return array_slice($data, 0, $limit);
                }
                return [];
            }

            Log::warning('Chomp API name search failed', [
                'query' => $query,
                'status' => $response->status(),
                'response' => $response->body()
            ]);

            return [];
        } catch (\Exception $e) {
            Log::error('Chomp API name search error', [
                'query' => $query,
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    /**
     * Get detailed information about a specific food product
     */
    public function getFoodDetails(string $foodId): ?array
    {
        if (empty($this->apiKey)) {
            Log::error('Chomp API key not configured');
            return null;
        }

        try {
            $response = Http::get("{$this->baseUrl}/food/branded/barcode.php", [
                'api_key' => $this->apiKey,
                'code' => $foodId,
                'user_id' => $this->apiUser
            ]);

            if ($response->successful()) {
                return $response->json();
            }

            Log::warning('Chomp API food details failed', [
                'food_id' => $foodId,
                'status' => $response->status(),
                'response' => $response->body()
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('Chomp API food details error', [
                'food_id' => $foodId,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Parse nutrition data from Chomp API response
     */
    public function parseNutritionData(array $chompData): array
    {
        // Helper to extract nutrient value by name
        $getNutrient = function ($name) use ($chompData) {
            if (!isset($chompData['nutrients']) || !is_array($chompData['nutrients'])) {
                return 0;
            }
            foreach ($chompData['nutrients'] as $nutrient) {
                if (isset($nutrient['name']) && strtolower($nutrient['name']) === strtolower($name)) {
                    return $nutrient['per_100g'] ?? 0;
                }
            }
            return 0;
        };

        return [
            'name' => $chompData['name'] ?? '',
            'brand' => $chompData['brand'] ?? '',
            'barcode' => $chompData['barcode'] ?? '',
            'serving_size' => $chompData['serving']['size_fulltext'] ?? ($chompData['serving']['size'] ?? ''),
            'calories' => $getNutrient('Energy'),
            'protein' => $getNutrient('Protein'),
            'carbohydrates' => $getNutrient('Carbohydrate, by difference'),
            'fat' => $getNutrient('Total lipid (fat)'),
            'fiber' => $getNutrient('Fiber, total dietary'),
            'sugar' => $getNutrient('Sugars, total including NLEA'),
            'sodium' => $getNutrient('Sodium, Na'),
            'cholesterol' => $getNutrient('Cholesterol'),
            'image_url' => $chompData['packaging_photos']['front']['display'] ?? null,
            'chomp_id' => $chompData['id'] ?? null,
        ];
    }
} 