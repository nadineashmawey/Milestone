
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProjectController;

Route::middleware('guest')->group(function () {
    Route::get('/sign-in', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/sign-in', [AuthController::class, 'login'])->name('login.submit');

    Route::get('/sign-up', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/sign-up', [AuthController::class, 'register'])->name('register.submit');
});

Route::post('/sign-out', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

Route::get('/', fn () => redirect()->route('projects.index'))->name('home');

Route::get('/my-projects', [ProjectController::class, 'index'])
    ->name('projects.index');

Route::get('/my-projects/create', [ProjectController::class, 'create'])
    ->name('projects.create');

Route::get('/my-projects/{project}', [ProjectController::class, 'show'])
    ->name('student.projects.show');
