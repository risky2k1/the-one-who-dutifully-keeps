<?php

use Botble\Base\Facades\AdminHelper;
use Illuminate\Support\Facades\Route;

AdminHelper::registerRoutes(function (): void {
    Route::group(['prefix' => 'kids', 'as' => 'kids.'], function (): void {
        // Reserved for Task 12 admin routes
    });
});
