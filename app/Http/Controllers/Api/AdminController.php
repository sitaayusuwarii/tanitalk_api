<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Post;
use App\Models\Comment;
use App\Models\Category;

class AdminController extends Controller
{
    public function dashboardStats()
    {
        // Menghitung jumlah data langsung dari database (Sangat Cepat)
        $stats = [
            'totalUsers'      => User::count(),
            'totalPosts'      => Post::count(),
            'totalCategories' => Category::count(),
            'totalComments'   => Comment::count(),
        ];

        return response()->json($stats);
    }

    public function getAllUsers()
    {
        // Ambil semua user, urutkan dari yang terbaru
        // withCount('posts') akan otomatis menghitung jumlah postingan user
        $users = User::withCount('posts')->orderBy('created_at', 'desc')->get();

        return response()->json($users);
    }
}