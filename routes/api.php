<?php

use App\Http\Controllers\Api\v1\CategoryController;
use App\Http\Controllers\Api\v1\MessageController;
use App\Http\Controllers\Api\v1\NotificationController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    // Categories
    Route::get('categories', [CategoryController::class, 'index']);

    // Messages
    Route::post('messages', [MessageController::class, 'store']);

    // Notifications
    Route::get('notifications', [NotificationController::class, 'index']);
});
