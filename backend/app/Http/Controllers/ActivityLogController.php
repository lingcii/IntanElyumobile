<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Http\JsonResponse;

class ActivityLogController extends Controller
{
    /**
     * GET /api/{role}/activity-logs
     */
    public function index(): JsonResponse
    {
        $logs = ActivityLog::with('user:id,name')
            ->latest()
            ->take(50)
            ->get();

        return response()->json(['logs' => $logs]);
    }
}
