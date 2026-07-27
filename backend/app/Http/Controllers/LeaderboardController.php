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
                    u.last_activity                                   AS last_activity_date,
                    COALESCE(u.xp, 0)                                 AS total_points,
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
                WHERE u.role = 'tourist' AND u.status = 'active'
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
            'points_asc'      => 'total_points ASC, completed_activities ASC, points_since DESC',
            'activities_desc' => 'completed_activities DESC, total_points DESC, points_since ASC',
            'name_asc'        => 'full_name ASC',
        ];
        $orderSql = $orderMap[$sortBy] ?? $orderMap['points_desc'];

        $cacheKey = "leaderboard:index:v2:{$search}:{$sortBy}:{$limit}:{$offset}";

        $myRank = null;
        $user = $request->user();
        if ($user) {
            try {
                $myRankRow = DB::selectOne(
                    $this->rankedCte() . "SELECT `rank` FROM ranked WHERE user_id = ?",
                    [$user->id]
                );
                if ($myRankRow) {
                    $myRank = (int) $myRankRow->rank;
                }
            } catch (\Throwable $e) {}
        }

        // Live ranked query computed directly on demand for the leaderboards
        $cachedData = $this->queryFromLiveCte($search, $orderSql, $limit, $offset);

        return response()->json([
            'success' => true,
            'myRank'  => $myRank,
            'my_rank' => $myRank,
            'users'   => $this->castRows($cachedData['rows']),
            'total'   => $cachedData['total'],
            'offset'  => $offset,
            'limit'   => $limit,
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
            $displayName = "Explorer #{$r->user_id}";

            return [
                'user_id'              => (int) $r->user_id,
                'name'                 => $displayName,
                'full_name'            => $displayName,
                'last_activity_date'   => $r->last_activity_date ?: null,
                'total_points'         => (int) $r->total_points,
                'completed_activities' => (int) $r->completed_activities,
                'rank'                 => (int) $r->rank,
                'points_since'         => $r->points_since,
            ];
        }, $rows);
    }
}
