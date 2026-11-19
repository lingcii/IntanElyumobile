<?php

namespace App\Http\Controllers\Tourist;

use App\Http\Controllers\Controller;
use App\Models\Favorite;
use App\Models\TouristSpot;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class FavoriteController extends Controller
{
    /**
     * GET /api/tourist/saved-places
     * Lightweight endpoint returning only the authenticated user's saved spots.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $savedPlaces = Cache::remember("favorites:user:{$user->id}", 300, function () use ($user) {
            if (!Schema::hasTable('favorites') || !Schema::hasTable('tourist_spots')) {
                return [];
            }

            $favoriteIds = Favorite::where('user_id', $user->id)->pluck('tourist_spot_id');
            if ($favoriteIds->isEmpty()) {
                return [];
            }

            return TouristSpot::whereIn('id', $favoriteIds)
                ->activeForTourists()
                ->withCount('feedbacks')
                ->get(['id', 'name', 'category', 'photo_url', 'latitude', 'longitude', 'visits', 'rating', 'description', 'entrance_fee', 'adult_fee', 'kids_fee', 'pwd_fee', 'senior_citizen_fee', 'entrance_fee_types', 'environmental_fee', 'fee_types', 'classification_status', 'municipality_id', 'status'])
                ->map(fn($s) => DashboardController::formatSpotData($s))
                ->values()
                ->all();
        });

        return response()->json([
            'success' => true,
            'data' => $savedPlaces,
            'savedPlaces' => $savedPlaces,
        ]);
    }

    /**
     * POST /api/tourist/destinations/{id}/favorite
     * Toggle a destination in/out of the user's saved places.
     */
    public function toggle(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $action = strtolower((string) $request->input('action', ''));
        $query = Favorite::where('user_id', $user->id)
            ->where('tourist_spot_id', $id);

        if ($action === 'remove') {
            $query->delete();
            Cache::forget("favorites:user:{$user->id}");
            return response()->json(['status' => 'removed', 'message' => 'Removed from Saved Places.']);
        }

        $spot = TouristSpot::activeForTourists()->find($id);
        if (!$spot) {
            if ($query->exists()) {
                $query->delete();
                Cache::forget("favorites:user:{$user->id}");
                return response()->json(['status' => 'removed', 'message' => 'Removed from Saved Places.']);
            }
            return response()->json(['message' => 'This tourist destination is not currently available.'], 404);
        }

        if ($action === 'add') {
            try {
                Favorite::firstOrCreate([
                    'user_id'         => $user->id,
                    'tourist_spot_id' => $id,
                ]);
            } catch (\Throwable $e) {}
            Cache::forget("favorites:user:{$user->id}");
            return response()->json(['status' => 'added', 'message' => 'Added to Saved Places! ❤️']);
        }

        if ($query->exists()) {
            $query->delete();
            Cache::forget("favorites:user:{$user->id}");
            return response()->json(['status' => 'removed', 'message' => 'Removed from Saved Places.']);
        }

        try {
            Favorite::firstOrCreate([
                'user_id'         => $user->id,
                'tourist_spot_id' => $id,
            ]);
        } catch (\Throwable $e) {}
        Cache::forget("favorites:user:{$user->id}");

        return response()->json(['status' => 'added', 'message' => 'Added to Saved Places! ❤️']);
    }
}
