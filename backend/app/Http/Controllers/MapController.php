<?php

namespace App\Http\Controllers;

use App\Models\Municipality;
use App\Models\TouristSpot;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MapController extends Controller
{
    /**
     * GET /api/lupto/map
     * Returns all municipalities with coordinates for LUPTO dashboard map.
     */
    public function luptoMapData(): JsonResponse
    {
        $municipalities = \Illuminate\Support\Facades\Cache::remember('map:lupto:municipalities', 3600, function () {
            return Municipality::select('id', 'name', 'latitude', 'longitude', 'attraction_count')
                ->orderBy('name')
                ->get();
        });

        return response()->json(['municipalities' => $municipalities]);
    }

    /**
     * GET /api/municipal/map
     * Returns municipality info + tourist spots for the user's municipality.
     */
    public function municipalityData(Request $request): JsonResponse
    {
        $municipalityId = (int) $request->session()->get('user_municipality_id', 0);
        $cacheKey = "map:muni:{$municipalityId}";

        $payload = \Illuminate\Support\Facades\Cache::remember($cacheKey, 60, function () use ($municipalityId) {
            $municipality = Municipality::find($municipalityId);
            $spots        = TouristSpot::where('municipality_id', $municipalityId)
                ->with('municipality:id,name')
                ->latest()
                ->get();

            return ['municipality' => $municipality, 'spots' => $spots];
        });

        return response()->json($payload);
    }
}
