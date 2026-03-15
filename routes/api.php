<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FeedController;
use App\Http\Controllers\CommentController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware(['auth:sanctum', 'verified']);

// Feed routes - session-based auth via Sanctum SPA
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/feed', [FeedController::class , 'getFeed']);
    Route::post('/posts/{post}/like', [FeedController::class , 'likePost']);
    Route::post('/users/{userId}/follow', [FeedController::class , 'followUser']);

    // Comment routes
    Route::get('/posts/{post}/comments', [CommentController::class , 'getComments']);
    Route::post('/posts/{post}/comments', [CommentController::class , 'addComment']);
    Route::post('/comments/{comment}/like', [CommentController::class , 'likeComment']);
});