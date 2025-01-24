<?php

use App\Http\Controllers\Projects\EmployeetodolistController;
use App\Http\Controllers\Projects\PorjectMilestoneController;
use App\Http\Controllers\Projects\TaskMilestoneController;
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
// these routes are for higher authorities

Route::middleware(['auth', 'role:super-admin|team-captain|bussiness-development-manager|team-leader'])->group(function () {

    // routes for task controller starts here
    Route::get('/task/{task}/milestone/create', [TaskMilestoneController::class, 'create'])->name('task.milestone.create');
    Route::post('/task/{task}/milestone', [TaskMilestoneController::class, 'store'])->name('task.milestone.store');
    Route::delete('/task/milestone/{id}', [TaskMilestoneController::class, 'destroy'])->name('task.milestone.delete');
    // routes for task milestones ends here


    // route for employeetodolist controller starts here
    Route::get('/actionplan/{id}/todolists', [EmployeetodolistController::class, 'taskindex'])->name('task.assignment.todo.index');
    Route::get('/actionplan/{id}/todolists/markascomplete', [EmployeetodolistController::class, 'markascomplete'])->name('task.assignment.todo.markascomplete');
    Route::post('/actionplan/{todolist}/reassign', [EmployeetodolistController::class, 'reassigntask'])->name('task.todo.reassigntask');


    // route for employeetodolist controller ends here

});

// the routes for higher authorities ends here
