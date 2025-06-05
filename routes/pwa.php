<?php

use LaravelPWA\Http\Controllers\LaravelPWAController;

Route::group(['as' => 'pwa.'], function()
{
    Route::get('/manifest.json', [LaravelPWAController::class, 'manifestJson'])
    ->name('manifest');
    Route::get('/offline', [LaravelPWAController::class, 'offline'])
    ->name('offline');
});
