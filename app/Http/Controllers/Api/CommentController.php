<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller; // ✅ penting!
use App\Models\Post;
use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CommentController extends Controller
{
    // 🔹 Ambil semua komentar untuk 1 post
    public function index(Post $post)
    {
        $comments = Comment::where('post_id', $post->id)
            ->with(['user:id,name,profile_picture'])
            ->latest()
            ->get();

        return response()->json($comments);
    }

    
public function store(Request $request, Post $post)
{
    Log::info('📩 Comment store() terpanggil'); // ✅ tambahkan log
    Log::info('User ID: ' . Auth::id());
    Log::info('Post ID: ' . $post->id);
    // ❌ OLD: Log::info('Isi komentar: ' . $request->comment);
    Log::info('Isi komentar: ' . $request->content); // ✅ NEW: Ganti menjadi content

    $request->validate([
        // ❌ OLD: 'comment' => 'required|string',
        'content' => 'required|string', // ✅ NEW: Ganti menjadi content
    ]);

    if (!Auth::check()) {
        return response()->json(['message' => 'User belum login'], 401);
    }

    $comment = Comment::create([
        'post_id' => $post->id,
        'user_id' => Auth::id(),
        // ❌ OLD: 'content' => $request->comment,
        'content' => $request->content, // ✅ NEW: Ganti menjadi content
    ]);

    $newComment = Comment::with(['user:id,name,profile_picture'])
        ->find($comment->id);

    Log::info('✅ Komentar berhasil disimpan', ['comment_id' => $comment->id]);

    return response()->json($newComment);
}
}
