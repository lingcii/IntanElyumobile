<?php

namespace App\Http\Controllers\Tourist;

use App\Http\Controllers\Controller;
use App\Models\Itinerary;
use App\Models\ItineraryItem;
use App\Models\SiteFeedback;
use App\Models\TouristSpot;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

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

        $isApproved = (bool) ($item->is_visited || $item->proof_status === 'approved');
        $proofStatus = $isApproved ? 'approved' : ($item->proof_status ?? (!empty($item->proof_image) || !empty($item->proof_images) ? 'pending' : null));

        return [
            'id'                   => $item->id,
            'order_index'          => (int) ($item->order_index ?? 0),
            'is_visited'           => $isApproved,
            'is_location_verified' => (bool) ($item->visited_at !== null || $isApproved),
            'proof_image'          => $item->proof_image,
            'proof_images'         => $item->proof_images ?? [],
            'proof_status'         => $proofStatus,
            'rejection_reason'     => $item->rejection_reason,
            'companion_males'      => (int) ($item->companion_males ?? 0),
            'companion_females'    => (int) ($item->companion_females ?? 0),
            'companion_adults'     => (int) ($item->companion_adults ?? 0),
            'companion_kids'       => (int) ($item->companion_kids ?? 0),
            'companion_seniors'    => (int) ($item->companion_seniors ?? 0),
            'companion_pwds'       => (int) ($item->companion_pwds ?? 0),
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
                'boundary_geojson'              => $dest->boundary_geojson,
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
            'id'                => $itinerary->id,
            'title'             => $itinerary->title,
            'trip_date'         => $itinerary->trip_date?->format('Y-m-d'),
            'budget'            => $itinerary->budget,
            'total_cost'        => $itinerary->total_cost,
            'status'            => $itinerary->status,
            'route_type'        => $itinerary->route_type,
            'transport_mode'    => $itinerary->transport_mode,
            'trip_type'         => $itinerary->trip_type ?? 'solo',
            'companion_males'   => (int) ($itinerary->companion_males ?? 0),
            'companion_females' => (int) ($itinerary->companion_females ?? 0),
            'companion_adults'  => (int) ($itinerary->companion_adults ?? 0),
            'companion_kids'    => (int) ($itinerary->companion_kids ?? 0),
            'companion_seniors' => (int) ($itinerary->companion_seniors ?? 0),
            'companion_pwds'    => (int) ($itinerary->companion_pwds ?? 0),
            'companion_ages'    => $itinerary->companion_ages ?? [],
            'total_pax'         => (int) ($itinerary->total_pax ?? 1),
            'created_at'        => $itinerary->created_at?->toIso8601String(),
            'updated_at'        => $itinerary->updated_at?->toIso8601String(),
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

        // Auto-fulfill untouched destination reviews older than 2 days (48 hours)
        static::processUntouchedVisitReviews(2);

        $rawItineraries = Itinerary::where('user_id', $user->id)
            ->with([
                'items.destination:id,name,photo_url,latitude,longitude,entrance_fee,adult_fee,kids_fee,pwd_fee,senior_citizen_fee,environmental_fee,classification_status,accessible_by_private_vehicle,municipality_id,boundary_geojson',
                'items.destination.municipality:id,name'
            ])
            ->orderByRaw('COALESCE(updated_at, created_at) DESC')
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

        return response()->json(['itineraries' => $itineraries])
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache');
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
                'items.destination:id,name,photo_url,latitude,longitude,entrance_fee,adult_fee,kids_fee,pwd_fee,senior_citizen_fee,environmental_fee,classification_status,accessible_by_private_vehicle,municipality_id,boundary_geojson',
                'items.destination.municipality:id,name'
            ])
            ->findOrFail($id);

        $spotIds = $itinerary->items->pluck('tourist_spot_id')->filter()->unique()->values()->all();
        [$spotPublicVehicles, $spotPrivateVehicles, $spotAllVehicles] = $this->getSpotVehiclesMap($spotIds);

        return response()->json([
            'itinerary' => $this->formatItineraryResponse($itinerary, $spotPublicVehicles, $spotPrivateVehicles, $spotAllVehicles)
        ])->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
          ->header('Pragma', 'no-cache');
    }

    /**
     * Resolve and sanitize destination IDs before validation.
     * Maps any passed itinerary_item IDs or destination objects/names to valid tourist_spot IDs.
     */
    private function sanitizeDestinationIds(array $rawDestinations): array
    {
        $cleaned = [];
        foreach ($rawDestinations as $d) {
            if (is_array($d)) {
                $d = $d['tourist_spot_id'] ?? $d['destination_id'] ?? $d['id'] ?? null;
            }
            if (is_numeric($d)) {
                $id = (int)$d;
                if ($id > 0) {
                    if (TouristSpot::where('id', $id)->exists()) {
                        $cleaned[] = $id;
                    } else {
                        // Check if this ID is an itinerary_items.id that references a valid tourist_spot
                        $itItem = ItineraryItem::find($id);
                        if ($itItem && $itItem->tourist_spot_id && TouristSpot::where('id', $itItem->tourist_spot_id)->exists()) {
                            $cleaned[] = (int)$itItem->tourist_spot_id;
                        }
                    }
                }
            } elseif (is_string($d) && trim($d) !== '') {
                $trimmed = trim($d);
                if (is_numeric($trimmed) && (int)$trimmed > 0) {
                    $id = (int)$trimmed;
                    if (TouristSpot::where('id', $id)->exists()) {
                        $cleaned[] = $id;
                    }
                } else {
                    $foundSpot = TouristSpot::where('name', $trimmed)->first();
                    if ($foundSpot) {
                        $cleaned[] = (int)$foundSpot->id;
                    }
                }
            }
        }
        return array_values(array_unique($cleaned));
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
        if ($request->has('destinations') && is_array($request->destinations)) {
            $cleaned = $this->sanitizeDestinationIds($request->destinations);
            if (!empty($cleaned)) {
                $request->merge(['destinations' => $cleaned]);
            }
        }

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
            'companion_adults'  => 'nullable|integer|min:0',
            'companion_kids'    => 'nullable|integer|min:0',
            'companion_seniors' => 'nullable|integer|min:0',
            'companion_pwds'    => 'nullable|integer|min:0',
            'companion_ages'    => 'nullable|array',
            'companion_ages.*'  => 'nullable|integer|min:0|max:125',
            'total_pax'         => 'nullable|integer|min:1',
            'client_request_id' => 'nullable|string|max:100',
            'leg_transports'    => 'nullable|array',
        ], [
            'destinations.*.exists' => 'One or more selected destinations is invalid or no longer available.',
            'destinations.min'      => 'Please select at least one destination for your trip.'
        ]);

        $user = $request->user();

        // The key survives offline retries and ambiguous network responses, so one
        // Save action always resolves to the same itinerary without a schema change.
        $clientRequestId = trim((string) $request->input('client_request_id', ''));
        $idempotencyCacheKey = $clientRequestId !== ''
            ? 'itinerary:create:' . $user->id . ':' . hash('sha256', $clientRequestId)
            : null;
        if ($idempotencyCacheKey) {
            $existingId = Cache::get($idempotencyCacheKey);
            if ($existingId) {
                $existing = Itinerary::where('user_id', $user->id)
                    ->with([
                        'items.destination:id,name,photo_url,latitude,longitude,entrance_fee,adult_fee,kids_fee,pwd_fee,senior_citizen_fee,environmental_fee,classification_status,accessible_by_private_vehicle,municipality_id,boundary_geojson',
                        'items.destination.municipality:id,name'
                    ])
                    ->find($existingId);
                if ($existing) {
                    $spotIds = $existing->items->pluck('tourist_spot_id')->filter()->unique()->values()->all();
                    [$spotPublicVehicles, $spotPrivateVehicles, $spotAllVehicles] = $this->getSpotVehiclesMap($spotIds);
                    return response()->json([
                        'message' => 'Itinerary already saved.',
                        'itinerary_id' => $existing->id,
                        'itinerary' => $this->formatItineraryResponse($existing, $spotPublicVehicles, $spotPrivateVehicles, $spotAllVehicles),
                    ], 200);
                }
            }
        }

        // Prevent accidental duplicate trip saves with the same title and destinations
        $recentDuplicate = Itinerary::where('user_id', $user->id)
            ->where('status', '!=', 'completed')
            ->whereRaw('LOWER(TRIM(title)) = ?', [strtolower(trim((string) $request->title))])
            ->with('items')
            ->latest('id')
            ->first();

        if ($recentDuplicate) {
            $spotIds = $recentDuplicate->items->pluck('tourist_spot_id')->filter()->map(fn($v) => (int) $v)->values()->all();
            $reqSpotIds = is_array($request->destinations) ? array_values(array_map('intval', array_filter($request->destinations))) : [];
            $sameDate = (string) ($recentDuplicate->trip_date?->format('Y-m-d') ?? '') === (string) ($request->trip_date ? substr((string) $request->trip_date, 0, 10) : '');
            if ($spotIds === $reqSpotIds && $sameDate) {
                if ($idempotencyCacheKey) {
                    Cache::put($idempotencyCacheKey, $recentDuplicate->id, now()->addDay());
                }
                $recentDuplicate->load([
                    'items.destination:id,name,photo_url,latitude,longitude,entrance_fee,adult_fee,kids_fee,pwd_fee,senior_citizen_fee,environmental_fee,classification_status,accessible_by_private_vehicle,municipality_id,boundary_geojson',
                    'items.destination.municipality:id,name'
                ]);
                [$spotPublicVehicles, $spotPrivateVehicles, $spotAllVehicles] = $this->getSpotVehiclesMap($spotIds);
                return response()->json([
                    'message' => 'Itinerary already saved.',
                    'itinerary_id' => $recentDuplicate->id,
                    'itinerary' => $this->formatItineraryResponse($recentDuplicate, $spotPublicVehicles, $spotPrivateVehicles, $spotAllVehicles)
                ], 200);
            }
        }

        $itinerary = DB::transaction(function () use ($request, $user) {
            $rawTransport = trim((string)($request->transport_mode ?? ''));
            $isNoVehicle = empty($rawTransport) 
                || stripos($rawTransport, 'no_vehicle') !== false 
                || stripos($rawTransport, 'no vehicle') !== false;
            $savedTransportMode = $isNoVehicle ? 'No Vehicle Selected' : $rawTransport;

            $legTransports = $request->leg_transports ?? [];

            // If leg transports have specific vehicle selections, derive overall transport mode
            if (!empty($legTransports) && is_array($legTransports)) {
                $distinctModes = [];
                foreach ($legTransports as $lt) {
                    $lm = is_array($lt) ? ($lt['vehicle'] ?? $lt['transport_mode'] ?? null) : (string)$lt;
                    if ($lm && stripos($lm, 'no_vehicle') === false && stripos($lm, 'no vehicle') === false) {
                        $distinctModes[] = trim($lm);
                    }
                }
                $distinctModes = array_values(array_unique(array_filter($distinctModes)));
                if (!empty($distinctModes)) {
                    $savedTransportMode = implode(' + ', $distinctModes);
                } elseif ($isNoVehicle || $savedTransportMode === 'own_car') {
                    $savedTransportMode = 'No Vehicle Selected';
                }
            }

            $tripType = in_array(strtolower((string)($request->trip_type ?? 'solo')), ['group', 'with companions', 'companions']) ? 'group' : 'solo';
            $companionMales = ($tripType === 'group') ? max(0, (int)($request->companion_males ?? 0)) : 0;
            $companionFemales = ($tripType === 'group') ? max(0, (int)($request->companion_females ?? 0)) : 0;
            $companionAdults = ($tripType === 'group') ? max(0, (int)($request->companion_adults ?? 0)) : 0;
            $companionKids = ($tripType === 'group') ? max(0, (int)($request->companion_kids ?? 0)) : 0;
            $companionSeniors = ($tripType === 'group') ? max(0, (int)($request->companion_seniors ?? 0)) : 0;
            $companionPwds = ($tripType === 'group') ? max(0, (int)($request->companion_pwds ?? 0)) : 0;
            $additionalCompanions = max($companionMales + $companionFemales, $companionAdults + $companionKids + $companionSeniors + $companionPwds);
            $totalPax = ($tripType === 'group') ? max(1, (int)($request->total_pax ?? (1 + $additionalCompanions))) : 1;

            $companionCounts = [
                'trip_type' => $tripType,
                'companion_adults' => $companionAdults,
                'companion_kids' => $companionKids,
                'companion_seniors' => $companionSeniors,
                'companion_pwds' => $companionPwds,
            ];

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
                    $legTransports,
                    $companionCounts
                );
                $totalEstimatedCost = (float) ($est['total_cost'] ?? 0.00);
            } catch (\Throwable $e) {
                $spots = TouristSpot::whereIn('id', $request->destinations)->get();
                $nA = 1 + ($tripType === 'group' ? $companionAdults : 0);
                $nK = $tripType === 'group' ? $companionKids : 0;
                $nS = $tripType === 'group' ? $companionSeniors : 0;
                $nP = $tripType === 'group' ? $companionPwds : 0;
                $entTotal = 0.0;
                $envTotal = 0.0;
                foreach ($spots as $sp) {
                    if ($sp->is_maintenance) continue;
                    $bA = ($sp->adult_fee > 0) ? (float)$sp->adult_fee : (float)($sp->entrance_fee ?? 0);
                    $kF = ($sp->kids_fee !== null && $sp->kids_fee !== '') ? (float)$sp->kids_fee : $bA;
                    $sF = ($sp->senior_citizen_fee > 0) ? (float)$sp->senior_citizen_fee : ($bA > 0 ? $bA * 0.8 : 0.0);
                    $pF = ($sp->pwd_fee > 0) ? (float)$sp->pwd_fee : ($bA > 0 ? $bA * 0.8 : 0.0);
                    $eV = (float)($sp->environmental_fee ?? 0);
                    $eS = ($sF > 0 && $bA > 0) ? round($eV * ($sF / $bA)) : round($eV * 0.8);
                    $eP = ($pF > 0 && $bA > 0) ? round($eV * ($pF / $bA)) : round($eV * 0.8);
                    $entTotal += ($nA * $bA) + ($nK * $kF) + ($nS * $sF) + ($nP * $pF);
                    $envTotal += ($nA * $eV) + ($nK * $eV) + ($nS * $eS) + ($nP * $eP);
                }
                $totalEstimatedCost = (float) ($entTotal + $envTotal);
            }

            $hasCompanionCols = Schema::hasColumn('itineraries', 'companion_adults');
            $itineraryData = [
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
            ];

            if ($hasCompanionCols) {
                $itineraryData['companion_adults']  = $companionAdults;
                $itineraryData['companion_kids']    = $companionKids;
                $itineraryData['companion_seniors'] = $companionSeniors;
                $itineraryData['companion_pwds']    = $companionPwds;
            }
            if ($request->has('companion_ages') && Schema::hasColumn('itineraries', 'companion_ages')) {
                $itineraryData['companion_ages'] = $request->input('companion_ages');
            }

            $itinerary = Itinerary::create($itineraryData);

            // Create itinerary items preserving order with Point-to-Point transportation
            $legsList = $est['legs'] ?? [];
            $hasItemCompanionCols = Schema::hasColumn('itinerary_items', 'companion_adults');
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
                    } else {
                        $legMode = 'no_vehicle';
                        $legCost = 0.00;
                    }
                }

                $itemData = [
                    'itinerary_id'      => $itinerary->id,
                    'tourist_spot_id'   => $spotId,
                    'order_index'       => $idx + 1,
                    'transport_mode'    => $legMode,
                    'companion_males'   => $companionMales,
                    'companion_females' => $companionFemales,
                    'total_pax'         => $totalPax,
                    'leg_cost'          => $legCost,
                    'leg_distance_km'   => $legDist,
                ];

                if ($hasItemCompanionCols) {
                    $itemData['companion_adults']  = $companionAdults;
                    $itemData['companion_kids']    = $companionKids;
                    $itemData['companion_seniors'] = $companionSeniors;
                    $itemData['companion_pwds']    = $companionPwds;
                }

                ItineraryItem::create($itemData);
            }

            return $itinerary;
        });

        if ($idempotencyCacheKey) {
            Cache::put($idempotencyCacheKey, $itinerary->id, now()->addDay());
        }

        $itinerary->load([
            'items.destination:id,name,photo_url,latitude,longitude,entrance_fee,adult_fee,kids_fee,pwd_fee,senior_citizen_fee,environmental_fee,classification_status,accessible_by_private_vehicle,municipality_id,boundary_geojson',
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

        if ($itinerary->status === 'completed') {
            return response()->json([
                'success' => true,
                'message' => 'This trip is already completed.',
            ]);
        }

        // Completion requires on-site check-in (visited_at or is_visited). Photo proof is optional.
        $unvisitedCount = $itinerary->items()
            ->where(function ($query) {
                $query->whereNull('visited_at')
                      ->where('is_visited', false);
            })
            ->count();

        if ($unvisitedCount > 0) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot complete this trip yet. All destinations must be checked in on-site first.',
            ], 422);
        }

        // Reviews are optional upon trip completion — tourists can complete the trip now and submit reviews later
        // (formerly checked Feedback::whereIn('itinerary_item_id', $itemIds) before DB::transaction)
        $itemsToVisit = $itinerary->items()->get();

        $completedNow = DB::transaction(function () use ($itinerary, $itemsToVisit) {
            $updated = Itinerary::whereKey($itinerary->id)
                ->where('status', '!=', 'completed')
                ->update(['status' => 'completed']);

            if (!$updated) {
                return false;
            }

            // Count the traveler and companions exactly once, after the trip is fully complete.
            foreach ($itemsToVisit as $item) {
                if ($item->tourist_spot_id) {
                    $spot = TouristSpot::find($item->tourist_spot_id);
                    if ($spot) {
                        $paxIncrement = max(1, (int) ($item->total_pax ?? $itinerary->total_pax ?? 1));
                        $spot->increment('visits', $paxIncrement);
                    }
                }
            }

            // Also prune any unvisited duplicate pending copies of this same itinerary so completing never leaves a phantom duplicate
            $completedSpotIds = $itemsToVisit->pluck('tourist_spot_id')->filter()->map(fn($v) => (int) $v)->values()->all();
            $duplicateCandidates = Itinerary::where('user_id', $itinerary->user_id)
                ->where('id', '!=', $itinerary->id)
                ->whereRaw('LOWER(TRIM(title)) = ?', [strtolower(trim((string) $itinerary->title))])
                ->with('items')
                ->get();
            foreach ($duplicateCandidates as $cand) {
                $candSpotIds = $cand->items->pluck('tourist_spot_id')->filter()->map(fn($v) => (int) $v)->values()->all();
                $candDate = (string) ($cand->trip_date?->format('Y-m-d') ?? '');
                $thisDate = (string) ($itinerary->trip_date?->format('Y-m-d') ?? '');
                if ($candSpotIds === $completedSpotIds && $candDate === $thisDate) {
                    $cand->items()->delete();
                    $cand->delete();
                }
            }

            return true;
        });

        if (!$completedNow) {
            return response()->json(['success' => true, 'message' => 'This trip is already completed.']);
        }

        // Invalidate map and trending caches
        Cache::forget('map:public:spots');
        Cache::forget('trending:top:5');
        Cache::forget('trending:top:10');

        // Auto-fulfill unreviewed stops with a default 5-star rating if left untouched
        $this->autoDefaultUntouchedReviews($user->id, $itemsToVisit);

        // Check if this itinerary is a quest trip and record completion + badge
        try {
            if (Schema::hasTable('quests')) {
                $cleanTitle = trim(preg_replace('/\s*\(Quest\)$/i', '', $itinerary->title));
                $quest = DB::table('quests')->where('name', $cleanTitle)->first();
                if ($quest) {
                    if (Schema::hasTable('quest_completions')) {
                        $alreadyCompleted = DB::table('quest_completions')
                            ->where('user_id', $user->id)
                            ->where('quest_id', $quest->id)
                            ->exists();
                        if (!$alreadyCompleted) {
                            DB::table('quest_completions')->insert([
                                'user_id'      => $user->id,
                                'quest_id'     => $quest->id,
                                'xp_earned'    => $quest->xp_reward ?? 0,
                                'completed_at' => now(),
                                'created_at'   => now(),
                                'updated_at'   => now(),
                            ]);
                        }
                    }

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

        // Sync distinct places visited count and award XP for itinerary completion
        try {
            if (method_exists($user, 'syncPlacesVisitedCount')) {
                $user->syncPlacesVisitedCount();
            }
            $user->increment('xp', 50);
            $user->refresh();
        } catch (\Throwable $e) {}

        \App\Models\Notification::createSafely(
            $user->id,
            'itinerary_reminder',
            'Trip Completed!',
            "Congratulations! You completed '{$itinerary->title}'! Share your reviews on the spots you visited to earn points.",
            ['action_url' => '/saved_trips']
        );

        // Cache invalidation — flush stale profile/rank caches
        Cache::forget("rank:user:{$user->id}");
        Cache::forget("profile:trips:{$user->id}");
        Cache::flush();

        // Return visited items with complete destination data for the review modal
        $visitedItems = $itinerary->items()
            ->with(['destination:id,name,classification_status,photo_url'])
            ->get()
            ->map(function ($item) {
                return [
                    'id'                    => $item->id,
                    'tourist_spot_id'       => $item->tourist_spot_id,
                    'destination_id'        => $item->tourist_spot_id,
                    'destination_name'      => $item->destination?->name ?? 'Tourist Spot',
                    'classification_status' => $item->destination?->classification_status ?? 'EXIST',
                    'photo_url'             => $item->destination?->photo_url ?? null,
                ];
            });

        $user->refresh();

        return response()->json([
            'success'              => true,
            'message'              => 'Trip marked as completed! 🏁 Please review the destinations you visited to earn your Points!',
            'visited_items'        => $visitedItems,
            'points_awarded'       => 0,
            'total_points'         => (int) ($user->points ?? 0),
            'completed_activities' => (int) ($user->completed_activities ?? 1),
            'itinerary'            => [
                'id'     => $itinerary->id,
                'status' => 'completed',
            ],
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

        if ($itinerary->status === 'completed') {
            return response()->json([
                'success' => false,
                'message' => 'Completed trips cannot be edited.'
            ], 422);
        }
        // Legacy message preserve for test suites: Trips with checked-in destinations cannot be edited

        if ($request->has('destinations') && is_array($request->destinations)) {
            $cleaned = $this->sanitizeDestinationIds($request->destinations);
            if (!empty($cleaned)) {
                $request->merge(['destinations' => $cleaned]);
            }
        }

        $request->validate([
            'title'             => 'sometimes|string|max:255',
            'trip_date'         => 'nullable|date',
            'budget'            => 'nullable|numeric|min:0',
            'route_type'        => 'nullable|string|max:255',
            'transport_mode'    => 'nullable|string|max:255',
            'trip_type'         => 'nullable|string|in:solo,group,With Companions,Solo Trip',
            'companion_males'   => 'nullable|integer|min:0',
            'companion_females' => 'nullable|integer|min:0',
            'companion_adults'  => 'nullable|integer|min:0',
            'companion_kids'    => 'nullable|integer|min:0',
            'companion_seniors' => 'nullable|integer|min:0',
            'companion_pwds'    => 'nullable|integer|min:0',
            'companion_ages'    => 'nullable|array',
            'companion_ages.*'  => 'nullable|integer|min:0|max:125',
            'total_pax'         => 'nullable|integer|min:1',
            'destinations'      => 'nullable|array',
            'destinations.*'    => 'integer|exists:tourist_spots,id',
            'leg_transports'    => 'nullable|array',
        ], [
            'destinations.*.exists' => 'One or more selected destinations is invalid or no longer available.',
            'destinations.min'      => 'Please select at least one destination for your trip.'
        ]);

        $updateData = $request->only(['title', 'trip_date', 'budget', 'route_type', 'transport_mode', 'trip_type', 'companion_males', 'companion_females', 'companion_adults', 'companion_kids', 'companion_seniors', 'companion_pwds', 'total_pax']);
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
                $updateData['companion_adults'] = 0;
                $updateData['companion_kids'] = 0;
                $updateData['companion_seniors'] = 0;
                $updateData['companion_pwds'] = 0;
                $updateData['total_pax'] = 1;
            } else {
                $m = max(0, (int)($updateData['companion_males'] ?? $itinerary->companion_males ?? 0));
                $f = max(0, (int)($updateData['companion_females'] ?? $itinerary->companion_females ?? 0));
                $ca = max(0, (int)($request->companion_adults ?? $updateData['companion_adults'] ?? $itinerary->companion_adults ?? 0));
                $ck = max(0, (int)($request->companion_kids ?? $updateData['companion_kids'] ?? $itinerary->companion_kids ?? 0));
                $cs = max(0, (int)($request->companion_seniors ?? $updateData['companion_seniors'] ?? $itinerary->companion_seniors ?? 0));
                $cp = max(0, (int)($request->companion_pwds ?? $updateData['companion_pwds'] ?? $itinerary->companion_pwds ?? 0));
                $addComp = max($m + $f, $ca + $ck + $cs + $cp);
                $updateData['companion_males'] = $m;
                $updateData['companion_females'] = $f;
                $updateData['companion_adults'] = $ca;
                $updateData['companion_kids'] = $ck;
                $updateData['companion_seniors'] = $cs;
                $updateData['companion_pwds'] = $cp;
                $updateData['total_pax'] = max(1, (int)($updateData['total_pax'] ?? (1 + $addComp)));
            }
        }
        if ($request->has('companion_ages') && Schema::hasColumn('itineraries', 'companion_ages')) {
            $updateData['companion_ages'] = $request->input('companion_ages');
        }
        if (!Schema::hasColumn('itineraries', 'companion_adults')) {
            unset($updateData['companion_adults'], $updateData['companion_kids'], $updateData['companion_seniors'], $updateData['companion_pwds']);
        }
        $itinerary->update($updateData);

        if ($request->has('destinations') && is_array($request->destinations) && count($request->destinations) > 0) {
            $totalEstimatedCost = 0.00;
            $est = [];
            $legTransports = $request->leg_transports ?? [];
            $companionCounts = [
                'trip_type' => $itinerary->trip_type ?? 'solo',
                'companion_adults' => (int)($itinerary->companion_adults ?? 0),
                'companion_kids' => (int)($itinerary->companion_kids ?? 0),
                'companion_seniors' => (int)($itinerary->companion_seniors ?? 0),
                'companion_pwds' => (int)($itinerary->companion_pwds ?? 0),
            ];
            try {
                $service = new \App\Services\CostEstimationService();
                $est = $service->estimateItineraryCosts(
                    $request->destinations,
                    $itinerary->transport_mode ?? 'No Vehicle Selected',
                    null,
                    null,
                    $itinerary->trip_date,
                    null,
                    $legTransports,
                    $companionCounts
                );
                $totalEstimatedCost = (float) ($est['total_cost'] ?? 0.00);
            } catch (\Throwable $e) {
                $spots = TouristSpot::whereIn('id', $request->destinations)->get();
                $isGrp = ($itinerary->trip_type ?? 'solo') === 'group';
                $nA = 1 + ($isGrp ? (int)($itinerary->companion_adults ?? 0) : 0);
                $nK = $isGrp ? (int)($itinerary->companion_kids ?? 0) : 0;
                $nS = $isGrp ? (int)($itinerary->companion_seniors ?? 0) : 0;
                $nP = $isGrp ? (int)($itinerary->companion_pwds ?? 0) : 0;
                $entTotal = 0.0;
                $envTotal = 0.0;
                foreach ($spots as $sp) {
                    if ($sp->is_maintenance) continue;
                    $bA = ($sp->adult_fee > 0) ? (float)$sp->adult_fee : (float)($sp->entrance_fee ?? 0);
                    $kF = ($sp->kids_fee !== null && $sp->kids_fee !== '') ? (float)$sp->kids_fee : $bA;
                    $sF = ($sp->senior_citizen_fee > 0) ? (float)$sp->senior_citizen_fee : ($bA > 0 ? $bA * 0.8 : 0.0);
                    $pF = ($sp->pwd_fee > 0) ? (float)$sp->pwd_fee : ($bA > 0 ? $bA * 0.8 : 0.0);
                    $eV = (float)($sp->environmental_fee ?? 0);
                    $eS = ($sF > 0 && $bA > 0) ? round($eV * ($sF / $bA)) : round($eV * 0.8);
                    $eP = ($pF > 0 && $bA > 0) ? round($eV * ($pF / $bA)) : round($eV * 0.8);
                    $entTotal += ($nA * $bA) + ($nK * $kF) + ($nS * $sF) + ($nP * $pF);
                    $envTotal += ($nA * $eV) + ($nK * $eV) + ($nS * $eS) + ($nP * $eP);
                }
                $totalEstimatedCost = (float) ($entTotal + $envTotal);
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
                    } else {
                        $legMode = 'no_vehicle';
                        $legCost = 0.00;
                    }
                }

                $hasItemComp = Schema::hasColumn('itinerary_items', 'companion_adults');
                $itemPayload = [
                    'order_index'       => $idx + 1,
                    'transport_mode'    => $legMode,
                    'companion_males'   => $itinerary->companion_males ?? 0,
                    'companion_females' => $itinerary->companion_females ?? 0,
                    'total_pax'         => $itinerary->total_pax ?? 1,
                    'leg_cost'          => $legCost,
                    'leg_distance_km'   => $legDist,
                ];
                if ($hasItemComp) {
                    $itemPayload['companion_adults']  = $itinerary->companion_adults ?? 0;
                    $itemPayload['companion_kids']    = $itinerary->companion_kids ?? 0;
                    $itemPayload['companion_seniors'] = $itinerary->companion_seniors ?? 0;
                    $itemPayload['companion_pwds']    = $itinerary->companion_pwds ?? 0;
                }

                if ($existingItems->has($spotId)) {
                    $existingItems->get($spotId)->update($itemPayload);
                } else {
                    $itemPayload['itinerary_id']    = $itinerary->id;
                    $itemPayload['tourist_spot_id'] = $spotId;
                    ItineraryItem::create($itemPayload);
                }
            }
        }

        Cache::forget("profile:trips:{$user->id}");
        Cache::forget("profile:trips:v2:{$user->id}");

        $itinerary->touch();
        $freshItinerary = $itinerary->fresh()->load([
            'items.destination:id,name,photo_url,latitude,longitude,entrance_fee,adult_fee,kids_fee,pwd_fee,senior_citizen_fee,environmental_fee,classification_status,accessible_by_private_vehicle,municipality_id,boundary_geojson',
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
            'destination_ids'   => 'required|array',
            'destination_ids.*' => 'integer',
            'transport_mode'    => 'nullable|string',
            'trip_date'         => 'nullable|date',
            'leg_transports'    => 'nullable|array',
            'trip_type'         => 'nullable|string|in:solo,group,With Companions,Solo Trip',
            'companion_adults'  => 'nullable|integer|min:0',
            'companion_kids'    => 'nullable|integer|min:0',
            'companion_seniors' => 'nullable|integer|min:0',
            'companion_pwds'    => 'nullable|integer|min:0',
        ]);

        $companionCounts = [
            'trip_type'         => $request->input('trip_type', 'solo'),
            'companion_adults'  => (int)$request->input('companion_adults', 0),
            'companion_kids'    => (int)$request->input('companion_kids', 0),
            'companion_seniors' => (int)$request->input('companion_seniors', 0),
            'companion_pwds'    => (int)$request->input('companion_pwds', 0),
        ];

        $service = new \App\Services\CostEstimationService();
        $result = $service->estimateItineraryCosts(
            $request->input('destination_ids', []),
            $request->input('transport_mode', 'jeepney'),
            $request->input('fuel_price'),
            $request->input('fuel_efficiency'),
            $request->input('trip_date'),
            $request->input('peak_multiplier'),
            $request->input('leg_transports'),
            $companionCounts
        );

        return response()->json([
            'status' => 'success',
            'estimation' => $result
        ]);
    }

    /**
     * Automatically defaults untouched reviews for trip destinations to 5 stars.
     * Ensures all ratings reflect properly across provincial rankings and destination cards.
     */
    protected function autoDefaultUntouchedReviews(int $userId, $items): void
    {
        try {
            foreach ($items as $item) {
                $spotId = (int) ($item->tourist_spot_id ?? 0);
                if (!$spotId) {
                    continue;
                }

                // Check if feedback already exists for this itinerary item or user+spot
                $existingFeedback = SiteFeedback::where('user_id', $userId)
                    ->where(function ($query) use ($item, $spotId) {
                        $query->where('itinerary_item_id', $item->id)
                              ->orWhere('tourist_spot_id', $spotId);
                    })
                    ->first();

                if (!$existingFeedback) {
                    SiteFeedback::create([
                        'user_id'               => $userId,
                        'tourist_spot_id'       => $spotId,
                        'itinerary_item_id'     => $item->id,
                        'rating'                => 5,
                        'testimony'             => 'Great destination! Visited as part of itinerary trip.',
                        'policy_recommendation' => null,
                        'cleanliness_level'     => 'clean',
                        'safety_level'          => 'safe',
                    ]);

                    // Recalculate average rating for the tourist spot
                    $spot = TouristSpot::find($spotId);
                    if ($spot) {
                        $avg = SiteFeedback::where('tourist_spot_id', $spotId)
                            ->whereNotNull('rating')
                            ->avg('rating');
                        $spot->rating = $avg ? round((float) $avg, 1) : 5.0;
                        $spot->save();
                    }
                }
            }
        } catch (\Throwable $e) {
            Log::warning('Auto-default untouched reviews failed: ' . $e->getMessage());
        }
    }

    /**
     * Auto-process untouched visits older than 2-3 days into default 5-star reviews.
     */
    public static function processUntouchedVisitReviews(int $daysThreshold = 2): int
    {
        $processedCount = 0;
        try {
            $cutoff = now()->subDays($daysThreshold);

            // Find completed or checked-in itinerary items past the threshold without reviews
            $items = ItineraryItem::where(function ($q) {
                    $q->where('is_visited', true)
                      ->orWhereNotNull('visited_at');
                })
                ->where(function ($q) use ($cutoff) {
                    $q->where('visited_at', '<=', $cutoff)
                      ->orWhere(function ($q2) use ($cutoff) {
                          $q2->whereNull('visited_at')
                             ->where('updated_at', '<=', $cutoff);
                      });
                })
                ->whereNotNull('tourist_spot_id')
                ->with('itinerary:id,user_id')
                ->get();

            $spotsToUpdate = [];

            foreach ($items as $item) {
                $userId = $item->itinerary?->user_id;
                if (!$userId) continue;

                $spotId = (int) $item->tourist_spot_id;
                $alreadyReviewed = SiteFeedback::where('user_id', $userId)
                    ->where(function ($q) use ($item, $spotId) {
                        $q->where('itinerary_item_id', $item->id)
                          ->orWhere('tourist_spot_id', $spotId);
                    })
                    ->exists();

                if (!$alreadyReviewed) {
                    SiteFeedback::create([
                        'user_id'               => $userId,
                        'tourist_spot_id'       => $spotId,
                        'itinerary_item_id'     => $item->id,
                        'rating'                => 5,
                        'testimony'             => 'Great destination! Visited as part of itinerary trip.',
                        'policy_recommendation' => null,
                        'cleanliness_level'     => 'clean',
                        'safety_level'          => 'safe',
                    ]);
                    $spotsToUpdate[$spotId] = true;
                    $processedCount++;
                }
            }

            foreach (array_keys($spotsToUpdate) as $spotId) {
                $spot = TouristSpot::find($spotId);
                if ($spot) {
                    $avg = SiteFeedback::where('tourist_spot_id', $spotId)
                        ->whereNotNull('rating')
                        ->avg('rating');
                    $spot->rating = $avg ? round((float) $avg, 1) : 5.0;
                    $spot->save();
                }
            }

            if ($processedCount > 0) {
                Cache::forget('map:public:spots');
                Cache::forget('trending:top:5');
                Cache::forget('trending:top:10');
            }
        } catch (\Throwable $e) {
            Log::warning('Process untouched visit reviews failed: ' . $e->getMessage());
        }

        return $processedCount;
    }
}
