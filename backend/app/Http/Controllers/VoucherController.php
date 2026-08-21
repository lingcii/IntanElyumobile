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

                return [
                    'id' => $v->id,
                    'title' => $v->voucher_name,
                    'category' => $category,
                    'partner' => $v->partner_establishment ?: ($v->municipality ? $v->municipality->name . ' Tourism' : 'LUPTO Tourism'),
                    'location' => $v->municipality ? $v->municipality->name . ', La Union' : 'San Juan, La Union',
                    'badge' => $badge,
                    'pointsCost' => (int) ($v->required_points ?: 100),
                    'code' => $v->voucher_code,
                    'expires' => $v->expires_at ? $v->expires_at->format('Y-m-d') : '2026-12-31',
                    'description' => $v->description ?: $v->terms_and_conditions ?: 'Present voucher code at merchant checkout.',
                    'image' => $v->image,
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
     * Redeem a specific admin voucher by ID using user points.
     */
    public function redeemVoucher(Request $request): JsonResponse
    {
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

        $cost = (int) ($voucher->required_points ?: 100);

        // Calculate points balance
        $earned = (int) UserPoint::where('user_id', $user->id)->sum('points');
        $redeemed = (int) PointRedemption::where('user_id', $user->id)->sum('points_cost');
        $balance = max(0, $earned - $redeemed);

        if ($balance < $cost) {
            return response()->json([
                'status' => 'error',
                'message' => "Insufficient points. You need {$cost} PTS but currently have {$balance} PTS."
            ], 400);
        }

        $redemption = DB::transaction(function() use ($user, $voucher, $cost) {
            // Deduct remaining quantity if tracked
            if ($voucher->remaining_quantity > 0) {
                $voucher->decrement('remaining_quantity');
                $voucher->increment('redeemed_quantity');
            }

            return PointRedemption::create([
                'user_id' => $user->id,
                'type' => $voucher->voucher_name,
                'points_cost' => $cost,
                'voucher_code' => $voucher->voucher_code ?: ('ELYU-' . strtoupper(Str::random(8))),
                'status' => 'active'
            ]);
        });

        // Trigger notification
        \App\Models\Notification::createSafely(
            $user->id,
            'favorite_update',
            '🎟️ Voucher Redeemed!',
            "Claimed '{$voucher->voucher_name}' ({$redemption->voucher_code}). Present code at merchant checkout!",
            ['action_url' => '/discount']
        );

        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('activity_logs')) {
                \App\Models\ActivityLog::create([
                    'user_id'    => $user->id,
                    'action'     => 'Voucher Redeemed',
                    'details'    => "Redeemed {$cost} points for '{$voucher->voucher_name}' (Code: {$redemption->voucher_code})",
                    'ip_address' => $request->ip() ?? '127.0.0.1',
                ]);
            }
        } catch (\Throwable $e) {}

        $newBalance = max(0, $balance - $cost);

        return response()->json([
            'status' => 'success',
            'message' => 'Voucher claimed successfully!',
            'new_balance' => $newBalance,
            'data' => $redemption
        ]);
    }
}
