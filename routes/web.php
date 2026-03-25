<?php

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Route;

/** @var class-string $controller */
$controller = Config::get('comments.controller');

/** @var string $prefix */
$prefix = Config::get('comments.route_prefix', 'comments');

Route::controller($controller)
    ->prefix($prefix)
    ->as('comments.')
    ->group(function (): void {
        Route::post('', 'store')->name('store');
        Route::put('{comment}', 'update')->name('update');
        Route::delete('{comment}', 'destroy')->name('destroy');

        // Only register the react route when reactions are enabled.
        if (Config::get('comments.reactions.enabled', true)) {
            Route::post('{comment}/react', 'react')->name('react');
        }

        Route::post('{comment}', 'reply')->name('reply');
    });
