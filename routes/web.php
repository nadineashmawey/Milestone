<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

if (app()->environment('local')) {
    Route::view('/ui-kit', 'ui-kit')->name('ui-kit');
}
require __DIR__.'/home.php'; // Home page and Help center. Loaded last, so its "/" replaces the welcome route above.
