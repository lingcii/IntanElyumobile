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

        // XP and Points balances - ensure points and xp are consistent
        $maxPoints = max((int) ($user->points ?? 0), (int) ($user->xp ?? 0));
        $userXp = $maxPoints;
        $userPoints = $maxPoints;

        if ($user->points !== $maxPoints || $user->xp !== $maxPoints) {
            try {
                $user->update(['points' => $maxPoints, 'xp' => $maxPoints]);
            } catch (\Throwable $e) {}
        }

        $history = collect();
        $todayCompleted = [
            'puzzle' => false,
            'memory_match' => false,
            'word_scramble' => false,
            'trivia' => false,
        ];
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
                            'points'      => (int) (preg_match('/([+-]?\d+)\s*(?:Points?|XP)/i', $log->details, $m) ? $m[1] : 0),
                            'xp'          => (int) (preg_match('/([+-]?\d+)\s*(?:Points?|XP)/i', $log->details, $m) ? $m[1] : 0),
                            'source'      => $log->action,
                            'description' => $log->details,
                            'created_at'  => $log->created_at ? $log->created_at->toIso8601String() : now()->toIso8601String(),
                            'date'        => $log->created_at ? $log->created_at->format('M d, Y h:i A') : now()->format('M d, Y h:i A'),
                        ];
                    });

                $todayLogs = \App\Models\ActivityLog::where('user_id', $user->id)
                    ->where('action', 'Points Awarded')
                    ->whereBetween('created_at', [now()->startOfDay(), now()->endOfDay()])
                    ->get();

                foreach ($todayLogs as $log) {
                    $details = strtolower($log->details ?? '');
                    if (str_contains($details, 'puzzle')) $todayCompleted['puzzle'] = true;
                    if (str_contains($details, 'memory')) $todayCompleted['memory_match'] = true;
                    if (str_contains($details, 'scramble')) $todayCompleted['word_scramble'] = true;
                    if (str_contains($details, 'trivia')) $todayCompleted['trivia'] = true;
                }
            }
        } catch (\Throwable $e) {
            $history = collect();
        }

        $vouchers = self::getUserVouchers($user->id);

        $level = (int) floor(max(0, $userXp) / 1000) + 1;

        return response()->json([
            'status' => 'success',
            'xp' => $userXp,
            'points' => $userPoints,
            'level' => $level,
            'earned_total' => $userXp,
            'redeemed_total' => $vouchers->sum('points_cost'),
            'history' => $history,
            'today_completed' => $todayCompleted,
            'vouchers' => $vouchers
        ]);
    }

    /**
     * POST /api/tourist/points/puzzle
     * Award XP for solving the sliding puzzle.
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
                    ->whereBetween('created_at', [now()->startOfDay(), now()->endOfDay()])
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

        $xp = 30;
        try {
            if (\Illuminate\Support\Facades\Schema::hasColumn('users', 'xp')) {
                $user->increment('xp', $xp);
            }
        } catch (\Throwable $e) {}

        UserPoint::awardPointsSafely(
            $user->id,
            $xp,
            'puzzle',
            'Successfully solved a sliding block puzzle'
        );

        $user->refresh();
        $finalPoints = max((int) ($user->points ?? 0), (int) ($user->xp ?? 0));
        try {
            $user->update(['points' => $finalPoints, 'xp' => $finalPoints]);
        } catch (\Throwable $e) {}

        return response()->json([
            'status' => 'success',
            'message' => "Congratulations! You earned {$xp} Points! You completed today's puzzle. Come back tomorrow!",
            'xp_awarded' => $xp,
            'points_awarded' => $xp,
            'points' => $finalPoints,
            'xp' => $finalPoints,
            'game_type' => 'puzzle',
            'completed_today' => true
        ]);
    }

    /**
     * POST /api/tourist/points/trivia
     * Award XP for answering trivia questions correctly.
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
                    ->whereBetween('created_at', [now()->startOfDay(), now()->endOfDay()])
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

        $xp = 20;
        try {
            if (\Illuminate\Support\Facades\Schema::hasColumn('users', 'xp')) {
                $user->increment('xp', $xp);
            }
        } catch (\Throwable $e) {}

        UserPoint::awardPointsSafely(
            $user->id,
            $xp,
            'trivia',
            'Answered La Union trivia questions correctly'
        );

        $user->refresh();
        $finalPoints = max((int) ($user->points ?? 0), (int) ($user->xp ?? 0));
        try {
            $user->update(['points' => $finalPoints, 'xp' => $finalPoints]);
        } catch (\Throwable $e) {}

        return response()->json([
            'status' => 'success',
            'message' => "Congratulations! You earned {$xp} Points! You completed today's trivia. Come back tomorrow!",
            'xp_awarded' => $xp,
            'points_awarded' => $xp,
            'points' => $finalPoints,
            'xp' => $finalPoints,
            'game_type' => 'trivia',
            'completed_today' => true
        ]);
    }

    /**
     * POST /api/tourist/points/minigame
     * Award XP for mini games (memory_match, word_scramble, etc.)
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
                    ->whereBetween('created_at', [now()->startOfDay(), now()->endOfDay()])
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
            'memory_match'  => ['xp' => 25, 'points' => 25, 'desc' => 'Completed La Union Memory Card Match'],
            'word_scramble' => ['xp' => 25, 'points' => 25, 'desc' => 'Unscrambled La Union Eco Explorer Words'],
            'puzzle'        => ['xp' => 30, 'points' => 30, 'desc' => 'Successfully solved a sliding block puzzle'],
            'trivia'        => ['xp' => 20, 'points' => 20, 'desc' => 'Answered La Union trivia questions correctly'],
        ];

        $config = $gameConfig[$gameType] ?? ['xp' => 50, 'points' => 50, 'desc' => 'Completed Mini Game'];
        $xp = $config['xp'];

        try {
            if (\Illuminate\Support\Facades\Schema::hasColumn('users', 'xp')) {
                $user->increment('xp', $xp);
            }
        } catch (\Throwable $e) {}

        UserPoint::awardPointsSafely(
            $user->id,
            $xp,
            $gameType,
            $config['desc']
        );

        $user->refresh();
        $finalPoints = max((int) ($user->points ?? 0), (int) ($user->xp ?? 0));
        try {
            $user->update(['points' => $finalPoints, 'xp' => $finalPoints]);
        } catch (\Throwable $e) {}

        return response()->json([
            'status' => 'success',
            'message' => "Congratulations! You earned {$xp} Points! You completed today's game. Come back tomorrow!",
            'xp_awarded' => $xp,
            'points_awarded' => $xp,
            'points' => $finalPoints,
            'xp' => $finalPoints,
            'game_type' => $gameType,
            'completed_today' => true
        ]);
    }

    /**
     * POST /api/tourist/points/redeem
     * Redeem points for Pasalubong Center or Environmental Fee voucher.
     */
    public function redeem(Request $request): JsonResponse
    {
        try {
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
                    'message' => "Insufficient points. You need {$cost} Points to redeem this reward, but you only have {$balance} Points."
                ], 400);
            }

            // Generate guaranteed unique voucher code
            $prefix = $type === 'pasalubong_discount' ? 'ELYU-PASA-' : 'ELYU-ENV-';
            $code = '';
            $attempts = 0;
            $letters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
            do {
                $suffix = '';
                for ($i = 0; $i < 4; $i++) {
                    $suffix .= $letters[random_int(0, 25)];
                }
                $code = $prefix . $suffix;
                $attempts++;
            } while (PointRedemption::where('voucher_code', $code)->exists() && $attempts < 10);

            // Start transaction
            $redemption = DB::transaction(function() use ($user, $type, $cost, $code) {
                try {
                    if (method_exists($user, 'deductPoints')) {
                        $user->deductPoints($cost);
                    } else {
                        $currentPts = (int) ($user->points ?? 0);
                        $newPts = max(0, $currentPts - $cost);
                        if (\Illuminate\Support\Facades\Schema::hasColumn('users', 'points')) {
                            $user->points = $newPts;
                            $user->save();
                        }
                    }
                } catch (\Throwable $e) {
                    try {
                        $user->decrement('points', $cost);
                    } catch (\Throwable $ignored) {}
                }

                $pr = PointRedemption::create([
                    'user_id' => $user->id,
                    'type' => $type,
                    'points_cost' => $cost,
                    'voucher_code' => $code,
                    'status' => 'active'
                ]);

                // Also sync to voucher_redemptions if table exists so staff QR scanning works seamlessly
                if (\Illuminate\Support\Facades\Schema::hasTable('voucher_redemptions')) {
                    try {
                        \Illuminate\Support\Facades\DB::table('voucher_redemptions')->insert([
                            'voucher_id'      => null,
                            'user_id'         => $user->id,
                            'redemption_code' => $code,
                            'qr_token'        => 'elyu_rdm_' . bin2hex(random_bytes(16)),
                            'points_used'     => $cost,
                            'reward_name_snapshot' => $type === 'pasalubong_discount' ? '₱50 Pasalubong Discount' : ($type === 'environmental_fee' ? 'Waived Environmental Fee' : ucwords(str_replace('_', ' ', $type))),
                            'status'          => 'claimed',
                            'redeemed_at'     => null,
                            'claimed_at'      => now(),
                            'created_at'      => now(),
                            'updated_at'      => now(),
                        ]);
                    } catch (\Throwable $ignored) {}
                }

                return $pr;
            });

            \App\Models\Notification::createSafely(
                $user->id,
                'favorite_update',
                'Voucher Redeemed!',
                "Redeemed voucher {$code} ({$type}). Present code at merchant checkout!",
                ['action_url' => '/discount']
            );

            try {
                if (\Illuminate\Support\Facades\Schema::hasTable('activity_logs')) {
                    $typeLabel = ucwords(str_replace('_', ' ', $type));
                    \App\Models\ActivityLog::create([
                        'user_id'    => $user->id,
                        'action'     => 'Points Redeemed',
                        'details'    => "Redeemed {$cost} Points for {$typeLabel} (Code: {$code})",
                        'ip_address' => $request->ip() ?? '127.0.0.1',
                    ]);
                }
            } catch (\Throwable $e) {}

            $newPoints = max(0, $balance - $cost);

            return response()->json([
                'status' => 'success',
                'message' => 'Reward redeemed successfully!',
                'new_balance' => $newPoints,
                'points' => $newPoints,
                'xp' => (int) ($user->xp ?? 0),
                'level' => (int) ($user->level ?? 1),
                'data' => $redemption
            ]);
        } catch (\Illuminate\Validation\ValidationException $ve) {
            return response()->json([
                'status' => 'error',
                'message' => $ve->validator->errors()->first() ?: 'Validation failed.',
                'errors' => $ve->errors()
            ], 422);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Point reward redemption error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to redeem reward. Please try again.'
            ], 500);
        }
    }

    /**
     * Get all active and redeemed vouchers for a given user.
     * Reusable across PointsController, LoginController, etc.
     */
    public static function getUserVouchers($userId)
    {
        $vouchers = collect();
        if (!$userId) {
            return $vouchers;
        }

        try {
            $pointRedemptions = collect();
            if (\Illuminate\Support\Facades\Schema::hasTable('point_redemptions')) {
                $pointRedemptions = PointRedemption::where('user_id', $userId)->latest()->get();
            }

            $voucherRedemptions = collect();
            if (\Illuminate\Support\Facades\Schema::hasTable('voucher_redemptions')) {
                $rawRedemptions = \Illuminate\Support\Facades\DB::table('voucher_redemptions')
                    ->leftJoin('vouchers', 'voucher_redemptions.voucher_id', '=', 'vouchers.id')
                    ->where('voucher_redemptions.user_id', $userId)
                    ->select(
                        'voucher_redemptions.id',
                        'voucher_redemptions.voucher_id',
                        \Illuminate\Support\Facades\DB::raw('COALESCE(vouchers.voucher_name, voucher_redemptions.reward_name_snapshot, "Voucher") as type'),
                        'voucher_redemptions.points_used as points_cost',
                        'voucher_redemptions.redemption_code as voucher_code',
                        'voucher_redemptions.status',
                        'voucher_redemptions.redeemed_at',
                        'voucher_redemptions.created_at',
                        'vouchers.partner_establishment',
                        'vouchers.description',
                        'vouchers.discount_value',
                        'vouchers.discount_type',
                        'vouchers.image',
                        'vouchers.terms_and_conditions',
                        'vouchers.expires_at',
                        'vouchers.expiration_type'
                    )
                    ->latest('voucher_redemptions.created_at')
                    ->get();

                $voucherRedemptions = $rawRedemptions->map(function ($r) {
                    $category = 'Food & Dining';
                    $text = strtolower(($r->type ?? '') . ' ' . ($r->partner_establishment ?? '') . ' ' . ($r->description ?? '') . ' ' . ($r->terms_and_conditions ?? ''));
                    if (str_contains($text, 'surf') || str_contains($text, 'activity') || str_contains($text, 'tour') || str_contains($text, 'hike') || str_contains($text, 'rental') || str_contains($text, 'lesson')) {
                        $category = 'Activities';
                    } elseif (str_contains($text, 'hotel') || str_contains($text, 'resort') || str_contains($text, 'stay') || str_contains($text, 'room') || str_contains($text, 'inn') || str_contains($text, 'villa')) {
                        $category = 'Accommodations';
                    } elseif (str_contains($text, 'pasalubong') || str_contains($text, 'souvenir') || str_contains($text, 'native') || str_contains($text, 'wine') || str_contains($text, 'craft') || str_contains($text, 'pass')) {
                        $category = 'Souvenirs';
                    }
                    $r->category = $category;
                    return $r;
                });
            }

            // Merge both collections so no user vouchers are dropped
            $merged = collect($voucherRedemptions);
            $seenCodes = $voucherRedemptions->pluck('voucher_code')->filter()->map(fn($c) => strtoupper(trim($c)))->all();

            foreach ($pointRedemptions as $pr) {
                $code = strtoupper(trim($pr->voucher_code ?? ''));
                if ($code && in_array($code, $seenCodes)) {
                    continue;
                }
                $type = $pr->type ?? 'Voucher';
                $category = 'Souvenirs';
                if ($type === 'environmental_fee') {
                    $category = 'Activities';
                }
                $merged->push((object) [
                    'id' => $pr->id,
                    'voucher_id' => null,
                    'type' => $type,
                    'points_cost' => (int) ($pr->points_cost ?? 0),
                    'voucher_code' => $pr->voucher_code,
                    'status' => $pr->status ?? 'active',
                    'redeemed_at' => null,
                    'created_at' => $pr->created_at ? $pr->created_at->toIso8601String() : now()->toIso8601String(),
                    'partner_establishment' => 'LUPTO Eco Tourism',
                    'description' => $type === 'pasalubong_discount' ? '₱50 Pasalubong Discount' : ($type === 'environmental_fee' ? 'Waived Environmental Fee' : 'Tourist Voucher'),
                    'discount_value' => '50.00',
                    'discount_type' => 'fixed',
                    'image' => null,
                    'terms_and_conditions' => null,
                    'expires_at' => null,
                    'expiration_type' => 'until_unusable',
                    'category' => $category
                ]);
            }

            return $merged->sortByDesc('created_at')->values();
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('PointsController getUserVouchers error: ' . $e->getMessage());
            return collect();
        }
    }
}

