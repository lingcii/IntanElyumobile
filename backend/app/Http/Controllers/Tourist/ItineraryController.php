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
    /**
     * Helper to load spot-to-vehicle-type mappings.
     *
     * @param array $spotIds
     * @return array [$spotPublicVehicles, $spotPrivateVehicles, $spotAllVehicles]
     */
    private function getSpotVehiclesMap(array $spotIds): array
    {
        $spotPublicVehicles = [];
        $spotPrivateVehicles = [];
        $spotAllVehicles = [];

        if (empty($spotIds)) {
            return [$spotPublicVehicles, $spotPrivateVehicles, $spotAllVehicles];
        }

        try {
            $spotVehicles = DB::table('tourist_spot_vehicle_type')
                ->join('vehicle_types', 'tourist_spot_vehicle_type.vehicle_type_id', '=', 'vehicle_types.id')
                ->whereIn('tourist_spot_vehicle_type.tourist_spot_id', $spotIds)
                ->select('tourist_spot_vehicle_type.tourist_spot_id', 'vehicle_types.name', 'vehicle_types.category')
                ->get();

            foreach ($spotVehicles as $sv) {
                $isPub = stripos($sv->category, 'Public') !== false;
                if ($isPub) {
                    $spotPublicVehicles[$sv->tourist_spot_id][] = $sv->name;
                } else {
                    $spotPrivateVehicles[$sv->tourist_spot_id][] = $sv->name;
                }
                $spotAllVehicles[$sv->tourist_spot_id][] = $sv->name;
            }
        } catch (\Throwable $e) {}

        return [$spotPublicVehicles, $spotPrivateVehicles, $spotAllVehicles];
    }

    /**
     * Format an itinerary item with complete destination metadata and vehicle capabilities.
     */
    private function formatItineraryItem($item, array $spotPublicVehicles, array $spotPrivateVehicles, array $spotAllVehicles): array
    {
        $dest = $item->destination;
        $imageUrl = $dest ? $dest->photo_url : null;
        $destId = $dest ? $dest->id : null;

        $pubList = ($destId && isset($spotPublicVehicles[$destId]))
            ? array_values(array_unique($spotPublicVehicles[$destId]))
            : [];
        $privList = ($destId && isset($spotPrivateVehicles[$destId]))
            ? array_values(array_unique($spotPrivateVehicles[$destId]))
            : [];
        $allList = ($destId && isset($spotAllVehicles[$destId]))
            ? array_values(array_unique($spotAllVehicles[$destId]))
            : [];

        return [
            'id'               => $item->id,
            'is_visited'       => $item->is_visited,
            'proof_image'      => $item->proof_image,
            'proof_status'     => $item->proof_status ?? ($item->is_visited ? 'approved' : 'pending'),
            'rejection_reason' => $item->rejection_reason,
            'visited_at'       => $item->visited_at,
            'destination' => $dest ? [
                'id'                     => $dest->id,
                'name'                   => $dest->name,
                'image'                  => $imageUrl,
                'latitude'               => $dest->latitude,
                'longitude'              => $dest->longitude,
                'entrance_fee'           => (float) ($dest->entrance_fee ?? 0),
                'adult_fee'              => (float) ($dest->adult_fee ?? 0),
                'kids_fee'               => (float) ($dest->kids_fee ?? 0),
                'pwd_fee'                => (float) ($dest->pwd_fee ?? 0),
                'senior_citizen_fee'     => (float) ($dest->senior_citizen_fee ?? 0),
                'environmental_fee'      => (float) ($dest->environmental_fee ?? 0),
                'classification_status'  => $dest->classification_status,
                'municipality'           => $dest->municipality?->name,
                'is_open_24_hours'       => (bool) ($dest->is_open_24_hours ?? 0),
                'is_maintenance'         => (bool) ($dest->is_maintenance ?? 0),
                'tour_guide_notice'      => $dest->tour_guide_notice,
                'tour_guide_needed'      => !empty($dest->tour_guide_notice) && !in_array(strtolower(trim($dest->tour_guide_notice)), ['no', 'none', 'false', '0']),
                'accessible_vehicles'    => $allList,
                'public_vehicles'        => $pubList,
                'private_vehicles'       => $privList,
                'has_available_vehicles' => !empty($allList),
            ] : null,
        ];
    }

    /**
     * Format a complete itinerary with its items.
     */
    private function formatItineraryResponse($itinerary, array $spotPublicVehicles, array $spotPrivateVehicles, array $spotAllVehicles): array
    {
        $items = $itinerary->items->map(function ($item) use ($spotPublicVehicles, $spotPrivateVehicles, $spotAllVehicles) {
            return $this->formatItineraryItem($item, $spotPublicVehicles, $spotPrivateVehicles, $spotAllVehicles);
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
    }

    /**
     * GET /api/tourist/itineraries
     * Returns all saved trips for the authenticated tourist.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $rawItineraries = Itinerary::where('user_id', $user->id)
            ->with([
                'items.destination:id,name,photo_url,latitude,longitude,entrance_fee,adult_fee,kids_fee,pwd_fee,senior_citizen_fee,environmental_fee,classification_status,municipality_id',
                'items.destination.municipality:id,name'
            ])
            ->orderByDesc('created_at')
            ->get();

        $spotIds = [];
        foreach ($rawItineraries as $it) {
            foreach ($it->items as $item) {
                if ($item->tourist_spot_id) {
                    $spotIds[] = $item->tourist_spot_id;
                }
            }
        }
        $spotIds = array_values(array_unique(array_filter($spotIds)));
        [$spotPublicVehicles, $spotPrivateVehicles, $spotAllVehicles] = $this->getSpotVehiclesMap($spotIds);

        $itineraries = $rawItineraries->map(function ($itinerary) use ($spotPublicVehicles, $spotPrivateVehicles, $spotAllVehicles) {
            return $this->formatItineraryResponse($itinerary, $spotPublicVehicles, $spotPrivateVehicles, $spotAllVehicles);
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
            ->with([
                'items.destination:id,name,photo_url,latitude,longitude,entrance_fee,adult_fee,kids_fee,pwd_fee,senior_citizen_fee,environmental_fee,classification_status,municipality_id',
                'items.destination.municipality:id,name'
            ])
            ->findOrFail($id);

        $spotIds = $itinerary->items->pluck('tourist_spot_id')->filter()->unique()->values()->all();
        [$spotPublicVehicles, $spotPrivateVehicles, $spotAllVehicles] = $this->getSpotVehiclesMap($spotIds);

        return response()->json([
            'itinerary' => $this->formatItineraryResponse($itinerary, $spotPublicVehicles, $spotPrivateVehicles, $spotAllVehicles)
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
            $rawTransport = trim((string)($request->transport_mode ?? ''));
            $isNoVehicle = empty($rawTransport) 
                || stripos($rawTransport, 'no_vehicle') !== false 
                || stripos($rawTransport, 'no vehicle') !== false;
            $savedTransportMode = $isNoVehicle ? 'No Vehicle Selected' : $rawTransport;

            // Accurate cost estimation using CostEstimationService with boundary-crossing fares
            $totalEstimatedCost = 0.00;
            try {
                $service = new \App\Services\CostEstimationService();
                $est = $service->estimateItineraryCosts(
                    $request->destinations,
                    $savedTransportMode,
                    null,
                    null,
                    $request->trip_date
                );
                $totalEstimatedCost = (float) ($est['total_cost'] ?? 0.00);
            } catch (\Throwable $e) {
                $spots = TouristSpot::whereIn('id', $request->destinations)->get();
                $totalEstimatedCost = (float) ($spots->sum('entrance_fee') + $spots->sum('environmental_fee'));
            }

            $itinerary = Itinerary::create([
                'user_id'        => $user->id,
                'title'          => $request->title,
                'trip_date'      => $request->trip_date,
                'budget'         => $request->budget,
                'total_cost'     => $totalEstimatedCost,
                'status'         => 'pending',
                'route_type'     => $request->route_type,
                'transport_mode' => $savedTransportMode,
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

        $itinerary->load([
            'items.destination:id,name,photo_url,latitude,longitude,entrance_fee,adult_fee,kids_fee,pwd_fee,senior_citizen_fee,environmental_fee,classification_status,municipality_id',
            'items.destination.municipality:id,name'
        ]);
        $spotIds = $itinerary->items->pluck('tourist_spot_id')->filter()->unique()->values()->all();
        [$spotPublicVehicles, $spotPrivateVehicles, $spotAllVehicles] = $this->getSpotVehiclesMap($spotIds);

        return response()->json([
            'message'      => 'Trip saved! 🎉',
            'itinerary_id' => $itinerary->id,
            'itinerary'    => $this->formatItineraryResponse($itinerary, $spotPublicVehicles, $spotPrivateVehicles, $spotAllVehicles),
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

        // Mark all items as visited and increment spot visits
        $itemsToVisit = $itinerary->items()->get();
        foreach ($itemsToVisit as $item) {
            if (!$item->is_visited) {
                $item->update([
                    'is_visited'   => true,
                    'visited_at'   => $item->visited_at ?? now(),
                    'proof_status' => 'approved',
                ]);
                if ($item->tourist_spot_id) {
                    $spot = TouristSpot::find($item->tourist_spot_id);
                    if ($spot) {
                        $spot->increment('visits');
                    }
                }
            }
        }

        // Invalidate map and trending caches
        Cache::forget('map:public:spots');
        Cache::forget('trending:top:5');
        Cache::forget('trending:top:10');

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
            'Trip Completed!',
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
            'destinations'   => 'nullable|array',
            'destinations.*' => 'integer|exists:tourist_spots,id',
        ]);

        $updateData = $request->only(['title', 'trip_date', 'budget', 'route_type', 'transport_mode']);
        if (isset($updateData['trip_date']) && $updateData['trip_date'] === '') {
            $updateData['trip_date'] = null;
        }
        if (isset($updateData['budget']) && $updateData['budget'] === '') {
            $updateData['budget'] = null;
        }
        if (array_key_exists('transport_mode', $updateData)) {
            $rawT = trim((string)($updateData['transport_mode'] ?? ''));
            $isNoVeh = empty($rawT) || stripos($rawT, 'no_vehicle') !== false || stripos($rawT, 'no vehicle') !== false;
            $updateData['transport_mode'] = $isNoVeh ? 'No Vehicle Selected' : $rawT;
        }
        $itinerary->update($updateData);

        if ($request->has('destinations') && is_array($request->destinations) && count($request->destinations) > 0) {
            $totalEstimatedCost = 0.00;
            try {
                $service = new \App\Services\CostEstimationService();
                $est = $service->estimateItineraryCosts(
                    $request->destinations,
                    $itinerary->transport_mode ?? 'No Vehicle Selected',
                    null,
                    null,
                    $itinerary->trip_date
                );
                $totalEstimatedCost = (float) ($est['total_cost'] ?? 0.00);
            } catch (\Throwable $e) {
                $spots = TouristSpot::whereIn('id', $request->destinations)->get();
                $totalEstimatedCost = (float) ($spots->sum('entrance_fee') + $spots->sum('environmental_fee'));
            }
            $itinerary->update(['total_cost' => $totalEstimatedCost]);

            $existingItems = $itinerary->items->keyBy('tourist_spot_id');
            // Remove items no longer in destinations
            $itinerary->items()->whereNotIn('tourist_spot_id', $request->destinations)->delete();

            // Re-create only new destination stops (preserves existing proof/visited statuses)
            foreach ($request->destinations as $spotId) {
                if (!$existingItems->has($spotId)) {
                    ItineraryItem::create([
                        'itinerary_id'    => $itinerary->id,
                        'tourist_spot_id' => $spotId,
                    ]);
                }
            }
        }

        Cache::forget("profile:trips:{$user->id}");

        $freshItinerary = $itinerary->fresh()->load([
            'items.destination:id,name,photo_url,latitude,longitude,entrance_fee,adult_fee,kids_fee,pwd_fee,senior_citizen_fee,environmental_fee,classification_status,municipality_id',
            'items.destination.municipality:id,name'
        ]);
        $spotIds = $freshItinerary->items->pluck('tourist_spot_id')->filter()->unique()->values()->all();
        [$spotPublicVehicles, $spotPrivateVehicles, $spotAllVehicles] = $this->getSpotVehiclesMap($spotIds);

        return response()->json([
            'message'    => 'Trip updated successfully!',
            'itinerary'  => $this->formatItineraryResponse($freshItinerary, $spotPublicVehicles, $spotPrivateVehicles, $spotAllVehicles),
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

