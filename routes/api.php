<?php

use App\Http\Controllers\Api\v1\MessageController;
use App\Http\Controllers\Api\v1\NotificationController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    // Messages
    Route::post('messages', [MessageController::class, 'store']);

    // Notifications
    Route::get('notifications', [NotificationController::class, 'index']);
});
