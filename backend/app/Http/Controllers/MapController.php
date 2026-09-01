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
}
