<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MusicController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\SuggestionController;
use App\Http\Controllers\SuggestionStatusController;

Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::middleware('auth:sanctum')->get('/user', [AuthController::class, 'user']);
    Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);
});

Route::apiResource('musics', MusicController::class);

Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('suggestions', SuggestionController::class);

    Route::post(
        'suggestions/{suggestion}/status/{status}',
        SuggestionStatusController::class
    );
});
