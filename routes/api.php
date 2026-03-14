<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FeedController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware(['auth:sanctum', 'verified']);

// Feed routes - session-based auth via Sanctum SPA
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/feed', [FeedController::class , 'getFeed']);
    Route::post('/posts/{post}/like', [FeedController::class , 'likePost']);
    Route::post('/posts/{post}/comments', [FeedController::class , 'addComment']);
    Route::post('/users/{userId}/follow', [FeedController::class , 'followUser']);
});