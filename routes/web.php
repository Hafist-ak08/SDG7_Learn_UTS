<?php

use App\Http\Controllers\GlosariumController;
use Illuminate\Support\Facades\Route;

Route::resource('glosarium', GlosariumController::class)->except(['show']);

Route::view('/', 'welcome');
Route::view('/{path}', 'welcome')
    ->where('path', '^(login|register|artikel|kuis|hasil-kuis|forum|profil|admin)(/.*)?$');