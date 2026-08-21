<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LeaderboardController extends Controller
{
    /**
     * Common Ranked CTE ensuring clean deduplication, municipality fallback,
     * comprehensive points & activity aggregation, and correct ordering.
     */
    private function rankedCte(): string
    {
        return "
            WITH user_summary AS (
                SELECT
                    u.id                                              AS user_id,
                    u.name                                            AS name,
                    COALESCE(NULLIF(u.name, ''), CONCAT('Explorer #', u.id)) AS full_name,
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
                    GREATEST(
                        COALESCE(u.completed_activities, 0),
                        COALESCE((SELECT COUNT(*) FROM itinerary_items ii JOIN itineraries it ON ii.itinerary_id = it.id WHERE it.user_id = u.id AND ii.is_visited = 1), 0),
                        COALESCE((SELECT COUNT(DISTINCT up.spot_id) FROM user_points up WHERE up.user_id = u.id AND up.spot_id IS NOT NULL), 0),
                        COALESCE((SELECT COUNT(*) FROM site_feedbacks fb WHERE fb.user_id = u.id), 0)
                    )                                                 AS completed_activities,
                    GREATEST(
                        COALESCE((SELECT COUNT(DISTINCT ii.tourist_spot_id) FROM itinerary_items ii JOIN itineraries it ON ii.itinerary_id = it.id WHERE it.user_id = u.id AND ii.is_visited = 1), 0),
                        COALESCE((SELECT COUNT(DISTINCT up.spot_id) FROM user_points up WHERE up.user_id = u.id AND up.spot_id IS NOT NULL), 0),
                        COALESCE(u.completed_activities, 0)
                    )                                                 AS places_visited,
                    COALESCE(u.created_at, NOW())                     AS points_since
                FROM users u
                LEFT JOIN municipalities m ON u.municipality_id = m.id
                WHERE u.role = 'tourist'
                GROUP BY u.id, u.name, u.email, u.avatar, u.home_location, m.name, u.bio, u.is_leaderboard_private, u.last_activity, u.xp, u.points, u.completed_activities, u.created_at
            ),
            ranked AS (
                SELECT
                    us.*,
                    ROW_NUMBER() OVER (
                        ORDER BY
                            us.total_xp             DESC,
                            us.points               DESC,
                            us.completed_activities DESC,
                            us.points_since         ASC,
                            us.user_id              ASC
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
        $rawSort = strtolower(trim($request->get('sort', 'points_desc')));
        $limit = min(max((int) $request->get('limit', 100), 1), 500);
        $offset = max((int) $request->get('offset', 0), 0);

        // Normalize various sorting parameter names
        $orderSql = match ($rawSort) {
            'lowest_points', 'lowest points', 'points_asc' => 'points ASC, total_xp ASC, user_id ASC',
            'xp_asc' => 'total_xp ASC, points ASC, user_id ASC',
            'points', 'points_desc', 'highest_points' => 'points DESC, total_xp DESC, user_id ASC',
            'most_activities', 'activities', 'activities_desc', 'completed_activities', 'visited', 'most_visited', 'places_visited', 'visited_desc' => 'completed_activities DESC, places_visited DESC, total_xp DESC, user_id ASC',
            'least_activities', 'activities_asc' => 'completed_activities ASC, total_xp ASC, user_id ASC',
            'name_asc', 'name' => 'full_name ASC, user_id ASC',
            'name_desc' => 'full_name DESC, user_id ASC',
            'recent', 'newest', 'latest' => 'points_since DESC, user_id ASC',
            default => '`rank` ASC, total_xp DESC, points DESC',
        };

        $myRank = null;
        $me = null;
        $user = $request->user();
        if ($user) {
            try {
                $myRankRow = DB::selectOne(
                    $this->rankedCte() . "SELECT * FROM ranked WHERE user_id = ?",
                    [$user->id]
                );
                if ($myRankRow) {
                    $myRank = (int) $myRankRow->rank;
                    $meRows = $this->castRows([(array) $myRankRow]);
                    $me = $meRows[0] ?? null;
                }
            } catch (\Throwable $e) {}
        }

        // Execute dynamic ranked query
        $cachedData = $this->queryFromLiveCte($search, $orderSql, $limit, $offset);
        $rows = $this->castRows($cachedData['rows']);

        $totalTourists = (int) ($cachedData['total'] ?? count($rows));
        $highestPoints = count($rows) > 0 ? (int) max(array_column($rows, 'total_points')) : 0;
        $totalActivities = (int) array_sum(array_column($rows, 'completed_activities'));

        return response()->json([
            'success'          => true,
            'myRank'           => $myRank,
            'my_rank'          => $myRank,
            'me'               => $me,
            'users'            => $rows,
            'leaders'          => $rows,
            'leaderboard'      => $rows,
            'data'             => $rows,
            'tourists'         => $rows,
            'total'            => $totalTourists,
            'total_tourists'   => $totalTourists,
            'totalTourists'    => $totalTourists,
            'highest_points'   => $highestPoints,
            'highestPoints'    => $highestPoints,
            'total_activities' => $totalActivities,
            'totalActivities'  => $totalActivities,
            'stats'            => [
                'total_tourists'   => $totalTourists,
                'totalTourists'    => $totalTourists,
                'highest_points'   => $highestPoints,
                'highestPoints'    => $highestPoints,
                'total_activities' => $totalActivities,
                'totalActivities'  => $totalActivities,
            ],
            'offset'           => $offset,
            'limit'            => $limit,
        ]);
    }

    /**
     * Fallback: Live CTE query using unified aggregation.
     */
    private function queryFromLiveCte(string $search, string $orderSql, int $limit, int $offset): array
    {
        $whereClause = '';
        $params = [];

        if ($search !== '') {
            $whereClause = "WHERE full_name LIKE ? OR CAST(user_id AS CHAR) LIKE ? OR municipality LIKE ?";
            $params = ["%{$search}%", "%{$search}%", "%{$search}%"];
        }

        $totalResult = DB::selectOne(
            $this->rankedCte() . "SELECT COUNT(*) as cnt FROM ranked {$whereClause}",
            $params
        );
        $total = $totalResult ? $totalResult->cnt : 0;

        $rows = DB::select(
            $this->rankedCte() . "SELECT * FROM ranked {$whereClause} ORDER BY {$orderSql} LIMIT {$limit} OFFSET {$offset}",
            $params
        );

        return [
            'total' => (int) $total,
            'rows'  => json_decode(json_encode($rows), true),
        ];
    }

    /**
     * Cast & format each user row with all possible field variations.
     */
    private function castRows(array $rows): array
    {
        return array_map(function ($r, $index) {
            $r = (object) $r;
            $isPrivate = (bool) ($r->is_leaderboard_private ?? false);
            $realName = !empty($r->name) ? $r->name : "Explorer #{$r->user_id}";
            $displayName = $isPrivate ? "Private Explorer" : $realName;

            $rankVal = isset($r->rank) && (int) $r->rank > 0 ? (int) $r->rank : ($index + 1);
            $pointsVal = (int) ($r->total_points ?? 0);
            $activitiesVal = (int) ($r->completed_activities ?? 0);
            $muniVal = $isPrivate ? 'La Union' : ($r->municipality ?: ($r->home_location ?: 'La Union'));

            return [
                'id'                     => (int) $r->user_id,
                'user_id'                => (int) $r->user_id,
                'rank'                   => $rankVal,
                'rank_number'            => $rankVal,
                'rank_no'                => $rankVal,
                'position'               => $rankVal,
                'ranking'                => $rankVal,
                'index'                  => $rankVal,
                'name'                   => $displayName,
                'full_name'              => $displayName,
                'real_name'              => $realName,
                'email'                  => $r->email ?? null,
                'avatar'                 => $isPrivate ? null : ($r->avatar ?? null),
                'home_location'          => $muniVal,
                'municipality'           => $muniVal,
                'municipality_name'      => $muniVal,
                'location'               => $muniVal,
                'bio'                    => $isPrivate ? null : ($r->bio ?? null),
                'is_leaderboard_private' => $isPrivate,
                'last_activity_date'     => $r->last_activity_date ?? null,
                'total_points'           => $pointsVal,
                'total_xp'               => $pointsVal,
                'xp'                     => $pointsVal,
                'points'                 => $pointsVal,
                'pts'                    => $pointsVal,
                'claimable_points'       => (int) ($r->claimable_points ?? $pointsVal),
                'completed_activities'   => $activitiesVal,
                'activities'             => $activitiesVal,
                'total_activities'       => $activitiesVal,
                'activities_count'       => $activitiesVal,
                'places_visited'         => (int) ($r->places_visited ?? $activitiesVal),
                'level'                  => (int) (floor($pointsVal / 1000) + 1),
                'points_since'           => $r->points_since ?? null,
            ];
        }, $rows, array_keys($rows));
    }
}
