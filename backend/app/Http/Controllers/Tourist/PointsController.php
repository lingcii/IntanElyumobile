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

        // Calculate earned points
        $earned = 0;
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('user_points') && \Illuminate\Support\Facades\Schema::hasColumn('user_points', 'points')) {
                $earned = (int) UserPoint::where('user_id', $user->id)->sum('points');
            }
        } catch (\Throwable $e) {
            $earned = 0;
        }

        // Calculate redeemed points
        $redeemed = 0;
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('point_redemptions') && \Illuminate\Support\Facades\Schema::hasColumn('point_redemptions', 'points_cost')) {
                $redeemed = (int) PointRedemption::where('user_id', $user->id)->sum('points_cost');
            }
        } catch (\Throwable $e) {
            $redeemed = 0;
        }

        $balance = max(0, $earned - $redeemed);

        $history = collect();
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('user_points')) {
                $history = UserPoint::where('user_id', $user->id)->latest()->get();
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
            'earned_total' => $earned,
            'redeemed_total' => $redeemed,
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
            if (\Illuminate\Support\Facades\Schema::hasTable('user_points')) {
                $todayCompleted = UserPoint::where('user_id', $user->id)
                    ->where('source', 'puzzle')
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
            if (\Illuminate\Support\Facades\Schema::hasTable('user_points')) {
                $todayCompleted = UserPoint::where('user_id', $user->id)
                    ->where('source', 'trivia')
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
            if (\Illuminate\Support\Facades\Schema::hasTable('user_points')) {
                $todayCompleted = UserPoint::where('user_id', $user->id)
                    ->where('source', $gameType)
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

        // Get points balance
        $earned = (int) UserPoint::where('user_id', $user->id)->sum('points');
        $redeemed = (int) PointRedemption::where('user_id', $user->id)->sum('points_cost');
        $balance = $earned - $redeemed;

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

        return response()->json([
            'status' => 'success',
            'message' => 'Reward redeemed successfully!',
            'data' => $redemption
        ]);
    }
}
