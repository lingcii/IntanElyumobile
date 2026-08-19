<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class LeaderboardController extends Controller
{
    /**
     * Optimized ranked CTE using denormalized users.completed_activities
     * instead of correlated subqueries. This is the fallback when
     * leaderboard_cache is empty.
     *
     * Techniques applied:
     *   3. Query Optimization — pre-aggregate via denormalized column
     *   5. Denormalization — uses users.completed_activities directly
     */
    private function rankedCte(): string
    {
        return "
            WITH ranked AS (
                SELECT
                    u.id                                              AS user_id,
                    u.name                                            AS name,
                    COALESCE(NULLIF(u.name, ''), CONCAT('Explorer #', u.id)) AS full_name,
                    u.email                                           AS email,
                    u.avatar                                          AS avatar,
                    u.home_location                                   AS home_location,
                    u.home_location                                   AS municipality,
                    u.bio                                             AS bio,
                    COALESCE(u.is_leaderboard_private, 0)             AS is_leaderboard_private,
                    u.last_activity                                   AS last_activity_date,
                    COALESCE(u.xp, 0)                                 AS total_points,
                    COALESCE(u.xp, 0)                                 AS total_xp,
                    GREATEST(
                        0,
                        COALESCE((SELECT SUM(up.points) FROM user_points up WHERE up.user_id = u.id), 0) -
                        COALESCE((SELECT SUM(pr.points_cost) FROM point_redemptions pr WHERE pr.user_id = u.id), 0)
                    )                                                 AS claimable_points,
                    GREATEST(
                        COALESCE(u.completed_activities, 0),
                        (SELECT COUNT(*) FROM itinerary_items ii JOIN itineraries it ON ii.itinerary_id = it.id WHERE it.user_id = u.id AND ii.is_visited = 1)
                    )                                                 AS completed_activities,
                    u.created_at                                      AS points_since,
                    ROW_NUMBER() OVER (
                        ORDER BY
                            COALESCE(u.xp, 0)                          DESC,
                            GREATEST(
                                COALESCE(u.completed_activities, 0),
                                (SELECT COUNT(*) FROM itinerary_items ii JOIN itineraries it ON ii.itinerary_id = it.id WHERE it.user_id = u.id AND ii.is_visited = 1)
                            ) DESC,
                            u.created_at                               ASC
                    ) AS `rank`
                FROM users u
                WHERE u.role = 'tourist' AND (u.status = 'active' OR u.status IS NULL)
            )
        ";
    }

    /**
     * GET /api/tourist/leaderboard  (authenticated)
     * GET /api/public/leaderboard   (public)
     */
    public function index(Request $request): JsonResponse
    {
        $search  = $request->get('search', '');
        $sortBy  = $request->get('sort', 'points_desc');
        $limit   = min(max((int) $request->get('limit', 100), 1), 100);
        $offset  = max((int) $request->get('offset', 0), 0);

        $orderMap = [
            'points_desc'     => 'total_points DESC, completed_activities DESC, points_since ASC',
            'xp_desc'         => 'total_points DESC, completed_activities DESC, points_since ASC',
            'pts_desc'        => 'claimable_points DESC, total_points DESC, points_since ASC',
            'points'          => 'claimable_points DESC, total_points DESC, points_since ASC',
            'activities_desc' => 'completed_activities DESC, total_points DESC, points_since ASC',
            'name_asc'        => 'full_name ASC',
        ];
        $orderSql = $orderMap[$sortBy] ?? $orderMap['points_desc'];

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

        // Live ranked query computed directly on demand for the leaderboards
        $cachedData = $this->queryFromLiveCte($search, $orderSql, $limit, $offset);
        $rows = $this->castRows($cachedData['rows']);

        $totalTourists = (int) ($cachedData['total'] ?? count($rows));
        $highestPoints = count($rows) > 0 ? (int) ($rows[0]['total_points'] ?? 0) : 0;
        $totalActivities = (int) array_sum(array_column($rows, 'completed_activities'));

        return response()->json([
            'success'          => true,
            'myRank'           => $myRank,
            'my_rank'          => $myRank,
            'me'               => $me,
            'users'            => $rows,
            'leaders'          => $rows,
            'total'            => $totalTourists,
            'total_tourists'   => $totalTourists,
            'highest_points'   => $highestPoints,
            'total_activities' => $totalActivities,
            'stats'            => [
                'total_tourists'   => $totalTourists,
                'highest_points'   => $highestPoints,
                'total_activities' => $totalActivities,
            ],
            'offset'           => $offset,
            'limit'            => $limit,
        ]);
    }

    /**
     * Technique 6: Read from the pre-computed leaderboard_cache table.
     */
    private function queryFromMaterializedView(string $search, string $orderSql, int $limit, int $offset): array
    {
        $whereClause = '';
        $params = [];

        if ($search) {
            $whereClause = "WHERE full_name LIKE ? OR CAST(user_id AS CHAR) LIKE ?";
            $params = ["%{$search}%", "%{$search}%"];
        } else {
            $whereClause = 'WHERE `rank` <= 100';
        }

        $total = DB::selectOne(
            "SELECT COUNT(*) as cnt FROM leaderboard_cache {$whereClause}",
            $params
        )->cnt;

        $rows = DB::select(
            "SELECT user_id, full_name, total_points, completed_activities, `rank`, last_activity AS last_activity_date, points_since FROM leaderboard_cache {$whereClause} ORDER BY {$orderSql} LIMIT {$limit} OFFSET {$offset}",
            $params
        );

        return [
            'total' => (int) $total,
            'rows'  => json_decode(json_encode($rows), true),
        ];
    }

    /**
     * Fallback: Live CTE query using optimized denormalized data.
     */
    private function queryFromLiveCte(string $search, string $orderSql, int $limit, int $offset): array
    {
        $whereClause = '';
        $params = [];

        if ($search) {
            $whereClause = "WHERE full_name LIKE ? OR CAST(user_id AS CHAR) LIKE ?";
            $params = ["%{$search}%", "%{$search}%"];
        } else {
            $whereClause = 'WHERE `rank` <= 100';
        }

        $total = DB::selectOne(
            $this->rankedCte() . "SELECT COUNT(*) as cnt FROM ranked {$whereClause}",
            $params
        )->cnt;

        $rows = DB::select(
            $this->rankedCte() . "SELECT * FROM ranked {$whereClause} ORDER BY {$orderSql} LIMIT {$limit} OFFSET {$offset}",
            $params
        );

        return [
            'total' => (int) $total,
            'rows'  => json_decode(json_encode($rows), true),
        ];
    }

    private function castRows(array $rows): array
    {
        return array_map(function ($r) {
            $r = (object) $r;
            $isPrivate = (bool) ($r->is_leaderboard_private ?? false);
            $realName = !empty($r->name) ? $r->name : "Explorer #{$r->user_id}";
            $displayName = $isPrivate ? "Private Explorer" : $realName;

            return [
                'id'                   => (int) $r->user_id,
                'user_id'              => (int) $r->user_id,
                'name'                 => $displayName,
                'full_name'            => $displayName,
                'real_name'            => $realName,
                'avatar'               => $isPrivate ? null : ($r->avatar ?? null),
                'home_location'        => $isPrivate ? null : ($r->home_location ?? null),
                'municipality'         => $isPrivate ? null : ($r->municipality ?? $r->home_location ?? null),
                'bio'                  => $isPrivate ? null : ($r->bio ?? null),
                'is_leaderboard_private' => $isPrivate,
                'last_activity_date'   => $r->last_activity_date ?? null,
                'total_points'         => (int) ($r->total_points ?? 0),
                'total_xp'             => (int) ($r->total_points ?? 0),
                'xp'                   => (int) ($r->total_points ?? 0),
                'claimable_points'     => (int) ($r->claimable_points ?? 0),
                'points'               => (int) ($r->claimable_points ?? 0),
                'pts'                  => (int) ($r->claimable_points ?? 0),
                'completed_activities' => (int) ($r->completed_activities ?? 0),
                'activities'           => (int) ($r->completed_activities ?? 0),
                'places_visited'       => (int) ($r->completed_activities ?? 0),
                'level'                => (int) (floor(((int) ($r->total_points ?? 0)) / 1000) + 1),
                'rank'                 => (int) $r->rank,
                'points_since'         => $r->points_since ?? null,
            ];
        }, $rows);
    }
}
