<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Post;

class AdminUserController extends Controller
{
    /**
     * Menampilkan detail user beserta statistik dan postingannya.
     */
    public function show($id)
    {
        try {
            // 1. Ambil User berdasarkan ID
            // withCount akan otomatis menghitung jumlah 'posts' dan 'comments' (yang ditulis user)
            $user = User::withCount(['posts', 'comments'])->findOrFail($id);

            // 2. Ambil semua postingan milik user tersebut
            // Kita juga hitung like dan komentar per postingan
            $posts = Post::where('user_id', $id)
                         ->withCount(['likes', 'comments']) // Hitung like & komen per post
                         ->latest() // Urutkan dari yang terbaru
                         ->get();

            // 3. Hitung Total Like yang DITERIMA User (Akumulasi dari semua post)
            // Kita jumlahkan kolom 'likes_count' dari collection $posts di atas
            $totalLikesReceived = $posts->sum('likes_count');

            // 4. Siapkan Data Respon yang Rapi
            // Kita format manual agar mudah dibaca di Flutter
            $userData = [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'role' => $user->role,
                'status' => 'active', // Bisa disesuaikan jika ada kolom status di DB
                'joined' => $user->created_at->format('d M Y'), // Format tanggal cantik
                'profile_picture' => $user->profile_picture,
                
                // Statistik
                'posts_count' => $user->posts_count,       // Jumlah post user
                'comments_count' => $user->comments_count, // Jumlah komen user
                'likes_received_count' => $totalLikesReceived, // Total like diterima
            ];

            return response()->json([
                'message' => 'Berhasil mengambil detail user',
                'user' => $userData,
                'posts' => $posts
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'message' => 'User tidak ditemukan'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan server',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}