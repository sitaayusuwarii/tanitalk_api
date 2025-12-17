<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SavedPost;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SavedPostController extends Controller
{
    // Simpan postingan
    public function save(Post $post)
    {
        $saved = SavedPost::firstOrCreate([
            'user_id' => Auth::id(),
            'post_id' => $post->id,
        ]);

        return response()->json([
            'message' => 'Post saved successfully',
            'saved' => true
        ]);
    }

    // Hapus dari saved
    public function unsave(Post $post)
    {
        SavedPost::where('user_id', Auth::id())
            ->where('post_id', $post->id)
            ->delete();

        return response()->json([
            'message' => 'Post unsaved successfully',
            'saved' => false
        ]);
    }

    // Ambil semua postingan yang disimpan
    public function list()
    {
        $savedPosts = SavedPost::where('user_id', Auth::id())
            ->with(['post', 'post.user'])
            ->latest()
            ->get();

        return response()->json($savedPosts);
    }

    // Cek apakah sudah disimpan
    public function isSaved(Post $post)
    {
        $exists = SavedPost::where('user_id', Auth::id())
            ->where('post_id', $post->id)
            ->exists();

        return response()->json(['saved' => $exists]);
    }
}
