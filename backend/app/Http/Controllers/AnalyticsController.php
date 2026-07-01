<?php

namespace App\Http\Controllers;

use App\Models\Analytics;
use App\Models\Municipality;
use App\Models\TouristSpot;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class AnalyticsController extends Controller
{
    /**
     * GET /api/{role}/analytics/summary
     */
    public function summary(): JsonResponse
    {
        $data = Cache::remember('analytics:summary', 120, function () {
            $totalMunis    = Municipality::count();
            $row           = DB::table('tourist_spots')
                ->selectRaw("COUNT(*) as total, SUM(status='approved') as approved")
                ->first();
            $totalVisits   = TouristSpot::sum('visits');
            $totalUsers    = DB::table('users')->where('role', 'tourist')->where('status', 'active')->count();

            $mvMuni = Municipality::leftJoin('tourist_spots as ts', 'ts.municipality_id', '=', 'municipalities.id')
                ->selectRaw('municipalities.name, COALESCE(SUM(ts.visits),0) as v')
                ->groupBy('municipalities.id', 'municipalities.name')
                ->orderByDesc('v')
                ->first();

            $mvSpot = TouristSpot::orderByDesc('visits')->first(['name', 'visits']);

            $totalAnalyticsVisits = Analytics::sum('visits');

            return [
                'total_municipalities'   => (int) $totalMunis,
                'total_spots'            => (int) $row->total,
                'approved_spots'         => (int) $row->approved,
                'total_visits'           => (int) $totalVisits,
                'total_analytics_visits' => (int) $totalAnalyticsVisits,
                'total_users'            => (int) $totalUsers,
                'most_visited_muni'      => $mvMuni->name ?? '—',
                'most_visited_muni_v'    => (int) ($mvMuni->v ?? 0),
                'most_visited_spot'      => $mvSpot->name ?? '—',
                'most_visited_spot_v'    => (int) ($mvSpot->visits ?? 0),
            ];
        });

        return response()->json([
            'success' => true,
            'summary' => $data,
        ]);
    }

    /**
     * GET /api/{role}/analytics/top-municipalities
     */
    public function topMunicipalities(Request $request): JsonResponse
    {
        $sortBy       = $request->get('sort', 'total_visits');
        $filterCat    = $request->filled('category') ? $request->get('category') : '';
        $filterStatus = $request->filled('spot_status') ? $request->get('spot_status') : '';
        $limit        = min((int) $request->get('limit', 10), 20);
        $cacheKey     = "analytics:top-municipalities:{$sortBy}:{$filterCat}:{$filterStatus}:{$limit}";

        $rows = Cache::remember($cacheKey, 120, function () use ($sortBy, $filterCat, $filterStatus, $limit) {
            $sortMap = [
                'total_visits'   => 'total_visits',
                'total_spots'    => 'total_spots',
                'approved_spots' => 'approved_spots',
                'avg_rating'     => 'avg_rating',
            ];
            $sortCol = $sortMap[$sortBy] ?? 'total_visits';

            $subQuery = DB::table('tourist_spots')
                ->selectRaw("municipality_id,
                    COUNT(*) as total_spots,
                    SUM(status='approved') as approved_spots,
                    COALESCE(SUM(visits),0) as total_visits,
                    COALESCE(AVG(rating),0) as avg_rating");

            if ($filterCat !== '')    $subQuery->where('category', $filterCat);
            if ($filterStatus !== '') $subQuery->where('classification_status', $filterStatus);

            $subQuery->groupBy('municipality_id');

            $data = Municipality::leftJoinSub($subQuery, 'ts', 'ts.municipality_id', '=', 'municipalities.id')
                ->leftJoin(DB::raw('(SELECT municipality_id, SUM(visits) as analytics_visits FROM analytics GROUP BY municipality_id) an'), 'an.municipality_id', '=', 'municipalities.id')
                ->selectRaw("municipalities.id, municipalities.name, municipalities.attraction_count,
                    COALESCE(ts.total_spots,0) as total_spots,
                    COALESCE(ts.approved_spots,0) as approved_spots,
                    COALESCE(ts.total_visits,0) as total_visits,
                    COALESCE(ts.avg_rating,0) as avg_rating,
                    COALESCE(an.analytics_visits,0) as analytics_visits")
                ->orderByDesc($sortCol)
                ->limit($limit)
                ->get();

            return $data->values()->map(function ($r, $i) {
                $r->rank             = $i + 1;
                $r->total_spots      = (int) $r->total_spots;
                $r->approved_spots   = (int) $r->approved_spots;
                $r->total_visits     = (int) $r->total_visits;
                $r->avg_rating       = round((float) $r->avg_rating, 1);
                $r->analytics_visits = (int) $r->analytics_visits;
                return $r;
            });
        });

        return response()->json(['success' => true, 'municipalities' => $rows]);
    }

    /**
     * GET /api/{role}/analytics/top-spots
     */
    public function topSpots(Request $request): JsonResponse
    {
        $sortBy       = $request->get('sort', 'visits');
        $filterMuni   = (int) $request->get('municipality_id', 0);
        $filterCat    = $request->get('category', '');
        $filterStatus = $request->get('spot_status', '');
        $limit        = min((int) $request->get('limit', 10), 20);
        $cacheKey     = "analytics:top-spots:{$sortBy}:{$filterMuni}:{$filterCat}:{$filterStatus}:{$limit}";

        $rows = Cache::remember($cacheKey, 120, function () use ($sortBy, $filterMuni, $filterCat, $filterStatus, $limit) {
            $sortMap = ['visits' => 'visits', 'rating' => 'rating', 'newest' => 'created_at'];
            $sortCol = $sortMap[$sortBy] ?? 'visits';

            $query = TouristSpot::with('municipality:id,name');
            if ($filterMuni)  $query->where('municipality_id', $filterMuni);
            if ($filterCat)   $query->where('category', $filterCat);
            if ($filterStatus) $query->where('classification_status', $filterStatus);

            return $query->orderByDesc($sortCol)->limit($limit)->get()
                ->values()->map(function ($r, $i) {
                    $r->rank = $i + 1;
                    return $r;
                });
        });

        return response()->json(['success' => true, 'spots' => $rows]);
    }

    /**
     * GET /api/{role}/analytics/chart-data
     */
    public function chartData(Request $request): JsonResponse
    {
        $year  = (int) $request->get('year', now()->year);
        $month = (int) $request->get('month', 0);
        $cacheKey = "analytics:chart-data:{$year}:{$month}";

        $data = Cache::remember($cacheKey, 3600, function () use ($year, $month) {
            $muniStats = Municipality::leftJoin('tourist_spots as ts', 'ts.municipality_id', '=', 'municipalities.id')
                ->selectRaw('municipalities.name, COUNT(ts.id) as spot_count, COALESCE(SUM(ts.visits),0) as total_visits')
                ->groupBy('municipalities.id', 'municipalities.name')
                ->get();

            $spotsByMuni  = $muniStats->sortByDesc('spot_count')->take(10)->values();
            $visitsByMuni = $muniStats->sortByDesc('total_visits')->take(10)->values();

            $catDist = TouristSpot::selectRaw('category, COUNT(*) as cnt')
                ->groupBy('category')->orderByDesc('cnt')->get();

            $monthlyQuery = Analytics::where('year', $year);
            if ($month) $monthlyQuery->where('month', $month);
            $monthly = $monthlyQuery->selectRaw('month, SUM(visits) as total_visits, SUM(transport_car) as car, SUM(transport_bus) as bus, SUM(transport_van) as van, SUM(transport_other) as other')
                ->groupBy('month')->orderBy('month')->get();

            $classDist = TouristSpot::whereNotNull('classification_status')
                ->selectRaw('classification_status as cls, COUNT(*) as cnt')
                ->groupBy('classification_status')->get();

            $transport = Analytics::where('year', $year)
                ->selectRaw('SUM(transport_car) as car, SUM(transport_bus) as bus, SUM(transport_van) as van, SUM(transport_other) as other, SUM(visits) as total')
                ->first();

            return [
                'spots_by_muni'  => $spotsByMuni,
                'visits_by_muni' => $visitsByMuni,
                'cat_dist'       => $catDist,
                'monthly_visits' => $monthly,
                'class_dist'     => $classDist,
                'transport'      => $transport,
            ];
        });

        return response()->json([
            'success'        => true,
            'spots_by_muni'  => $data['spots_by_muni'],
            'visits_by_muni' => $data['visits_by_muni'],
            'cat_dist'       => $data['cat_dist'],
            'monthly_visits' => $data['monthly_visits'],
            'class_dist'     => $data['class_dist'],
            'transport'      => $data['transport'],
        ])->header('Cache-Control', 'private, max-age=300');
    }

    /**
     * GET /api/{role}/analytics/monthly-trend
     */
    public function monthlyTrend(Request $request): JsonResponse
    {
        $year = (int) $request->get('year', now()->year);
        $cacheKey = "analytics:monthly-trend:{$year}";

        $data = Cache::remember($cacheKey, 3600, function () use ($year) {
            $current  = Analytics::where('year', $year)->selectRaw('month, SUM(visits) as visits')->groupBy('month')->orderBy('month')->get();
            $previous = Analytics::where('year', $year - 1)->selectRaw('month, SUM(visits) as visits')->groupBy('month')->orderBy('month')->get();
            return ['current' => $current, 'previous' => $previous];
        });

        return response()->json(['success' => true, 'current' => $data['current'], 'previous' => $data['previous'], 'year' => $year])->header('Cache-Control', 'private, max-age=300');
    }

    /**
     * GET /api/{role}/analytics/filter-options
     */
    public function filterOptions(): JsonResponse
    {
        $data = Cache::remember('analytics:filter-options', 300, function () {
            $munis      = Municipality::orderBy('name')->get(['id', 'name']);
            $years      = Analytics::distinct()->orderByDesc('year')->pluck('year');
            $categories = ['Beach', 'Mountain', 'Historical', 'Waterfalls', 'Adventure', 'Farm', 'Religious', 'Other'];
            return ['munis' => $munis, 'years' => $years, 'categories' => $categories];
        });

        return response()->json(['success' => true, 'municipalities' => $data['munis'], 'categories' => $data['categories'], 'years' => $data['years']]);
    }

    /**
     * GET /api/{role}/analytics/full
     * Combined analytics (used by LUPTO/MUNICIPAL/PITCO dashboard analytics tab).
     */
    public function full(Request $request): JsonResponse
    {
        $year = (int) $request->get('year', now()->year);
        $cacheKey = "analytics:full:{$year}";

        $data = Cache::remember($cacheKey, 3600, function () use ($year) {
            $monthlyTrends = Analytics::selectRaw('year, month, SUM(visits) as total_visits')
                ->groupBy('year', 'month')->orderBy('year')->orderBy('month')->get();

            $topSpots = TouristSpot::where('status', 'approved')
                ->with('municipality:id,name')
                ->orderByDesc('visits')->limit(5)->get();

            $transportData = Analytics::where('year', $year)
                ->selectRaw('SUM(transport_car) as car, SUM(transport_bus) as bus, SUM(transport_van) as van, SUM(transport_other) as other')
                ->first();

            $rankings = Municipality::join('analytics as a', 'a.municipality_id', '=', 'municipalities.id')
                ->where('a.year', $year)
                ->selectRaw('municipalities.name, SUM(a.visits) as total_visits, AVG(a.avg_spend) as avg_spend')
                ->groupBy('municipalities.id', 'municipalities.name')
                ->orderByDesc('total_visits')->get();

            $costBreakdown = Municipality::join('analytics as a', 'a.municipality_id', '=', 'municipalities.id')
                ->where('a.year', $year)
                ->selectRaw('municipalities.name, AVG(a.avg_spend) as avg_spend, SUM(a.visits) as total_visits, (AVG(a.avg_spend)*SUM(a.visits)) as estimated_revenue')
                ->groupBy('municipalities.id', 'municipalities.name')
                ->orderByDesc('estimated_revenue')->limit(10)->get();

            $municipalityVisits = Municipality::join('analytics as a', 'a.municipality_id', '=', 'municipalities.id')
                ->where('a.year', $year)->where('a.month', now()->month)
                ->selectRaw('municipalities.name, SUM(a.visits) as total_visits')
                ->groupBy('municipalities.id', 'municipalities.name')
                ->orderByDesc('total_visits')->get();

            return [
                'monthlyTrends'     => $monthlyTrends,
                'topSpots'          => $topSpots,
                'transportData'     => $transportData,
                'rankings'          => $rankings,
                'costBreakdown'     => $costBreakdown,
                'municipalityVisits' => $municipalityVisits,
            ];
        });

        return response()->json($data)->header('Cache-Control', 'private, max-age=300');
    }
}
