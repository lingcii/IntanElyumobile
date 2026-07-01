<?php

namespace App\Http\Controllers;

use App\Models\Alert;
use App\Models\Analytics;
use App\Models\Municipality;
use App\Models\SystemStatus;
use App\Models\TouristSpot;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    /**
     * GET /api/{role}/dashboard
     * Returns KPIs, municipality map pins, approved spots, system status, alerts.
     */
    public function index(Request $request): JsonResponse
    {
        $role           = $request->session()->get('user_role');
        $municipalityId = (int) $request->session()->get('user_municipality_id', 0);
        $isMuni         = in_array($role, User::$MUNICIPAL_ROLES) && $municipalityId;

        $cacheKey = "dashboard:data:{$role}:{$municipalityId}";

        $payload = Cache::remember($cacheKey, 10, function () use ($isMuni, $municipalityId) {
            if ($isMuni) {
                // Specific municipality
                $totalMunicipalities = 1;
                $spotCounts = DB::table('tourist_spots')
                    ->where('municipality_id', $municipalityId)
                    ->selectRaw("COUNT(*) as total, COALESCE(SUM(status='approved'), 0) as approved, COALESCE(SUM(status='pending'), 0) as pending")
                    ->first();
                $totalSpots = (int) ($spotCounts->total ?? 0);
                $approvedSpots = (int) ($spotCounts->approved ?? 0);
                $pendingSpots = (int) ($spotCounts->pending ?? 0);

                $monthlyVisits = (int) Analytics::where('municipality_id', $municipalityId)
                    ->where('year', now()->year)
                    ->where('month', now()->month)
                    ->sum('visits');

                if ($monthlyVisits === 0) {
                    $monthlyVisits = (int) Analytics::where('municipality_id', $municipalityId)->where('year', now()->year)->sum('visits');
                }
            } else {
                // Province-wide (LUPTO / PICTO)
                $totalMunicipalities = Municipality::count();
                $spotCounts = DB::table('tourist_spots')
                    ->selectRaw("COUNT(*) as total, COALESCE(SUM(status='approved'), 0) as approved, COALESCE(SUM(status='pending'), 0) as pending")
                    ->first();
                $totalSpots = (int) ($spotCounts->total ?? 0);
                $approvedSpots = (int) ($spotCounts->approved ?? 0);
                $pendingSpots = (int) ($spotCounts->pending ?? 0);

                $monthlyVisits = (int) Analytics::where('year', now()->year)
                    ->where('month', now()->month)
                    ->sum('visits');

                if ($monthlyVisits === 0) {
                    $monthlyVisits = (int) Analytics::where('year', now()->year)->sum('visits');
                }
            }

            // System uptime
            $uptimes = SystemStatus::pluck('uptime')->toArray();
            if (!empty($uptimes)) {
                $avg = array_sum(array_map(fn($u) => (float) str_replace('%', '', $u), $uptimes)) / count($uptimes);
                $uptimeVal = number_format($avg, 2) . '%';
            } else {
                $uptimeVal = '99.95%';
            }

            $kpis = [
                'total_municipalities' => $totalMunicipalities,
                'total_tourist_spots'  => $totalSpots,
                'total_approved_spots' => $approvedSpots,
                'total_pending_spots'  => $pendingSpots,
                'total_visits'         => $monthlyVisits,
                'totalTouristSpots'    => $totalSpots,
                'activeUsers'          => User::where('status', 'active')->count(),
                'monthlyVisits'        => $monthlyVisits,
                'systemUptime'         => $uptimeVal,
            ];

            // Municipalities
            if ($isMuni) {
                $rawMunis = Municipality::where('id', $municipalityId)->get();
            } else {
                $rawMunis = Municipality::orderBy('name')->get();
            }

            // Fetch category counts in one query
            $catQuery = DB::table('tourist_spots')
                ->where('status', 'approved')
                ->select('municipality_id', 'category', DB::raw('count(*) as count'));

            if ($isMuni) {
                $catQuery->where('municipality_id', $municipalityId);
            }

            $catRows = $catQuery->groupBy('municipality_id', 'category')
                ->get()
                ->groupBy('municipality_id');

            $municipalities = $rawMunis->map(function ($m) use ($catRows) {
                $m->categories = $catRows->get($m->id, collect())->values();
                return $m;
            });

            // Approved Tourist Spots list
            $spotsQuery = TouristSpot::where('status', 'approved')
                ->with('municipality:id,name')
                ->select('id', 'name', 'municipality_id', 'category', 'entrance_fee', 'description', 'photo_url', 'classification_status', 'status', 'created_at');

            if ($isMuni) {
                $spotsQuery->where('municipality_id', $municipalityId);
            }

            $approvedSpotsList = $spotsQuery->get();

            $systemStatuses  = SystemStatus::select('id', 'service_name', 'status', 'uptime', 'last_checked')->get();
            $recentAlerts    = Alert::where('is_read', false)->latest()->take(5)->get(['id', 'message', 'type', 'created_at']);

            // Consolidated Analytics: Visitor Trends
            $currentYear = now()->year;
            $currentTrendQuery = Analytics::where('year', $currentYear);
            if ($isMuni) {
                $currentTrendQuery->where('municipality_id', $municipalityId);
            }
            $visitorTrends = $currentTrendQuery->selectRaw('month, SUM(visits) as visits')
                ->groupBy('month')
                ->orderBy('month')
                ->get()
                ->toArray();

            // Consolidated Analytics: Category Distribution
            $catDistQuery = TouristSpot::where('status', 'approved')
                ->selectRaw('category, COUNT(*) as cnt');
            if ($isMuni) {
                $catDistQuery->where('municipality_id', $municipalityId);
            }
            $catDist = $catDistQuery->groupBy('category')
                ->orderByDesc('cnt')
                ->get()
                ->toArray();

            // Consolidated Analytics: Top 5 Municipalities
            $topMunis = [];
            if (!$isMuni) {
                $topMunis = Municipality::leftJoin('tourist_spots as ts', 'ts.municipality_id', '=', 'municipalities.id')
                    ->selectRaw('municipalities.id, municipalities.name, COALESCE(SUM(ts.visits), 0) as total_visits')
                    ->groupBy('municipalities.id', 'municipalities.name')
                    ->orderByDesc('total_visits')
                    ->limit(5)
                    ->get()
                    ->toArray();
            }

            return [
                'kpis'                 => $kpis,
                'municipalities'       => $municipalities->toArray(),
                'touristSpots'         => $approvedSpotsList->toArray(),
                'systemStatuses'       => $systemStatuses->toArray(),
                'alerts'               => $recentAlerts->toArray(),
                'visitorTrends'        => $visitorTrends,
                'categoryDistribution' => $catDist,
                'topMunicipalities'    => $topMunis,
            ];
        });

        $etag = '"' . md5(json_encode($payload['kpis']) . count($payload['municipalities']) . count($payload['touristSpots'])) . '"';

        if ($request->hasHeader('If-None-Match') && $request->header('If-None-Match') === $etag) {
            return response()->json(null, 304)
                ->header('Cache-Control', 'private, max-age=10')
                ->header('ETag', $etag);
        }

        return response()->json($payload)
            ->header('Cache-Control', 'private, max-age=10')
            ->header('ETag', $etag);
    }

    /**
     * GET /api/{role}/dashboard/pending-spots
     * Pending tourist spots for approval (LUPTO / PITCO).
     */
    public function pendingSpots(): JsonResponse
    {
        $spots = TouristSpot::where('status', 'pending')
            ->with('municipality:id,name')
            ->latest()
            ->get();

        return response()->json(['spots' => $spots]);
    }

    /**
     * POST /api/{role}/dashboard/approve-spot
     */
    public function approveSpot(Request $request): JsonResponse
    {
        $request->validate(['id' => 'required|integer']);
        $spot = TouristSpot::findOrFail($request->id);

        DB::transaction(function () use ($spot) {
            $spot->update(['status' => 'approved']);
            Municipality::where('id', $spot->municipality_id)
                ->increment('attraction_count');
        });

        // Clear all relevant caches
        Cache::flush();

        return response()->json(['success' => true, 'message' => 'Tourist spot approved.']);
    }

    /**
     * POST /api/{role}/dashboard/reject-spot
     */
    public function rejectSpot(Request $request): JsonResponse
    {
        $request->validate(['id' => 'required|integer']);
        TouristSpot::findOrFail($request->id)->update(['status' => 'rejected']);

        // Clear all relevant caches
        Cache::flush();

        return response()->json(['success' => true, 'message' => 'Tourist spot rejected.']);
    }

    /**
     * POST /api/{role}/dashboard/batch-approve-spots
     */
    public function batchApproveSpots(Request $request): JsonResponse
    {
        $request->validate(['ids' => 'required|array', 'ids.*' => 'integer']);

        DB::transaction(function () use ($request) {
            foreach ($request->ids as $id) {
                $spot = TouristSpot::where('id', $id)->where('status', 'pending')->first();
                if ($spot) {
                    $spot->update(['status' => 'approved']);
                    Municipality::where('id', $spot->municipality_id)->increment('attraction_count');
                }
            }
        });

        // Clear all relevant caches
        Cache::flush();

        return response()->json(['success' => true, 'message' => 'Selected spots approved.']);
    }
}
