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
})->where('path', '.*')->name('ta.assets.css');

Route::get('/admin/assets/js/{path}', function (string $path) {
    abort_if(str_contains($path, '..'), 404);
    $file = resource_path('js/'.$path);
    abort_unless(is_file($file), 404);

    return response()->file($file, ['Content-Type' => 'text/javascript; charset=UTF-8']);
})->where('path', '.*');

Route::get('/admin/{page?}', function (?string $page = 'dashboard') {
    $pages = [
        'dashboard' => 'index.html',
        'index.html' => 'index.html',
        'users' => 'users.html',
        'users.html' => 'users.html',
        'imports' => 'imports.html',
        'imports.html' => 'imports.html',
        'courses' => 'courses.html',
        'courses.html' => 'courses.html',
        'projects' => 'projects.html',
        'projects.html' => 'projects.html',
        'announcements' => 'announcements.html',
        'announcements.html' => 'announcements.html',
        'audit' => 'audit.html',
        'audit.html' => 'audit.html',
    ];

    abort_unless(isset($pages[$page]), 404);

    return response()->file(
        resource_path('views/pages/admin/'.$pages[$page]),
        ['Content-Type' => 'text/html; charset=UTF-8'],
    );
})->where('page', 'dashboard|index\.html|users(?:\.html)?|imports(?:\.html)?|courses(?:\.html)?|projects(?:\.html)?|announcements(?:\.html)?|audit(?:\.html)?');

Route::get('/ta/assets/css/{path}', function (string $path) {
    abort_if(str_contains($path, '..'), 404);
    $file = resource_path('css/'.$path);
    abort_unless(is_file($file), 404);
    return response()->file($file, ['Content-Type' => 'text/css; charset=UTF-8']);
})->where('path', '.*');

Route::get('/ta/assets/js/{path}', function (string $path) {
    abort_if(str_contains($path, '..'), 404);
    $file = resource_path('js/'.$path);
    abort_unless(is_file($file), 404);
    return response()->file($file, ['Content-Type' => 'text/javascript; charset=UTF-8']);
})->where('path', '.*')->name('ta.assets.js');

/*
| Frontend-only TA prototype
| Blade renders the page structure, shared CSS controls the design, and
| page-specific JavaScript provides prototype interactions. Database and
| application logic remain intentionally deferred to phase two.
*/
Route::redirect('/ta', '/ta/dashboard');
Route::view('/ta/dashboard', 'ta.dashboard')->name('ta.dashboard');
Route::view('/ta/reviews', 'ta.reviews')->name('ta.reviews');
Route::view('/ta/issues', 'ta.issues')->name('ta.issues');
Route::view('/ta/invitations', 'ta.assignments')->name('ta.assignments');
Route::view('/ta/tasks', 'ta.tasks')->name('ta.tasks');
