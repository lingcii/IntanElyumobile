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

        $hasExplicit = ($destId && isset($spotAllVehicles[$destId]) && !empty($spotAllVehicles[$destId]));
        $isDrivable = (bool) ($dest?->accessible_by_private_vehicle ?? 1);

        $normalizeVehName = function ($v) {
            $vUpper = strtoupper(trim((string) $v));
            if ($vUpper === 'MPUJ' || $vUpper === 'MPUJ (MODERN JEEPNEY)') return 'Modern Jeepney';
            if ($vUpper === 'TPUJ' || $vUpper === 'TPUJ (TRADITIONAL JEEPNEY)') return 'Traditional Jeepney';
            if ($vUpper === 'PUB_REGULAR' || $vUpper === 'PUB_ORDINARY' || $vUpper === 'PUB REGULAR' || $vUpper === 'PUB ORDINARY') return 'Regular Bus';
            if ($vUpper === 'PUB_AIRCON' || $vUpper === 'PUB AIRCON') return 'Aircon Bus';
            if ($vUpper === 'UVE') return 'UV Express';
            if ($vUpper === 'TAXI') return 'Taxi';
            if ($vUpper === 'CAR' || $vUpper === 'OWN_CAR') return 'Car';
            if ($vUpper === 'MOTORCYCLE') return 'Motorcycle';
            if ($vUpper === 'TRICYCLE' || $vUpper === 'TRYCICLE') return 'Tricycle';
            if ($vUpper === 'VAN') return 'Van';
            return trim((string) $v);
        };

        if ($hasExplicit) {
            $pubList = isset($spotPublicVehicles[$destId])
                ? array_values(array_unique(array_map($normalizeVehName, $spotPublicVehicles[$destId])))
                : [];
            $privList = isset($spotPrivateVehicles[$destId])
                ? array_values(array_unique(array_map($normalizeVehName, $spotPrivateVehicles[$destId])))
                : [];
            $allList = isset($spotAllVehicles[$destId])
                ? array_values(array_unique(array_map($normalizeVehName, $spotAllVehicles[$destId])))
                : [];
        } else {
            if ($isDrivable) {
                $privList = ['Car', 'Motorcycle', 'Tricycle', 'Van'];
                $pubList = ['Aircon Bus', 'Modern Jeepney', 'Regular Bus', 'Taxi', 'Traditional Jeepney', 'Tricycle', 'UV Express'];
                $allList = ['Aircon Bus', 'Car', 'Modern Jeepney', 'Motorcycle', 'Regular Bus', 'Taxi', 'Traditional Jeepney', 'Tricycle', 'UV Express', 'Van'];
            } else {
                $privList = ['Motorcycle', 'Tricycle'];
                $pubList = ['Motorcycle', 'Tricycle'];
                $allList = ['Motorcycle', 'Tricycle'];
            }
        }
        sort($pubList);
        sort($privList);
        sort($allList);

        return [
            'id'                   => $item->id,
            'order_index'          => (int) ($item->order_index ?? 0),
            'is_visited'           => (bool) $item->is_visited,
            'is_location_verified' => (bool) ($item->visited_at !== null),
            'proof_image'          => $item->proof_image,
            'proof_images'         => $item->proof_images ?? [],
            'proof_status'         => $item->proof_status ?? ($item->proof_image ? ($item->is_visited ? 'approved' : 'pending') : null),
            'rejection_reason'     => $item->rejection_reason,
            'companion_males'      => (int) ($item->companion_males ?? 0),
            'companion_females'    => (int) ($item->companion_females ?? 0),
            'total_pax'            => (int) ($item->total_pax ?? 1),
            'visited_at'           => $item->visited_at,
            'transport_mode'   => $item->transport_mode,
            'leg_cost'         => (float) ($item->leg_cost ?? 0),
            'leg_distance_km'  => $item->leg_distance_km !== null ? (float) $item->leg_distance_km : null,
            'destination' => $dest ? [
                'id'                            => $dest->id,
                'name'                          => $dest->name,
                'image'                         => $imageUrl,
                'latitude'                      => $dest->latitude,
                'longitude'                     => $dest->longitude,
                'entrance_fee'                  => (float) ($dest->entrance_fee ?? 0),
                'adult_fee'                     => (float) ($dest->adult_fee ?? 0),
                'kids_fee'                      => (float) ($dest->kids_fee ?? 0),
                'pwd_fee'                       => (float) ($dest->pwd_fee ?? 0),
                'senior_citizen_fee'            => (float) ($dest->senior_citizen_fee ?? 0),
                'environmental_fee'             => (float) ($dest->environmental_fee ?? 0),
                'classification_status'         => $dest->classification_status,
                'accessible_by_private_vehicle' => (bool) ($dest->accessible_by_private_vehicle ?? 1),
                'municipality'                  => $dest->municipality?->name,
                'is_open_24_hours'              => (bool) ($dest->is_open_24_hours ?? 0),
                'is_maintenance'                => (bool) ($dest->is_maintenance ?? 0),
                'tour_guide_notice'             => $dest->tour_guide_notice,
                'tour_guide_needed'             => !empty($dest->tour_guide_notice) && !in_array(strtolower(trim($dest->tour_guide_notice)), ['no', 'none', 'false', '0']),
                'accessible_vehicles'           => $allList,
                'public_vehicles'               => $pubList,
                'private_vehicles'              => $privList,
                'has_available_vehicles'        => !empty($allList),
            ] : null,
        ];
    }

    /**
     * Format a complete itinerary with its items.
     */
    private function formatItineraryResponse($itinerary, array $spotPublicVehicles, array $spotPrivateVehicles, array $spotAllVehicles): array
    {
        $items = $itinerary->items->sortBy('order_index')->values()->map(function ($item) use ($spotPublicVehicles, $spotPrivateVehicles, $spotAllVehicles) {
            return $this->formatItineraryItem($item, $spotPublicVehicles, $spotPrivateVehicles, $spotAllVehicles);
        });

        return [
            'id'             => $itinerary->id,
            'title'          => $itinerary->title,
            'trip_date'      => $itinerary->trip_date?->format('Y-m-d'),
            'budget'         => $itinerary->budget,
            'total_cost'     => $itinerary->total_cost,
            'status'            => $itinerary->status,
            'route_type'        => $itinerary->route_type,
            'transport_mode'    => $itinerary->transport_mode,
            'trip_type'         => $itinerary->trip_type ?? 'solo',
            'companion_males'   => (int) ($itinerary->companion_males ?? 0),
            'companion_females' => (int) ($itinerary->companion_females ?? 0),
            'total_pax'         => (int) ($itinerary->total_pax ?? 1),
            'items'             => $items,
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
                'items.destination:id,name,photo_url,latitude,longitude,entrance_fee,adult_fee,kids_fee,pwd_fee,senior_citizen_fee,environmental_fee,classification_status,accessible_by_private_vehicle,municipality_id',
                'items.destination.municipality:id,name'
            ])
            ->orderByDesc('created_at')
            ->get();

        // Deduplicate any accidental duplicate itineraries created within close time windows
        $seenSignatures = [];
        $rawItineraries = $rawItineraries->filter(function ($it) use (&$seenSignatures) {
            $sig = trim(strtolower((string)$it->title)) . '|' . ($it->trip_date ? $it->trip_date->format('Y-m-d') : '');
            if (isset($seenSignatures[$sig])) {
                return false;
            }
            $seenSignatures[$sig] = true;
            return true;
        })->values();

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
                'items.destination:id,name,photo_url,latitude,longitude,entrance_fee,adult_fee,kids_fee,pwd_fee,senior_citizen_fee,environmental_fee,classification_status,accessible_by_private_vehicle,municipality_id',
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
     *   - transport_mode (optional)
     *   - leg_transports (optional array of per-leg transports)
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'title'          => 'required|string|max:255',
            'destinations'   => 'required|array|min:1',
            'destinations.*' => 'integer|exists:tourist_spots,id',
            'trip_date'      => 'nullable|date',
            'budget'         => 'nullable|numeric|min:0',
            'route_type'        => 'nullable|string|max:255',
            'transport_mode'    => 'nullable|string|max:255',
            'trip_type'         => 'nullable|string|in:solo,group,With Companions,Solo Trip',
            'companion_males'   => 'nullable|integer|min:0',
            'companion_females' => 'nullable|integer|min:0',
            'total_pax'         => 'nullable|integer|min:1',
            'leg_transports'    => 'nullable|array',
        ]);

        $user = $request->user();

        // Prevent duplicate creation from rapid concurrent sync or accidental double-submissions
        $recentDuplicate = Itinerary::where('user_id', $user->id)
            ->where('title', trim($request->title))
            ->where('created_at', '>=', now()->subSeconds(15))
            ->first();

        if ($recentDuplicate) {
            $spotIds = $recentDuplicate->items->pluck('tourist_spot_id')->filter()->unique()->values()->all();
            [$spotPublicVehicles, $spotPrivateVehicles, $spotAllVehicles] = $this->getSpotVehiclesMap($spotIds);
            return response()->json([
                'message' => 'Itinerary already saved.',
                'itinerary_id' => $recentDuplicate->id,
                'itinerary' => $this->formatItineraryResponse($recentDuplicate, $spotPublicVehicles, $spotPrivateVehicles, $spotAllVehicles)
            ], 200);
        }

        $itinerary = DB::transaction(function () use ($request, $user) {
            $rawTransport = trim((string)($request->transport_mode ?? ''));
            $isNoVehicle = empty($rawTransport) 
                || stripos($rawTransport, 'no_vehicle') !== false 
                || stripos($rawTransport, 'no vehicle') !== false;
            $savedTransportMode = $isNoVehicle ? 'No Vehicle Selected' : $rawTransport;

            $legTransports = $request->leg_transports ?? [];

            // Accurate cost estimation using CostEstimationService with Point-to-Point leg fares
            $totalEstimatedCost = 0.00;
            $est = [];
            try {
                $service = new \App\Services\CostEstimationService();
                $est = $service->estimateItineraryCosts(
                    $request->destinations,
                    $savedTransportMode,
                    null,
                    null,
                    $request->trip_date,
                    null,
                    $legTransports
                );
                $totalEstimatedCost = (float) ($est['total_cost'] ?? 0.00);
            } catch (\Throwable $e) {
                $spots = TouristSpot::whereIn('id', $request->destinations)->get();
                $totalEstimatedCost = (float) ($spots->sum('entrance_fee') + $spots->sum('environmental_fee'));
            }

            $tripType = in_array(strtolower((string)($request->trip_type ?? 'solo')), ['group', 'with companions', 'companions']) ? 'group' : 'solo';
            $companionMales = ($tripType === 'group') ? max(0, (int)($request->companion_males ?? 0)) : 0;
            $companionFemales = ($tripType === 'group') ? max(0, (int)($request->companion_females ?? 0)) : 0;
            $totalPax = ($tripType === 'group') ? max(1, (int)($request->total_pax ?? (1 + $companionMales + $companionFemales))) : 1;

            $itinerary = Itinerary::create([
                'user_id'           => $user->id,
                'title'             => $request->title,
                'trip_date'         => $request->trip_date,
                'budget'            => $request->budget,
                'total_cost'        => $totalEstimatedCost,
                'status'            => 'pending',
                'route_type'        => $request->route_type,
                'transport_mode'    => $savedTransportMode,
                'trip_type'         => $tripType,
                'companion_males'   => $companionMales,
                'companion_females' => $companionFemales,
                'total_pax'         => $totalPax,
            ]);

            // Create itinerary items preserving order with Point-to-Point transportation
            $legsList = $est['legs'] ?? [];
            foreach ($request->destinations as $idx => $spotId) {
                $legMode = null;
                $legCost = 0.00;
                $legDist = null;

                if (isset($legTransports[$idx])) {
                    if (is_array($legTransports[$idx])) {
                        $legMode = $legTransports[$idx]['vehicle'] ?? $legTransports[$idx]['transport_mode'] ?? null;
                        $legCost = (float)($legTransports[$idx]['cost'] ?? $legTransports[$idx]['leg_cost'] ?? 0.00);
                        $legDist = isset($legTransports[$idx]['distance_km']) ? (float)$legTransports[$idx]['distance_km'] : (isset($legTransports[$idx]['leg_distance_km']) ? (float)$legTransports[$idx]['leg_distance_km'] : null);
                    } else {
                        $legMode = (string)$legTransports[$idx];
                    }
                }

                // If no explicit leg mode passed, look up from calculated legs
                if (!$legMode) {
                    $matchLeg = $legsList[$idx] ?? ($idx > 0 && isset($legsList[$idx - 1]) ? $legsList[$idx - 1] : null);
                    if ($matchLeg) {
                        $legMode = $matchLeg['vehicle_mode'] ?? null;
                        $legCost = (float)($matchLeg['leg_total'] ?? 0.00);
                        $legDist = (float)($matchLeg['distance_km'] ?? 0.00);
                    } elseif ($savedTransportMode === 'own_car' || $savedTransportMode === 'car') {
                        $legMode = 'own_car';
                        $legCost = 0.00;
                    } elseif ($savedTransportMode === 'motorcycle') {
                        $legMode = 'motorcycle';
                        $legCost = 0.00;
                    }
                }

                ItineraryItem::create([
                    'itinerary_id'      => $itinerary->id,
                    'tourist_spot_id'   => $spotId,
                    'order_index'       => $idx + 1,
                    'transport_mode'    => $legMode,
                    'companion_males'   => $companionMales,
                    'companion_females' => $companionFemales,
                    'total_pax'         => $totalPax,
                    'leg_cost'          => $legCost,
                    'leg_distance_km'   => $legDist,
                ]);
            }

            return $itinerary;
        });

        $itinerary->load([
            'items.destination:id,name,photo_url,latitude,longitude,entrance_fee,adult_fee,kids_fee,pwd_fee,senior_citizen_fee,environmental_fee,classification_status,accessible_by_private_vehicle,municipality_id',
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
                        $paxIncrement = max(1, (int) ($item->total_pax ?? $itinerary->total_pax ?? 1));
                        $spot->increment('visits', $paxIncrement);
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
            'title'             => 'sometimes|string|max:255',
            'trip_date'         => 'nullable|date',
            'budget'            => 'nullable|numeric|min:0',
            'route_type'        => 'nullable|string|max:255',
            'transport_mode'    => 'nullable|string|max:255',
            'trip_type'         => 'nullable|string|in:solo,group,With Companions,Solo Trip',
            'companion_males'   => 'nullable|integer|min:0',
            'companion_females' => 'nullable|integer|min:0',
            'total_pax'         => 'nullable|integer|min:1',
            'destinations'      => 'nullable|array',
            'destinations.*'    => 'integer|exists:tourist_spots,id',
            'leg_transports'    => 'nullable|array',
        ]);

        $updateData = $request->only(['title', 'trip_date', 'budget', 'route_type', 'transport_mode', 'trip_type', 'companion_males', 'companion_females', 'total_pax']);
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
        if (array_key_exists('trip_type', $updateData)) {
            $isGrp = in_array(strtolower((string)$updateData['trip_type']), ['group', 'with companions', 'companions']);
            $updateData['trip_type'] = $isGrp ? 'group' : 'solo';
            if (!$isGrp) {
                $updateData['companion_males'] = 0;
                $updateData['companion_females'] = 0;
                $updateData['total_pax'] = 1;
            } else {
                $m = max(0, (int)($updateData['companion_males'] ?? $itinerary->companion_males ?? 0));
                $f = max(0, (int)($updateData['companion_females'] ?? $itinerary->companion_females ?? 0));
                $updateData['companion_males'] = $m;
                $updateData['companion_females'] = $f;
                $updateData['total_pax'] = max(1, (int)($updateData['total_pax'] ?? (1 + $m + $f)));
            }
        }
        $itinerary->update($updateData);

        if ($request->has('destinations') && is_array($request->destinations) && count($request->destinations) > 0) {
            $totalEstimatedCost = 0.00;
            $est = [];
            $legTransports = $request->leg_transports ?? [];
            try {
                $service = new \App\Services\CostEstimationService();
                $est = $service->estimateItineraryCosts(
                    $request->destinations,
                    $itinerary->transport_mode ?? 'No Vehicle Selected',
                    null,
                    null,
                    $itinerary->trip_date,
                    null,
                    $legTransports
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

            $legsList = $est['legs'] ?? [];
            // Re-create or update destination stops with leg transportation
            foreach ($request->destinations as $idx => $spotId) {
                $legMode = null;
                $legCost = 0.00;
                $legDist = null;

                if (isset($legTransports[$idx])) {
                    if (is_array($legTransports[$idx])) {
                        $legMode = $legTransports[$idx]['vehicle'] ?? $legTransports[$idx]['transport_mode'] ?? null;
                        $legCost = (float)($legTransports[$idx]['cost'] ?? $legTransports[$idx]['leg_cost'] ?? 0.00);
                        $legDist = isset($legTransports[$idx]['distance_km']) ? (float)$legTransports[$idx]['distance_km'] : (isset($legTransports[$idx]['leg_distance_km']) ? (float)$legTransports[$idx]['leg_distance_km'] : null);
                    } else {
                        $legMode = (string)$legTransports[$idx];
                    }
                }

                if (!$legMode) {
                    $matchLeg = $legsList[$idx] ?? ($idx > 0 && isset($legsList[$idx - 1]) ? $legsList[$idx - 1] : null);
                    if ($matchLeg) {
                        $legMode = $matchLeg['vehicle_mode'] ?? null;
                        $legCost = (float)($matchLeg['leg_total'] ?? 0.00);
                        $legDist = (float)($matchLeg['distance_km'] ?? 0.00);
                    } elseif ($itinerary->transport_mode === 'own_car' || $itinerary->transport_mode === 'car') {
                        $legMode = 'own_car';
                        $legCost = 0.00;
                    } elseif ($itinerary->transport_mode === 'motorcycle') {
                        $legMode = 'motorcycle';
                        $legCost = 0.00;
                    }
                }

                if ($existingItems->has($spotId)) {
                    $existingItems->get($spotId)->update([
                        'order_index'       => $idx + 1,
                        'transport_mode'    => $legMode,
                        'companion_males'   => $itinerary->companion_males ?? 0,
                        'companion_females' => $itinerary->companion_females ?? 0,
                        'total_pax'         => $itinerary->total_pax ?? 1,
                        'leg_cost'          => $legCost,
                        'leg_distance_km'   => $legDist,
                    ]);
                } else {
                    ItineraryItem::create([
                        'itinerary_id'      => $itinerary->id,
                        'tourist_spot_id'   => $spotId,
                        'order_index'       => $idx + 1,
                        'transport_mode'    => $legMode,
                        'companion_males'   => $itinerary->companion_males ?? 0,
                        'companion_females' => $itinerary->companion_females ?? 0,
                        'total_pax'         => $itinerary->total_pax ?? 1,
                        'leg_cost'          => $legCost,
                        'leg_distance_km'   => $legDist,
                    ]);
                }
            }
        }

        Cache::forget("profile:trips:{$user->id}");
        Cache::forget("profile:trips:v2:{$user->id}");

        $freshItinerary = $itinerary->fresh()->load([
            'items.destination:id,name,photo_url,latitude,longitude,entrance_fee,adult_fee,kids_fee,pwd_fee,senior_citizen_fee,environmental_fee,classification_status,accessible_by_private_vehicle,municipality_id',
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

        if ((int) $itinerary->user_id !== (int) $user->id && !in_array($user->role, ['admin', 'super_admin'])) {
            return response()->json(['message' => 'Unauthorized to delete this trip.'], 403);
        }

        $title = $itinerary->title;
        $userId = $itinerary->user_id;
        $createdAt = $itinerary->created_at;

        $itinerary->items()->delete();
        $itinerary->delete();

        // Also clean up any accidental duplicate clone itineraries created within 60 seconds of this trip
        if (!empty($title) && $createdAt) {
            try {
                $duplicates = Itinerary::where('user_id', $userId)
                    ->where('title', $title)
                    ->whereBetween('created_at', [
                        $createdAt->copy()->subSeconds(60),
                        $createdAt->copy()->addSeconds(60)
                    ])
                    ->get();
                foreach ($duplicates as $dup) {
                    $dup->items()->delete();
                    $dup->delete();
                }
            } catch (\Throwable $e) {}
        }

        // Cache invalidation — flush stale profile/rank caches
        Cache::forget("rank:user:{$user->id}");
        Cache::forget("profile:trips:{$user->id}");
        Cache::forget("profile:trips:v2:{$user->id}");

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
            'leg_transports' => 'nullable|array',
        ]);

        $service = new \App\Services\CostEstimationService();
        $result = $service->estimateItineraryCosts(
            $request->input('destination_ids', []),
            $request->input('transport_mode', 'jeepney'),
            $request->input('fuel_price'),
            $request->input('fuel_efficiency'),
            $request->input('trip_date'),
            $request->input('peak_multiplier'),
            $request->input('leg_transports')
        );

        return response()->json([
            'status' => 'success',
            'estimation' => $result
        ]);
    }
}

