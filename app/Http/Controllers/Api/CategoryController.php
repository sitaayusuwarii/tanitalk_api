<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;

class CategoryController extends Controller
{
    public function index()
    {
        // Mengambil data terbaru dulu
        $categories = Category::orderBy('created_at', 'desc')->get();
        return response()->json($categories);
    }

    public function store(Request $request)
    {
        $request->validate([
            // Validasi input 'category'
            'category' => 'required|string|unique:categories,category',
        ]);

        $category = Category::create([
            'category' => $request->category, // Pastikan menggunakan request->category
        ]);

        return response()->json($category, 201);
    }

    public function destroy(string $id)
    {
        $category = Category::findOrFail($id);
        $category->delete();

        return response()->json(['message' => 'Kategori dihapus']);
    }
}