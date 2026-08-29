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
        $trending = Cache::remember("trending:top:{$trendingLimit}", 30, function () use ($trendingLimit) {
            return TouristSpot::where(function($q) {
                    $q->whereIn('status', ['approved', 'active', 'published', 'EXIST', 'exist', 'pending'])
                      ->orWhereNull('status');
                })
                ->orderByDesc('visits')
                ->limit($trendingLimit)
                ->get(['id', 'name', 'category', 'photo_url', 'latitude', 'longitude', 'visits', 'rating', 'description', 'entrance_fee', 'classification_status', 'municipality_id'])
                ->map(fn($s) => $this->formatSpot($s))
                ->toArray();
        });

        // Saved/Favorite places
        $favoriteIds = collect();
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('favorites')) {
                $favoriteIds = Favorite::where('user_id', $user->id)->pluck('tourist_spot_id');
            }
        } catch (\Throwable $e) {
            $favoriteIds = collect();
        }

        $savedPlaces = collect();
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('tourist_spots') && $favoriteIds->isNotEmpty()) {
                $savedPlaces = TouristSpot::whereIn('id', $favoriteIds)
                    ->where(function($q) {
                        $q->whereIn('status', ['approved', 'active', 'published', 'EXIST', 'exist', 'pending'])
                          ->orWhereNull('status');
                    })
                    ->get(['id', 'name', 'category', 'photo_url', 'latitude', 'longitude', 'visits', 'rating', 'description', 'entrance_fee', 'classification_status', 'municipality_id'])
                    ->map(fn($s) => $this->formatSpot($s));
            }
        } catch (\Throwable $e) {
            $savedPlaces = collect();
        }

        // Recommendations: Near Me feature
        $lat = $request->query('lat');
        $lng = $request->query('lng');
        $timeLabel = '📍 Near Me';

        $recommended = collect();
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('tourist_spots')) {
                $recommendedQuery = TouristSpot::where(function($q) {
                        $q->whereIn('status', ['approved', 'active', 'published', 'EXIST', 'exist', 'pending'])
                          ->orWhereNull('status');
                    })
                    ->when($favoriteIds->isNotEmpty(), function($q) use ($favoriteIds) {
                        $q->whereNotIn('id', $favoriteIds);
                    })
                    ->get(['id', 'name', 'category', 'photo_url', 'latitude', 'longitude', 'rating', 'description', 'entrance_fee', 'classification_status', 'municipality_id']);

                if ($lat && $lng) {
                    $recommendedQuery = $recommendedQuery->sortBy(function($spot) use ($lat, $lng) {
                        return pow($spot->latitude - $lat, 2) + pow($spot->longitude - $lng, 2);
                    });
                } else {
                    $recommendedQuery = $recommendedQuery->sortByDesc('rating');
                }

                $recommended = $recommendedQuery->take(5)->values()->map(fn($s) => $this->formatSpot($s));
            }
        } catch (\Throwable $e) {
            $recommended = collect();
        }

        // Stats — use denormalized counter (Technique 5: Denormalization)
        $placesVisited = (int) ($user->completed_activities ?? 0);

        // Rank — Technique 2: Server-Side Caching + Technique 6: Materialized Views
        $myRank = Cache::remember("rank:user:{$user->id}", 60, function () use ($user) {
            try {
                if (\Illuminate\Support\Facades\Schema::hasTable('leaderboard_cache')) {
                    $cached = DB::table('leaderboard_cache')->where('user_id', $user->id)->first();
                    if ($cached) {
                        return (int) $cached->rank;
                    }
                }

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
                return $rankData ? (int) $rankData->rank : 1;
            } catch (\Throwable $e) {
                return 1;
            }
        });

        // Points balance directly from users table
        $points = (int) ($user->points ?? 0);

        $unreadNotifications = 0;
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('notifications')) {
                $unreadNotifications = Notification::where('user_id', $user->id)
                    ->where('is_read', false)
                    ->count();
            }
        } catch (\Throwable $e) {
            $unreadNotifications = 0;
        }

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
                'rank'                 => $myRank,
                'unread_notifications' => $unreadNotifications,
            ],
            'trending'     => $trending,
            'savedPlaces'  => $savedPlaces,
            'recommended'  => $recommended,
            'timeLabel'    => $timeLabel,
            'announcements'=> [],
            'myRank'       => $myRank,
            'my_rank'      => $myRank,
        ]);
    }

    private function formatSpot($spot): array
    {
        $imageUrl = $spot->photo_url;
        $imagesList = [];
        if ($spot->photo_url) {
            $imagesList[] = $spot->photo_url;
        }
        if ($spot->relationLoaded('images') && $spot->images->isNotEmpty()) {
            foreach ($spot->images as $imgObj) {
                if ($imgObj->photo_url && !in_array($imgObj->photo_url, $imagesList)) {
                    $imagesList[] = $imgObj->photo_url;
                }
            }
        }

        // Resolve municipality name for local image matching
        $muniName = null;
        if ($spot->municipality_id) {
            try {
                $muniName = \Illuminate\Support\Facades\DB::table('municipalities')
                    ->where('id', $spot->municipality_id)
                    ->value('name');
            } catch (\Throwable $e) {}
        }

        return [
            'id'           => $spot->id,
            'name'         => $spot->name,
            'category'     => $spot->category,
            'image'        => $imageUrl,
            'photo_url'    => $imageUrl,
            'images'       => $imagesList,
            'latitude'     => $spot->latitude,
            'longitude'    => $spot->longitude,
            'rating'       => $spot->rating,
            'visits'       => $spot->visits,
            'description'  => $spot->description,
            'entrance_fee' => $spot->entrance_fee,
            'classification_status' => $spot->classification_status,
            'municipality_id' => $spot->municipality_id,
            'municipality' => $muniName,
        ];
    }
}
