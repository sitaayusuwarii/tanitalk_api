<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Post;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class PostController extends Controller
{
    // Ambil semua post
    public function index(Request $request)
    {
        $query = Post::query()->with('user')->latest();

        if ($request->has('user')) {
            $query->whereHas('user', fn($q) => $q->where('name', $request->user));
        }

        $posts = $query->get()->map(fn($p) => [
            'id' => $p->id,
            'user' => $p->user->name,
            'image_url' => $p->image_url,
            'description' => $p->description,
            'category' => $p->category,
            'created_at' => $p->created_at->toDateTimeString(),
        ]);

        return response()->json($posts);
    }

    // Buat post baru
   public function store(Request $request)
{
    $request->validate([
        'image' => 'nullable|image|max:2048',
        'description' => 'nullable|string',
        'category' => 'nullable|string',
    ]);

    $imageUrl = null;
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

    // return data dengan format sama seperti index()
    return response()->json([
        'id' => $post->id,
        'user' => $post->user->name ?? Auth::user()->name,
        'image_url' => $post->image_url,
        'description' => $post->description,
        'likes' => $post->likes->count(),
        'comments' => $post->comments->count(),
        'category' => $post->category,
        'created_at' => $post->created_at->toDateTimeString(),
    ], 201);
}

}
