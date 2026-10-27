<?php

use Botble\Kids\Http\Controllers\API\AuthController;
use Botble\Kids\Http\Controllers\API\DeviceController;
use Illuminate\Support\Facades\Route;

Route::group([
    'prefix' => 'api/v1/kids',
    'middleware' => ['api'],
], function (): void {
    // Auth
    Route::post('auth/login', [AuthController::class, 'login'])->middleware('throttle:kids-login');
    Route::post('auth/logout', [AuthController::class, 'logout'])->middleware(['auth:sanctum', 'kids.token:parent']);

    // Device
    Route::group(['prefix' => 'device', 'middleware' => ['auth:sanctum', 'kids.token:device']], function (): void {
        Route::post('unlock', [DeviceController::class, 'unlock'])->middleware('throttle:kids-pin');
        Route::get('children', [DeviceController::class, 'children']);
        Route::post('children/{child}/session', [DeviceController::class, 'startKidSession']);
    });

    // Kid endpoints
    Route::group(['prefix' => 'me', 'middleware' => ['auth:sanctum', 'kids.token:kid']], function (): void {
        Route::get('', function () {
            return response()->json(['message' => 'kid home']);
        });
    });

    // Parent endpoints
    Route::group(['prefix' => 'parent', 'middleware' => ['auth:sanctum', 'kids.token:parent']], function (): void {
        Route::get('children', function () {
            return response()->json(['message' => 'parent children']);
        });
    });
});
