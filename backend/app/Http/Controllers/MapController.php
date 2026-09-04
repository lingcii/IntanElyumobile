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
        $spots = \Illuminate\Support\Facades\Cache::remember('map:public:spots', 30, function () {
            $spotVehicleMap = [];
            try {
                $spotVehicleMap = \Illuminate\Support\Facades\DB::table('tourist_spot_vehicle_type')
                    ->join('vehicle_types', 'tourist_spot_vehicle_type.vehicle_type_id', '=', 'vehicle_types.id')
                    ->select('tourist_spot_vehicle_type.tourist_spot_id', 'vehicle_types.name')
                    ->get()
                    ->groupBy('tourist_spot_id');
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

            return TouristSpot::where(function($q) {
                    $q->whereIn('status', ['approved', 'active', 'published', 'EXIST', 'exist', 'pending'])
                      ->orWhereNull('status');
                })
                ->with('municipality:id,name')
                ->with('images')
                ->get(['id', 'name', 'category', 'municipality_id', 'barangay', 'latitude', 'longitude',
                       'entrance_fee', 'environmental_fee', 'fee_types', 'route_guide', 'tour_guide_notice',
                       'accessible_by_private_vehicle', 'photo_url', 'description', 'opening_time', 'closing_time',
                       'is_maintenance', 'rating', 'visits', 'classification_status'])
                ->map(function ($spot) use ($spotVehicleMap, $spotServiceCenterMap) {
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

                    $vehiclesList = isset($spotVehicleMap[$spot->id])
                        ? $spotVehicleMap[$spot->id]->pluck('name')->unique()->values()->toArray()
                        : [];

                    $feeTypes = $spot->fee_types;
                    if (is_string($feeTypes)) {
                        $decoded = json_decode($feeTypes, true);
                        $feeTypes = is_array($decoded) ? $decoded : [];
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
                        'accessible_vehicles'           => $vehiclesList,
                    ];
                })->values()->toArray();  // toArray() stores a plain array in cache — safe to serialize
        });

        return response()->json(['destinations' => $spots]);
    }

    /**
     * GET /api/public/municipalities
     * Returns all municipalities with their tourist spot counts for zone overlays.
     */
    public function publicMunicipalities(): JsonResponse
    {
        $municipalities = \Illuminate\Support\Facades\Cache::remember('map:public:municipalities', 300, function () {
            return Municipality::withCount(['touristSpots' => function ($q) {
                $q->where('status', 'approved');
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

        return response()->json(['municipalities' => $municipalities]);
    }

    /**
     * GET /api/public/fares
     * Returns latest active fare rates per vehicle type and vehicle data from Railway DB for the mobile app.
     */
    public function publicFares(Request $request): JsonResponse
    {
        $cacheKey = 'map:public:fares:v2';

        $fares = \Illuminate\Support\Facades\Cache::remember($cacheKey, 300, function () {
            $allActiveGuides = FareGuide::with(['matrices' => function ($q) {
                $q->orderBy('distance_km', 'asc');
            }])
            ->where('status', 'active')
            ->get();

            // Find San Juan's guide specifically (Guide #29)
            $sanJuanGuide = $allActiveGuides->first(function ($g) {
                return (stripos($g->title, 'san juan') !== false || stripos($g->region, 'san juan') !== false)
                    && strcasecmp($g->vehicle_type, 'Tricycle') === 0;
            });

            // If not found by title/region, fallback to ID 29 or any active tricycle guide
            if (!$sanJuanGuide) {
                $sanJuanGuide = $allActiveGuides->firstWhere('id', 29)
                    ?? $allActiveGuides->first(fn($g) => strcasecmp($g->vehicle_type, 'Tricycle') === 0);
            }

            // MPUJ / Jeepney guide
            $jeepGuide = $allActiveGuides->first(function ($g) {
                return in_array(strtoupper($g->vehicle_type), ['MPUJ', 'PUJ_ORDINARY', 'PUJ_AIRCON', 'JEEPNEY']);
            });

            // Bus guide
            $busGuide = $allActiveGuides->first(function ($g) {
                return in_array(strtoupper($g->vehicle_type), ['PUB_AIRCON', 'PUB_ORDINARY', 'BUS']);
            });

            $byMunicipality = [];
            foreach ($allActiveGuides as $g) {
                $muniKey = strtolower(trim(preg_replace('/[^a-zA-Z0-9]/', ' ', $g->region ?: $g->title)));
                $muniKey = preg_replace('/\s+/', '_', trim($muniKey));
                $rawTitle = strtolower(trim($g->title));

                $guideData = [
                    'id'           => $g->id,
                    'title'        => $g->title,
                    'region'       => $g->region,
                    'vehicle_type' => $g->vehicle_type,
                    'steps_count'  => $g->matrices->count(),
                    'base_fare'    => (float) ($g->matrices->first()?->regular_fare ?? 16.32),
                    'discounted_base' => (float) ($g->matrices->first()?->discounted_fare ?? 13.06),
                    'rates'        => $g->matrices->map(function ($m) {
                        return [
                            'distance_km'     => (float) $m->distance_km,
                            'regular_fare'    => (float) $m->regular_fare,
                            'discounted_fare' => (float) $m->discounted_fare,
                        ];
                    })->values()->toArray(),
                ];

                $byMunicipality[$muniKey] = $guideData;
                $byMunicipality[$rawTitle] = $guideData;
                if (stripos($g->title, 'san juan') !== false || stripos($g->region, 'san juan') !== false) {
                    $byMunicipality['san_juan'] = $guideData;
                    $byMunicipality['san juan'] = $guideData;
                }
            }

            $sanJuanRates = $sanJuanGuide ? $sanJuanGuide->matrices->map(function ($m) {
                return [
                    'distance_km'     => (float) $m->distance_km,
                    'regular_fare'    => (float) $m->regular_fare,
                    'discounted_fare' => (float) $m->discounted_fare,
                ];
            })->values()->toArray() : [];

            $jeepRates = $jeepGuide ? $jeepGuide->matrices->map(function ($m) {
                return [
                    'distance_km'     => (float) $m->distance_km,
                    'regular_fare'    => (float) $m->regular_fare,
                    'discounted_fare' => (float) $m->discounted_fare,
                ];
            })->values()->toArray() : [];

            $busRates = $busGuide ? $busGuide->matrices->map(function ($m) {
                return [
                    'distance_km'     => (float) $m->distance_km,
                    'regular_fare'    => (float) $m->regular_fare,
                    'discounted_fare' => (float) $m->discounted_fare,
                ];
            })->values()->toArray() : [];

            $result = [
                'tricycle' => [
                    'title'           => $sanJuanGuide ? $sanJuanGuide->title : 'San Juan Tricycle Fare Matrix',
                    'municipality'    => 'San Juan',
                    'vehicle_type'    => 'Tricycle',
                    'steps_count'     => count($sanJuanRates),
                    'base_fare'       => (float) ($sanJuanGuide?->matrices->first()?->regular_fare ?? 16.32),
                    'discounted_base' => (float) ($sanJuanGuide?->matrices->first()?->discounted_fare ?? 13.06),
                    'rates'           => $sanJuanRates,
                ],
                'jeepney' => [
                    'title'        => $jeepGuide ? $jeepGuide->title : 'MPUJ Fare Matrix',
                    'vehicle_type' => 'Jeepney',
                    'steps_count'  => count($jeepRates),
                    'rates'        => $jeepRates,
                ],
                'lutrampco' => [
                    'title'        => $jeepGuide ? $jeepGuide->title : 'LUTRAMPCO MPUJ Fare Matrix',
                    'vehicle_type' => 'Jeepney',
                    'steps_count'  => count($jeepRates),
                    'rates'        => $jeepRates,
                ],
                'mini_bus' => [
                    'title'        => $jeepGuide ? $jeepGuide->title : 'Mini Bus / PUJ Aircon Fare Matrix',
                    'vehicle_type' => 'Mini Bus',
                    'steps_count'  => count($jeepRates),
                    'rates'        => $jeepRates,
                ],
                'van' => [
                    'title'        => $jeepGuide ? $jeepGuide->title : 'UV Express / Van Fare Matrix',
                    'vehicle_type' => 'Van',
                    'steps_count'  => count($jeepRates),
                    'rates'        => $jeepRates,
                ],
                'private_bus' => [
                    'title'        => $busGuide ? $busGuide->title : 'PUB Aircon Fare Matrix',
                    'vehicle_type' => 'Bus',
                    'steps_count'  => count($busRates),
                    'rates'        => $busRates,
                ],
                'bus' => [
                    'title'        => $busGuide ? $busGuide->title : 'PUB Aircon Fare Matrix',
                    'vehicle_type' => 'Bus',
                    'steps_count'  => count($busRates),
                    'rates'        => $busRates,
                ],
                'san_juan' => [
                    'title'           => $sanJuanGuide ? $sanJuanGuide->title : 'San Juan Tricycle Fare Matrix',
                    'municipality'    => 'San Juan',
                    'vehicle_type'    => 'Tricycle',
                    'steps_count'     => count($sanJuanRates),
                    'base_fare'       => (float) ($sanJuanGuide?->matrices->first()?->regular_fare ?? 16.32),
                    'discounted_base' => (float) ($sanJuanGuide?->matrices->first()?->discounted_fare ?? 13.06),
                    'rates'           => $sanJuanRates,
                ],
                'by_municipality' => $byMunicipality,
            ];

            return $result;
        });

        $vehicles = \Illuminate\Support\Facades\Cache::remember('public:vehicles', 300, function () {
            try {
                return \App\Models\Vehicle::where('is_active', true)->get();
            } catch (\Throwable $e) {
                return [];
            }
        });

        $fuelPrice = \Illuminate\Support\Facades\Cache::remember('system:fuel_price', 300, function () {
            return \Illuminate\Support\Facades\DB::table('system_settings')
                ->where('key', 'fuel_price')
                ->value('value') ?? '65.00';
        });

        $vehicleTypes = \Illuminate\Support\Facades\Cache::remember('public:vehicle_types', 300, function () {
            try {
                return \Illuminate\Support\Facades\DB::table('vehicle_types')->get();
            } catch (\Throwable $e) {
                return [];
            }
        });

        return response()->json([
            'success'       => true,
            'fares'         => $fares,
            'vehicles'      => $vehicles,
            'vehicle_types' => $vehicleTypes,
            'fuel_price'    => (float) $fuelPrice
        ]);
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

        $radius = (int) ($request->query('radius', 600));
        if ($radius < 150) $radius = 150;
        if ($radius > 800) $radius = 800;

        $limit = (int) ($request->query('limit', 6));
        if ($limit < 1) $limit = 1;
        if ($limit > 10) $limit = 10;

        $roundedLat = round($lat, 3);
        $roundedLng = round($lng, 3);
        $cacheKey = "map:public:amenities:v12:{$roundedLat}:{$roundedLng}:{$radius}:{$limit}";

        $amenities = \Illuminate\Support\Facades\Cache::remember($cacheKey, 43200, function () use ($lat, $lng, $radius, $limit) {
            $results = [];
            $earthRadius = 6371000;

            $genericTerms = [
                'facility', 'atm', 'bank', 'convenience store', 'convenience', 'supermarket',
                'supermarket / store', 'store', 'pharmacy', 'gas station', 'fuel',
                'hospital', 'clinic', 'health clinic', 'police station', 'police',
                'public toilet', 'toilets', 'parking', 'restaurant', 'cafe', 'fast food'
            ];

            // 1. Primary: High-speed local verified dataset (2,700+ real, verified establishments across La Union)
            $localFile = storage_path('app/la_union_amenities.json');
            if (!file_exists($localFile)) {
                $localFile = base_path('../frontend/Mobile/src/assets/la_union_amenities.json');
            }

            if (file_exists($localFile)) {
                try {
                    $allLocal = json_decode(file_get_contents($localFile), true) ?: [];
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
                } catch (\Throwable $e) {}
            }

            // 2. Secondary fallback: Overpass API query if fewer than 2 amenities found locally
            if (count($results) < 2) {
                try {
                    $query = '[out:json][timeout:8];(' .
                        'nwr["amenity"~"^(atm|bank|pharmacy|fuel|hospital|clinic|police|cafe|restaurant|fast_food|parking|toilets|information)$"](around:' . $radius . ',' . $lat . ',' . $lng . ');' .
                        'nwr["shop"~"^(convenience|supermarket|chemist|bakery|gift|souvenir)$"](around:' . $radius . ',' . $lat . ',' . $lng . ');' .
                        ');out center 35;';

                    $mirrors = [
                        'https://overpass.kumi.systems/api/interpreter',
                        'https://overpass-api.de/api/interpreter'
                    ];

                    foreach ($mirrors as $mirrorUrl) {
                        $ch = curl_init($mirrorUrl);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                        curl_setopt($ch, CURLOPT_POST, true);
                        curl_setopt($ch, CURLOPT_POSTFIELDS, 'data=' . urlencode($query));
                        curl_setopt($ch, CURLOPT_USERAGENT, 'IntanElyuTourism/1.0');
                        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
                        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
                        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                        $res = curl_exec($ch);
                        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                        curl_close($ch);

                        if ($httpCode === 200 && $res) {
                            $data = json_decode($res, true);
                            $elements = $data['elements'] ?? [];

                            foreach ($elements as $el) {
                                $eLat = $el['lat'] ?? ($el['center']['lat'] ?? null);
                                $eLng = $el['lon'] ?? ($el['center']['lon'] ?? null);
                                if (!$eLat || !$eLng) continue;

                                $tags = $el['tags'] ?? [];
                                $rawType = strtolower($tags['amenity'] ?? ($tags['shop'] ?? ($tags['tourism'] ?? 'amenity')));

                                $type = 'other';
                                $label = 'Facility';
                                $icon = 'fa-solid fa-location-dot';
                                $color = '#64748b';

                                if ($rawType === 'atm') {
                                    $type = 'atm';
                                    $label = 'ATM';
                                    $icon = 'fa-solid fa-money-bill-wave';
                                    $color = '#10b981';
                                } elseif ($rawType === 'bank') {
                                    $type = 'bank';
                                    $label = 'Bank';
                                    $icon = 'fa-solid fa-building-columns';
                                    $color = '#059669';
                                } elseif (in_array($rawType, ['convenience', 'supermarket'])) {
                                    $type = 'convenience';
                                    $label = $rawType === 'supermarket' ? 'Supermarket' : 'Convenience Store';
                                    $icon = 'fa-solid fa-store';
                                    $color = '#f59e0b';
                                } elseif (in_array($rawType, ['pharmacy', 'chemist'])) {
                                    $type = 'pharmacy';
                                    $label = 'Pharmacy';
                                    $icon = 'fa-solid fa-prescription-bottle-medical';
                                    $color = '#ec4899';
                                } elseif ($rawType === 'fuel') {
                                    $type = 'fuel';
                                    $label = 'Gas Station';
                                    $icon = 'fa-solid fa-gas-pump';
                                    $color = '#f97316';
                                } elseif (in_array($rawType, ['hospital', 'clinic'])) {
                                    $type = 'medical';
                                    $label = $rawType === 'hospital' ? 'Hospital' : 'Clinic';
                                    $icon = 'fa-solid fa-hospital';
                                    $color = '#06b6d4';
                                } elseif ($rawType === 'police') {
                                    $type = 'police';
                                    $label = 'Police Station';
                                    $icon = 'fa-solid fa-shield-halved';
                                    $color = '#3b82f6';
                                } elseif ($rawType === 'cafe') {
                                    $type = 'cafe';
                                    $label = 'Cafe';
                                    $icon = 'fa-solid fa-mug-hot';
                                    $color = '#8b5cf6';
                                } elseif ($rawType === 'restaurant') {
                                    $type = 'restaurant';
                                    $label = 'Restaurant';
                                    $icon = 'fa-solid fa-utensils';
                                    $color = '#ea580c';
                                } elseif ($rawType === 'fast_food') {
                                    $type = 'fast_food';
                                    $label = 'Fast Food';
                                    $icon = 'fa-solid fa-burger';
                                    $color = '#e11d48';
                                } elseif ($rawType === 'parking') {
                                    $type = 'parking';
                                    $label = 'Parking';
                                    $icon = 'fa-solid fa-square-parking';
                                    $color = '#0284c7';
                                } elseif ($rawType === 'toilets') {
                                    $type = 'toilets';
                                    $label = 'Restroom';
                                    $icon = 'fa-solid fa-restroom';
                                    $color = '#06b6d4';
                                } elseif (in_array($rawType, ['gift', 'souvenir'])) {
                                    $type = 'souvenir';
                                    $label = 'Pasalubong / Souvenir';
                                    $icon = 'fa-solid fa-gift';
                                    $color = '#a855f7';
                                } elseif ($rawType === 'information') {
                                    $type = 'information';
                                    $label = 'Tourism Info';
                                    $icon = 'fa-solid fa-circle-info';
                                    $color = '#10b981';
                                } elseif ($rawType === 'bakery') {
                                    $type = 'bakery';
                                    $label = 'Bakery';
                                    $icon = 'fa-solid fa-bread-slice';
                                    $color = '#d97706';
                                } else {
                                    continue;
                                }

                                $name = trim($tags['name'] ?? '');
                                if (empty($name) || in_array(strtolower($name), $genericTerms)) {
                                    $brand = trim($tags['brand'] ?? '');
                                    $operator = trim($tags['operator'] ?? '');
                                    if (!empty($brand)) $name = ($type === 'atm' ? "{$brand} ATM" : $brand);
                                    elseif (!empty($operator)) $name = ($type === 'atm' ? "{$operator} ATM" : $operator);
                                }

                                // Strict accuracy: skip if still generic or unnamed
                                if (empty($name) || strlen($name) < 3 || in_array(strtolower($name), $genericTerms) || str_starts_with(strtolower($name), 'unnamed')) {
                                    continue;
                                }

                                $dLat = deg2rad($eLat - $lat);
                                $dLon = deg2rad($eLng - $lng);
                                $val = sin($dLat / 2) * sin($dLat / 2) +
                                       cos(deg2rad($lat)) * cos(deg2rad($eLat)) *
                                       sin($dLon / 2) * sin($dLon / 2);
                                $dist = round($earthRadius * 2 * atan2(sqrt($val), sqrt(1 - $val)));

                                // Strictly hide if not close to the tourist site (<= radius)
                                if ($dist <= $radius) {
                                    $results[] = [
                                        'id'              => ($el['type'] ?? 'node') . '_' . ($el['id'] ?? uniqid()),
                                        'name'            => $name,
                                        'type'            => $type,
                                        'raw_type'        => $rawType,
                                        'label'           => $label,
                                        'icon'            => $icon,
                                        'color'           => $color,
                                        'lat'             => (float) $eLat,
                                        'lng'             => (float) $eLng,
                                        'distance_meters' => (int) $dist,
                                    ];
                                }
                            }
                            break;
                        }
                    }
                } catch (\Throwable $e) {}
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

            // Pick diverse, non-overlapping amenities (at most 1 per primary category, min 80m distance to avoid collisions)
            $selected = [];
            $seenCategories = [];
            foreach ($unique as $item) {
                $t = $item['type'];
                if (in_array($t, ['atm', 'bank'])) $broadCat = 'financial';
                elseif (in_array($t, ['restaurant', 'fast_food'])) $broadCat = 'dining';
                elseif (in_array($t, ['cafe', 'bakery'])) $broadCat = 'cafe';
                else $broadCat = $t;

                if (isset($seenCategories[$broadCat])) {
                    continue; // Keep only the closest one per category to prevent marker stacking
                }

                // Ensure marker isn't immediately on top of an already chosen marker (< 80m)
                $tooClose = false;
                foreach ($selected as $s) {
                    $dLat = deg2rad($item['lat'] - $s['lat']);
                    $dLon = deg2rad($item['lng'] - $s['lng']);
                    $v = sin($dLat / 2) * sin($dLat / 2) + cos(deg2rad($item['lat'])) * cos(deg2rad($s['lat'])) * sin($dLon / 2) * sin($dLon / 2);
                    $distBetween = round($earthRadius * 2 * atan2(sqrt($v), sqrt(1 - $v)));
                    if ($distBetween < 80) {
                        $tooClose = true;
                        break;
                    }
                }
                if ($tooClose) continue;

                $seenCategories[$broadCat] = true;
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
