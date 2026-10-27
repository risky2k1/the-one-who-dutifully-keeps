<?php

use Botble\Kids\Http\Controllers\API\AuthController;
use Botble\Kids\Http\Controllers\API\DeviceController;
use Botble\Kids\Http\Controllers\API\Kid\HomeController;
use Botble\Kids\Http\Controllers\API\Kid\SessionController;
use Botble\Kids\Http\Controllers\API\Parent\AssignmentController;
use Botble\Kids\Http\Controllers\API\Parent\ChildController;
use Botble\Kids\Http\Controllers\API\Parent\PinController;
use Botble\Kids\Http\Controllers\API\Parent\ReportController;
use Botble\Kids\Http\Controllers\API\Parent\ResultController;
use Botble\Kids\Http\Controllers\API\Parent\TopicController;
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

    // Parent endpoints
    Route::group(['prefix' => 'parent', 'middleware' => ['auth:sanctum', 'kids.token:parent']], function (): void {
        Route::get('children', [ChildController::class, 'index']);
        Route::post('children', [ChildController::class, 'store']);
        Route::patch('children/{child}', [ChildController::class, 'update']);

        Route::put('pin', [PinController::class, 'update']);
        Route::get('topics', [TopicController::class, 'index']);

        Route::get('assignments', [AssignmentController::class, 'index']);
        Route::post('assignments', [AssignmentController::class, 'store']);
        Route::patch('assignments/{assignment}', [AssignmentController::class, 'update']);
        Route::delete('assignments/{assignment}', [AssignmentController::class, 'destroy']);

        Route::get('children/{child}/results', [ResultController::class, 'index']);
        Route::get('children/{child}/weak-skills', [ResultController::class, 'weakSkills']);
        Route::get('sessions/{session}', [ResultController::class, 'sessionReview']);
        Route::post('questions/{question}/report', [ReportController::class, 'report']);
    });

    // Kid endpoints
    Route::group(['prefix' => 'me', 'middleware' => ['auth:sanctum', 'kids.token:kid']], function (): void {
        Route::get('', [HomeController::class, 'index']);
        Route::get('topics', [HomeController::class, 'topics']);
        Route::get('rewards', [HomeController::class, 'rewards']);
        Route::post('sessions', [SessionController::class, 'start']);
        Route::post('sessions/{session}/questions/{question}/answer', [SessionController::class, 'answer']);
        Route::post('sessions/{session}/complete', [SessionController::class, 'complete']);
    });
});
