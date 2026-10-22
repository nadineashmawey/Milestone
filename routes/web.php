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
})->where('path', '.*');

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
})->where('path', '.*');

Route::get('/ta/{page?}', function (?string $page = 'dashboard') {
    $pages = [
        'dashboard' => 'dashboard.html',
        'reviews' => 'reviews.html',
        'issues' => 'issues.html',
        'invitations' => 'invitations.html',
        'chat' => 'chat.html',
        'tasks' => 'tasks.html',
    ];
    abort_unless(isset($pages[$page]), 404);
    return response()->file(resource_path('views/pages/ta/'.$pages[$page]), ['Content-Type' => 'text/html; charset=UTF-8']);
})->where('page', 'dashboard|reviews|issues|invitations|chat|tasks');
