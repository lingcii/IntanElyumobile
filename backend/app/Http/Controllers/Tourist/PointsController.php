<?php

namespace App\Http\Controllers\Tourist;

use App\Http\Controllers\Controller;
use App\Models\UserPoint;
use App\Models\PointRedemption;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class PointsController extends Controller
{
    /**
     * GET /api/tourist/points/balance
     * Returns the total points, details of earned points, and redeemed vouchers.
     */
    public function getBalance(Request $request): JsonResponse
    {
        $user = $request->user();

        // Balance directly from users table
        $balance = (int) ($user->points ?? 0);

        $history = collect();
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('activity_logs')) {
                $history = \App\Models\ActivityLog::where('user_id', $user->id)
                    ->whereIn('action', ['Points Awarded', 'Points Redeemed', 'Voucher Redeemed'])
                    ->latest()
                    ->limit(50)
                    ->get()
                    ->map(function ($log) {
                        return [
                            'id'          => $log->id,
                            'points'      => (int) (preg_match('/([+-]?\d+)\s*Points?/i', $log->details, $m) ? $m[1] : 0),
                            'source'      => $log->action,
                            'description' => $log->details,
                            'created_at'  => $log->created_at ? $log->created_at->toIso8601String() : now()->toIso8601String(),
                            'date'        => $log->created_at ? $log->created_at->format('M d, Y h:i A') : now()->format('M d, Y h:i A'),
                        ];
                    });
            }
        } catch (\Throwable $e) {
            $history = collect();
        }

        $vouchers = collect();
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('point_redemptions')) {
                $vouchers = PointRedemption::where('user_id', $user->id)->latest()->get();
            }
        } catch (\Throwable $e) {
            $vouchers = collect();
        }

        return response()->json([
            'status' => 'success',
            'points' => $balance,
            'earned_total' => $balance,
            'redeemed_total' => $vouchers->sum('points_cost'),
            'history' => $history,
            'vouchers' => $vouchers
        ]);
    }

    /**
     * POST /api/tourist/points/puzzle
     * Award points for solving the sliding puzzle.
     */
    public function awardPuzzlePoints(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['status' => 'error', 'message' => 'Unauthenticated.'], 401);
        }

        $todayCompleted = false;
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('activity_logs')) {
                $todayCompleted = \App\Models\ActivityLog::where('user_id', $user->id)
                    ->where('action', 'Points Awarded')
                    ->where('details', 'LIKE', '%Puzzle%')
                    ->whereDate('created_at', now()->toDateString())
                    ->exists();
            }
        } catch (\Throwable $e) {
            $todayCompleted = false;
        }

        if ($todayCompleted) {
            return response()->json([
                'status' => 'error',
                'message' => 'You already completed the puzzle today. Come back tomorrow!'
            ], 429);
        }

        $points = 100;
        UserPoint::awardPointsSafely(
            $user->id,
            $points,
            'puzzle',
            'Successfully solved a sliding block puzzle'
        );

        try {
            if (\Illuminate\Support\Facades\Schema::hasColumn('users', 'xp')) {
                $user->increment('xp', 25);
            }
        } catch (\Throwable $e) {}

        return response()->json([
            'status' => 'success',
            'message' => "Congratulations! You earned {$points} Points!",
            'points_awarded' => $points
        ]);
    }

    /**
     * POST /api/tourist/points/trivia
     * Award points for answering trivia questions correctly.
     */
    public function awardTriviaPoints(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['status' => 'error', 'message' => 'Unauthenticated.'], 401);
        }

        $todayCompleted = false;
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('activity_logs')) {
                $todayCompleted = \App\Models\ActivityLog::where('user_id', $user->id)
                    ->where('action', 'Points Awarded')
                    ->where('details', 'LIKE', '%Trivia%')
                    ->whereDate('created_at', now()->toDateString())
                    ->exists();
            }
        } catch (\Throwable $e) {
            $todayCompleted = false;
        }

        if ($todayCompleted) {
            return response()->json([
                'status' => 'error',
                'message' => 'You already completed the trivia today. Come back tomorrow!'
            ], 429);
        }

        $points = 50;
        UserPoint::awardPointsSafely(
            $user->id,
            $points,
            'trivia',
            'Answered La Union trivia questions correctly'
        );

        try {
            if (\Illuminate\Support\Facades\Schema::hasColumn('users', 'xp')) {
                $user->increment('xp', 25);
            }
        } catch (\Throwable $e) {}

        return response()->json([
            'status' => 'success',
            'message' => "Congratulations! You earned {$points} Points!",
            'points_awarded' => $points
        ]);
    }

    /**
     * POST /api/tourist/points/minigame
     * Award points for mini games (memory_match, word_scramble, etc.)
     */
    public function awardMiniGamePoints(Request $request): JsonResponse
    {
        $request->validate([
            'game_type' => 'required|string|in:memory_match,word_scramble,puzzle,trivia',
        ]);

        $user = $request->user();
        if (!$user) {
            return response()->json(['status' => 'error', 'message' => 'Unauthenticated.'], 401);
        }

        $gameType = $request->game_type;

        $todayCompleted = false;
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('activity_logs')) {
                $todayCompleted = \App\Models\ActivityLog::where('user_id', $user->id)
                    ->where('action', 'Points Awarded')
                    ->where('details', 'LIKE', '%' . str_replace('_', ' ', $gameType) . '%')
                    ->whereDate('created_at', now()->toDateString())
                    ->exists();
            }
        } catch (\Throwable $e) {
            $todayCompleted = false;
        }

        if ($todayCompleted) {
            return response()->json([
                'status' => 'error',
                'message' => 'You already played this game today. Come back tomorrow!'
            ], 429);
        }

        $gameConfig = [
            'memory_match' => ['points' => 75, 'desc' => 'Completed La Union Memory Card Match'],
            'word_scramble' => ['points' => 75, 'desc' => 'Unscrambled La Union Eco Explorer Words'],
            'puzzle'        => ['points' => 100, 'desc' => 'Successfully solved a sliding block puzzle'],
            'trivia'        => ['points' => 50, 'desc' => 'Answered La Union trivia questions correctly'],
        ];

        $config = $gameConfig[$gameType] ?? ['points' => 50, 'desc' => 'Completed Mini Game'];
        $points = $config['points'];

        UserPoint::awardPointsSafely(
            $user->id,
            $points,
            $gameType,
            $config['desc']
        );

        try {
            if (\Illuminate\Support\Facades\Schema::hasColumn('users', 'xp')) {
                $user->increment('xp', 25);
            }
        } catch (\Throwable $e) {}

        return response()->json([
            'status' => 'success',
            'message' => "Congratulations! You earned {$points} Points!",
            'points_awarded' => $points
        ]);
    }

    /**
     * POST /api/tourist/points/redeem
     * Redeem points for Pasalubong Center or Environmental Fee voucher.
     */
    public function redeem(Request $request): JsonResponse
    {
        $request->validate([
            'type' => 'required|string|in:pasalubong_discount,environmental_fee',
        ]);

        $user = $request->user();
        $type = $request->type;

        // Costs
        $costs = [
            'pasalubong_discount' => 100, // 100 points
            'environmental_fee' => 150, // 150 points
        ];

        $cost = $costs[$type];

        // Get points balance directly from users table
        $balance = (int) ($user->points ?? 0);

        if ($balance < $cost) {
            return response()->json([
                'status' => 'error',
                'message' => "Insufficient points. You need {$cost} points to redeem this reward, but you only have {$balance} points."
            ], 400);
        }

        // Generate voucher code
        $prefix = $type === 'pasalubong_discount' ? 'ELYU-PASA-' : 'ELYU-ENV-';
        $code = $prefix . strtoupper(Str::random(8));

        // Start transaction
        $redemption = DB::transaction(function() use ($user, $type, $cost, $code) {
            try {
                if (\Illuminate\Support\Facades\Schema::hasColumn('users', 'points')) {
                    $user->decrement('points', $cost);
                }
            } catch (\Throwable $e) {}

            return PointRedemption::create([
                'user_id' => $user->id,
                'type' => $type,
                'points_cost' => $cost,
                'voucher_code' => $code,
                'status' => 'active'
            ]);
        });

        \App\Models\Notification::createSafely(
            $user->id,
            'favorite_update',
            '🎟️ Voucher Redeemed!',
            "Redeemed voucher {$code} ({$type}). Present code at merchant checkout!",
            ['action_url' => '/discount']
        );

        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('activity_logs')) {
                $typeLabel = ucwords(str_replace('_', ' ', $type));
                \App\Models\ActivityLog::create([
                    'user_id'    => $user->id,
                    'action'     => 'Points Redeemed',
                    'details'    => "Redeemed {$cost} points for {$typeLabel} (Code: {$code})",
                    'ip_address' => $request->ip() ?? '127.0.0.1',
                ]);
            }
        } catch (\Throwable $e) {}

        return response()->json([
            'status' => 'success',
            'message' => 'Reward redeemed successfully!',
            'data' => $redemption
        ]);
    }
}
