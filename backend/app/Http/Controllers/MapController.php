<?php

namespace App\Http\Controllers;

use App\Models\FareGuide;
use App\Models\FareMatrix;
use App\Models\Municipality;
use App\Models\TouristSpot;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MapController extends Controller
{
    /**
     * GET /api/public/map
     * Returns all approved tourist spots for the mobile map view (no auth required).
     */
    public function publicMapData(): JsonResponse
    {
        $spots = \Illuminate\Support\Facades\Cache::remember('map:public:spots:v4', 900, function () {
            $spotPublicVehicles = [];
            $spotPrivateVehicles = [];
            $spotAllVehicles = [];
            try {
                $spotVehicles = \Illuminate\Support\Facades\DB::table('tourist_spot_vehicle_type')
                    ->join('vehicle_types', 'tourist_spot_vehicle_type.vehicle_type_id', '=', 'vehicle_types.id')
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

            $spotServiceCenterMap = [];
            try {
                $serviceCenters = \Illuminate\Support\Facades\DB::table('tourist_spot_service_center')
                    ->join('service_centers', 'tourist_spot_service_center.service_center_id', '=', 'service_centers.id')
                    ->select(
                        'tourist_spot_service_center.tourist_spot_id',
                        'service_centers.id',
                        'service_centers.name',
                        'service_centers.type',
                        'service_centers.contact_number',
                        'service_centers.address',
                        'service_centers.description'
                    )
                    ->get();
                
                foreach ($serviceCenters as $sc) {
                    $spotServiceCenterMap[$sc->tourist_spot_id][] = [
                        'id'             => $sc->id,
                        'name'           => $sc->name,
                        'type'           => $sc->type,
                        'contact_number' => $sc->contact_number,
                        'address'        => $sc->address,
                        'description'    => $sc->description,
                    ];
                }
            } catch (\Throwable $e) {}

            return TouristSpot::activeForTourists()
                ->with('municipality:id,name')
                ->with('images')
                ->get(['id', 'name', 'category', 'municipality_id', 'barangay', 'latitude', 'longitude',
                       'entrance_fee', 'adult_fee', 'kids_fee', 'pwd_fee', 'senior_citizen_fee', 'entrance_fee_types',
                       'environmental_fee', 'fee_types', 'route_guide', 'tour_guide_notice',
                       'accessible_by_private_vehicle', 'photo_url', 'description', 'opening_time', 'closing_time',
                       'is_maintenance', 'rating', 'visits', 'classification_status', 'status'])
                ->map(function ($spot) use ($spotPublicVehicles, $spotPrivateVehicles, $spotAllVehicles, $spotServiceCenterMap) {
                    $imageUrl = $spot->photo_url;
                    if (!$imageUrl && $spot->images->isNotEmpty()) {
                        $imageUrl = $spot->images->first()->photo_url;
                    }
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

                    $pubList = isset($spotPublicVehicles[$spot->id])
                        ? array_values(array_unique($spotPublicVehicles[$spot->id]))
                        : [];
                    $privList = isset($spotPrivateVehicles[$spot->id])
                        ? array_values(array_unique($spotPrivateVehicles[$spot->id]))
                        : [];
                    $allList = isset($spotAllVehicles[$spot->id])
                        ? array_values(array_unique($spotAllVehicles[$spot->id]))
                        : [];

                    $feeTypes = $spot->fee_types;
                    if (is_string($feeTypes)) {
                        $decoded = json_decode($feeTypes, true);
                        $feeTypes = is_array($decoded) ? $decoded : [];
                    }

                    $entranceFeeTypes = $spot->entrance_fee_types;
                    if (is_string($entranceFeeTypes)) {
                        $decoded = json_decode($entranceFeeTypes, true);
                        $entranceFeeTypes = is_array($decoded) ? $decoded : [];
                    }

                    return [
                        'id'                            => $spot->id,
                        'name'                          => $spot->name,
                        'category'                      => $spot->category,
                        'municipality'                  => $spot->municipality?->name,
                        'barangay'                      => $spot->barangay,
                        'lat'                           => $spot->latitude,
                        'lng'                           => $spot->longitude,
                        'entrance_fee'                  => (float) ($spot->entrance_fee ?? 0),
                        'adult_fee'                     => (float) ($spot->adult_fee ?? 0),
                        'kids_fee'                      => (float) ($spot->kids_fee ?? 0),
                        'pwd_fee'                       => (float) ($spot->pwd_fee ?? 0),
                        'senior_citizen_fee'            => (float) ($spot->senior_citizen_fee ?? 0),
                        'entrance_fee_types'            => $entranceFeeTypes ?? [],
                        'environmental_fee'             => (float) ($spot->environmental_fee ?? 0),
                        'fee_types'                     => $feeTypes ?? [],
                        'route_guide'                   => $spot->route_guide,
                        'tour_guide_notice'             => $spot->tour_guide_notice,
                        'accessible_by_private_vehicle' => (bool) ($spot->accessible_by_private_vehicle ?? 1),
                        'service_centers'               => $spotServiceCenterMap[$spot->id] ?? [],
                        'photo_url'                     => $imageUrl,
                        'images'                        => $imagesList,
                        'description'                   => $spot->description,
                        'opening_time'                  => $spot->opening_time,
                        'closing_time'                  => $spot->closing_time,
                        'is_maintenance'                => $spot->is_maintenance,
                        'rating'                        => $spot->rating,
                        'visits'                        => $spot->visits,
                        'classification_status'         => $spot->classification_status,
                        'status'                        => $spot->status ?? 'approved',
                        'accessible_vehicles'           => $allList,
                        'public_vehicles'               => $pubList,
                        'private_vehicles'              => $privList,
                        'has_available_vehicles'        => !empty($allList),
                    ];
                })->values()->toArray();  // toArray() stores a plain array in cache — safe to serialize
        });

        return response()->json(['destinations' => $spots])
            ->header('Cache-Control', 'public, max-age=600, stale-while-revalidate=1800');
    }

    /**
     * GET /api/public/municipalities
     * Returns all municipalities with their tourist spot counts for zone overlays.
     */
    public function publicMunicipalities(): JsonResponse
    {
        $municipalities = \Illuminate\Support\Facades\Cache::remember('map:public:municipalities', 3600, function () {
            return Municipality::withCount(['touristSpots' => function ($q) {
                $q->activeForTourists();
            }])
            ->get(['id', 'name', 'latitude', 'longitude'])
            ->map(function ($m) {
                return [
                    'id'         => $m->id,
                    'name'       => $m->name,
                    'lat'        => $m->latitude,
                    'lng'        => $m->longitude,
                    'spot_count' => $m->tourist_spots_count ?? 0,
                ];
            })->values()->toArray();
        });

        return response()->json(['municipalities' => $municipalities])
            ->header('Cache-Control', 'public, max-age=3600, stale-while-revalidate=7200');
    }

    /**
     * GET /api/public/fares
     * Returns latest active fare rates per vehicle type and vehicle data from Railway DB for the mobile app.
     */
    public function publicFares(Request $request): JsonResponse
    {
        $cacheKey = 'map:public:fares:v5';

        $fares = \Illuminate\Support\Facades\Cache::remember($cacheKey, 7200, function () {
            $allActiveGuides = FareGuide::with(['matrices' => function ($q) {
                $q->orderBy('distance_km', 'asc');
            }])
            ->where('status', 'active')
            ->where('is_archived', 0)
            ->get();

            $formatGuide = function ($g) {
                if (!$g) return null;
                $rates = $g->matrices->map(function ($m) {
                    return [
                        'distance_km'     => (float) $m->distance_km,
                        'regular_fare'    => (float) $m->regular_fare,
                        'discounted_fare' => (float) $m->discounted_fare,
                    ];
                })->values()->toArray();

                return [
                    'id'              => $g->id,
                    'title'           => $g->title,
                    'region'          => $g->region,
                    'vehicle_type'    => $g->vehicle_type,
                    'steps_count'     => count($rates),
                    'base_fare'       => (float) ($g->matrices->first()?->regular_fare ?? 0),
                    'discounted_base' => (float) ($g->matrices->first()?->discounted_fare ?? 0),
                    'rates'           => $rates,
                ];
            };

            // Inter-municipal / provincial guides
            $mpujGuide = $allActiveGuides->first(function ($g) {
                return strtoupper($g->vehicle_type) === 'MPUJ';
            });

            $tpujGuide = $allActiveGuides->first(function ($g) {
                return in_array(strtoupper($g->vehicle_type), ['TPUJ', 'PUJ_ORDINARY', 'JEEPNEY']);
            });

            $pubAirconGuide = $allActiveGuides->first(function ($g) {
                return strtoupper($g->vehicle_type) === 'PUB_AIRCON';
            });

            $pubOrdinaryGuide = $allActiveGuides->first(function ($g) {
                return in_array(strtoupper($g->vehicle_type), ['PUB_ORDINARY', 'PUB_REGULAR']);
            });

            $uveGuide = $allActiveGuides->first(function ($g) {
                return in_array(strtoupper($g->vehicle_type), ['UVE', 'UV EXPRESS', 'VAN']);
            });

            $allMuniNames = Municipality::pluck('name')->toArray();
            if (empty($allMuniNames)) {
                $allMuniNames = [
                    'San Juan', 'San Fernando City', 'Bacnotan', 'Agoo', 'Aringay', 
                    'Bangar', 'Bauang', 'Burgos', 'Caba', 'Luna', 'Naguilian', 
                    'Pugo', 'Rosario', 'San Gabriel', 'Santol', 'Santo Tomas', 
                    'Sudipen', 'Tubao', 'Bagulin', 'Balaoan'
                ];
            }

            $byMunicipality = [];
            $activeVehiclesByMuni = [];

            // Initialize all municipalities with lowercase/slug keys
            foreach ($allMuniNames as $mName) {
                $clean = trim(preg_replace('/^(municipality of|city of)\s+/i', '', $mName));
                $clean = trim(preg_replace('/,\s*la\s*union$/i', '', $clean));
                $k = strtolower(trim(preg_replace('/[^a-zA-Z0-9]/', '_', $clean)));
                $r = strtolower($clean);

                $byMunicipality[$k] = [];
                $byMunicipality[$r] = [];
                $activeVehiclesByMuni[$k] = [];
                $activeVehiclesByMuni[$r] = [];
            }

            // Distribute guides
            foreach ($allActiveGuides as $g) {
                $gData = $formatGuide($g);
                $vKey = strtolower(trim(preg_replace('/[^a-zA-Z0-9]/', '_', $g->vehicle_type)));
                $isAllMuni = stripos($g->region, 'All Municipalities') !== false || stripos($g->title, 'All Municipalities') !== false;

                if ($isAllMuni) {
                    foreach ($byMunicipality as $mKey => &$mMap) {
                        $mMap[$vKey] = $gData;
                        if (!in_array($g->vehicle_type, $activeVehiclesByMuni[$mKey])) {
                            $activeVehiclesByMuni[$mKey][] = $g->vehicle_type;
                        }
                    }
                    unset($mMap);
                } else {
                    $rawRegion = trim($g->region ?: $g->title);
                    $cleanMuni = trim(preg_replace('/^(municipality of|city of)\s+/i', '', $rawRegion));
                    $cleanMuni = trim(preg_replace('/,\s*la\s*union$/i', '', $cleanMuni));
                    $mKey = strtolower(trim(preg_replace('/[^a-zA-Z0-9]/', '_', $cleanMuni)));
                    $mRaw = strtolower($cleanMuni);

                    if (!isset($byMunicipality[$mKey])) {
                        $byMunicipality[$mKey] = [];
                        $byMunicipality[$mRaw] = [];
                        $activeVehiclesByMuni[$mKey] = [];
                        $activeVehiclesByMuni[$mRaw] = [];
                    }

                    $byMunicipality[$mKey][$vKey] = $gData;
                    $byMunicipality[$mRaw][$vKey] = $gData;
                    $byMunicipality[$mKey]['default'] = $gData;
                    $byMunicipality[$mRaw]['default'] = $gData;

                    if (!in_array($g->vehicle_type, $activeVehiclesByMuni[$mKey])) {
                        $activeVehiclesByMuni[$mKey][] = $g->vehicle_type;
                        $activeVehiclesByMuni[$mRaw][] = $g->vehicle_type;
                    }
                }
            }

            $result = [
                'mpuj' => $formatGuide($mpujGuide),
                'tpuj' => $formatGuide($tpujGuide),
                'pub_aircon' => $formatGuide($pubAirconGuide),
                'pub_ordinary' => $formatGuide($pubOrdinaryGuide),
                'pub_regular' => $formatGuide($pubOrdinaryGuide),
                'tricycle' => null, // Tricycle is municipality-specific, found under by_municipality
                'uve' => $formatGuide($uveGuide),
                // Compatibility aliases
                'jeepney' => $formatGuide($mpujGuide ?? $tpujGuide),
                'lutrampco' => $formatGuide($mpujGuide ?? $tpujGuide),
                'mini_bus' => $formatGuide($uveGuide ?? $mpujGuide),
                'van' => $formatGuide($uveGuide),
                'bus' => $formatGuide($pubAirconGuide ?? $pubOrdinaryGuide),
                'private_bus' => $formatGuide($pubAirconGuide),
                'by_municipality' => $byMunicipality,
                'active_vehicles_by_municipality' => $activeVehiclesByMuni,
            ];

            return $result;
        });

        $activeFareVehicleTypes = \Illuminate\Support\Facades\Cache::remember('public:active_fare_vehicle_types:v4', 300, function () {
            try {
                $dbTypes = \Illuminate\Support\Facades\DB::table('vehicle_types')
                    ->select('name', 'category')
                    ->orderBy('category', 'desc')
                    ->orderBy('name', 'asc')
                    ->get()
                    ->map(fn($v) => ['name' => $v->name, 'category' => $v->category])
                    ->unique('name')
                    ->values()
                    ->toArray();

                if (!empty($dbTypes)) {
                    return $dbTypes;
                }
            } catch (\Throwable $e) {}

            return [
                ['name' => 'MPUJ', 'category' => 'Public Vehicle'],
                ['name' => 'TPUJ', 'category' => 'Public Vehicle'],
                ['name' => 'PUB_Aircon', 'category' => 'Public Vehicle'],
                ['name' => 'PUB_Ordinary', 'category' => 'Public Vehicle'],
                ['name' => 'PUB_Regular', 'category' => 'Public Vehicle'],
                ['name' => 'Tricycle', 'category' => 'Public Vehicle'],
                ['name' => 'UVE', 'category' => 'Public Vehicle'],
                ['name' => 'TAXI', 'category' => 'Public Vehicle'],
                ['name' => 'Car', 'category' => 'Private Vehicle'],
                ['name' => 'Van', 'category' => 'Private Vehicle'],
                ['name' => 'Motorcycle', 'category' => 'Private Vehicle'],
            ];
        });

        $fuelPrice = \Illuminate\Support\Facades\Cache::remember('system:fuel_price', 3600, function () {
            return \Illuminate\Support\Facades\DB::table('system_settings')
                ->where('key', 'fuel_price')
                ->value('value') ?? '65.00';
        });

        return response()->json([
            'success'                         => true,
            'fares'                           => $fares,
            'vehicles'                        => [],
            'vehicle_types'                   => $activeFareVehicleTypes,
            'active_vehicles_by_municipality' => $fares['active_vehicles_by_municipality'] ?? [],
            'fuel_price'                      => (float) $fuelPrice
        ])->header('Cache-Control', 'public, max-age=600, stale-while-revalidate=1800');
    }

    /**
     * GET /api/public/amenities
     * Returns nearby real-world amenities (ATMs, convenience stores, pharmacies, gas stations, clinics, etc.)
     * around a given coordinate with Haversine distance calculation and caching.
     */
    public function publicAmenities(Request $request): JsonResponse
    {
        $lat = filter_var($request->query('lat'), FILTER_VALIDATE_FLOAT);
        $lng = filter_var($request->query('lng'), FILTER_VALIDATE_FLOAT);

        if ($lat === false || $lng === false) {
            return response()->json([
                'success'   => false,
                'message'   => 'Valid lat and lng query parameters are required.',
                'amenities' => []
            ], 400);
        }

        $radius = (int) ($request->query('radius', 800));
        if ($radius < 150) $radius = 150;
        if ($radius > 1200) $radius = 1200;

        $limit = (int) ($request->query('limit', 20));
        if ($limit < 1) $limit = 1;
        if ($limit > 40) $limit = 40;

        $roundedLat = round($lat, 3);
        $roundedLng = round($lng, 3);
        $cacheKey = "map:public:amenities:v16:{$roundedLat}:{$roundedLng}:{$radius}:{$limit}";

        $amenities = \Illuminate\Support\Facades\Cache::remember($cacheKey, 43200, function () use ($lat, $lng, $radius, $limit) {
            $results = [];
            $earthRadius = 6371000;

            $genericTerms = [
                'facility', 'atm', 'bank', 'convenience store', 'convenience', 'supermarket',
                'supermarket / store', 'store', 'pharmacy', 'gas station', 'fuel',
                'hospital', 'clinic', 'health clinic', 'police station', 'police',
                'public toilet', 'toilets', 'parking', 'restaurant', 'cafe', 'fast food',
                'hotel', 'motel', 'resort', 'church', 'chapel', 'park', 'vulcanizing', 'car repair'
            ];

            // 1. High-speed cached verified dataset (5,600+ real, verified establishments across La Union)
            $localFile = storage_path('app/la_union_amenities.json');
            if (!file_exists($localFile)) {
                $localFile = base_path('../frontend/Mobile/src/assets/la_union_amenities.json');
            }

            $allLocal = [];
            if (file_exists($localFile)) {
                try {
                    $allLocal = \Illuminate\Support\Facades\Cache::rememberForever('map:local_amenities_dataset_v1', function () use ($localFile) {
                        return json_decode(file_get_contents($localFile), true) ?: [];
                    });
                } catch (\Throwable $e) {
                    $allLocal = json_decode(file_get_contents($localFile), true) ?: [];
                }
            }

            foreach ($allLocal as $item) {
                $itemLat = (float) ($item['lat'] ?? 0);
                $itemLng = (float) ($item['lng'] ?? 0);
                if (!$itemLat || !$itemLng) continue;

                $name = trim($item['name'] ?? '');
                $lowerName = strtolower($name);

                // Strict accuracy filter: exclude vague, generic or unnamed entries
                if (empty($name) || strlen($name) < 3 || in_array($lowerName, $genericTerms) || str_starts_with($lowerName, 'unnamed')) {
                    continue;
                }

                $dLat = deg2rad($itemLat - $lat);
                $dLon = deg2rad($itemLng - $lng);
                $val = sin($dLat / 2) * sin($dLat / 2) +
                       cos(deg2rad($lat)) * cos(deg2rad($itemLat)) *
                       sin($dLon / 2) * sin($dLon / 2);
                $dist = round($earthRadius * 2 * atan2(sqrt($val), sqrt(1 - $val)));

                // Strictly hide if not close to the tourist site (<= radius)
                if ($dist <= $radius) {
                    $item['distance_meters'] = (int) $dist;
                    $results[] = $item;
                }
            }

            // Deduplicate by name + type + rounded coords
            $unique = [];
            $seenKeys = [];
            foreach ($results as $item) {
                $dedupKey = strtolower($item['name']) . '_' . round($item['lat'], 4) . '_' . round($item['lng'], 4);
                if (isset($seenKeys[$dedupKey])) continue;
                $seenKeys[$dedupKey] = true;
                $unique[] = $item;
            }

            usort($unique, fn($a, $b) => $a['distance_meters'] <=> $b['distance_meters']);

            // Show all distinct verified establishments in this site within radius
            $selected = [];
            foreach ($unique as $item) {
                $normName = strtolower(trim($item['name']));
                $tooCloseDuplicate = false;
                foreach ($selected as $s) {
                    // Only skip if the exact same establishment name is already selected within 12 meters
                    if (strtolower(trim($s['name'])) === $normName) {
                        $dLat = deg2rad($item['lat'] - $s['lat']);
                        $dLon = deg2rad($item['lng'] - $s['lng']);
                        $v = sin($dLat / 2) * sin($dLat / 2) + cos(deg2rad($item['lat'])) * cos(deg2rad($s['lat'])) * sin($dLon / 2) * sin($dLon / 2);
                        $distBetween = round($earthRadius * 2 * atan2(sqrt($v), sqrt(1 - $v)));
                        if ($distBetween < 12) {
                            $tooCloseDuplicate = true;
                            break;
                        }
                    }
                }
                if ($tooCloseDuplicate) continue;

                $selected[] = $item;
                if (count($selected) >= $limit) break;
            }

            return $selected;
        });

        return response()->json([
            'success'   => true,
            'count'     => count($amenities),
            'amenities' => $amenities
        ]);
    }
}
