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
                ['name' => 'Aircon Bus', 'category' => 'Public Vehicle'],
                ['name' => 'Car', 'category' => 'Private Vehicle'],
                ['name' => 'Modern Jeepney', 'category' => 'Public Vehicle'],
                ['name' => 'Motorcycle', 'category' => 'Private Vehicle'],
                ['name' => 'Regular Bus', 'category' => 'Public Vehicle'],
                ['name' => 'Taxi', 'category' => 'Public Vehicle'],
                ['name' => 'Traditional Jeepney', 'category' => 'Public Vehicle'],
                ['name' => 'Tricycle', 'category' => 'Public Vehicle'],
                ['name' => 'UV Express', 'category' => 'Public Vehicle'],
                ['name' => 'Van', 'category' => 'Private Vehicle'],
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

