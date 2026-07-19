<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use Illuminate\Http\Request;

class VehicleController extends Controller
{
    public function index()
    {
        return response()->json(Vehicle::where('is_active', true)->get());
    }

    public function show($id)
    {
        $vehicle = Vehicle::findOrFail($id);
        return response()->json($vehicle);
    }
}
