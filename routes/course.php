<?php

use App\Http\Controllers\Course\CourseController;
use App\Http\Controllers\Course\EmployeecorseController;
use App\Http\Controllers\Course\LessonController;
use App\Http\Controllers\Teammembers\DashboardController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/
// these routes are for checking the logged-in user

Route::get('/course/dashboard/check', [DashboardController::class, 'coursedashboard'])->middleware(['auth', 'verified'])->name('course.dashboard.check');




Route::middleware(['auth', 'role:super-admin|team-captain|bussiness-development-manager|team-leader|team-member'])->prefix('learners')->group(function () {

    // routes for course controller starts here
    Route::get('/courses', [CourseController::class, 'index'])->name('course.index');
    Route::get('/course/create', [CourseController::class, 'create'])->name('course.create');
    Route::post('/course/create', [CourseController::class, 'store'])->name('course.store');
    Route::get('/course/view/{id}', [CourseController::class, 'show'])->name('course.show');
    Route::get('/course/edit/{id}', [CourseController::class, 'edit'])->name('course.edit');
    Route::put('/course/update/{id}', [CourseController::class, 'update'])->name('course.update');
    Route::delete('/course/delete/{id}', [CourseController::class, 'destroy'])->name('course.destroy');

    // routes for course controller ends here


    // routes for projects controller starts here
    Route::get('/courses/{course}/lessons', [LessonController::class, 'index'])->name('course.lesson.index');
    Route::get('/courses/{course}/lessons/create', [LessonController::class, 'create'])->name('course.lesson.create');
    Route::post('/courses/{course}/lessons', [LessonController::class, 'store'])->name('course.lesson.store');
    Route::get('/courses/{course}/lessons/{lesson}', [LessonController::class, 'show'])->name('course.lesson.show');
    Route::get('/courses/{course}/lessons/{lesson}/edit', [LessonController::class, 'edit'])->name('course.lesson.edit');
    Route::put('/courses/{course}/lessons/{lesson}', [LessonController::class, 'update'])->name('course.lesson.update');
    Route::delete('/courses/{course}/lessons/{lesson}', [LessonController::class, 'destroy'])->name('course.lesson.destroy');


    //route for course request controller

    Route::get('/course/{course}/request/enroll', [EmployeecorseController::class, 'store'])->name('course.request.done');
});

// the routes for higher authorities ends here
