<?php

use App\Http\Controllers\Reports\ReportingController;
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

Route::middleware(['auth', 'role:super-admin|team-captain|bussiness-development-manager|team-leader'])->prefix('reports')->group(function () {
    // routes for reporting section starts here.....
    Route::get('/crossteam/reports/{search?}', [ReportingController::class, 'employeeactionplanreport'])->name('report.crossteam');
    Route::get('/project-timeline/reports/{search?}', [ReportingController::class, 'projectwithtimelinereport'])->name('report.timeline');

    // routes for reporting sections ends here....
});



// the routes for team members ends here
