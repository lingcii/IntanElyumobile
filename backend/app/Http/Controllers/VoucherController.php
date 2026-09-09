<?php

namespace App\Http\Controllers;

use App\Models\Voucher;
use App\Models\PointRedemption;
use App\Models\UserPoint;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class VoucherController extends Controller
{
    /**
     * GET /api/vouchers
     * Returns all active vouchers created by Admin in the database.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Voucher::with('municipality');

            // Include active vouchers created by Admin in Railway DB
            $query->where(function($q) {
                $q->where('status', 'active')
                  ->orWhereNull('status');
            });

            $vouchers = $query->latest()->get();

            // Format response for Mobile app compatibility
            $formatted = $vouchers->map(function($v) {
                $category = 'Food & Dining';
                $text = strtolower(($v->voucher_name ?? '') . ' ' . ($v->partner_establishment ?? '') . ' ' . ($v->description ?? ''));
                if (str_contains($text, 'surf') || str_contains($text, 'activity') || str_contains($text, 'tour') || str_contains($text, 'hike') || str_contains($text, 'rental') || str_contains($text, 'lesson')) {
                    $category = 'Activities';
                } elseif (str_contains($text, 'hotel') || str_contains($text, 'resort') || str_contains($text, 'stay') || str_contains($text, 'room') || str_contains($text, 'inn') || str_contains($text, 'villa')) {
                    $category = 'Accommodations';
                } elseif (str_contains($text, 'pasalubong') || str_contains($text, 'souvenir') || str_contains($text, 'native') || str_contains($text, 'wine') || str_contains($text, 'craft') || str_contains($text, 'pass')) {
                    $category = 'Souvenirs';
                } elseif (str_contains($text, 'coffee') || str_contains($text, 'dining') || str_contains($text, 'food') || str_contains($text, 'restaurant') || str_contains($text, 'cafe') || str_contains($text, 'spice') || str_contains($text, 'dish') || str_contains($text, 'snack')) {
                    $category = 'Food & Dining';
                }

                $badge = 'PROMO';
                if ($v->discount_value) {
                    $badge = $v->discount_type === 'percentage' ? $v->discount_value . '% OFF' : '₱' . $v->discount_value . ' OFF';
                }

                // Resolve image to Cloudflare R2 URL
                $r2PublicUrl = rtrim(env('CLOUDFLARE_R2_URL', 'https://pub-268a50c87a9249ccbf90d35e77ddc65b.r2.dev'), '/');
                $imageUrl = null;
                if (!empty($v->image)) {
                    if (str_starts_with($v->image, 'http://') || str_starts_with($v->image, 'https://')) {
                        $imageUrl = $v->image;
                    } else {
                        $imageUrl = $r2PublicUrl . '/' . ltrim($v->image, '/');
                    }
                } else {
                    $muniName = $v->municipality ? $v->municipality->name : null;
                    if (!$muniName) {
                        $partnerLower = strtolower($v->partner_establishment ?? '');
                        $muniList = ['san fernando', 'san gabriel', 'san juan', 'santo tomas', 'agoo', 'aringay', 'bacnotan', 'bagulin', 'balaoan', 'bangar', 'bauang', 'burgos', 'caba', 'luna', 'naguilian', 'pugo', 'rosario', 'santol', 'sudipen', 'tubao'];
                        foreach ($muniList as $m) {
                            if (str_contains($partnerLower, $m)) {
                                $muniName = strtoupper(str_replace(' ', '-', $m));
                                break;
                            }
                        }
                    }
                    if ($muniName) {
                        $slug = strtoupper(str_replace(' ', '-', trim($muniName)));
                        $imageUrl = $r2PublicUrl . '/logo/' . $slug . '.png';
                    } else {
                        $imageUrl = $r2PublicUrl . '/logo/LUPTO.png';
                    }
                }

                $isExpired = $v->expires_at ? $v->expires_at->isPast() : false;

                return [
                    'id' => $v->id,
                    'title' => $v->voucher_name,
                    'category' => $category,
                    'partner' => $v->partner_establishment ?: ($v->municipality ? $v->municipality->name . ' Tourism' : 'LUPTO Tourism'),
                    'location' => $v->municipality ? $v->municipality->name . ', La Union' : 'San Juan, La Union',
                    'badge' => $badge,
                    'xpCost' => (int) ($v->required_points ?: 100),
                    'pointsCost' => (int) ($v->required_points ?: 100),
                    'points' => (int) ($v->required_points ?: 100),
                    'required_points' => (int) ($v->required_points ?: 100),
                    'code' => $v->voucher_code,
                    'expires' => $v->expires_at ? $v->expires_at->toIso8601String() : '2026-12-31T23:59:59Z',
                    'expires_formatted' => $v->expires_at ? $v->expires_at->format('M d, Y') : 'Dec 31, 2026',
                    'is_expired' => $isExpired,
                    'description' => $v->description ?: $v->terms_and_conditions ?: 'Present voucher code at merchant checkout.',
                    'image' => $imageUrl,
                    'available_quantity' => $v->available_quantity,
                    'remaining_quantity' => $v->remaining_quantity,
                ];
            });

            return response()->json([
                'status' => 'success',
                'data' => $formatted,
                'raw' => $vouchers
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve vouchers.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * POST /api/tourist/points/redeem-voucher
     * Redeem a specific admin voucher by ID using user Points.
     */
    public function redeemVoucher(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'voucher_id' => 'required|integer',
            ]);

            $user = $request->user();
            if (!$user) {
                return response()->json(['status' => 'error', 'message' => 'Unauthenticated.'], 401);
            }

            $voucher = Voucher::find($request->voucher_id);
            if (!$voucher || $voucher->status !== 'active') {
                return response()->json(['status' => 'error', 'message' => 'Voucher is inactive or unavailable.'], 404);
            }

            if ($voucher->expires_at && $voucher->expires_at->isPast()) {
                return response()->json(['status' => 'error', 'message' => 'Voucher has expired.'], 400);
            }

            if ($voucher->remaining_quantity !== null && $voucher->remaining_quantity <= 0) {
                return response()->json(['status' => 'error', 'message' => 'Voucher is fully claimed.'], 400);
            }

            // Check if user has reached max claims for this voucher
            $maxPerUser = (int) ($voucher->maximum_redemption_per_user ?: 1);
            $alreadyClaimedCount = PointRedemption::where('user_id', $user->id)
                ->where(function($q) use ($voucher) {
                    $q->where('type', $voucher->voucher_name);
                    if ($voucher->voucher_code) {
                        $q->orWhere('voucher_code', 'LIKE', $voucher->voucher_code . '%');
                    }
                })
                ->count();

            if ($alreadyClaimedCount >= $maxPerUser) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'You have already redeemed this voucher.'
                ], 400);
            }

            $cost = (int) ($voucher->required_points ?: 100);

            // Get user's Points balance directly from users table
            $balance = (int) ($user->points ?? 0);

            if ($balance < $cost) {
                return response()->json([
                    'status' => 'error',
                    'message' => "Insufficient points. You need {$cost} Points but currently have {$balance} Points."
                ], 400);
            }

            // Generate guaranteed unique redemption voucher code (avoids unique constraint violation)
            $baseCode = $voucher->voucher_code ? strtoupper(trim($voucher->voucher_code)) : 'ELYU';
            $uniqueCode = '';
            $attempts = 0;
            do {
                $suffix = strtoupper(Str::random(4));
                $uniqueCode = "{$baseCode}-{$suffix}";
                $attempts++;
            } while (PointRedemption::where('voucher_code', $uniqueCode)->exists() && $attempts < 10);

            if ($attempts >= 10) {
                $uniqueCode = 'ELYU-' . strtoupper(Str::random(10));
            }

            $redemption = DB::transaction(function() use ($user, $voucher, $cost, $uniqueCode) {
                // Deduct remaining quantity if tracked
                if ($voucher->remaining_quantity > 0) {
                    $voucher->decrement('remaining_quantity');
                    $voucher->increment('redeemed_quantity');
                }

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

                return PointRedemption::create([
                    'user_id' => $user->id,
                    'type' => $voucher->voucher_name,
                    'points_cost' => $cost,
                    'voucher_code' => $uniqueCode,
                    'status' => 'active'
                ]);
            });

            // Trigger notification safely
            \App\Models\Notification::createSafely(
                $user->id,
                'favorite_update',
                'Voucher Redeemed!',
                "Claimed '{$voucher->voucher_name}' (Code: {$uniqueCode}). Present code at merchant checkout!",
                ['action_url' => '/discount']
            );

            try {
                if (\Illuminate\Support\Facades\Schema::hasTable('activity_logs')) {
                    \App\Models\ActivityLog::create([
                        'user_id'    => $user->id,
                        'action'     => 'Voucher Redeemed',
                        'details'    => "Redeemed {$cost} Points for '{$voucher->voucher_name}' (Code: {$uniqueCode})",
                        'ip_address' => $request->ip() ?? '127.0.0.1',
                    ]);
                }
            } catch (\Throwable $e) {}

            $newPoints = max(0, $balance - $cost);

            return response()->json([
                'status' => 'success',
                'message' => 'Voucher claimed successfully!',
                'new_balance' => $newPoints,
                'points' => $newPoints,
                'xp' => (int) ($user->xp ?? 0),
                'level' => (int) ($user->level ?? 1),
                'promo_code' => $voucher->voucher_code,
                'claim_code' => $uniqueCode,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'points' => $newPoints,
                    'xp' => (int) ($user->xp ?? 0),
                    'level' => (int) ($user->level ?? 1),
                ],
                'data' => $redemption
            ]);
        } catch (\Illuminate\Validation\ValidationException $ve) {
            return response()->json([
                'status' => 'error',
                'message' => $ve->validator->errors()->first() ?: 'Validation failed.',
                'errors' => $ve->errors()
            ], 422);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Voucher redemption error: ' . $e->getMessage(), [
                'exception' => $e,
                'request' => $request->all()
            ]);
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to redeem voucher. Please try again.'
            ], 500);
        }
    }
}
