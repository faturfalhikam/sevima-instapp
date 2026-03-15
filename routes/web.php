<?php

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\FeedController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\EditProfileController;
use App\Http\Controllers\PublicProfileController;
use App\Http\Controllers\UserSearchController;


Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/', [FeedController::class , 'index'])->name('dashboard');
    Route::get('/posts/create', [PostController::class , 'create'])->name('posts.create');
    Route::post('/posts', [PostController::class , 'store'])->name('posts.store');
    Route::get('/profile', [ProfileController::class , 'show'])->name('profile.detail');
    Route::get('/profile/edit', [EditProfileController::class , 'edit'])->name('profile.edit');
    Route::post('/profile/edit', [EditProfileController::class , 'update'])->name('profile.update');
    Route::get('/users/search', [UserSearchController::class , 'search'])->name('users.search');
    Route::get('/users/{user}', [PublicProfileController::class , 'show'])->name('users.show');
    Route::post('/users/{user}/follow', [PublicProfileController::class , 'toggleFollow'])->name('users.follow');
});