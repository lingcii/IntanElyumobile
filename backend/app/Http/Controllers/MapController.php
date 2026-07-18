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
        $spots = \Illuminate\Support\Facades\Cache::remember('map:public:spots', 300, function () {
            return TouristSpot::where('status', 'approved')
                ->with('municipality:id,name')
                ->with('images')
                ->get(['id', 'name', 'category', 'municipality_id', 'latitude', 'longitude',
                       'entrance_fee', 'photo_url', 'description', 'opening_time', 'closing_time',
                       'is_maintenance', 'rating', 'visits', 'classification_status'])
                ->map(function ($spot) {
                    $imageUrl = $spot->photo_url;
                    if (!$imageUrl && $spot->images->isNotEmpty()) {
                        $imageUrl = $spot->images->first()->photo_url;
                    }
                    return [
                        'id'                    => $spot->id,
                        'name'                  => $spot->name,
                        'category'              => $spot->category,
                        'municipality'          => $spot->municipality?->name,
                        'lat'                   => $spot->latitude,
                        'lng'                   => $spot->longitude,
                        'entrance_fee'          => $spot->entrance_fee,
                        'photo_url'             => $imageUrl,
                        'description'           => $spot->description,
                        'opening_time'          => $spot->opening_time,
                        'closing_time'          => $spot->closing_time,
                        'is_maintenance'        => $spot->is_maintenance,
                        'rating'                => $spot->rating,
                        'visits'                => $spot->visits,
                        'classification_status' => $spot->classification_status,
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
     * Returns latest active fare rates per vehicle type for the mobile app.
     */
    public function publicFares(): JsonResponse
    {
        $cacheKey = 'map:public:fares';

        $fares = \Illuminate\Support\Facades\Cache::remember($cacheKey, 300, function () {
            $vehicleMap = [
                'Tricycle'    => 'tricycle',
                'PUJ_Ordinary'=> 'jeepney',
                'PUB_Ordinary'=> 'lutrampco',
                'PUJ_Aircon'  => 'mini_bus',
                'PUB_Aircon'  => 'private_bus',
                'Van'         => 'van',
            ];

            $result = [];

            foreach ($vehicleMap as $dbType => $frontendType) {
                $guide = FareGuide::where('vehicle_type', $dbType)
                    ->where('status', 'active')
                    ->latest('effective_date')
                    ->first();

                if ($guide) {
                    $matrices = FareMatrix::where('fare_guide_id', $guide->id)
                        ->orderBy('distance_km')
                        ->get(['distance_km', 'regular_fare', 'discounted_fare']);
                    $result[$frontendType] = [
                        'title'    => $guide->title,
                        'rates'    => $matrices,
                    ];
                }
            }

            return $result;
        });

        return response()->json(['success' => true, 'fares' => $fares]);
    }
}
