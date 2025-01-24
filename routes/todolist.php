<?php

use App\Http\Controllers\Projects\EmployeetodolistController;
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
// the routes for team members

Route::middleware(['auth', 'role:team-member'])->prefix('employee')->group(function () {
    // routes for sending tdl starts here

    Route::get('/task/todolist', [EmployeetodolistController::class, 'taskindex'])->name('task.todolist.index');
    Route::post('/task/{id}/todolist', [EmployeetodolistController::class, 'taskstore'])->name('task.todolist.store');
    Route::put('task/todolist/{id}', [EmployeetodolistController::class, 'taskupdate'])->name('task.todolist.update');
    // Route::delete('/task/{id}/todolist/{id}', [EmployeetodolistController::class, 'taskdestroy'])->name('task.todolist.destroy');

    // routes for sending tld ends here




    Route::get('/reassigns/todolists', [EmployeetodolistController::class, 'tdlindex'])->name('reassign.todolist.index');
    Route::put('/todolist/update/{id}', [EmployeetodolistController::class, 'todolistupdate'])->name('employee.todolist.update');
    Route::get('/reassigns/seenat/{id}', [EmployeetodolistController::class, 'seenreasing'])->name('reassign.seenat.update');
});



// the routes for team members ends here
