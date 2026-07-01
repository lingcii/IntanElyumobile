<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LeaderboardController extends Controller
{
    private function rankedCte(): string
    {
        return "
            WITH ranked AS (
                SELECT
                    u.id                                              AS user_id,
                    u.name                                            AS full_name,
                    u.last_activity                                   AS last_activity_date,
                    COALESCE(up.total_points, 0)                      AS total_points,
                    COALESCE(up.completed_activities, 0)              AS completed_activities,
                    COALESCE(up.points_since, u.created_at)           AS points_since,
                    ROW_NUMBER() OVER (
                        ORDER BY
                            COALESCE(up.total_points, 0)               DESC,
                            COALESCE(up.completed_activities, 0)        DESC,
                            COALESCE(up.points_since, u.created_at)     ASC
                    ) AS `rank`
                FROM users u
                LEFT JOIN user_points up ON up.user_id = u.id
                WHERE u.role = 'tourist' AND u.status = 'active'
            )
        ";
    }

    public function top3(): JsonResponse
    {
        $rows = \Illuminate\Support\Facades\Cache::remember('leaderboard:top3', 60, function () {
            return DB::select($this->rankedCte() . 'SELECT * FROM ranked WHERE `rank` <= 3 ORDER BY `rank` ASC');
        });

        return response()->json(['success' => true, 'top3' => $this->castRows($rows)]);
    }

    public function kpis(): JsonResponse
    {
        $kpi = \Illuminate\Support\Facades\Cache::remember('leaderboard:kpis', 60, function () {
            return DB::selectOne("
                SELECT
                    COUNT(u.id)                               AS total_users,
                    COALESCE(SUM(up.total_points), 0)         AS grand_points,
                    COALESCE(SUM(up.completed_activities), 0) AS total_activities,
                    COALESCE(MAX(up.total_points), 0)         AS highest_points
                FROM users u
                LEFT JOIN user_points up ON up.user_id = u.id
                WHERE u.role = 'tourist' AND u.status = 'active'
            ");
        });

        return response()->json([
            'success' => true,
            'kpis'    => [
                'total_users'      => (int) $kpi->total_users,
                'grand_points'     => (int) $kpi->grand_points,
                'total_activities' => (int) $kpi->total_activities,
                'highest_points'   => (int) $kpi->highest_points,
            ],
        ]);
    }

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

        $whereClause = '';
        $params      = [];

        if ($request->filled('search')) {
            $search = $request->get('search');
            $whereClause = "WHERE full_name LIKE ? OR CAST(user_id AS CHAR) LIKE ?";
            $params      = ["%{$search}%", "%{$search}%"];
        } else {
            $whereClause = 'WHERE `rank` <= 100';
        }

        $cacheKey = "leaderboard:index:{$search}:{$sortBy}:{$limit}:{$offset}";

        $cachedData = \Illuminate\Support\Facades\Cache::remember($cacheKey, 60, function () use ($whereClause, $params, $orderSql, $limit, $offset) {
            $total = DB::selectOne($this->rankedCte() . "SELECT COUNT(*) as cnt FROM ranked {$whereClause}", $params)->cnt;

            $rows = DB::select(
                $this->rankedCte() . "SELECT * FROM ranked {$whereClause} ORDER BY {$orderSql} LIMIT {$limit} OFFSET {$offset}",
                $params
            );

            return [
                'total' => (int) $total,
                'rows'  => $rows
            ];
        });

        return response()->json([
            'success' => true,
            'users'   => $this->castRows($cachedData['rows']),
            'total'   => $cachedData['total'],
            'offset'  => $offset,
            'limit'   => $limit,
        ]);
    }

    private function castRows(array $rows): array
    {
        return array_map(function ($r) {
            return [
                'user_id'              => (int) $r->user_id,
                'full_name'            => $r->full_name,
                'last_activity_date'   => $r->last_activity_date ?: null,
                'total_points'         => (int) $r->total_points,
                'completed_activities' => (int) $r->completed_activities,
                'rank'                 => (int) $r->rank,
                'points_since'         => $r->points_since,
            ];
        }, $rows);
    }
}
