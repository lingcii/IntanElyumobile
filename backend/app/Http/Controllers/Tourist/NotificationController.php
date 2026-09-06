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

            // Ensure a Welcome notification exists in the notifications table for this tourist
            $hasWelcome = Notification::where('user_id', $user->id)
                ->where('type', 'welcome')
                ->exists();

            if (!$hasWelcome) {
                $firstName = trim(explode(' ', $user->name ?? 'Explorer')[0]);
                Notification::createSafely(
                    $user->id,
                    'welcome',
                    '👋 Welcome to Intan Elyu!',
                    "Welcome to Intan Elyu, {$firstName}! Explore top tourist spots in La Union, plan your personalized itineraries, and earn XP with AR check-ins!",
                    [
                        'module'     => 'welcome',
                        'action_url' => 'dashboard'
                    ]
                );
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
}
