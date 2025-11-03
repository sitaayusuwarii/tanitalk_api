<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class LikeController extends Controller
{
    public function toggleLike(Request $request, $postId)
{
    $user = $request->user();
    $like = Like::where('post_id', $postId)->where('user_id', $user->id)->first();

    if ($like) {
        $like->delete();
        return response()->json(['liked' => false]);
    } else {
        Like::create(['post_id' => $postId, 'user_id' => $user->id]);
        return response()->json(['liked' => true]);
    }
}

}
