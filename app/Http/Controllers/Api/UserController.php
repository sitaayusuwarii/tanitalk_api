<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\User;

class UserController extends Controller
{
    public function update(Request $request)
    {
        $user = Auth::user();

        // Validasi input
        $request->validate([
            'name' => 'required|string|max:255',
            'bio' => 'nullable|string',
            'location' => 'nullable|string',
            'phone' => 'nullable|string',
            'avatar' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        // Upload avatar jika ada
        if ($request->hasFile('avatar')) {
            $path = $request->file('avatar')->store('avatars', 'public');
            $user->avatar_url = asset('storage/' . $path);
        }

        // Update data lainnya
        $user->name = $request->name;
        $user->bio = $request->bio;
        $user->location = $request->location;
        $user->phone = $request->phone;
        $user->save();

        return response()->json([
            'message' => 'Profil berhasil diperbarui',
            'user' => $user
        ]);
    }

   public function show($id)
{
    $authId = auth()->id();
    $user = User::find($id);

    if (!$user) {
        return response()->json([
            'success' => false,
            'message' => 'User not found'
        ], 404);
    }

    // CEK apakah user login sudah follow user ini
    $isFollowing = \DB::table('follows')
        ->where('follower_id', $authId)
        ->where('followed_id', $id)
        ->exists();

    return response()->json([
        'success' => true,
        'user' => $user,
        'is_following' => $isFollowing,
        'followers_count' => $user->followers()->count(),
        'following_count' => $user->following()->count(),
    ]);
}

public function search(Request $request)
{
    $query = $request->query('q');

    if (!$query) {
        return response()->json([
            'data' => []
        ], 200);
    }

    $users = User::where('name', 'LIKE', "%{$query}%")
        ->select('id', 'name', 'avatar_url', 'bio')
        ->get();

    return response()->json([
        'success' => true,
        'data' => $users
    ]);
}




}
