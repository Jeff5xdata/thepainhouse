<?php

use App\Models\User;
use App\Models\FoodItem;
use App\Models\FoodLog;
use App\Services\ChompApiService;
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

test('chomp api service can parse nutrition data', function () {
    $chompService = app(ChompApiService::class);
    
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
    
    $parsedData = $chompService->parseNutritionData($mockData);
    
    expect($parsedData)->toHaveKeys([
        'name', 'brand', 'barcode', 'calories', 'protein', 'carbohydrates', 'fat'
    ]);
    expect($parsedData['name'])->toBe('Test Product');
    expect($parsedData['calories'])->toBe(150);
});
