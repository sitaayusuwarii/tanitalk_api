<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Like;
use App\Models\Post;
use App\Models\Notification;
use Illuminate\Support\Facades\Auth;

class LikeController extends Controller
{
    public function toggleLike($id)
{
    $user = Auth::user();
    $post = Post::findOrFail($id);

    $like = Like::where('user_id', $user->id)
                ->where('post_id', $post->id)
                ->first();

    if ($like) {
        // ❌ UNLIKE
        $like->delete();
        $liked = false;

        // (opsional) hapus notif like lama
        Notification::where('type', 'like')
            ->where('post_id', $post->id)
            ->where('from_user_id', $user->id)
            ->delete();

    } else {
        // ❤️ LIKE
        Like::create([
            'user_id' => $user->id,
            'post_id' => $post->id,
        ]);
        $liked = true;

        // 🔔 BUAT NOTIF LIKE
        if ($post->user_id !== $user->id) {
            Notification::create([
                'user_id'      => $post->user_id, // pemilik post
                'from_user_id' => $user->id,       // yang like
                'post_id'      => $post->id,
                'type'         => 'like',
                'is_read'      => false,
            ]);
        }
    }

    return response()->json([
        'liked_by_user' => $liked,
        'likes_count'   => $post->likes()->count(),
    ]);
}



//     public function like(Post $post)
// {
//     $user = auth()->user();

//     $post->likes()->syncWithoutDetaching([$user->id]);

//     if ($post->user_id !== $user->id) {
//         Notification::create([
//             'user_id' => $post->user_id,
//             'from_user_id' => $user->id,
//             'post_id' => $post->id,
//             'type' => 'like',
//         ]);
//     }

//     return response()->json(['message' => 'Liked']);
// }

}
