<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\Comment;
use Illuminate\Http\Request;
use App\Models\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CommentController extends Controller
{
    // 🔹 Ambil semua komentar + balasan untuk 1 post
    public function index(Post $post)
    {
        $comments = Comment::where('post_id', $post->id)
            ->whereNull('parent_id') // ⬅️ komentar utama
            ->with([
                'user:id,name,avatar_url',
                'replies.user:id,name,avatar_url',
            ])
            ->withCount('replies', 'likes') // ⬅️ buat Tampilkan Balasan (x)
             ->withExists([
                'likes as is_liked' => fn ($q) =>
                    $q->where('user_id', auth()->id())
            ])
            ->latest()
            ->get()
            ->map(function ($comment) {
                // avatar komentar utama
                $comment->user->avatar_full = $comment->user->avatar_url
                    ? asset('storage/' . $comment->user->avatar_url)
                    : null;

                // avatar balasan
                $comment->replies->map(function ($reply) {
                    $reply->user->avatar_full = $reply->user->avatar_url
                        ? asset('storage/' . $reply->user->avatar_url)
                        : null;
                    return $reply;
                });

                return $comment;
            });

        return response()->json($comments);
    }

    // 🔹 Simpan komentar / balasan
    public function store(Request $request, Post $post)
    {
        Log::info('📩 Comment store() terpanggil');
        Log::info('User ID: ' . Auth::id());
        Log::info('Post ID: ' . $post->id);
        Log::info('Isi komentar: ' . $request->content);
        Log::info('Parent ID: ' . $request->parent_id);

        $request->validate([
            'content' => 'required|string',
            'parent_id' => 'nullable|exists:comments,id',
        ]);

        if (!Auth::check()) {
            return response()->json(['message' => 'User belum login'], 401);
        }

        // 1️⃣ SIMPAN KOMENTAR / BALASAN
        $comment = Comment::create([
            'post_id'   => $post->id,
            'user_id'   => Auth::id(),
            'content'   => $request->content,
            'parent_id' => $request->parent_id, // ⬅️ penting
        ]);

        // 2️⃣ 🔔 NOTIFIKASI (hanya komentar utama)
        if ($post->user_id !== Auth::id() && !$request->parent_id) {
            Notification::create([
                'user_id'      => $post->user_id,
                'from_user_id' => Auth::id(),
                'post_id'      => $post->id,
                'type'         => 'comment',
                'comment'      => $request->content,
            ]);
        }

        // 3️⃣ AMBIL DATA KOMENTAR BARU
        $newComment = Comment::with(['user:id,name,avatar_url'])
            ->find($comment->id);

        // 4️⃣ AVATAR FULL
        $newComment->user->avatar_full = $newComment->user->avatar_url
            ? asset('storage/' . $newComment->user->avatar_url)
            : null;

        return response()->json($newComment);
    }
}
