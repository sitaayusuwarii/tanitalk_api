<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
{
    $type = $request->query('type'); // like / comment / follow

    $notifications = Notification::where('user_id', auth()->id())
        ->when($type && $type !== 'semua', function ($q) use ($type) {
            $q->where('type', $type);
        })
        ->with([
            'fromUser:id,name,avatar_url',
            'post.user:id,name,avatar_url',
            'post.category:id,category'
        ])
        ->latest()
        ->get()
        ->map(function ($notif) {

            $data = [
                'id' => $notif->id,
                'type' => $notif->type,
                'is_read' => (bool) $notif->is_read,
                'created_at' => $notif->created_at->diffForHumans(),

                'from_user' => [
                    'id' => $notif->fromUser->id,
                    'name' => $notif->fromUser->name,
                    'avatar_url' => $notif->fromUser->avatar_url,
                ],
            ];

            // 🔔 FOLLOW NOTIFICATION (TANPA POST)
            if ($notif->type === 'follow') {
                $isFollowingBack = \DB::table('follows')
                    ->where('follower_id', auth()->id())
                    ->where('followed_id', $notif->from_user_id)
                    ->exists();

                $data['follow'] = [
                    'is_following_back' => $isFollowingBack
                ];

                return $data;
            }

            // ❤️ LIKE / 💬 COMMENT (PASTI ADA POST)
            $data['post'] = [
                'id' => $notif->post->id,
                'user' => [
                    'id' => $notif->post->user->id,
                    'name' => $notif->post->user->name,
                    'avatar_url' => $notif->post->user->avatar_url,
                ],
                'image_url' => $notif->post->image_url,
                'description' => $notif->post->description,
                'category' => $notif->post->category,
                'liked_by_user' => true,
                'likes_count' => $notif->post->likes()->count(),
                'comments_count' => $notif->post->comments()->count(),
                'created_at' => $notif->post->created_at,
            ];

            $data['comment'] = $notif->comment;

            return $data;
        });

    return response()->json($notifications);
    }


    public function markAsRead(Notification $notification)
    {
        // optional: security check
        if ($notification->user_id !== auth()->id()) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $notification->update(['is_read' => true]);

        return response()->json(['message' => 'Marked as read']);
    }

    public function destroy(Notification $notification)
{
    // keamanan
    if ($notification->user_id !== auth()->id()) {
        return response()->json(['message' => 'Forbidden'], 403);
    }

    $notification->delete();

    return response()->json([
        'success' => true
    ]);
}


}
