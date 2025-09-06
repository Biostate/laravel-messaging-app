<?php

use App\Http\Controllers\Api\MessageController;
use Illuminate\Support\Facades\Route;

Route::prefix('messages')->group(function () {
    Route::get('/', [MessageController::class, 'index']);
    Route::get('/{id}', [MessageController::class, 'show']);
    Route::get('/stats/overview', [MessageController::class, 'stats']);
});
