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
                $vehicleTypes = $dbTypes;
            } else {
                throw new \Exception('No records in vehicle_types table');
            }
        } catch (\Throwable $e) {
            $vehicleTypes = [
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
        }

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

