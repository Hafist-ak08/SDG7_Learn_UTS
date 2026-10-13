<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApiController;

// Root route - welcome page
Route::get('/', function () {
    return view('welcome');
});

// API routes (following API contract - /api/v1/)
Route::prefix('api/v1')->group(function () {
    $apiController = new ApiController();

    // Articles endpoint
    Route::get('/artikel', [$apiController::class, 'index'])
        ->name('api.article.list');

    // Quizzes endpoint
    Route::get('/kuis', [$apiController::class, 'quizzes'])
        ->name('api.quiz.list');

    // Forum posts endpoint
    Route::get('/forum/postingan', [$apiController::class, 'forumPosts'])
        ->name('api.forum.posts');

    // Quiz results endpoint
    Route::get('/kuis/{quizId}/hasil', [$apiController::class, 'quizResults'])
        ->name('api.quiz.results');
});
