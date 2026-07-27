<?php

namespace App\Http\Controllers\Tourist;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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

            $notifications = Notification::where('user_id', $user->id)
                ->orderByDesc('created_at')
                ->limit(50)
                ->get();

            // If user has no notifications, create welcome notification
            if ($notifications->isEmpty()) {
                $welcomeNotif = Notification::createSafely(
                    $user->id,
                    'welcome',
                    '🌴 Welcome to Intan-Elyu!',
                    "Welcome {$user->name}! Explore La Union tourist spots, plan itineraries, and earn XP with AR check-ins!"
                );
                if ($welcomeNotif) {
                    $notifications = collect([$welcomeNotif]);
                }
            }

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
}
