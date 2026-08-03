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
            $guides = FareGuide::with(['matrices' => function($q) {
                $q->orderBy('distance_km', 'asc');
            }])->where('status', 'active')->get();

            return response()->json([
                'status' => 'success',
                'guides' => $guides
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to load fare matrices.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
