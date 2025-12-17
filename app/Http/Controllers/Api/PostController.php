<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Post;
use Illuminate\Support\Facades\Auth;

class PostController extends Controller
{
    // 📦 Ambil semua post
   // 📦 Ambil semua post (dengan filter kategori opsional)
public function index(Request $request)
{
    $currentUser = Auth::user();

    $categoryId = $request->query('category_id'); // Ambil category_id dari query

    $query = Post::with(['user', 'likes', 'category'])
        ->withCount(['likes', 'comments'])
        ->latest();

    // Filter jika category_id ada dan bukan 0 (0 = Semua)
    if ($categoryId && $categoryId != 0) {
        $query->where('category_id', $categoryId);
    }

    $posts = $query->get()->map(function ($p) use ($currentUser) {

        return [
            'id' => $p->id,

            'user' => [
                'id' => $p->user->id,
                'name' => $p->user->name,
                'avatar_url' => $p->user->avatar_url,
                'is_following' => $currentUser 
                    && $currentUser->id !== $p->user->id
                    ? $currentUser->following()->where('followed_id', $p->user->id)->exists()
                    : false,
            ],

            'image_url' => $p->image_url
                ? (str_starts_with($p->image_url, 'http')
                    ? $p->image_url
                    : asset('storage/' . $p->image_url))
                : null,

            'description' => $p->description,

            'category' => $p->category ? [
                'id' => $p->category->id,
                'category' => $p->category->category,
            ] : null,

            'liked_by_user' => $currentUser
                ? $p->likes->where('user_id', $currentUser->id)->isNotEmpty()
                : false,

            'likes_count' => $p->likes_count,
            'comments_count' => $p->comments_count,
            'created_at' => $p->created_at->toDateTimeString(),
        ];
    });

    return response()->json([
        'success' => true,
        'data' => $posts
    ]);
}


    // 📝 Buat post baru
    public function store(Request $request)
    {
        $request->validate([
            'image'        => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'description'  => 'nullable|string|max:255',
            'category_id'  => 'required|exists:categories,id',
        ]);

        $imageUrl = null;

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('posts', 'public');
            $imageUrl = $path;
        }

        $post = Post::create([
            'user_id'     => Auth::id(),
            'image_url'   => $imageUrl,
            'description' => $request->description,
            'category_id' => $request->category_id,
        ]);

        return response()->json([
            'id' => $post->id,

            'user' => [
                'id' => $post->user->id,
                'name' => $post->user->name,
                'avatar_url' => $post->user->avatar_url,
                'is_following' => false,
            ],

            'image_url' => $post->image_url
                ? asset('storage/' . $post->image_url)
                : null,

            'description' => $post->description,

            // 🔥 FIX CATEGORY FIELD NAME
            'category' => [
                'id' => $post->category->id,
                'category' => $post->category->category,
            ],

            'likes_count' => 0,
            'comments_count' => 0,
            'liked_by_user' => false,
            'created_at' => $post->created_at->toDateTimeString(),
        ], 201);
    }

    // 📌 Ambil post milik user sendiri
    public function myPosts(Request $request)
    {
        $user = $request->user();

        $posts = $user->posts()
            ->with(['likes', 'comments'])
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'posts' => $posts
        ]);
    }

    // ❌ Hapus post
    public function destroy($id)
    {
        $post = Post::find($id);

        if (!$post) {
            return response()->json(['message' => 'Post tidak ditemukan'], 404);
        }

        $post->delete();
        return response()->json(['message' => 'Post berhasil dihapus'], 200);
    }

    // 📌 Ambil post milik user lain
    public function userPosts($id)
    {
        $posts = Post::where('user_id', $id)
            ->with('user')
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'posts' => $posts
        ]);
    }

    // 🫂 Post dari user yang di-follow
   public function followingPosts(Request $request)
{
    $currentUser = $request->user();

    $followingIds = $currentUser->following()->pluck('followed_id');

    $posts = Post::whereIn('user_id', $followingIds)
        ->with(['user', 'likes', 'category'])
        ->withCount(['likes', 'comments'])
        ->latest()
        ->get()
        ->map(function ($p) use ($currentUser) {

            return [
                'id' => $p->id,

                'user' => [
                    'id' => $p->user->id,
                    'name' => $p->user->name,
                    'avatar_url' => $p->user->avatar_url,
                    'is_following' => true, // karena ini tab koneksi
                ],

                'image_url' => $p->image_url
                    ? (str_starts_with($p->image_url, 'http')
                        ? $p->image_url
                        : asset('storage/' . $p->image_url))
                    : null,

                'description' => $p->description,

                'category' => $p->category ? [
                    'id' => $p->category->id,
                    'category' => $p->category->category,
                ] : null,

                'liked_by_user' => $p->likes->where('user_id', $currentUser->id)->isNotEmpty(),
                'likes_count' => $p->likes_count,
                'comments_count' => $p->comments_count,
                'created_at' => $p->created_at->toDateTimeString(),
            ];
        });

    return response()->json([
        'success' => true,
        'data' => $posts
    ]);
}


}
