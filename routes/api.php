<?php

use App\Http\Controllers\Api\CarController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BookingController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/login', [AuthController::class, 'login']);

Route::get('/car-types', [CarController::class, 'getCarTypes']); 

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/user', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // KeyFleet resources

    // Bookings
    Route::group(['prefix' => 'bookings'], function () {
        Route::get('/', [BookingController::class, 'index']);
        Route::get('/{id}', [BookingController::class, 'show']);
    });

    // Cars
    Route::group(['prefix' => 'cars'], function () {
      Route::post('/', [CarController::class, 'store']);
      Route::get('/', [CarController::class, 'index']);
      Route::get('/{id}', [CarController::class, 'show']);
  });

});