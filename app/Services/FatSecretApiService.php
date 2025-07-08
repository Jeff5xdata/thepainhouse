<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * FatSecretApiService - Food nutrition data service
 * 
 * Uses the FatSecret Platform API endpoints:
 * - Name search: https://platform.fatsecret.com/rest/server.api (method=foods.search)
 * - Barcode search: https://platform.fatsecret.com/rest/server.api (method=food.find_id_for_barcode)
 * - Food details: https://platform.fatsecret.com/rest/server.api (method=food.get.v4)
 * 
 * NOTE: You need valid OAuth credentials from https://platform.fatsecret.com/ for this service to work.
 */
class FatSecretApiService
{
    private string $baseUrl = 'https://platform.fatsecret.com/rest/server.api';
    private string $oauthUrl = 'https://oauth.fatsecret.com/connect/token';
    private ?string $consumerKey;
    private ?string $consumerSecret;
    private ?string $accessToken;
    private ?int $tokenExpiresAt;

    public function __construct()
    {
        $this->consumerKey = config('services.fatsecret.consumer_key');
        $this->consumerSecret = config('services.fatsecret.consumer_secret');
        $this->accessToken = config('services.fatsecret.access_token');
        $this->tokenExpiresAt = config('services.fatsecret.token_expires_at');
    }

    /**
     * Search for food products by barcode using the dedicated barcode endpoint
     * 
     * Uses the food.find_id_for_barcode endpoint as specified in the FatSecret API documentation:
     * https://platform.fatsecret.com/docs/v1/food.find_id_for_barcode
     */
    public function searchByBarcode(string $barcode): ?array
    {
        if (empty($this->consumerKey) || empty($this->consumerSecret)) {
            Log::error('FatSecret API credentials not configured');
            return null;
        }

        try {
            // Format barcode as GTIN-13 (13-digit number)
            $formattedBarcode = $this->formatBarcodeAsGtin13($barcode);
            
            // Use the dedicated barcode endpoint
            $params = [
                'method' => 'food.find_id_for_barcode',
                'barcode' => $formattedBarcode,
                'format' => 'json'
            ];
            
            $response = $this->makeAuthenticatedRequest($params);

            if ($response && isset($response['food_id'])) {
                $foodId = $response['food_id']['value'] ?? $response['food_id'];
                
                Log::info('FatSecret API barcode search successful', [
                    'barcode' => $barcode,
                    'formatted_barcode' => $formattedBarcode,
                    'food_id' => $foodId
                ]);
                
                // Get detailed food information using the food_id
                return $this->getFoodDetails($foodId);
            }

            Log::warning('FatSecret API barcode search - no results found', [
                'barcode' => $barcode,
                'formatted_barcode' => $formattedBarcode
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('FatSecret API barcode search error', [
                'barcode' => $barcode,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Format barcode as GTIN-13 (13-digit number)
     * 
     * According to FatSecret API docs, barcodes must be specified as GTIN-13 numbers:
     * - UPC-A, EAN-13 and EAN-8 barcodes may be specified
     * - UPC-E barcodes should be converted to their UPC-A equivalent
     * - 13-digit number filled in with zeros for the spaces to the left
     */
    public function formatBarcodeAsGtin13(string $barcode): string
    {
        // Remove any non-digit characters
        $barcode = preg_replace('/[^0-9]/', '', $barcode);
        
        // Handle different barcode formats
        $length = strlen($barcode);
        
        if ($length === 13) {
            // Already GTIN-13/EAN-13
            return $barcode;
        } elseif ($length === 12) {
            // UPC-A - add leading zero to make it GTIN-13
            return '0' . $barcode;
        } elseif ($length === 8) {
            // EAN-8 - add 5 leading zeros to make it GTIN-13
            return str_pad($barcode, 13, '0', STR_PAD_LEFT);
        } elseif ($length < 13) {
            // Pad with leading zeros to make it 13 digits
            return str_pad($barcode, 13, '0', STR_PAD_LEFT);
        } else {
            // If longer than 13 digits, truncate to 13
            return substr($barcode, 0, 13);
        }
    }

    /**
     * Search for food products by name
     */
    public function searchByName(string $query, int $limit = 10): array
    {
        if (empty($this->consumerKey) || empty($this->consumerSecret)) {
            Log::error('FatSecret API credentials not configured');
            return [];
        }

        // Add lookup delay to prevent rapid API calls
        $delay = config('services.fatsecret.lookup_delay', 500); // Default 500ms
        if ($delay > 0) {
            usleep($delay * 1000); // Convert milliseconds to microseconds
            Log::info('FatSecret API name search delay applied', [
                'query' => $query,
                'delay_ms' => $delay
            ]);
        }

        try {
            $params = [
                'method' => 'foods.search',
                'search_expression' => $query,
                'format' => 'json',
                'max_results' => $limit
            ];
            
            $response = $this->makeAuthenticatedRequest($params);

            // Debug logging to see response structure
            Log::info('FatSecret API response structure', [
                'query' => $query,
                'response' => $response,
                'response_keys' => $response ? array_keys($response) : 'null',
                'has_foods' => $response && isset($response['foods']),
                'foods_keys' => $response && isset($response['foods']) ? array_keys($response['foods']) : 'no foods',
                'has_food' => $response && isset($response['foods']['food']),
                'food_count' => $response && isset($response['foods']['food']) ? (is_array($response['foods']['food']) ? count($response['foods']['food']) : 1) : 0
            ]);

            if ($response && isset($response['foods']['food'])) {
                $foods = $response['foods']['food'];
                
                // Ensure we have an array
                if (!is_array($foods)) {
                    $foods = [$foods];
                }
                
                Log::info('FatSecret API name search returning items', [
                    'query' => $query,
                    'returned_count' => count($foods)
                ]);
                
                return $foods;
            }

            Log::warning('FatSecret API name search - no results found', [
                'query' => $query
            ]);

            return [];
        } catch (\Exception $e) {
            Log::error('FatSecret API name search error', [
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
        if (empty($this->consumerKey) || empty($this->consumerSecret)) {
            Log::error('FatSecret API credentials not configured');
            return null;
        }

        try {
            $params = [
                'method' => 'food.get.v4',
                'food_id' => $foodId,
                'format' => 'json'
            ];
            
            $response = $this->makeAuthenticatedRequest($params);

            // Add detailed logging to debug the response
            Log::info('FatSecret API getFoodDetails response', [
                'food_id' => $foodId,
                'response' => $response,
                'response_keys' => $response ? array_keys($response) : 'null',
                'has_food' => $response && isset($response['food']),
                'food_keys' => $response && isset($response['food']) ? array_keys($response['food']) : 'no food'
            ]);

            if ($response && isset($response['food'])) {
                return $response['food'];
            }

            Log::warning('FatSecret API food details failed', [
                'food_id' => $foodId,
                'response' => $response
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('FatSecret API food details error', [
                'food_id' => $foodId,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Get OAuth 2.0 access token using client credentials flow
     */
    private function getAccessToken(): ?string
    {
        if (empty($this->consumerKey) || empty($this->consumerSecret)) {
            Log::error('FatSecret API credentials not configured');
            return null;
        }

        // Check if we have a valid token
        if ($this->accessToken && $this->tokenExpiresAt && time() < $this->tokenExpiresAt) {
            return $this->accessToken;
        }

        try {
            $response = Http::withBasicAuth($this->consumerKey, $this->consumerSecret)
                ->asForm()
                ->post($this->oauthUrl, [
                    'grant_type' => 'client_credentials',
                    'scope' => 'premier barcode'
                ]);

            if ($response->successful()) {
                $tokenData = $response->json();
                
                $this->accessToken = $tokenData['access_token'];
                $this->tokenExpiresAt = time() + ($tokenData['expires_in'] ?? 86400);
                
                Log::info('FatSecret API access token obtained', [
                    'expires_in' => $tokenData['expires_in'] ?? 86400,
                    'token_type' => $tokenData['token_type'] ?? 'Bearer'
                ]);
                
                return $this->accessToken;
            }

            Log::error('FatSecret API token request failed', [
                'status' => $response->status(),
                'response' => $response->body()
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('FatSecret API token request error', [
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Make an authenticated request to the FatSecret API
     */
    private function makeAuthenticatedRequest(array $params): ?array
    {
        try {
            // Get or refresh access token
            $accessToken = $this->getAccessToken();
            if (!$accessToken) {
                Log::error('FatSecret API - unable to obtain access token');
                return null;
            }

            $headers = [
                'Authorization' => 'Bearer ' . $accessToken
            ];

            // Log the request (without sensitive data)
            Log::info('FatSecret API request', [
                'url' => $this->baseUrl,
                'full_url' => $this->baseUrl . '?' . http_build_query($params),
                'method' => $params['method'] ?? 'unknown',
                'params' => array_diff_key($params, ['oauth_consumer_key' => '', 'oauth_signature' => ''])
            ]);

            $response = Http::withHeaders($headers)
                ->asForm()
                ->post($this->baseUrl, $params);

            if ($response->successful()) {
                return $response->json();
            }

            Log::warning('FatSecret API request failed', [
                'status' => $response->status(),
                'response' => $response->body()
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('FatSecret API request error', [
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Parse nutrition data from FatSecret API response
     */
    public function parseNutritionData(array $fatSecretData): array
    {
        // Helper to extract nutrient value by name
        $getNutrient = function ($name) use ($fatSecretData) {
            if (!isset($fatSecretData['servings']['serving'])) {
                return 0;
            }
            
            $serving = $fatSecretData['servings']['serving'];
            if (is_array($serving) && isset($serving[0])) {
                $serving = $serving[0]; // Use first serving
            }
            
            if (!isset($serving['nutrition_facts']['nutrition_fact'])) {
                return 0;
            }
            
            $nutritionFacts = $serving['nutrition_facts']['nutrition_fact'];
            if (!is_array($nutritionFacts)) {
                $nutritionFacts = [$nutritionFacts];
            }
            
            foreach ($nutritionFacts as $fact) {
                if (isset($fact['name']) && strtolower($fact['name']) === strtolower($name)) {
                    return (float) ($fact['value'] ?? 0);
                }
            }
            return 0;
        };

        $serving = $fatSecretData['servings']['serving'] ?? [];
        if (is_array($serving) && isset($serving[0])) {
            $serving = $serving[0];
        }

        // Fallback: If nutrition_facts are missing, try to get values directly from serving
        $calories = $getNutrient('Calories');
        $protein = $getNutrient('Protein');
        $carbohydrates = $getNutrient('Carbohydrate');
        $fat = $getNutrient('Fat');
        $fiber = $getNutrient('Fiber');
        $sugar = $getNutrient('Sugar');
        $sodium = $getNutrient('Sodium');
        $cholesterol = $getNutrient('Cholesterol');

        if ($calories === 0 && isset($serving['calories'])) $calories = (float)$serving['calories'];
        if ($protein === 0 && isset($serving['protein'])) $protein = (float)$serving['protein'];
        if ($carbohydrates === 0 && isset($serving['carbohydrate'])) $carbohydrates = (float)$serving['carbohydrate'];
        if ($fat === 0 && isset($serving['fat'])) $fat = (float)$serving['fat'];
        if ($fiber === 0 && isset($serving['fiber'])) $fiber = (float)$serving['fiber'];
        if ($sugar === 0 && isset($serving['sugar'])) $sugar = (float)$serving['sugar'];
        if ($sodium === 0 && isset($serving['sodium'])) $sodium = (float)$serving['sodium'];
        if ($cholesterol === 0 && isset($serving['cholesterol'])) $cholesterol = (float)$serving['cholesterol'];

        return [
            'name' => $fatSecretData['food_name'] ?? '',
            'brand' => $fatSecretData['brand_name'] ?? '',
            'barcode' => !empty($fatSecretData['barcode']) ? $fatSecretData['barcode'] : null,
            'serving_size' =>
                (isset($serving['metric_serving_amount']) && isset($serving['metric_serving_unit']))
                    ? ($serving['metric_serving_amount'] . ' ' . $serving['metric_serving_unit'])
                    : ($serving['serving_description'] ?? ''),
            'calories' => $calories,
            'protein' => $protein,
            'carbohydrates' => $carbohydrates,
            'fat' => $fat,
            'fiber' => $fiber,
            'sugar' => $sugar,
            'sodium' => $sodium,
            'cholesterol' => $cholesterol,
            'image_url' => $fatSecretData['food_url'] ?? null,
            'fatsecret_id' => $fatSecretData['food_id'] ?? null,
        ];
    }
} 