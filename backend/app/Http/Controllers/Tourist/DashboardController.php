<?php

namespace App\Http\Controllers\Tourist;

use App\Http\Controllers\Controller;
use App\Models\TouristSpot;
use App\Models\Favorite;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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

        // Trending: top 5 by visits
        $trending = TouristSpot::where('status', 'approved')
            ->orderByDesc('visits')
            ->limit(5)
            ->get(['id', 'name', 'category', 'photo_url', 'latitude', 'longitude', 'visits', 'rating', 'description', 'entrance_fee', 'barangay', 'classification_status'])
            ->map(fn($s) => $this->formatSpot($s));

        // Saved/Favorite places
        $favoriteIds = Favorite::where('user_id', $user->id)->pluck('tourist_spot_id');
        $savedPlaces = TouristSpot::whereIn('id', $favoriteIds)
            ->where('status', 'approved')
            ->get(['id', 'name', 'category', 'photo_url', 'latitude', 'longitude', 'visits', 'rating', 'description', 'entrance_fee', 'barangay', 'classification_status'])
            ->map(fn($s) => $this->formatSpot($s));

        // Recommendations: Near Me feature
        $lat = $request->query('lat');
        $lng = $request->query('lng');
        $timeLabel = '📍 Near Me';

        $recommendedQuery = TouristSpot::where('status', 'approved')
            ->whereNotIn('id', $favoriteIds)
            ->get(['id', 'name', 'category', 'photo_url', 'latitude', 'longitude', 'rating', 'description', 'entrance_fee', 'barangay', 'classification_status']);

        if ($lat && $lng) {
            $recommendedQuery = $recommendedQuery->sortBy(function($spot) use ($lat, $lng) {
                return pow($spot->latitude - $lat, 2) + pow($spot->longitude - $lng, 2);
            });
        } else {
            $recommendedQuery = $recommendedQuery->sortByDesc('rating');
        }

        $recommended = $recommendedQuery->take(5)->values()->map(fn($s) => $this->formatSpot($s));

        // Stats
        $placesVisited = \App\Models\ItineraryItem::whereHas('itinerary', fn($q) => $q->where('user_id', $user->id))
            ->where('is_visited', true)
            ->count();

        return response()->json([
            'user' => [
                'id'     => $user->id,
                'name'   => $user->name,
                'email'  => $user->email,
                'xp'     => $xp,
                'level'  => $level,
                'avatar' => $user->avatar,
            ],
            'stats' => [
                'placesVisited' => $placesVisited,
            ],
            'trending'     => $trending,
            'savedPlaces'  => $savedPlaces,
            'recommended'  => $recommended,
            'timeLabel'    => $timeLabel,
            'announcements'=> [],
            'myRank'       => null, // Placeholder
        ]);
    }

    private function formatSpot($spot): array
    {
        $imageUrl = null;
        if ($spot->photo_url) {
            $imageUrl = str_starts_with($spot->photo_url, 'http')
                ? $spot->photo_url
                : 'http://localhost:8000/storage/' . $spot->photo_url;
        }

        return [
            'id'           => $spot->id,
            'name'         => $spot->name,
            'category'     => $spot->category,
            'image'        => $imageUrl,
            'location'     => $spot->barangay,
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
