<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class UserNotificationController extends Controller
{
    public function index(Request $request)
    {
        $notifications = $request->user()
            ->userNotifications()
            ->latest('created_at')
            ->limit(100)
            ->get()
            ->map(fn(\App\Models\UserNotification $notification) => [
                'id' => $notification->id,
                'source' => $notification->source,
                'title' => $notification->title,
                'body' => $notification->body,
                'raw' => $notification->data,
                'createdAt' => optional($notification->created_at)?->toISOString(),
                'read' => !is_null($notification->read_at),
            ])
            ->values();

        return response()->json([
            'data' => $notifications,
        ]);
    }

    public function markRead(Request $request, \App\Models\UserNotification $notification)
    {
        abort_if($notification->user_id !== $request->user()->id, 403);

        if (is_null($notification->read_at)) {
            $notification->read_at = now();
            $notification->save();
        }

        return response()->json(['message' => 'Notification marked as read.']);
    }

    public function markAllRead(Request $request)
    {
        $request->user()
            ->userNotifications()
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json(['message' => 'All notifications marked as read.']);
    }

    public function clear(Request $request)
    {
        $request->user()->userNotifications()->delete();

        return response()->json(['message' => 'Notification inbox cleared.']);
    }
}
