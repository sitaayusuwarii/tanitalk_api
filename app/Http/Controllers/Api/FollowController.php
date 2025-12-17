<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Notification;
use Illuminate\Support\Facades\DB;

class FollowController extends Controller
{
    public function toggleFollow($id)
    {
        $authId = auth()->id();

        if ($authId == $id) {
            return response()->json(['message' => 'Invalid action'], 400);
        }

        $already = DB::table('follows')
            ->where('follower_id', $authId)
            ->where('followed_id', $id)
            ->first();

        if ($already) {
            // UNFOLLOW
            DB::table('follows')
                ->where('follower_id', $authId)
                ->where('followed_id', $id)
                ->delete();

            return response()->json([
                'success' => true,
                'message' => 'Unfollowed',
                'is_following' => false
            ]);
        }

        // ✅ FOLLOW
        DB::table('follows')->insert([
            'follower_id' => $authId,
            'followed_id' => $id,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // 🧹 HAPUS NOTIF FOLLOW LAMA (ANTI DOBEL)
        Notification::where('user_id', $id)
            ->where('from_user_id', $authId)
            ->where('type', 'follow')
            ->delete();

        // 🔔 BUAT NOTIF FOLLOW BARU
        Notification::create([
            'user_id'      => $id,
            'from_user_id' => $authId,
            'type'         => 'follow',
            'is_read'      => false,
            'post_id'      => null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Followed',
            'is_following' => true
        ]);
    }
}
