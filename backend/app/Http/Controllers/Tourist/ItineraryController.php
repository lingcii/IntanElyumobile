<?php

namespace App\Http\Controllers\Tourist;

use App\Http\Controllers\Controller;
use App\Models\Itinerary;
use App\Models\ItineraryItem;
use App\Models\TouristSpot;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ItineraryController extends Controller
{
    /**
     * GET /api/tourist/itineraries
     * Returns all saved trips for the authenticated tourist.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $itineraries = Itinerary::where('user_id', $user->id)
            ->with(['items.destination:id,name,photo_url,latitude,longitude,entrance_fee,classification_status'])
            ->orderByDesc('created_at')
            ->get()
            ->map(function ($itinerary) {
                $items = $itinerary->items->map(function ($item) {
                    $dest = $item->destination;
                    $imageUrl = $dest ? $dest->photo_url : null;

                    return [
                        'id'               => $item->id,
                        'is_visited'       => $item->is_visited,
                        'proof_image'      => $item->proof_image,
                        'proof_status'     => $item->proof_status ?? ($item->is_visited ? 'approved' : 'pending'),
                        'rejection_reason' => $item->rejection_reason,
                        'visited_at'       => $item->visited_at,
                        'destination' => $dest ? [
                            'id'           => $dest->id,
                            'name'         => $dest->name,
                            'image'        => $imageUrl,
                            'latitude'     => $dest->latitude,
                            'longitude'    => $dest->longitude,
                            'entrance_fee' => $dest->entrance_fee,
                            'classification_status' => $dest->classification_status,
                        ] : null,
                    ];
                });

                return [
                    'id'             => $itinerary->id,
                    'title'          => $itinerary->title,
                    'trip_date'      => $itinerary->trip_date?->format('Y-m-d'),
                    'budget'         => $itinerary->budget,
                    'total_cost'     => $itinerary->total_cost,
                    'status'         => $itinerary->status,
                    'route_type'     => $itinerary->route_type,
                    'transport_mode' => $itinerary->transport_mode,
                    'items'          => $items,
                ];
            });

        return response()->json(['itineraries' => $itineraries]);
    }

    /**
     * GET /api/tourist/itineraries/{id}
     * Return a single itinerary with items and destinations.
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $itinerary = Itinerary::where('user_id', $user->id)
            ->with(['items.destination:id,name,photo_url,latitude,longitude,entrance_fee,classification_status'])
            ->findOrFail($id);

        $items = $itinerary->items->map(function ($item) {
            $dest = $item->destination;
            $imageUrl = $dest ? $dest->photo_url : null;
            return [
                'id'               => $item->id,
                'is_visited'       => $item->is_visited,
                'proof_image'      => $item->proof_image,
                'proof_status'     => $item->proof_status ?? ($item->is_visited ? 'approved' : 'pending'),
                'rejection_reason' => $item->rejection_reason,
                'visited_at'       => $item->visited_at,
                'destination' => $dest ? [
                    'id'           => $dest->id,
                    'name'         => $dest->name,
                    'image'        => $imageUrl,
                    'latitude'     => $dest->latitude,
                    'longitude'    => $dest->longitude,
                    'entrance_fee' => $dest->entrance_fee,
                    'classification_status' => $dest->classification_status,
                ] : null,
            ];
        });

        return response()->json([
            'itinerary' => [
                'id'             => $itinerary->id,
                'title'          => $itinerary->title,
                'trip_date'      => $itinerary->trip_date?->format('Y-m-d'),
                'budget'         => $itinerary->budget,
                'total_cost'     => $itinerary->total_cost,
                'status'         => $itinerary->status,
                'route_type'     => $itinerary->route_type,
                'transport_mode' => $itinerary->transport_mode,
                'items'          => $items,
            ]
        ]);
    }

    /**
     * POST /api/tourist/itineraries
     * Save a draft plan as a named itinerary.
     *
     * Body:
     *   - title (required)
     *   - destinations (array of spot IDs, required)
     *   - trip_date (optional)
     *   - budget (optional)
     *   - transport (optional)
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'title'          => 'required|string|max:255',
            'destinations'   => 'required|array|min:1',
            'destinations.*' => 'integer|exists:tourist_spots,id',
            'trip_date'      => 'nullable|date',
            'budget'         => 'nullable|numeric|min:0',
            'route_type'     => 'nullable|string|max:255',
            'transport_mode' => 'nullable|string|max:255',
        ]);

        $user = $request->user();

        $itinerary = DB::transaction(function () use ($request, $user) {
            // Rough cost estimation based on entrance fees
            $spots = TouristSpot::whereIn('id', $request->destinations)->get();
            $totalFee = $spots->sum('entrance_fee');

            $itinerary = Itinerary::create([
                'user_id'        => $user->id,
                'title'          => $request->title,
                'trip_date'      => $request->trip_date,
                'budget'         => $request->budget,
                'total_cost'     => $totalFee,
                'status'         => 'pending',
                'route_type'     => $request->route_type,
                'transport_mode' => $request->transport_mode,
            ]);

            // Create itinerary items preserving order
            foreach ($request->destinations as $spotId) {
                ItineraryItem::create([
                    'itinerary_id'    => $itinerary->id,
                    'tourist_spot_id' => $spotId,
                ]);
            }

            return $itinerary;
        });

        $itinerary->load(['items.destination:id,name,photo_url,latitude,longitude,entrance_fee,classification_status']);
        $items = $itinerary->items->map(function ($item) {
            $dest = $item->destination;
            $imageUrl = $dest ? $dest->photo_url : null;
            return [
                'id'               => $item->id,
                'is_visited'       => $item->is_visited,
                'proof_image'      => $item->proof_image,
                'proof_status'     => $item->proof_status ?? ($item->is_visited ? 'approved' : 'pending'),
                'rejection_reason' => $item->rejection_reason,
                'visited_at'       => $item->visited_at,
                'destination' => $dest ? [
                    'id'           => $dest->id,
                    'name'         => $dest->name,
                    'image'        => $imageUrl,
                    'latitude'     => $dest->latitude,
                    'longitude'    => $dest->longitude,
                    'entrance_fee' => $dest->entrance_fee,
                    'classification_status' => $dest->classification_status,
                ] : null,
            ];
        });

        return response()->json([
            'message'      => 'Trip saved! 🎉',
            'itinerary_id' => $itinerary->id,
            'itinerary'    => [
                'id'             => $itinerary->id,
                'title'          => $itinerary->title,
                'trip_date'      => $itinerary->trip_date?->format('Y-m-d'),
                'budget'         => $itinerary->budget,
                'total_cost'     => $itinerary->total_cost,
                'status'         => $itinerary->status,
                'route_type'     => $itinerary->route_type,
                'transport_mode' => $itinerary->transport_mode,
                'items'          => $items,
            ]
        ], 201);
    }

    /**
     * PATCH /api/tourist/itineraries/{id}/complete
     * Mark an itinerary as completed.
     */
    public function markCompleted(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $itinerary = Itinerary::where('user_id', $user->id)->findOrFail($id);

        $itinerary->update(['status' => 'completed']);

        try {
            $user->increment('xp', 100);
            $user->increment('completed_activities');
        } catch (\Throwable $e) {}

        // Check if this itinerary is a quest trip and record completion + badge
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('quests')) {
                $cleanTitle = trim(preg_replace('/\s*\(Quest\)$/i', '', $itinerary->title));
                $quest = \App\Models\Quest::where('name', $cleanTitle)->first();
                if ($quest) {
                    if (\Illuminate\Support\Facades\Schema::hasTable('quest_completions')) {
                        \App\Models\QuestCompletion::firstOrCreate([
                            'user_id'  => $user->id,
                            'quest_id' => $quest->id,
                        ], [
                            'xp_earned'    => $quest->xp_reward,
                            'completed_at' => now(),
                        ]);
                    }
                    $user->increment('xp', $quest->xp_reward);

                    // Add badge to user's badges array on profile
                    $badges = is_array($user->badges) ? $user->badges : (json_decode($user->badges ?? '[]', true) ?? []);
                    $badgeName = $quest->badge_name ?? "{$quest->name} Explorer";
                    $exists = collect($badges)->contains(fn($b) => is_array($b) && (($b['badge'] ?? $b['name'] ?? '') === $badgeName));
                    if (!$exists) {
                        $badges[] = [
                            'badge'       => $badgeName,
                            'name'        => $badgeName,
                            'icon'        => $quest->badge_icon ?? '🏅',
                            'description' => "Earned by completing {$quest->name}",
                            'unlocked_at' => now()->toIso8601String(),
                        ];
                        $user->update(['badges' => json_encode($badges)]);
                    }
                }
            }
        } catch (\Throwable $e) {}

        \App\Models\Notification::createSafely(
            $user->id,
            'itinerary_reminder',
            '🎉 Trip Completed!',
            "Congratulations! You completed '{$itinerary->title}' and earned +100 XP!",
            ['action_url' => '/saved_trips']
        );

        // Cache invalidation — flush stale profile/rank caches
        Cache::forget("rank:user:{$user->id}");
        Cache::forget("profile:trips:{$user->id}");
        Cache::flush();

        // Return visited items with spot IDs for frontend review modal
        $visitedItems = $itinerary->items()
            ->where('is_visited', true)
            ->with('destination:id,name')
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'tourist_spot_id' => $item->tourist_spot_id,
                    'destination_name' => $item->destination?->name ?? 'Unknown',
                ];
            });

        return response()->json([
            'message' => 'Trip marked as completed! 🏁',
            'visited_items' => $visitedItems,
        ]);
    }

    /**
     * PUT /api/tourist/itineraries/{id}
     * Update an existing itinerary (title, trip_date, budget, destinations).
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $itinerary = Itinerary::where('user_id', $user->id)->findOrFail($id);

        $request->validate([
            'title'          => 'sometimes|string|max:255',
            'trip_date'      => 'nullable|date',
            'budget'         => 'nullable|numeric|min:0',
            'route_type'     => 'nullable|string|max:255',
            'transport_mode' => 'nullable|string|max:255',
        ]);

        $itinerary->update($request->only(['title', 'trip_date', 'budget', 'route_type', 'transport_mode']));

        return response()->json([
            'message'    => 'Trip updated successfully!',
            'itinerary'  => $itinerary->fresh()            ->load(['items.destination:id,name,photo_url,latitude,longitude,entrance_fee,classification_status']),
        ]);
    }

    /**
     * DELETE /api/tourist/itineraries/{id}
     * Delete an itinerary and its items.
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $itinerary = Itinerary::find($id);

        if (!$itinerary) {
            return response()->json([
                'success' => true,
                'message' => 'Trip already deleted or not found.'
            ], 200);
        }

        if ($itinerary->user_id !== $user->id && $user->role === 'tourist') {
            return response()->json(['message' => 'Unauthorized to delete this trip.'], 403);
        }

        $itinerary->items()->delete();
        $itinerary->delete();

        // Cache invalidation — flush stale profile/rank caches
        Cache::forget("rank:user:{$user->id}");
        Cache::forget("profile:trips:{$user->id}");

        return response()->json([
            'success' => true,
            'message' => 'Trip deleted successfully.'
        ]);
    }

    /**
     * POST /api/tourist/itineraries/estimate-cost
     * Estimate itinerary costs including distance, fuel, fares, and peak season multipliers.
     */
    public function estimateCost(Request $request): JsonResponse
    {
        $request->validate([
            'destination_ids' => 'required|array',
            'destination_ids.*' => 'integer',
            'transport_mode' => 'nullable|string',
            'trip_date' => 'nullable|date',
        ]);

        $service = new \App\Services\CostEstimationService();
        $result = $service->estimateItineraryCosts(
            $request->input('destination_ids', []),
            $request->input('transport_mode', 'jeepney'),
            $request->input('fuel_price'),
            $request->input('fuel_efficiency'),
            $request->input('trip_date'),
            $request->input('peak_multiplier')
        );

        return response()->json([
            'status' => 'success',
            'estimation' => $result
        ]);
    }
}

