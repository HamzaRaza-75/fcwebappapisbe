<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Projects\EmployeeTaskController;
use App\Http\Controllers\Teammembers\AttendenceController;
use App\Http\Controllers\Teammembers\LandingController;
use Illuminate\Support\Facades\Route;

// , 'role:team-member'

Route::middleware(['auth', 'role:team-member'])->prefix('employee')->group(function () {

    Route::get('/dashboard', [LandingController::class, 'index'])->name('employess.dashboard');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::post('/profile', [ProfileController::class, 'store'])->name('profile.store');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');



    // Route::get('/profile' , [EmployeeProgressController::class , 'index'])->name('employessprogress.index');

    // routes for tasks controller starts here
    Route::get('/tasks', [EmployeeTaskController::class, 'index'])->name('employee.task.index');
    Route::get('/tasks/{taskmilestone}', [EmployeeTaskController::class, 'show'])->name('employee.task.show');
    Route::post('/tasks/{taskmilestone}/create/todolist', [EmployeeTaskController::class, 'store'])->name('employee.task.store');

    // *
    // **
    // ***
    // *****
    // ****** ispy be dehan dena hy iski apis shyad bnani prhen
    Route::get('/tasks/{id}/edit', [EmployeeTaskController::class, 'edit'])->name('employee.task.edit');
    Route::put('/tasks/{id}', [EmployeeTaskController::class, 'update'])->name('employee.task.update');
    Route::delete('/tasks/{id}', [EmployeeTaskController::class, 'destroy'])->name('employee.task.destroy');
    // *
    // **
    // ***
    // *****
    // ****** ispy be dehan dena hy iski apis shyad bnani prhen

});
