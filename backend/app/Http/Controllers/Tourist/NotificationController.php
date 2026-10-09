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
            if (!Schema::hasTable('notifications')) {
                return response()->json([
                    'notifications' => [],
                    'unread_count'  => 0,
                ]);
            }

            // Ensure a Welcome notification exists only if not previously dismissed
            $hasWelcome = Notification::where('user_id', $user->id)
                ->where('type', 'welcome')
                ->exists();

            $welcomeDismissed = \Illuminate\Support\Facades\Cache::get("welcome_notif_dismissed_{$user->id}", false);

            if (!$hasWelcome && !$welcomeDismissed) {
                $hadNotificationsBefore = \Illuminate\Support\Facades\Cache::get("user_had_notifs_{$user->id}", false);
                if (!$hadNotificationsBefore) {
                    $firstName = trim(explode(' ', $user->name ?? 'Explorer')[0]);
                    Notification::createSafely(
                        $user->id,
                        'welcome',
                        'Welcome to Intan Elyu',
                        "Welcome to Intan Elyu, {$firstName}! Explore top tourist spots in La Union, plan your personalized itineraries, and earn XP with AR check-ins!",
                        [
                            'module'     => 'welcome',
                            'action_url' => 'dashboard'
                        ]
                    );
                    \Illuminate\Support\Facades\Cache::forever("user_had_notifs_{$user->id}", true);
                }
            }

            $notifications = Notification::where('user_id', $user->id)
                ->orderByDesc('created_at')
                ->limit(50)
                ->get()
                ->map(function ($item) {
                    $item->elapsed_seconds = max(0, now()->diffInSeconds($item->created_at));
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
                'error'         => $e->getMessage()
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
            }
        } catch (\Throwable $e) {
            return response()->json(['error' => 'Failed to delete notification.'], 500);
        }

        $unreadCount = Notification::where('user_id', $user->id)
            ->where('is_read', false)
            ->count();

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
