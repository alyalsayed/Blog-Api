<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\TagController;
use App\Http\Controllers\Api\PostController;
use App\Http\Controllers\Api\StatsController;


Route::get('/', function () {
        return 'API';
});

Route::controller(AuthController::class)->group(function () {
    Route::post('register', 'register');
    Route::post('login', 'login');
    Route::post('verify', 'verifyCode');
    Route::post('logout', 'logout')->middleware('auth:sanctum');
});

Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('tags', TagController::class);
    Route::get('/posts/trashed', [PostController::class, 'trashed'])->name('posts.trashed');

    Route::apiResource('posts', PostController::class);

    Route::patch('/posts/restore/{post}', [PostController::class, 'restore'])->name('posts.restore');

    Route::get('/stats', [StatsController::class, 'index'])->name('stats.index');

});