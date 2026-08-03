<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VehicleController extends Controller
{
    /**
     * GET /api/public/vehicles or /api/vehicles
     * Returns all active vehicles from Railway DB.
     */
    public function index(): JsonResponse
    {
        try {
            $vehicles = \Illuminate\Support\Facades\Cache::remember('public:vehicles', 300, function () {
                return Vehicle::where('is_active', true)->get();
            });

            $vehicleTypes = \Illuminate\Support\Facades\Cache::remember('public:vehicle_types', 300, function () {
                return \Illuminate\Support\Facades\DB::table('vehicle_types')->get();
            });

            return response()->json([
                'success'       => true,
                'vehicles'      => $vehicles,
                'vehicle_types' => $vehicleTypes
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch vehicle data from Railway DB.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    public function show($id): JsonResponse
    {
        try {
            $vehicle = Vehicle::findOrFail($id);
            return response()->json([
                'success' => true,
                'vehicle' => $vehicle
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Vehicle not found.'
            ], 404);
        }
    }
}

