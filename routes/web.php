<?php

use Illuminate\Support\Facades\Route;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

Route::get('/', function () {
    return "jugnu";
});

Route::get('/create-user', function () {
    $user = User::create([
        'name' => 'Muhammad Hamza Raza',
        'email' => 'admin123@gmail.com',
        'password' => Hash::make('admin123'),
        'status' => 'active',
    ]);


    $role = Role::create(['name' => 'team-captain']);
    $user->assignRole($role);
    return "hi hamza";
});
