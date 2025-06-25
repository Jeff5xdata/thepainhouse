<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\FoodController;
use App\Http\Controllers\TrainerRequestController;
use App\Http\Controllers\MessageController;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Food API routes
Route::middleware(['auth:sanctum', 'throttle:60,1'])->group(function () {
    Route::post('/food/search', [FoodController::class, 'searchByName']);
    Route::post('/food/barcode', [FoodController::class, 'searchByBarcode']);
    Route::post('/food/store', [FoodController::class, 'storeFoodItem']);
});

// Trainer Request API Routes
Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/trainer-requests', [TrainerRequestController::class, 'store']);
    Route::post('/trainer-requests/{trainerRequest}/accept', [TrainerRequestController::class, 'accept']);
    Route::post('/trainer-requests/{trainerRequest}/decline', [TrainerRequestController::class, 'decline']);
    Route::get('/trainer-requests/pending', [TrainerRequestController::class, 'pending']);
});

// Message API Routes
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/messages', [MessageController::class, 'index']);
    Route::get('/messages/{message}', [MessageController::class, 'show']);
    Route::post('/messages', [MessageController::class, 'store']);
    Route::post('/messages/{message}/read', [MessageController::class, 'markAsRead']);
    Route::get('/messages/unread/count', [MessageController::class, 'unreadCount']);
    Route::get('/messages/conversation/{userId}', [MessageController::class, 'conversation']);
});
