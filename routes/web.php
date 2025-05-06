<?php

use Illuminate\Support\Facades\Route;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

Route::get('/', function () {
    return "jugnu";
});

Route::get('/create-user', function () {
    User::create([
        'name' => 'Muhammad Hamza Raza',
        'email' => 'admin123@gmail.com',
        'password' => Hash::make('admin123'),
        'status' => 'active',
    ]);

    return "hi hamza";
});
