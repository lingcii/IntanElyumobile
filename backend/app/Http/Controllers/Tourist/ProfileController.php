<?php

namespace App\Http\Controllers\Tourist;

use App\Http\Controllers\Controller;
use App\Models\Itinerary;
use App\Models\ItineraryItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ProfileController extends Controller
{
    /**
     * GET /api/tourist/profile
     * Returns detailed profile data for the tourist mobile app.
     */
    public function show(Request $request): JsonResponse
    {
        $user = $request->user();

        // 1. Rank — Technique 2: Server-Side Caching + Technique 6: Materialized Views
        $myRank = Cache::remember("rank:user:{$user->id}", 60, function () use ($user) {
            // Try materialized view first
            $cached = DB::table('leaderboard_cache')->where('user_id', $user->id)->first();
            if ($cached) {
                return (int) $cached->rank;
            }

            // Fallback: live CTE with denormalized column (Technique 3 + 5)
            $rankData = DB::selectOne("
                WITH ranked AS (
                    SELECT
                        u.id AS user_id,
                        ROW_NUMBER() OVER (
                            ORDER BY
                                COALESCE(u.xp, 0) DESC,
                                COALESCE(u.completed_activities, 0) DESC,
                                u.created_at ASC
                        ) AS `rank`
                    FROM users u
                    WHERE u.role = 'tourist' AND u.status = 'active'
                )
                SELECT `rank` FROM ranked WHERE user_id = ?
            ", [$user->id]);
            return $rankData ? (int) $rankData->rank : null;
        });

        // 2. Places Visited — use denormalized counter (Technique 5)
        $placesVisited = (int) ($user->completed_activities ?? 0);

        // 3. Completed Trips (Trip History) — Technique 2: Server-Side Caching
        $completedTrips = Cache::remember("profile:trips:{$user->id}", 120, function () use ($user) {
            return Itinerary::where('user_id', $user->id)
                ->where('status', 'completed')
                ->with('items')
                ->orderByDesc('updated_at')
                ->get()
                ->map(function ($trip) {
                    return [
                        'id' => $trip->id,
                        'title' => $trip->title,
                        'trip_date' => $trip->trip_date ? $trip->trip_date->format('Y-m-d') : null,
                        'total_cost' => $trip->total_cost,
                        'items' => $trip->items->map(function ($item) {
                            return [
                                'id' => $item->id,
                                'is_visited' => $item->is_visited
                            ];
                        }),
                    ];
                })
                ->toArray();
        });

        return response()->json([
            'success' => true,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'xp' => (int) ($user->xp ?? 0),
                'avatar' => $user->avatar
            ],
            'places_visited' => $placesVisited,
            'my_rank' => $myRank,
            'completed_trips' => $completedTrips
        ]);
    }

    /**
     * POST /api/tourist/profile
     * Update profile (name, avatar)
     */
    public function update(Request $request): JsonResponse
    {
        $user = $request->user();

        $request->validate([
            'name' => 'sometimes|string|max:255',
            'avatar' => 'sometimes|image|mimes:jpeg,png,jpg,gif|max:2048',
            'is_leaderboard_private' => 'sometimes|boolean',
        ]);

        if ($request->has('name')) {
            $user->name = $request->input('name');
        }

        if ($request->hasFile('avatar')) {
            $path = $request->file('avatar')->store('avatars', 'public');
            $user->avatar = 'storage/' . $path;
        }

        if ($request->has('is_leaderboard_private')) {
            $user->is_leaderboard_private = $request->boolean('is_leaderboard_private');
            // Invalidate leaderboard cache
            Cache::flush();
        }

        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully',
            'user' => $user
        ]);
    }
}
