<?php

namespace App\Http\Controllers;

use App\Models\FareGuide;
use App\Models\FareMatrix;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FareController extends Controller
{
    /**
     * GET /api/fares
     * Returns active fare guides and distance fare matrices stored in database.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $guides = FareGuide::with(['matrices' => function ($q) {
                $q->orderBy('distance_km', 'asc');
            }])->where('status', 'active')->get();

            $sanJuanGuide = $guides->first(function ($g) {
                return (stripos($g->title, 'san juan') !== false || stripos($g->region, 'san juan') !== false)
                    && strcasecmp($g->vehicle_type, 'Tricycle') === 0;
            }) ?? $guides->firstWhere('id', 29);

            $byMunicipality = [];
            foreach ($guides as $g) {
                $mKey = strtolower(trim(preg_replace('/[^a-zA-Z0-9]/', '_', $g->region ?: $g->title)));
                $byMunicipality[$mKey] = $g;
            }

            return response()->json([
                'status'          => 'success',
                'success'         => true,
                'guides'          => $guides,
                'san_juan'        => $sanJuanGuide,
                'by_municipality' => $byMunicipality,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Failed to load fare matrices.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }
}
