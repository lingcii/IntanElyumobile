<?php

namespace App\Http\Controllers\Tourist;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class NotificationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        try {
            // Sync upcoming trip reminders (1 day before & day of trip)
            ItineraryController::syncUpcomingTripReminders((int) $user->id);

            // Auto-prune read notifications older than 30 days once per day per user
            if (\Illuminate\Support\Facades\Cache::add("notif_prune:{$user->id}", 1, 86400)) {
                try {
                    Notification::where('user_id', $user->id)
                        ->where('is_read', true)
                        ->where('created_at', '<', now()->subDays(30))
                        ->delete();
                } catch (\Throwable $pruneErr) {}
            }

            $notifications = Notification::where('user_id', $user->id)
                ->orderByDesc('created_at')
                ->limit(50)
                ->get()
                ->map(function ($item) {
                    $createdTs = $item->created_at ? \Carbon\Carbon::parse($item->created_at)->timestamp : now()->timestamp;
                    $item->elapsed_seconds = max(0, now()->timestamp - $createdTs);
                    $item->server_time = now()->timestamp;
                    return $item;
                });

            $unreadCount = Notification::where('user_id', $user->id)
                ->where('is_read', false)
                ->count();

            return response()->json([
                'notifications' => $notifications,
                'unread_count'  => $unreadCount,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'notifications' => [],
                'unread_count'  => 0,
            ]);
        }
    }

    public function markRead(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        try {
            if (Schema::hasTable('notifications')) {
                Notification::where('id', $id)
                    ->where('user_id', $user->id)
                    ->update(['is_read' => true]);
            }
        } catch (\Throwable $e) {}

        return response()->json(['message' => 'Marked as read.']);
    }

    public function markAllRead(Request $request): JsonResponse
    {
        $user = $request->user();

        try {
            if (Schema::hasTable('notifications')) {
                Notification::where('user_id', $user->id)
                    ->where('is_read', false)
                    ->update(['is_read' => true]);
            }
        } catch (\Throwable $e) {}

        return response()->json(['message' => 'All notifications marked as read.']);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        try {
            if (Schema::hasTable('notifications')) {
                $notif = Notification::where('id', $id)
                    ->where('user_id', $user->id)
                    ->first();

                if ($notif) {
                    if ($notif->type === 'welcome') {
                        \Illuminate\Support\Facades\Cache::forever("welcome_notif_dismissed_{$user->id}", true);
                    }
                    $notif->delete();
                }

                $unreadCount = Notification::where('user_id', $user->id)
                    ->where('is_read', false)
                    ->count();
            } else {
                $unreadCount = 0;
            }
        } catch (\Throwable $e) {
            return response()->json(['error' => 'Failed to delete notification.'], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'Notification deleted.',
            'unread_count' => $unreadCount
        ]);
    }

    public function clearAll(Request $request): JsonResponse
    {
        $user = $request->user();

        try {
            if (Schema::hasTable('notifications')) {
                \Illuminate\Support\Facades\Cache::forever("welcome_notif_dismissed_{$user->id}", true);
                \Illuminate\Support\Facades\Cache::forever("user_had_notifs_{$user->id}", true);
                Notification::where('user_id', $user->id)->delete();
            }
        } catch (\Throwable $e) {
            return response()->json(['error' => 'Failed to clear notifications.'], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'All notifications deleted.',
            'unread_count' => 0
        ]);
    }
}
