<?php

use Illuminate\Support\Facades\Route;

Route::group([
    'middleware' => 'api',
    'prefix' => 'api/v1',
    'namespace' => 'Botble\Portfolio\Http\Controllers\API',
], function (): void {
    Route::group(['prefix' => 'portfolio'], function (): void {
        Route::get('', 'PortfolioApiController@index');
        Route::get('projects', 'PortfolioApiController@projects');
        Route::get('projects/{slug}', 'PortfolioApiController@project');
        Route::get('categories', 'PortfolioApiController@categories');
        Route::get('skills', 'PortfolioApiController@skills');
        Route::get('experiences', 'PortfolioApiController@experiences');
    });
});

