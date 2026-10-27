<?php

use Botble\Base\Facades\AdminHelper;
use Botble\Kids\Http\Controllers\KidsParentController;
use Botble\Kids\Http\Controllers\KidsReportController;
use Botble\Kids\Http\Controllers\KidsTopicController;
use Botble\Kids\Http\Controllers\KidsTopicPreviewController;
use Botble\Kids\Http\Controllers\KidsWordTemplateController;
use Illuminate\Support\Facades\Route;

AdminHelper::registerRoutes(function (): void {
    Route::group(['prefix' => 'kids', 'as' => 'kids.', 'permission' => false], function (): void {
        Route::resource('topics', KidsTopicController::class);
        Route::get('topics/{topic}/preview', [KidsTopicPreviewController::class, 'show'])->name('topics.preview');

        Route::resource('word-templates', KidsWordTemplateController::class);
        Route::resource('parents', KidsParentController::class);

        Route::resource('reports', KidsReportController::class)->only(['index', 'destroy']);
        Route::match(['get', 'post', 'put'], 'reports/{report}/resolve', [KidsReportController::class, 'resolve'])->name('reports.resolve');
    });
});
