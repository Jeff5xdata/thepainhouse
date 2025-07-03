<?php

use App\Models\User;
use App\Models\FoodItem;
use App\Models\FoodLog;
use App\Services\FatSecretApiService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('user can access nutrition page', function () {
    $user = User::factory()->create();
    
    $response = $this->actingAs($user)
        ->get('/nutrition');
    
    $response->assertStatus(200);
    $response->assertSee('Food Tracker');
});

test('user can create food log entry', function () {
    $user = User::factory()->create();
    $foodItem = FoodItem::factory()->create([
        'name' => 'Test Food',
        'calories' => 100,
        'protein' => 10,
        'carbohydrates' => 20,
        'fat' => 5,
    ]);
    
    $response = $this->actingAs($user)
        ->post('/api/food/store', [
            'name' => 'Test Food',
            'calories' => 100,
            'protein' => 10,
            'carbohydrates' => 20,
            'fat' => 5,
        ]);
    
    $response->assertStatus(201);
    $this->assertDatabaseHas('food_items', [
        'name' => 'Test Food',
        'calories' => 100,
    ]);
});

test('food log can be created with valid data', function () {
    $user = User::factory()->create();
    $foodItem = FoodItem::factory()->create();
    
    $foodLog = FoodLog::create([
        'user_id' => $user->id,
        'food_item_id' => $foodItem->id,
        'meal_type' => 'breakfast',
        'quantity' => 1.5,
        'consumed_date' => now()->format('Y-m-d'),
        'notes' => 'Test note',
    ]);
    
    $this->assertDatabaseHas('food_logs', [
        'user_id' => $user->id,
        'food_item_id' => $foodItem->id,
        'meal_type' => 'breakfast',
        'quantity' => 1.5,
    ]);
    
    expect($foodLog->getNutritionValues())->toHaveKeys([
        'calories', 'protein', 'carbohydrates', 'fat'
    ]);
});

test('fatsecret api service can parse nutrition data', function () {
    $fatSecretService = app(FatSecretApiService::class);
    
    $mockData = [
        'name' => 'Test Product',
        'brand' => 'Test Brand',
        'barcode' => '123456789',
        'nutrition' => [
            'serving_size' => '100g',
            'calories' => 150,
            'protein' => 15,
            'carbohydrates' => 25,
            'fat' => 8,
        ],
        'id' => 'test-123',
    ];
    
    $parsedData = $fatSecretService->parseNutritionData($mockData);
    
    expect($parsedData)->toHaveKeys([
        'name', 'brand', 'barcode', 'calories', 'protein', 'carbohydrates', 'fat'
    ]);
    expect($parsedData['name'])->toBe('Test Product');
    expect($parsedData['calories'])->toBe(150);
});

test('barcode formatting handles different formats correctly', function () {
    $fatSecretService = app(FatSecretApiService::class);
    
    // Test UPC-A (12 digits) - should add leading zero
    $upcA = '123456789012';
    $formattedUpcA = $fatSecretService->formatBarcodeAsGtin13($upcA);
    expect($formattedUpcA)->toBe('0123456789012');
    expect($formattedUpcA)->toHaveLength(13);
    
    // Test EAN-13 (13 digits) - should remain unchanged
    $ean13 = '1234567890123';
    $formattedEan13 = $fatSecretService->formatBarcodeAsGtin13($ean13);
    expect($formattedEan13)->toBe('1234567890123');
    expect($formattedEan13)->toHaveLength(13);
    
    // Test EAN-8 (8 digits) - should add 5 leading zeros
    $ean8 = '12345678';
    $formattedEan8 = $fatSecretService->formatBarcodeAsGtin13($ean8);
    expect($formattedEan8)->toBe('0000012345678');
    expect($formattedEan8)->toHaveLength(13);
    
    // Test shorter barcode - should pad with zeros
    $short = '12345';
    $formattedShort = $fatSecretService->formatBarcodeAsGtin13($short);
    expect($formattedShort)->toBe('0000000012345');
    expect($formattedShort)->toHaveLength(13);
    
    // Test longer barcode - should truncate
    $long = '12345678901234567890';
    $formattedLong = $fatSecretService->formatBarcodeAsGtin13($long);
    expect($formattedLong)->toBe('1234567890123');
    expect($formattedLong)->toHaveLength(13);
    
    // Test barcode with non-digit characters - should remove them
    $withChars = '123-456-789-012';
    $formattedWithChars = $fatSecretService->formatBarcodeAsGtin13($withChars);
    expect($formattedWithChars)->toBe('0001234567890');
    expect($formattedWithChars)->toHaveLength(13);
    
    // Test empty string - should pad with zeros
    $empty = '';
    $formattedEmpty = $fatSecretService->formatBarcodeAsGtin13($empty);
    expect($formattedEmpty)->toBe('0000000000000');
    expect($formattedEmpty)->toHaveLength(13);
});
