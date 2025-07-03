<?php

// Import necessary classes for API functionality
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\FoodController;
use App\Http\Controllers\TrainerRequestController;
use App\Http\Controllers\MessageController;

// Get authenticated user information via Sanctum authentication
// This route returns the current authenticated user's data
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Food API routes with Sanctum authentication and rate limiting (60 requests per minute)
// These routes handle food item search and storage functionality
Route::middleware(['auth:sanctum', 'throttle:60,1'])->group(function () {
    // Search for food items by name
    Route::post('/food/search', [FoodController::class, 'searchByName']);
    // Search for food items by barcode
    Route::post('/food/barcode', [FoodController::class, 'searchByBarcode']);
    // Store new food items in the database
    Route::post('/food/store', [FoodController::class, 'storeFoodItem']);
});

// Trainer Request API Routes with Sanctum authentication
// These routes handle trainer-client relationship management
Route::middleware(['auth:sanctum'])->group(function () {
    // Create a new trainer request
    Route::post('/trainer-requests', [TrainerRequestController::class, 'store']);
    // Accept a trainer request
    Route::post('/trainer-requests/{trainerRequest}/accept', [TrainerRequestController::class, 'accept']);
    // Decline a trainer request
    Route::post('/trainer-requests/{trainerRequest}/decline', [TrainerRequestController::class, 'decline']);
    // Get pending trainer requests
    Route::get('/trainer-requests/pending', [TrainerRequestController::class, 'pending']);
});

// Message API Routes with Sanctum authentication
// These routes handle messaging functionality between users
Route::middleware(['auth:sanctum'])->group(function () {
    // Get all messages for the authenticated user
    Route::get('/messages', [MessageController::class, 'index']);
    // Get a specific message by ID
    Route::get('/messages/{message}', [MessageController::class, 'show']);
    // Create a new message
    Route::post('/messages', [MessageController::class, 'store']);
    // Mark a message as read
    Route::post('/messages/{message}/read', [MessageController::class, 'markAsRead']);
    // Get count of unread messages
    Route::get('/messages/unread/count', [MessageController::class, 'unreadCount']);
    // Get conversation messages with a specific user
    Route::get('/messages/conversation/{userId}', [MessageController::class, 'conversation']);
});
