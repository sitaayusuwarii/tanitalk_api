<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PostController;
use App\Http\Controllers\Api\LikeController;
use App\Http\Controllers\Api\CommentController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\FollowController;
use App\Http\Controllers\Api\SavedPostController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\CommentLikeController;

Route::prefix('/auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
});

Route::get('/categories', [CategoryController::class, 'index']);


Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return response()->json($request->user());
});

Route::middleware('auth:sanctum')->get('/categories/{id}/posts', [CategoryController::class, 'postsByCategory']);


// routes/api.php
Route::middleware('auth:sanctum')->get('/posts/following', [PostController::class, 'followingPosts']);


Route::middleware('auth:sanctum')->group(function () {
    Route::post('/users/{id}/follow', [FollowController::class, 'toggleFollow']);
    Route::get('/posts', [PostController::class, 'index']);
    Route::post('/posts', [PostController::class, 'store']);
    Route::get('/my-posts', [PostController::class, 'myPosts']);
    Route::get('/user/{id}', [UserController::class, 'show']);
    Route::get('/users/{id}/posts', [PostController::class, 'userPosts']);
    Route::post('/user/update', [UserController::class, 'update']);
    Route::post('/posts/{id}/like', [LikeController::class, 'toggleLike']);

    Route::get('/posts/{post}/comments', [CommentController::class, 'index']);
    Route::post('/posts/{post}/comments', [CommentController::class, 'store']);
    Route::delete('/posts/{id}', [PostController::class, 'destroy']);


    Route::post('/posts/{post}/save', [SavedPostController::class, 'save']);
    Route::post('/posts/{post}/unsave', [SavedPostController::class, 'unsave']);

    Route::get('/saved-posts', [SavedPostController::class, 'list']);
    Route::get('/posts/{post}/is-saved', [SavedPostController::class, 'isSaved']);

    Route::get('/posts/following', [PostController::class, 'followingPosts']);
    Route::get('/categories/search', [CategoryController::class, 'search']);
    Route::get('/users/search', [UserController::class, 'search']);

    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'markAsRead']);

    Route::delete('/notifications', [NotificationController::class, 'destroyAll']);
    Route::delete('/notifications/{notification}', [NotificationController::class, 'destroy']);

    Route::post('/comments/{comment}/like', [CommentLikeController::class, 'toggle']);

});