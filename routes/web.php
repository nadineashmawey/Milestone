<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

if (app()->environment('local')) {
    Route::view('/ui-kit', 'ui-kit')->name('ui-kit');
}

/*
| Frontend-only admin prototype
| HTML, CSS, and JavaScript stay in resources during phase one. Laravel is
| used only to provide reviewable URLs; application logic comes in phase two.
*/
Route::get('/admin/assets/css/{path}', function (string $path) {
    abort_if(str_contains($path, '..'), 404);
    $file = resource_path('css/'.$path);
    abort_unless(is_file($file), 404);

    return response()->file($file, ['Content-Type' => 'text/css; charset=UTF-8']);
})->where('path', '.*')->name('admin.assets.css');

Route::get('/admin/assets/js/{path}', function (string $path) {
    abort_if(str_contains($path, '..'), 404);
    $file = resource_path('js/'.$path);
    abort_unless(is_file($file), 404);

    return response()->file($file, ['Content-Type' => 'text/javascript; charset=UTF-8']);
})->where('path', '.*')->name('admin.assets.js');

/* Frontend-only admin prototype: application and database logic follow in phase two. */
Route::redirect('/admin', '/admin/dashboard');
Route::view('/admin/dashboard', 'admin.dashboard')->name('admin.dashboard');
Route::view('/admin/users', 'admin.users')->name('admin.users');
Route::view('/admin/imports', 'admin.imports')->name('admin.imports');
Route::view('/admin/courses', 'admin.courses')->name('admin.courses');
Route::view('/admin/projects', 'admin.projects')->name('admin.projects');
Route::view('/admin/announcements', 'admin.announcements')->name('admin.announcements');
Route::view('/admin/audit', 'admin.audit')->name('admin.audit');
