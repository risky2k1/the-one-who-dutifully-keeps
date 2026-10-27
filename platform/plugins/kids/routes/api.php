<?php

use Illuminate\Support\Facades\Route;

Route::group([
    'prefix' => 'api/v1/kids',
    'middleware' => ['api'],
], function (): void {
    Route::middleware('auth:sanctum')->get('me', function () {
        return response()->json(['message' => 'ok']);
    });
});
