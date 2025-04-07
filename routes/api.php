<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TripController;

Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);
});

Route::middleware('auth:sanctum')->group(function () {

    Route::prefix('auth')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
    });

    Route::prefix('trips')->group(function () {

        Route::post('/index', [TripController::class, 'index']);
        Route::get('/{trip}', [TripController::class, 'show']);
        Route::post('/', [TripController::class, 'store']);
        Route::put('/{trip}', [TripController::class, 'update']);
        Route::put('/{trip}/status', [TripController::class, 'updateStatus'])
        ->middleware('role:admin');
    });

});
