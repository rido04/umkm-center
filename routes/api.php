<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UmkmController;
use App\Http\Controllers\Api\EventController;
use App\Http\Controllers\Api\RegionController;
use App\Http\Controllers\Api\ProductController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function(){
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::apiResource('umkms', UmkmController::class);
    Route::apiResource('products', ProductController::class);
    Route::apiResource('regions', RegionController::class);
    Route::apiResource('events', EventController::class);
});

Route::get('/products', [ProductController::class, 'index']);
Route::get('/umkms', [UmkmController::class, 'index']);
Route::get('/events', [EventController::class, 'index']);
