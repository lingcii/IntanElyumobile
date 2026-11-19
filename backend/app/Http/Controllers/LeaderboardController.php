<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class LeaderboardController extends Controller
{
    /**
     * Common Ranked CTE ensuring clean deduplication, municipality fallback,
     * comprehensive points & activity aggregation, and correct ordering.
     */
    private function rankedCte(string $orderSql = 'total_points DESC, total_xp DESC, completed_activities DESC, user_id ASC', string $filterSql = ''): string
    {
        return "
            WITH tourist_numbers AS (
                SELECT id, ROW_NUMBER() OVER (ORDER BY id ASC) AS tourist_number
                FROM users
                WHERE role = 'tourist'
            ),
            visited_counts AS (
                SELECT i.user_id, COUNT(*) AS visited_cnt
                FROM itinerary_items ii
                INNER JOIN itineraries i ON ii.itinerary_id = i.id
                WHERE ii.tourist_spot_id IS NOT NULL
                  AND (ii.is_visited = 1 OR ii.visited_at IS NOT NULL OR i.status = 'completed')
                GROUP BY i.user_id
            ),
            user_summary AS (
                SELECT
                    u.id                                              AS user_id,
                    u.name                                            AS name,
                    COALESCE(NULLIF(u.name, ''), CONCAT('Explorer #', u.id)) AS full_name,
                    COALESCE(tn.tourist_number, 1)                    AS tourist_number,
                    u.email                                           AS email,
                    u.avatar                                          AS avatar,
                    u.home_location                                   AS home_location,
                    COALESCE(
                        NULLIF(m.name, ''),
                        NULLIF(u.home_location, ''),
                        'La Union'
                    )                                                 AS municipality,
                    u.bio                                             AS bio,
                    COALESCE(u.is_leaderboard_private, 0)             AS is_leaderboard_private,
                    u.last_activity                                   AS last_activity_date,
                    COALESCE(u.xp, 0)                                 AS total_xp,
                    COALESCE(u.xp, 0)                                 AS xp,
                    COALESCE(u.points, 0)                             AS points,
                    COALESCE(u.points, 0)                             AS total_points,
                    COALESCE(u.points, 0)                             AS claimable_points,
                    COALESCE(NULLIF(vc.visited_cnt, 0), u.completed_activities, 0) AS completed_activities,
                    COALESCE(NULLIF(vc.visited_cnt, 0), u.completed_activities, 0) AS places_visited,
                    COALESCE(u.created_at, NOW())                     AS points_since,
                    COALESCE(u.created_at, NOW())                     AS created_at
                FROM users u
                LEFT JOIN tourist_numbers tn ON tn.id = u.id
                LEFT JOIN visited_counts vc ON vc.user_id = u.id
                LEFT JOIN municipalities m ON u.municipality_id = m.id
                WHERE u.role = 'tourist' AND (u.status = 'active' OR u.status IS NULL)
                  {$filterSql}
            ),
            ranked AS (
                SELECT
                    us.*,
                    ROW_NUMBER() OVER (
                        ORDER BY {$orderSql}
                    ) AS `rank`
                FROM user_summary us
            )
        ";
    }

    /**
     * GET /api/{role}/leaderboard
     * GET /api/tourist/leaderboard
     * GET /api/public/leaderboard
     */
    public function index(Request $request): JsonResponse
    {
        $search = trim($request->get('search', ''));
        $rawSort = strtolower(trim($request->get('sort', 'points')));
        $rawPeriod = strtolower(trim($request->get('period', 'all')));
        $period = in_array($rawPeriod, ['week', 'month'], true) ? $rawPeriod : 'all';

        $myRank = null;
        $me = null;
        $user = $request->user();
        if (!$user) {
            $bearerToken = $request->bearerToken();
            if ($bearerToken) {
                $user = \App\Models\User::where('api_token', $bearerToken)->first();
            }
        }

        // Leaderboard limit: strictly maximum 10 tourists for tourists and public
        $isStaff = $user && in_array(strtolower($user->role ?? ''), ['admin', 'lupto', 'picto', 'super_admin']);
        $maxLimit = $isStaff ? 100 : 10;
        $limit = min(max((int) $request->get('limit', 10), 1), $maxLimit);
        $offset = max((int) $request->get('offset', 0), 0);

        $isVisitedSort = in_array($rawSort, [
            'most_activities', 'activities', 'activities_desc', 'completed_activities',
            'visited', 'most_visited', 'places_visited', 'visited_desc', 'least_activities', 'activities_asc'
        ], true);

        $viewerId = $user ? (int) $user->id : 0;

        if ($period !== 'all') {
            $periodResult = $this->queryForPeriod($period, $search, $isVisitedSort, $rawSort, $limit, $offset, $user, $isStaff, $viewerId);
            $rows = $periodResult['rows'];
            $totalTourists = $periodResult['total'];
            $myRank = $periodResult['my_rank'];
            $me = $periodResult['me'];
        } else {
            // Normalize sorting parameter names (Prioritize Points, Visited & XP)
            $orderSql = match ($rawSort) {
                'xp_asc' => 'total_xp ASC, total_points ASC, completed_activities ASC, user_id ASC',
                'xp', 'top_xp', 'xp_desc' => 'total_xp DESC, total_points DESC, completed_activities DESC, user_id ASC',
                'points', 'top_points', 'points_desc', 'highest_points' => 'total_points DESC, total_xp DESC, completed_activities DESC, user_id ASC',
                'points_asc', 'lowest_points' => 'total_points ASC, total_xp ASC, completed_activities ASC, user_id ASC',
                'most_activities', 'activities', 'activities_desc', 'completed_activities', 'visited', 'most_visited', 'places_visited', 'visited_desc' => 'completed_activities DESC, places_visited DESC, total_points DESC, total_xp DESC, user_id ASC',
                'least_activities', 'activities_asc' => 'completed_activities ASC, total_points ASC, total_xp ASC, user_id ASC',
                'name_asc', 'name' => 'full_name ASC, user_id ASC',
                'name_desc' => 'full_name DESC, user_id ASC',
                'recent', 'newest', 'latest' => 'points_since DESC, user_id ASC',
                default => 'total_points DESC, total_xp DESC, completed_activities DESC, user_id ASC',
            };

            // Filter out users with zero score for the requested metric (using live visited_counts for Visited sort)
            $isXpSort = in_array($rawSort, ['xp', 'top_xp', 'xp_desc', 'xp_asc'], true);
            $isMetaSort = in_array($rawSort, ['name', 'name_asc', 'name_desc', 'recent', 'newest', 'latest'], true);
            if ($isVisitedSort) {
                $filterSql = 'AND COALESCE(NULLIF(vc.visited_cnt, 0), u.completed_activities, 0) > 0';
            } elseif ($isXpSort) {
                $filterSql = 'AND COALESCE(u.xp, 0) > 0';
            } elseif ($isMetaSort) {
                $filterSql = 'AND (COALESCE(u.points, 0) > 0 OR COALESCE(u.xp, 0) > 0 OR COALESCE(NULLIF(vc.visited_cnt, 0), u.completed_activities, 0) > 0)';
            } else {
                $filterSql = 'AND COALESCE(u.points, 0) > 0';
            }

            if ($user) {
                if (method_exists($user, 'syncPlacesVisitedCount')) {
                    $user->syncPlacesVisitedCount();
                }
                if (method_exists($user, 'syncPointsBalance')) {
                    $user->syncPointsBalance();
                }
            }

            // Execute dynamic ranked query once (single CTE pass returns rows + total_count)
            $cachedData = $this->queryFromLiveCte($search, $orderSql, $limit, $offset, $filterSql);
            $rows = $this->castRows($cachedData['rows'], $isStaff, $viewerId);
            $totalTourists = (int) ($cachedData['total'] ?? count($rows));

            if ($user) {
                $uPts = (int) ($user->points ?? $user->xp ?? 0);
                $uActs = (int) ($user->completed_activities ?? 0);
                if ($uPts > 0 || $uActs > 0) {
                    // Reuse current user's row if already in this page (avoids a second CTE query)
                    foreach ($rows as $row) {
                        if ((int) ($row['user_id'] ?? $row['id'] ?? 0) === (int) $user->id) {
                            $myRank = (int) ($row['rank'] ?? 0) ?: null;
                            $me = $row;
                            break;
                        }
                    }
                    if ($myRank === null) {
                        try {
                            $myRankRow = DB::selectOne(
                                $this->rankedCte($orderSql, $filterSql) . "SELECT * FROM ranked WHERE user_id = ?",
                                [$user->id]
                            );
                            if ($myRankRow) {
                                $myRank = (int) $myRankRow->rank;
                                $meRows = $this->castRows([(array) $myRankRow], $isStaff, $viewerId);
                                $me = $meRows[0] ?? null;
                            }
                        } catch (\Throwable $e) {
                        }
                    }
                }
            }
        }
        $highestXp = count($rows) > 0 ? (int) max(array_column($rows, 'total_xp')) : 0;
        $highestPoints = count($rows) > 0 ? (int) max(array_column($rows, 'total_points')) : 0;
        $totalActivities = (int) array_sum(array_column($rows, 'completed_activities'));

        return response()->json([
            'success' => true,
            'myRank' => $myRank,
            'my_rank' => $myRank,
            'me' => $me,
            'users' => $rows,
            'leaders' => $rows,
            'leaderboard' => $rows,
            'data' => $rows,
            'tourists' => $rows,
            'total' => $totalTourists,
            'total_tourists' => $totalTourists,
            'totalTourists' => $totalTourists,
            'highest_xp' => $highestXp,
            'highestXp' => $highestXp,
            'highest_points' => $highestPoints,
            'highestPoints' => $highestPoints,
            'total_activities' => $totalActivities,
            'totalActivities' => $totalActivities,
            'stats' => [
                'total_tourists' => $totalTourists,
                'totalTourists' => $totalTourists,
                'highest_xp' => $highestXp,
                'highestXp' => $highestXp,
                'highest_points' => $highestPoints,
                'highestPoints' => $highestPoints,
                'total_activities' => $totalActivities,
                'totalActivities' => $totalActivities,
            ],
            'offset' => $offset,
            'limit' => $limit,
            'updated_at' => now()->toIso8601String(),
        ])->header('Cache-Control', 'no-store, no-cache, must-revalidate');
    }

    /**
     * Aggregate points (from activity_logs) and visited stops (from itinerary_items) within week/month.
     */
    private function queryForPeriod(string $period, string $search, bool $isVisitedSort, string $rawSort, int $limit, int $offset, $currentUser, bool $isStaff = false, int $viewerId = 0): array
    {
        $nowPh = now('Asia/Manila');
        $cutoffPh = $period === 'week'
            ? $nowPh->copy()->startOfWeek(\Carbon\Carbon::SUNDAY)
            : $nowPh->copy()->startOfMonth();
        $cutoff = $cutoffPh->copy()->setTimezone(config('app.timezone', 'UTC'));
        $pointsByUser = [];
        $visitedByUser = [];
        $latestAtByUser = [];

        try {
            $hasActivityLogs = Cache::remember('schema:has_activity_logs_v1', 3600, fn() => \Illuminate\Support\Facades\Schema::hasTable('activity_logs'));
            if ($hasActivityLogs) {
                $logs = DB::table('activity_logs')
                    ->where('action', 'Points Awarded')
                    ->where('created_at', '>=', $cutoff)
                    ->whereNotNull('user_id')
                    ->get(['user_id', 'details', 'created_at']);
                foreach ($logs as $log) {
                    $uid = (int) $log->user_id;
                    if ($uid <= 0) continue;
                    if (preg_match('/Earned\s*\+?(\d+)\s*Points/i', (string) ($log->details ?? ''), $m)) {
                        $pointsByUser[$uid] = ($pointsByUser[$uid] ?? 0) + (int) $m[1];
                    }
                    if (!isset($latestAtByUser[$uid]) || $log->created_at > $latestAtByUser[$uid]) {
                        $latestAtByUser[$uid] = $log->created_at;
                    }
                }
            }
        } catch (\Throwable $e) {}

        try {
            $visitRows = DB::table('itinerary_items as ii')
                ->join('itineraries as i', 'ii.itinerary_id', '=', 'i.id')
                ->whereNotNull('i.user_id')
                ->whereNotNull('ii.tourist_spot_id')
                ->where(function ($q) {
                    $q->where('ii.is_visited', true)
                      ->orWhereNotNull('ii.visited_at')
                      ->orWhere('i.status', 'completed');
                })
                ->whereRaw('COALESCE(ii.visited_at, ii.updated_at, ii.created_at) >= ?', [$cutoff])
                ->selectRaw('i.user_id as user_id, COUNT(*) as cnt, MAX(COALESCE(ii.visited_at, ii.updated_at, ii.created_at)) as latest_at')
                ->groupBy('i.user_id')
                ->get();
            foreach ($visitRows as $vr) {
                $uid = (int) $vr->user_id;
                $visitedByUser[$uid] = (int) $vr->cnt;
                if (!isset($latestAtByUser[$uid]) || $vr->latest_at > $latestAtByUser[$uid]) {
                    $latestAtByUser[$uid] = $vr->latest_at;
                }
            }
        } catch (\Throwable $e) {}

        $activeUserIds = array_values(array_unique(array_merge(array_keys($pointsByUser), array_keys($visitedByUser))));
        if (empty($activeUserIds)) {
            return ['rows' => [], 'total' => 0, 'my_rank' => null, 'me' => null];
        }

        $users = DB::table('users as u')
            ->leftJoin('municipalities as m', 'u.municipality_id', '=', 'm.id')
            ->where('u.role', 'tourist')
            ->where(function ($q) {
                $q->where('u.status', 'active')->orWhereNull('u.status');
            })
            ->whereIn('u.id', $activeUserIds)
            ->select([
                'u.id as user_id',
                'u.name',
                'u.email',
                'u.avatar',
                'u.home_location',
                'u.bio',
                'u.is_leaderboard_private',
                'u.created_at',
                DB::raw("COALESCE(NULLIF(m.name, ''), NULLIF(u.home_location, ''), 'La Union') as municipality"),
            ])
            ->get();

        $touristNumMap = Cache::remember('leaderboard:tourist_num_map_v1', 60, function () {
            $ids = DB::table('users')->where('role', 'tourist')->orderBy('id')->pluck('id')->all();
            return array_flip($ids);
        });

        $candidates = [];
        foreach ($users as $u) {
            $uid = (int) $u->user_id;
            $pts = (int) ($pointsByUser[$uid] ?? 0);
            $acts = (int) ($visitedByUser[$uid] ?? 0);
            if ($isVisitedSort && $acts <= 0) continue;
            if (!$isVisitedSort && $pts <= 0) continue;

            if ($search !== '') {
                $hay = strtolower(($u->name ?? '') . ' ' . $uid . ' ' . ($u->municipality ?? ''));
                if (!str_contains($hay, strtolower($search))) continue;
            }

            $candidates[] = [
                'user_id' => $uid,
                'tourist_number' => isset($touristNumMap[$uid]) ? ($touristNumMap[$uid] + 1) : $uid,
                'name' => $u->name,
                'full_name' => $u->name ?: "Explorer #{$uid}",
                'email' => $u->email,
                'avatar' => $u->avatar,
                'home_location' => $u->home_location,
                'municipality' => $u->municipality,
                'bio' => $u->bio,
                'is_leaderboard_private' => $u->is_leaderboard_private,
                'last_activity_date' => $latestAtByUser[$uid] ?? $u->created_at,
                'total_xp' => $pts,
                'xp' => $pts,
                'points' => $pts,
                'total_points' => $pts,
                'claimable_points' => $pts,
                'completed_activities' => $acts,
                'places_visited' => $acts,
                'points_since' => $latestAtByUser[$uid] ?? $u->created_at,
                'created_at' => $u->created_at,
            ];
        }

        usort($candidates, function ($a, $b) use ($isVisitedSort, $rawSort) {
            if ($isVisitedSort) {
                if ($b['completed_activities'] !== $a['completed_activities']) {
                    return $b['completed_activities'] <=> $a['completed_activities'];
                }
                if ($b['total_points'] !== $a['total_points']) {
                    return $b['total_points'] <=> $a['total_points'];
                }
                return $a['user_id'] <=> $b['user_id'];
            }
            if (in_array($rawSort, ['points_asc', 'lowest_points'], true)) {
                return ($a['total_points'] <=> $b['total_points']) ?: ($a['user_id'] <=> $b['user_id']);
            }
            if ($b['total_points'] !== $a['total_points']) {
                return $b['total_points'] <=> $a['total_points'];
            }
            if ($b['completed_activities'] !== $a['completed_activities']) {
                return $b['completed_activities'] <=> $a['completed_activities'];
            }
            return $a['user_id'] <=> $b['user_id'];
        });

        $myRank = null;
        $me = null;
        $ranked = [];
        foreach ($candidates as $idx => $row) {
            $row['rank'] = $idx + 1;
            $ranked[] = $row;
            if ($currentUser && (int) $currentUser->id === (int) $row['user_id']) {
                $myRank = $row['rank'];
                $castMe = $this->castRows([$row], $isStaff, $viewerId);
                $me = $castMe[0] ?? null;
            }
        }

        $paged = array_slice($ranked, $offset, $limit);
        return [
            'rows' => $this->castRows($paged, $isStaff, $viewerId),
            'total' => count($ranked),
            'my_rank' => $myRank,
            'me' => $me,
        ];
    }

    /**
     * Fallback: Live CTE query using unified aggregation.
     */
    private function queryFromLiveCte(string $search, string $orderSql, int $limit, int $offset, string $filterSql = ''): array
    {
        $whereClause = '';
        $params = [];

        if ($search !== '') {
            $whereClause = "WHERE full_name LIKE ? OR CAST(user_id AS CHAR) LIKE ? OR municipality LIKE ?";
            $params = ["%{$search}%", "%{$search}%", "%{$search}%"];
        }

        $rows = DB::select(
            $this->rankedCte($orderSql, $filterSql) . "SELECT r.*, COUNT(*) OVER() AS total_count FROM ranked r {$whereClause} ORDER BY r.`rank` ASC LIMIT {$limit} OFFSET {$offset}",
            $params
        );

        $rowsArray = json_decode(json_encode($rows), true);
        if (!empty($rowsArray)) {
            $total = (int) ($rowsArray[0]['total_count'] ?? count($rowsArray));
            foreach ($rowsArray as &$r) {
                unset($r['total_count']);
            }
            unset($r);
        } elseif ($offset > 0) {
            $totalResult = DB::selectOne(
                $this->rankedCte($orderSql, $filterSql) . "SELECT COUNT(*) as cnt FROM ranked {$whereClause}",
                $params
            );
            $total = $totalResult ? (int) $totalResult->cnt : 0;
        } else {
            $total = 0;
        }

        return [
            'total' => $total,
            'rows' => $rowsArray,
        ];
    }

    /**
     * Cast & format each user row with all possible field variations.
     */
    private function castRows(array $rows, bool $isStaff = false, int $viewerId = 0): array
    {
        return array_map(function ($r, $index) use ($isStaff, $viewerId) {
            $r = (object) $r;
            $touristNumber = isset($r->tourist_number) && (int) $r->tourist_number > 0 ? (int) $r->tourist_number : (int) $r->user_id;
            $canViewPii = $isStaff || ($viewerId > 0 && $viewerId === (int) $r->user_id);
            $anonName = "Explorer #{$touristNumber}";
            $displayName = ($canViewPii && !empty($r->name)) ? $r->name : $anonName;
            $realName = $displayName;

            $rankVal = isset($r->rank) && (int) $r->rank > 0 ? (int) $r->rank : ($index + 1);
            $xpVal = (int) ($r->total_xp ?? $r->xp ?? 0);
            $pointsVal = (int) ($r->points ?? $r->total_points ?? 0);
            $activitiesVal = (int) ($r->completed_activities ?? 0);
            $muniVal = $r->municipality ?: ($r->home_location ?: 'La Union');

            return [
                'id' => (int) $r->user_id,
                'user_id' => (int) $r->user_id,
                'tourist_number' => $touristNumber,
                'tourist_id' => $touristNumber,
                'explorer_number' => $touristNumber,
                'explorer_name' => $anonName,
                'rank' => $rankVal,
                'rank_number' => $rankVal,
                'rank_no' => $rankVal,
                'position' => $rankVal,
                'ranking' => $rankVal,
                'index' => $rankVal,
                'name' => $displayName,
                'full_name' => $displayName,
                'real_name' => $realName,
                'email' => $canViewPii ? ($r->email ?? null) : null,
                'avatar' => $r->avatar ?? null,
                'home_location' => $muniVal,
                'municipality' => $muniVal,
                'municipality_name' => $muniVal,
                'location' => $muniVal,
                'bio' => $r->bio ?? null,
                'is_leaderboard_private' => (bool) ($r->is_leaderboard_private ?? false),
                'last_activity_date' => $r->last_activity_date ?? null,
                'total_xp' => $xpVal,
                'xp' => $xpVal,
                'total_points' => $pointsVal,
                'points' => $pointsVal,
                'pts' => $pointsVal,
                'claimable_points' => (int) ($r->claimable_points ?? $pointsVal),
                'completed_activities' => $activitiesVal,
                'activities' => $activitiesVal,
                'total_activities' => $activitiesVal,
                'activities_count' => $activitiesVal,
                'places_visited' => (int) ($r->places_visited ?? $activitiesVal),
                'level' => (int) (floor($xpVal / 1000) + 1),
                'points_since' => $r->points_since ?? null,
                'created_at' => (!empty($r->created_at) ? date('Y-m-d H:i:s', strtotime($r->created_at)) : (!empty($r->points_since) ? date('Y-m-d H:i:s', strtotime($r->points_since)) : null)),
                'created_at_formatted' => (!empty($r->created_at) ? date('M d, Y h:i A', strtotime($r->created_at)) : (!empty($r->points_since) ? date('M d, Y h:i A', strtotime($r->points_since)) : null)),
                'date_created' => (!empty($r->created_at) ? date('M d, Y h:i A', strtotime($r->created_at)) : (!empty($r->points_since) ? date('M d, Y h:i A', strtotime($r->points_since)) : null)),
                'date_created_formatted' => (!empty($r->created_at) ? date('M d, Y h:i A', strtotime($r->created_at)) : (!empty($r->points_since) ? date('M d, Y h:i A', strtotime($r->points_since)) : null)),
                'created_date' => (!empty($r->created_at) ? date('Y-m-d', strtotime($r->created_at)) : null),
                'created_time' => (!empty($r->created_at) ? date('h:i A', strtotime($r->created_at)) : null),
                'timestamp' => (!empty($r->created_at) ? date('Y-m-d H:i:s', strtotime($r->created_at)) : null),
                'registered_at' => (!empty($r->created_at) ? date('M d, Y h:i A', strtotime($r->created_at)) : null),
                'registered_date' => (!empty($r->created_at) ? date('M d, Y h:i A', strtotime($r->created_at)) : null),
            ];
        }, $rows, array_keys($rows));
    }
}
