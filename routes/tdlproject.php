<?php

use App\Http\Controllers\Projects\EmployeeProjectController;
use App\Http\Controllers\Projects\EmployeetodolistController;
use App\Http\Controllers\Projects\TaskController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Projects\ProjectController;
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
// these routes are for higher authorities

Route::middleware(['auth', 'role:super-admin|team-captain|bussiness-development-manager|team-leader'])->group(function () {

    // routes for task controller starts here

    Route::get('/active-tasks', [TaskController::class, 'index'])->name('task.index');
    Route::get('/all-tasks/{search?}', [TaskController::class, 'alltasks'])->name('task.all');
    Route::get('/add-task', [TaskController::class, 'create'])->name('task.create');
    Route::post('/add-task', [TaskController::class, 'store'])->name('task.store');
    Route::get('/view-task/{id}', [TaskController::class, 'show'])->name('task.show');
    Route::get('/edit-task/{id}', [TaskController::class, 'edit'])->name('task.edit');
    Route::put('/update-task/{id}', [TaskController::class, 'update'])->name('task.update');
    Route::delete('/delete-task/{id}', [TaskController::class, 'destroy'])->name('task.destroy');
    Route::get('/view-task/{id}/mark-as-read', [TaskController::class, 'markasread'])->name('task.read');

    // routes for task controller ends here
});

// the routes for higher authorities ends here





// the routes for team members

Route::middleware(['auth', 'role:team-member'])->prefix('employee')->group(function () {

    // routes for sending tdl starts here

    Route::get('/todolist', [EmployeetodolistController::class, 'index'])->name('todolist.index');
    Route::get('/todolist/create', [EmployeetodolistController::class, 'create'])->name('todolist.create');
    Route::post('/todolist', [EmployeetodolistController::class, 'store'])->name('todolist.store');
    Route::get('/todolist/{id}', [EmployeetodolistController::class, 'show'])->name('todolist.show');
    Route::get('/todolist/{id}/edit', [EmployeetodolistController::class, 'edit'])->name('todolist.edit');
    Route::put('/todolist/{id}', [EmployeetodolistController::class, 'update'])->name('todolist.update');
    Route::delete('/todolist/{id}', [EmployeetodolistController::class, 'destroy'])->name('todolist.destroy');

    // routes for sending tld ends here
});
