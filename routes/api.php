<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UmkmController;
use App\Http\Controllers\Api\EventController;
use App\Http\Controllers\Api\RegionController;
use App\Http\Controllers\Api\ProductController;

// route buat cek role pas abis login nih tod (pake GET kalo di postman)
Route::middleware('auth:sanctum')->get('/me', function (Request $request) {
    return response()->json([
        'user' => $request->user(),
        'roles' => $request->user()->getRoleNames(),
    ]);
});


Route::post('login', [AuthController::class, 'login']);

// admin routes pake validasi
Route::middleware('auth:sanctum')->group(function(){
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::apiResource('umkms', UmkmController::class);
    Route::apiResource('products', ProductController::class);
    Route::apiResource('regions', RegionController::class);
    Route::apiResource('events', EventController::class);
});

// pengunjung routes tanpa validasi
Route::get('/products', [ProductController::class, 'index']);
Route::get('/umkms', [UmkmController::class, 'index']);
Route::get('/events', [EventController::class, 'index']);
Route::get('/events', [EventController::class, 'index']);
