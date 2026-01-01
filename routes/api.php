<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PostController;
use App\Http\Controllers\Api\LikeController;
use App\Http\Controllers\Api\CommentController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\AdminUserController;

// --- PUBLIC ROUTES (Siapa saja bisa akses) ---
Route::prefix('/auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']); // Default daftar jadi Petani
    Route::post('/login', [AuthController::class, 'login']);
});

Route::apiResource('categories', CategoryController::class);

// --- PROTECTED ROUTES (Harus Login) ---
Route::middleware('auth:sanctum')->group(function () {
    
    // User Info & Logout
    Route::get('/user', function (Request $request) {
        return response()->json($request->user());
    });
    Route::post('/logout', [AuthController::class, 'logout']); // Tambahan untuk logout

    // Fitur Umum (Petani & Admin bisa akses)
    Route::get('/posts', [PostController::class, 'index']);
    Route::post('/posts', [PostController::class, 'store']);
    Route::get('/my-posts', [PostController::class, 'myPosts']);
    Route::post('/user/update', [UserController::class, 'update']);
    Route::post('/posts/{id}/like', [LikeController::class, 'toggleLike']);
    Route::get('/posts/{id}/comments', [CommentController::class, 'index']);
    Route::post('/posts/{id}/comments', [CommentController::class, 'store']);
    Route::delete('/posts/{id}', [PostController::class, 'destroy']);

    // === KHUSUS SUPER ADMIN (Admin Biasa/Petani DILARANG Masuk Sini) ===
    // 'role:super_admin' memanggil alias yang kamu buat di bootstrap/app.php
    Route::middleware(['role:super_admin'])->group(function () {
        
        // Endpoint untuk Super Admin membuat akun Admin atau Petani baru
        Route::post('/admin/create-user', [AuthController::class, 'createUserByAdmin']);

        // Endpoint untuk Super Admin melihat semua user
        Route::get('/admin/users', [App\Http\Controllers\Api\AdminController::class, 'getAllUsers']);

        // Endpoint untuk Super Admin membuat akun Admin atau Petani baru
        Route::post('/admin/create-user', [AuthController::class, 'createUserByAdmin']);
        
    });

    //Endpoint khusus Dashboard (Admin dan Super Admin)
    Route::middleware(['role:admin,super_admin'])->group(function () {
        Route::get('/admin/stats', [App\Http\Controllers\Api\AdminController::class, 'dashboardStats']);
    
        // Route Detail User 
        Route::get('/admin/users/{id}', [AdminUserController::class, 'show']);
        });

});