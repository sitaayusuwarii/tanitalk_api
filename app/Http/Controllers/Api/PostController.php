<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Post;
use Illuminate\Support\Facades\Auth;

class PostController extends Controller
{
    // 📦 Ambil semua post
 public function index(Request $request)
{
    $userId = Auth::id(); // ambil user yang sedang login

    $query = Post::with(['user', 'likes'])
                 ->withCount(['likes', 'comments'])
                 ->latest();

    $posts = $query->get()->map(function($p) use ($userId) {
        return [
            'id' => $p->id,
            'user' => $p->user->name,
            'image_url' => $p->image_url 
                ? (str_starts_with($p->image_url, 'http') 
                    ? $p->image_url 
                    : asset('storage/' . $p->image_url))
                : null,
            'description' => $p->description,
            'category' => $p->category,
            'liked_by_user' => $p->likes->where('user_id', $userId)->isNotEmpty(),
            'likes_count' => $p->likes_count,
            'comments_count' => $p->comments_count,
            'created_at' => $p->created_at->toDateTimeString(),
        ];
    });

    return response()->json($posts);
}





    // 📝 Buat post baru
    public function store(Request $request)
    {
        $request->validate([
            'image' => 'nullable|image|max:2048',
            'description' => 'nullable|string',
            'category' => 'nullable|string',
        ]);

        $imageUrl = null;

        // Simpan gambar jika ada
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('posts', 'public');
            $imageUrl = asset("storage/$path");
        }

        $post = Post::create([
            'user_id' => Auth::id(),
            'image_url' => $imageUrl,
            'description' => $request->description,
            'category' => $request->category,
        ]);

        // kembalikan respons JSON
       return response()->json([
            'id' => $post->id,
            'user' => $post->user->name ?? Auth::user()->name,
            'image_url' => $post->image_url,
            'description' => $post->description,
            'category' => $post->category,
            'likes_count' => 0,
            'comments_count' => 0,
            'liked_by_user' => false, // default baru dibuat user pasti belum like
            'created_at' => $post->created_at->toDateTimeString(),
        ], 201);
    }

    public function myPosts(Request $request)
{
    // Ambil user yang sedang login
    $user = $request->user();

    // Ambil semua postingan milik user tersebut
    $posts = $user->posts()->with(['likes', 'comments'])->latest()->get();

    // Kirim response dalam bentuk JSON
    return response()->json([
        'success' => true,
        'posts' => $posts
    ]);
}

public function destroy($id)
{
    $post = Post::find($id);
    if (!$post) {
        return response()->json(['message' => 'Post tidak ditemukan'], 404);
    }

    $post->delete();
    return response()->json(['message' => 'Post berhasil dihapus'], 200);
}


}
