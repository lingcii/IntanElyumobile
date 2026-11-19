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
            $query = FareGuide::with(['matrices' => function ($q) {
                $q->orderBy('distance_km', 'asc');
            }])->where('status', 'active');
            if (\Illuminate\Support\Facades\Schema::hasColumn('fare_guides', 'is_archived')) {
                $query->where(function ($q) {
                    $q->where('is_archived', 0)->orWhereNull('is_archived');
                });
            }
            $guides = $query->get();

            $sanJuanGuide = $guides->first(function ($g) {
                return (stripos($g->title, 'san juan') !== false || stripos($g->region, 'san juan') !== false)
                    && strcasecmp($g->vehicle_type, 'Tricycle') === 0;
            });

            $byMunicipality = [];
            foreach ($guides as $g) {
                $mKey = strtolower(trim(preg_replace('/[^a-zA-Z0-9]/', '_', $g->region ?: $g->title)));
                $vKey = strtolower(trim(preg_replace('/[^a-zA-Z0-9]/', '_', $g->vehicle_type ?: 'default')));
                if (!isset($byMunicipality[$mKey]) || !is_array($byMunicipality[$mKey])) {
                    $byMunicipality[$mKey] = [];
                }
                $byMunicipality[$mKey][$vKey] = $g;
                if (!isset($byMunicipality[$mKey]['default'])) {
                    $byMunicipality[$mKey]['default'] = $g;
                }
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
