<?php

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Route;

/**
 * @var string $controller
 */
$controller = Config::get('comments.controller');
Route::controller($controller)
    ->prefix('comments')
    ->as('comments.')
    ->group(function () {
        Route::post('', 'store')->name('store');
        Route::delete('{comment}', 'destroy')->name('destroy');
        Route::put('{comment}', 'update')->name('update');
        Route::post('{comment}', 'reply')->name('reply');
    });
