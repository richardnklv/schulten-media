<?php

use Illuminate\Support\Facades\Route;

Route::get('/login', function () {
    return view('auth.login');
})->name('login');

// Add a redirect from home to dashboard
Route::get('/', function () {
    return redirect('/dashboard');
});

// Remove the auth:sanctum middleware from here
Route::get('/dashboard', function () {
    return view('dashboard');
});