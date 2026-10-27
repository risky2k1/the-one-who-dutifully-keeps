<?php

use Botble\Base\Facades\AdminHelper;
use Illuminate\Support\Facades\Route;

Route::group(['namespace' => 'Botble\Portfolio\Http\Controllers'], function (): void {
    AdminHelper::registerRoutes(function (): void {
        Route::group(['prefix' => 'portfolio', 'as' => 'portfolio.'], function (): void {
            Route::resource('projects', 'PortfolioProjectController');
            Route::resource('categories', 'PortfolioCategoryController');
            Route::resource('skills', 'PortfolioSkillGroupController');
            Route::resource('experiences', 'PortfolioExperienceController');

            Route::get('settings', [
                'as' => 'settings',
                'uses' => 'PortfolioSettingController@edit',
                'permission' => 'portfolio.settings',
            ]);

            Route::put('settings', [
                'as' => 'settings.update',
                'uses' => 'PortfolioSettingController@update',
                'permission' => 'portfolio.settings',
            ]);
        });
    });
});
