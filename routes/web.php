<?php

use Illuminate\Support\Facades\Route;

// Root route - welcome page
Route::get('/', function () {
    return view('welcome');
});
