<?php

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Route;

Route::post('comments', Config::get('comments.controller').'@store')->name('comments.store');
Route::delete('comments/{comment}', Config::get('comments.controller').'@destroy')->name('comments.destroy');
Route::put('comments/{comment}', Config::get('comments.controller').'@update')->name('comments.update');
Route::post('comments/{comment}', Config::get('comments.controller').'@reply')->name('comments.reply');
