<?php

namespace App\Http\Controllers;

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
            $vehicleTypes = [
                ['name' => 'MPUJ', 'category' => 'Public Vehicle'],
                ['name' => 'PUB_Aircon', 'category' => 'Public Vehicle'],
                ['name' => 'Tricycle', 'category' => 'Public Vehicle'],
                ['name' => 'Car', 'category' => 'Private Vehicle'],
                ['name' => 'TAXI', 'category' => 'Private Vehicle'],
                ['name' => 'Van', 'category' => 'Private Vehicle'],
                ['name' => 'Motorcycle', 'category' => 'Private Vehicle'],
            ];

            return response()->json([
                'success'       => true,
                'vehicles'      => [],
                'vehicle_types' => $vehicleTypes
            ]);
    }

    public function show($id): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => 'Vehicles database has been deprecated in favor of active fare guides.'
        ], 404);
    }
}

