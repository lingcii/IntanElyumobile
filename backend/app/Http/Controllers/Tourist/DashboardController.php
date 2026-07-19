<?php

namespace App\Http\Controllers\Tourist;

use App\Http\Controllers\Controller;
use App\Models\TouristSpot;
use App\Models\Favorite;
use App\Models\Notification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * GET /api/tourist/dashboard
     * Returns user profile, XP, trending spots, saved places, and recommendations.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        // XP calculations
        $xp        = (int) ($user->xp ?? 0);
        $level     = (int) ($user->level ?? 1);
        $xpPerLevel = 1000;

        // Trending: top spots by visits (default 5, configurable via ?limit=)
        // Technique 2: Server-Side Caching — 2 minute TTL for trending spots
        $trendingLimit = min((int) $request->query('limit', 5), 50);
        $trending = Cache::remember("trending:top:{$trendingLimit}", 120, function () use ($trendingLimit) {
            return TouristSpot::where('status', 'approved')
                ->orderByDesc('visits')
                ->limit($trendingLimit)
                ->get(['id', 'name', 'category', 'photo_url', 'latitude', 'longitude', 'visits', 'rating', 'description', 'entrance_fee', 'classification_status'])
                ->map(fn($s) => $this->formatSpot($s))
                ->toArray();
        });

        // Saved/Favorite places
        $favoriteIds = Favorite::where('user_id', $user->id)->pluck('tourist_spot_id');
        $savedPlaces = TouristSpot::whereIn('id', $favoriteIds)
            ->where('status', 'approved')
            ->get(['id', 'name', 'category', 'photo_url', 'latitude', 'longitude', 'visits', 'rating', 'description', 'entrance_fee', 'classification_status'])
            ->map(fn($s) => $this->formatSpot($s));

        // Recommendations: Near Me feature
        $lat = $request->query('lat');
        $lng = $request->query('lng');
        $timeLabel = '📍 Near Me';

        $recommendedQuery = TouristSpot::where('status', 'approved')
            ->whereNotIn('id', $favoriteIds)
            ->get(['id', 'name', 'category', 'photo_url', 'latitude', 'longitude', 'rating', 'description', 'entrance_fee', 'classification_status']);

        if ($lat && $lng) {
            $recommendedQuery = $recommendedQuery->sortBy(function($spot) use ($lat, $lng) {
                return pow($spot->latitude - $lat, 2) + pow($spot->longitude - $lng, 2);
            });
        } else {
            $recommendedQuery = $recommendedQuery->sortByDesc('rating');
        }

        $recommended = $recommendedQuery->take(5)->values()->map(fn($s) => $this->formatSpot($s));

        // Stats — use denormalized counter (Technique 5: Denormalization)
        $placesVisited = (int) ($user->completed_activities ?? 0);

        // Rank — Technique 2: Server-Side Caching + Technique 6: Materialized Views
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

        // Calculate points balance
        $earnedPoints = (int) \App\Models\UserPoint::where('user_id', $user->id)->sum('points');
        $redeemedPoints = (int) \App\Models\PointRedemption::where('user_id', $user->id)->sum('points_cost');
        $points = max(0, $earnedPoints - $redeemedPoints);

        return response()->json([
            'user' => [
                'id'     => $user->id,
                'name'   => $user->name,
                'email'  => $user->email,
                'xp'     => $xp,
                'level'  => $level,
                'points' => $points,
                'avatar' => $user->avatar,
            ],
            'stats' => [
                'placesVisited'        => $placesVisited,
                'unread_notifications' => Notification::where('user_id', $user->id)
                    ->where('is_read', false)
                    ->count(),
            ],
            'trending'     => $trending,
            'savedPlaces'  => $savedPlaces,
            'recommended'  => $recommended,
            'timeLabel'    => $timeLabel,
            'announcements'=> [],
            'myRank'       => $myRank,
        ]);
    }

    private function formatSpot($spot): array
    {
        $imageUrl = $spot->photo_url;

        return [
            'id'           => $spot->id,
            'name'         => $spot->name,
            'category'     => $spot->category,
            'image'        => $imageUrl,
            'latitude'     => $spot->latitude,
            'longitude'    => $spot->longitude,
            'rating'       => $spot->rating,
            'visits'       => $spot->visits,
            'description'  => $spot->description,
            'entrance_fee' => $spot->entrance_fee,
            'classification_status' => $spot->classification_status,
        ];
    }
}
