<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Services\ChompApiService;

echo "Testing Chomp API...\n";

$chompService = app(ChompApiService::class);

// Test name search
echo "Testing name search for 'apple'...\n";
$results = $chompService->searchByName('apple');

if (empty($results)) {
    echo "No results returned from name search\n";
} else {
    echo "Found " . count($results) . " results\n";
    
    // Dump the first result to see the structure
    echo "Raw API response structure for first result:\n";
    echo json_encode($results[0], JSON_PRETTY_PRINT) . "\n\n";
    
    foreach ($results as $index => $result) {
        echo "Result " . ($index + 1) . ":\n";
        echo "  Name: " . ($result['name'] ?? 'N/A') . "\n";
        echo "  Brand: " . ($result['brand'] ?? 'N/A') . "\n";
        echo "  ID: " . ($result['id'] ?? 'N/A') . "\n";
        if (isset($result['nutrition'])) {
            echo "  Nutrition: " . json_encode($result['nutrition']) . "\n";
        }
        echo "\n";
    }
}

// Test parsing nutrition data
if (!empty($results)) {
    echo "Testing nutrition data parsing...\n";
    $firstResult = $results[0];
    $nutritionData = $chompService->parseNutritionData($firstResult);
    
    echo "Parsed nutrition data:\n";
    foreach ($nutritionData as $key => $value) {
        echo "  $key: $value\n";
    }
} 