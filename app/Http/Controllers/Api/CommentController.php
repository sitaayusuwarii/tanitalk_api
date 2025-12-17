<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CommentController extends Controller
{
    public function index($id)
    {
        // Debug: Cek apakah ID masuk
        Log::info("Mengambil komentar untuk Post ID: " . $id);

        $comments = Comment::where('post_id', $id) 
            ->with(['user:id,name,profile_picture']) 
            ->orderBy('created_at', 'asc') //terlama di atas, terbaru di bawah
            ->get();

        return response()->json($comments);
    }

    // Terima parameter $id
    public function store(Request $request, $id)
    {
        Log::info('📩 Comment store() terpanggil untuk Post ID: ' . $id);

        $request->validate([
            'content' => 'required|string',
        ]);

        if (!Auth::check()) {
            return response()->json(['message' => 'User belum login'], 401);
        }

        // Cek manual apakah post ada
        $postExists = Post::where('id', $id)->exists();
        if (!$postExists) {
            return response()->json(['message' => 'Post tidak ditemukan'], 404);
        }

        $comment = Comment::create([
            'post_id' => $id, 
            'user_id' => Auth::id(),
            'content' => $request->input('content'),
        ]);

        // Return data lengkap dengan user agar bisa langsung tampil di Flutter
        $newComment = Comment::with(['user:id,name,profile_picture'])
            ->find($comment->id);

        return response()->json($newComment, 201);
    }
}