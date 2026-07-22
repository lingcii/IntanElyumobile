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
        $earned = (int) UserPoint::where('user_id', $user->id)->sum('points');

        // Calculate redeemed points
        $redeemed = (int) PointRedemption::where('user_id', $user->id)->sum('points_cost');

        $balance = max(0, $earned - $redeemed);

        $history = UserPoint::where('user_id', $user->id)->latest()->get();
        $vouchers = PointRedemption::where('user_id', $user->id)->latest()->get();

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

        $todayCompleted = UserPoint::where('user_id', $user->id)
            ->where('source', 'puzzle')
            ->whereDate('created_at', now()->toDateString())
            ->exists();

        if ($todayCompleted) {
            return response()->json([
                'status' => 'error',
                'message' => 'You already completed the puzzle today. Come back tomorrow!'
            ], 429);
        }

        $points = 100;
        UserPoint::create([
            'user_id' => $user->id,
            'points' => $points,
            'source' => 'puzzle',
            'description' => 'Successfully solved a sliding block puzzle',
        ]);

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

        $todayCompleted = UserPoint::where('user_id', $user->id)
            ->where('source', 'trivia')
            ->whereDate('created_at', now()->toDateString())
            ->exists();

        if ($todayCompleted) {
            return response()->json([
                'status' => 'error',
                'message' => 'You already completed the trivia today. Come back tomorrow!'
            ], 429);
        }

        $points = 50;
        UserPoint::create([
            'user_id' => $user->id,
            'points' => $points,
            'source' => 'trivia',
            'description' => 'Answered La Union trivia questions correctly',
        ]);

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
        $gameType = $request->game_type;

        $todayCompleted = UserPoint::where('user_id', $user->id)
            ->where('source', $gameType)
            ->whereDate('created_at', now()->toDateString())
            ->exists();

        if ($todayCompleted) {
            return response()->json([
                'status' => 'error',
                'message' => 'You already played this game today. Come back tomorrow!'
            ], 429);
        }

        $gameConfig = [
            'memory_match' => ['points' => 75, 'desc' => 'Completed La Union Memory Card Match'],
            'word_scramble' => ['points' => 75, 'desc' => 'Unscrambled La Union Eco Explorer Words'],
            'puzzle' => ['points' => 100, 'desc' => 'Successfully solved a sliding block puzzle'],
            'trivia' => ['points' => 50, 'desc' => 'Answered La Union trivia questions correctly'],
        ];

        $config = $gameConfig[$gameType];
        $points = $config['points'];

        UserPoint::create([
            'user_id' => $user->id,
            'points' => $points,
            'source' => $gameType,
            'description' => $config['desc'],
        ]);

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

        return response()->json([
            'status' => 'success',
            'message' => 'Reward redeemed successfully!',
            'data' => $redemption
        ]);
    }
}
