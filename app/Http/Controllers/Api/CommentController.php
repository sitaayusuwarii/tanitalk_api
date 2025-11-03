<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function store(Request $request, $postId)
{
    $request->validate(['content' => 'required']);
    $user = $request->user();

    $comment = Comment::create([
        'user_id' => $user->id,
        'post_id' => $postId,
        'content' => $request->content,
    ]);

    return response()->json([
        'message' => 'Komentar ditambahkan',
        'comment' => $comment->load('user'),
    ]);
}

}
