<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UmkmController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\EventController;
use App\Http\Controllers\Api\RegionController;
use App\Http\Controllers\Api\ProductController;

Route::post('login', [AuthController::class, 'login']);

Route::prefix('public')->group(function () {
    Route::get('umkms', [UmkmController::class, 'index']);
    Route::get('umkms/{id}', [UmkmController::class, 'show']);
    Route::get('products', [ProductController::class, 'index']);
    Route::get('products/{id}', [ProductController::class, 'show']);
    Route::get('events', [EventController::class, 'index']);
    Route::get('events/{id}', [EventController::class, 'show']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', function (Request $request) {
        return response()->json([
            'user' => $request->user(),
            'roles' => $request->user()->getRoleNames(),
        ]);
    });

    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/umkms/dropdown', [UmkmController::class, 'dropdown']);
    Route::get('users/dropdown', [UserController::class, 'dropdown']);
    Route::get('/events/dropdown', [EventController::class, 'dropdown']);
    Route::get('/products/dropdown', [ProductController::class, 'dropdown']);
    Route::apiResource('users', UserController::class);
    Route::apiResource('umkms', UmkmController::class);
    Route::apiResource('products', ProductController::class);
    Route::apiResource('regions', RegionController::class);
    Route::apiResource('events', EventController::class);
});
