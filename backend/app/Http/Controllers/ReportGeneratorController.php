<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;

class ReportGeneratorController extends Controller
{
    /**
     * GET /api/{role}/reports
     *
     * Placeholder — returns static report list.
     * Replace with real report generation logic as needed.
     */
    public function index(): JsonResponse
    {
        $reports = [
            ['report_name' => 'Tourism Summary Report',     'type' => 'monthly',   'generated_at' => now()->startOfMonth()->toDateString(),   'generated_by' => 'System'],
            ['report_name' => 'Visitor Analytics Report',   'type' => 'quarterly', 'generated_at' => now()->firstOfQuarter()->toDateString(),  'generated_by' => 'System'],
            ['report_name' => 'Fare Data Compliance Report','type' => 'weekly',    'generated_at' => now()->startOfWeek()->toDateString(),     'generated_by' => 'System'],
        ];

        return response()->json(['reports' => $reports]);
    }
}
