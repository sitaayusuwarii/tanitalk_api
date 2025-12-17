<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use Illuminate\Http\Request;

class CommentLikeController extends Controller
{
    /**
     * LIKE / UNLIKE komentar
     */
    public function toggle(Comment $comment)
    {
        $user = auth()->user();

        if (!$user) {
            return response()->json([
                'message' => 'Unauthenticated'
            ], 401);
        }

        // cek apakah user sudah like
        $alreadyLiked = $comment->likes()
            ->where('user_id', $user->id)
            ->exists();

        if ($alreadyLiked) {
            // UNLIKE
            $comment->likes()->detach($user->id);
        } else {
            // LIKE
            $comment->likes()->attach($user->id);
        }

        return response()->json([
            'liked' => !$alreadyLiked,
            'likes_count' => $comment->likes()->count(),
        ]);
    }
}
