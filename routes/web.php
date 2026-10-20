<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::view('/ta/reviews', 'ta.reviews')->name('ta.reviews');

if (app()->environment('local')) {
    Route::view('/ui-kit', 'ui-kit')->name('ui-kit');
}
