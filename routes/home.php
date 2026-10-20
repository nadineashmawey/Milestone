<?php

// Zina: public Home page and Help center. Loaded from the end of web.php.

use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProblemReportController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/help', [HomeController::class, 'help'])->name('help');

// 5 reports a minute per IP (per user when signed in). CSRF comes from the web middleware group.
Route::post('/help/reports', [ProblemReportController::class, 'store'])
    ->middleware('throttle:5,1')
    ->name('help.reports.store');
