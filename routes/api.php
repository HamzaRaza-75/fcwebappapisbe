<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:api', 'role:admin'])->get('/user', function (Request $request) {
    return "hi";
});



Route::get('/jugnu', function () {
    return response()->json(['data' => 'Hi hamza'], 200);
});






require  __DIR__ . '/teamcaptain.php';
require __DIR__ . '/auth.php';
require __DIR__ . '/course.php';
require __DIR__ . '/employess.php';
require __DIR__ . '/optmilestones.php';
require __DIR__ . '/reporting.php';
require __DIR__ . '/tdlproject.php';
require __DIR__ . '/todolist.php';
