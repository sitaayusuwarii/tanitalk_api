<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Post;

class CategoryController extends Controller
{
    // GET /categories
    public function index()
    {
        return response()->json([
            'success' => true,
            'data' => Category::all()->map(function ($c) {
                return [
                    'id' => $c->id,
                    'category' => $c->category, // ← BENAR
                ];
            })
        ]);
    }

    // GET /categories/{id}/posts
    public function postsByCategory($id)
    {
        $posts = Post::where('category_id', $id)
            ->with(['user', 'likes', 'comments', 'category'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $posts
        ]);
    }

    // POST /categories
    public function store(Request $request)
    {
        $request->validate([
            'category' => 'required|string|unique:categories,category',
        ]);

        $category = Category::create([
            'category' => $request->category,
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $category->id,
                'category' => $category->category,
            ],
        ]);
    }

    // DELETE /categories/{id}
    public function destroy($id)
    {
        $category = Category::findOrFail($id);
        $category->delete();

        return response()->json(['message' => 'Kategori dihapus']);
    }

public function search(Request $request)
{
    $q = $request->query('q');

    $category = Category::where('category', 'LIKE', "%$q%")->first();

    return response()->json([
        'success' => true,
        'data' => $category,
    ]);
}

}
