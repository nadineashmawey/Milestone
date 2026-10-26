<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfessorCourseController;

Route::get('/', function () {
    return view('welcome');
});
Route::get('/professor/courses', [
    ProfessorCourseController::class,
    'index',
])->name('professor.courses.index');

if (app()->environment('local')) {
    Route::view('/ui-kit', 'ui-kit')->name('ui-kit');
}

Route::view('/professor/projects/create', 'projects.create')->name('professor.projects.create');
