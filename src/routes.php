<?php

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Route;

/** @var class-string $controller */
$controller = Config::get('comments.controller');

Route::controller($controller)
    ->prefix('comments')
    ->as('comments.')
    ->group(function (): void {
        Route::post('', 'store')->name('store');
        Route::put('{comment}', 'update')->name('update');
        Route::delete('{comment}', 'destroy')->name('destroy');
        Route::post('{comment}', 'reply')->name('reply');
    });
