<?php

use App\Http\Controllers\Superadmin\CompanyController;
use App\Http\Controllers\Superadmin\EmployessController;
use App\Http\Controllers\Superadmin\TeamController;
use App\Http\Controllers\TeamCaptain\TcDashboardController;
use App\Http\Controllers\TeamCaptain\TeamrequestController;
use App\Http\Controllers\Shedule\SheduleController;
use App\Http\Controllers\TeamCaptain\ClientController;
use App\Http\Controllers\Teammembers\AttendenceController;
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

Route::middleware(['auth', 'role:super-admin'])->group(function () {

    Route::get('/add-company', [CompanyController::class, 'create'])->name('company.add');
    Route::post('/add-company', [CompanyController::class, 'store'])->name('company.store');
    Route::get('/edit-company/{id}', [CompanyController::class, 'edit'])->name('company.edit');
    Route::put('/update-company/{id}', [CompanyController::class, 'update'])->name('company.update');
    Route::get('/delete-company/{id}', [CompanyController::class, 'destroy'])->name('company.destroy');
    Route::get('/all-company', [CompanyController::class, 'index'])->name('company.index');

    Route::get('/add-team', [TeamController::class, 'create'])->name('team.create');
    Route::post('/add-team', [TeamController::class, 'store'])->name('team.store');
    Route::post('/team/{team}/create', [TeamController::class, 'positioncreate'])->name('team.position.create');

    Route::get('/edit-team/{id}', [TeamController::class, 'edit'])->name('team.edit');
    Route::put('/update-team/{id}', [TeamController::class, 'update'])->name('team.update');
    Route::delete('/delete-team/{id}', [TeamController::class, 'destroy'])->name('team.destroy');
    Route::delete('/team/position/{position}/delete', [TeamController::class, 'positiondestroy'])->name('team.position.destroy');
});


Route::middleware(['auth', 'role:super-admin|team-captain|bussiness-development-manager|team-leader|human-resources'])->group(function () {

    Route::get('/teamcaptain/dashboard', [TcDashboardController::class, 'index'])->name('teamcaptain.dashboard');

    Route::get('/all-team', [TeamController::class, 'index'])->name('team.index');
    Route::get('/view-team/{id}', [TeamController::class, 'show'])->name('team.show');

    Route::get('/all-employess', [EmployessController::class, 'index'])->name('employess.index');
    Route::get('/view-employee/{id}', [EmployessController::class, 'show'])->name('employess.show');
    Route::get('/free-employess/list', [EmployessController::class, 'viewFreeEmployess'])->name('employess.free');


    // routes for tasks controller starts here
    Route::get('/attendance', [AttendenceController::class, 'index'])->name('attendance.index');
    Route::get('/attendance/show/{id?}', [AttendenceController::class, 'show'])->name('attendance.show');
    // Route::post('/attendance/{attendance}/create', [AttendenceController::class, 'store'])->name('attendance.store');
    // Route::get('/attendance/{id}/edit', [AttendenceController::class, 'edit'])->name('attendance.edit');
    // Route::put('/attendance/{id}', [AttendenceController::class, 'update'])->name('attendance.update');
    // Route::delete('/attendance/{id}', [AttendenceController::class, 'destroy'])->name('attendance.destroy');

});

Route::middleware(['auth', 'role:super-admin|team-captain'])->group(function () {

    // team routes


    // team request routes

    Route::get('/all-requests', [TeamrequestController::class, 'index'])->name('teamrequest.index');

    // employess request route ends here



    Route::post('/view-employee/{id}', [EmployessController::class, 'update'])->name('employess.update');
    Route::get('/block-user/{id}', [EmployessController::class, 'blockUser'])->name('employess.block');
    Route::get('/unblock-user/{id}', [EmployessController::class, 'unblockUser'])->name('employess.unblock');

    // employess list routes starts here


    Route::get('/all-shedule', [SheduleController::class, 'index'])->name('shedule.index');
});


// accepting team request
Route::middleware(['auth', 'role:super-admin|team-captain'])->group(function () {
    Route::post('/accept-team-request/{id}', [TeamrequestController::class, 'store'])->name('teamrequest.store');
    Route::get('/reject-team-request/{id}', [TeamrequestController::class, 'rejectteamrequest'])->name('teamrequest.rejectteamrequest');



    // routes for clients
    Route::delete('/client/{id}/delete', [ClientController::class, 'destroy'])->name('client.destroy');
});


Route::middleware(['auth', 'role:super-admin|team-captain|bussiness-administration-manager'])->group(function () {
    Route::post('/accept-team-request/{id}', [TeamrequestController::class, 'store'])->name('teamrequest.store');
    Route::get('/reject-team-request/{id}', [TeamrequestController::class, 'rejectteamrequest'])->name('teamrequest.rejectteamrequest');



    // routes for clients
    Route::get('/all-clients', [ClientController::class, 'index'])->name('client.index');
    Route::post('/client/create', [ClientController::class, 'store'])->name('client.store');
    Route::get('/client/{id}', [ClientController::class, 'show'])->name('client.show');
});


// Route::get('/assignrole' , [RegisteredUserController::class , 'assignrole']);
