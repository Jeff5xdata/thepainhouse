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
            $url = "{$this->baseUrl}/food/branded/barcode.php";
            $params = [
                'api_key' => $this->apiKey,
                'code' => $barcode,
                'user_id' => $this->apiUser
            ];
            
            // Log the URL and parameters being sent
            Log::info('Chomp API barcode search request', [
                'url' => $url,
                'params' => array_merge($params, ['api_key' => substr($this->apiKey, 0, 10) . '...']),
                'full_url' => $url . '?' . http_build_query($params)
            ]);
            
            $response = Http::get($url, $params);

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

        if (empty($this->apiUser)) {
            Log::error('Chomp API user not configured');
            return [];
        }

        // Add lookup delay to prevent rapid API calls
        $delay = config('services.chomp.lookup_delay', 500); // Default 500ms
        if ($delay > 0) {
            usleep($delay * 1000); // Convert milliseconds to microseconds
            Log::info('Chomp API name search delay applied', [
                'query' => $query,
                'delay_ms' => $delay
            ]);
        }

        try {
            $url = "{$this->baseUrl}/food/ingredient/search.php";
            $params = [
                'api_key' => $this->apiKey,
                'name' => $query,
                'user_id' => $this->apiUser
            ];
            
            // Log the URL and parameters being sent
            Log::info('Chomp API name search request', [
                'url' => $url,
                'params' => array_merge($params, ['api_key' => substr($this->apiKey, 0, 10) . '...']),
                'full_url' => $url . '?' . http_build_query($params)
            ]);
            
            $response = Http::get($url, $params);

            if ($response->successful()) {
                $data = $response->json();
                
                // Debug logging
                Log::info('Chomp API name search response structure', [
                    'query' => $query,
                    'data_keys' => array_keys($data),
                    'has_items' => isset($data['items']),
                    'items_count' => isset($data['items']) ? count($data['items']) : 0,
                    'is_array' => is_array($data),
                ]);
                
                // The API returns data with an 'items' key containing the array of results
                if (isset($data['items']) && is_array($data['items'])) {
                    $result = array_slice($data['items'], 0, $limit);
                    Log::info('Chomp API name search returning items', [
                        'query' => $query,
                        'returned_count' => count($result)
                    ]);
                    return $result;
                } elseif (is_array($data) && !empty($data)) {
                    // Fallback: if data is directly an array (not wrapped in 'items')
                    $result = array_slice($data, 0, $limit);
                    Log::info('Chomp API name search returning array data', [
                        'query' => $query,
                        'returned_count' => count($result)
                    ]);
                    return $result;
                }
                
                Log::warning('Chomp API name search - no valid data structure found', [
                    'query' => $query,
                    'data_structure' => gettype($data),
                    'data_keys' => is_array($data) ? array_keys($data) : 'not array'
                ]);
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

        if (empty($this->apiUser)) {
            Log::error('Chomp API user not configured');
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
            'chomp_id' => $chompData['barcode'] ?? null,
        ];
    }
} 