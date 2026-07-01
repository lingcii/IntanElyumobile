<?php

namespace App\Http\Controllers;

use App\Models\Municipality;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MunicipalityController extends Controller
{
    /** GET /api/{role}/municipalities */
    public function index(): JsonResponse
    {
        return response()->json(['municipalities' => Municipality::orderBy('name')->get()]);
    }

    /** GET /api/{role}/municipalities/{id} */
    public function show(int $id): JsonResponse
    {
        return response()->json(['municipality' => Municipality::findOrFail($id)]);
    }
}
