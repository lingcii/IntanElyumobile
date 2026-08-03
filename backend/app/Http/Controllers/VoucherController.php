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
                return [
                    'id' => $v->id,
                    'title' => $v->voucher_name,
                    'category' => $v->discount_type ?: 'General',
                    'partner' => $v->partner_establishment ?: ($v->municipality ? $v->municipality->name . ' Tourism' : 'LUPTO Tourism'),
                    'location' => $v->municipality ? $v->municipality->name . ', La Union' : 'La Union',
                    'badge' => $v->discount_value ? ($v->discount_type === 'percentage' ? $v->discount_value . '% OFF' : '₱' . $v->discount_value . ' OFF') : 'PROMO',
                    'pointsCost' => $v->required_points ?: 100,
                    'code' => $v->voucher_code,
                    'expires' => $v->expires_at ? $v->expires_at->format('Y-m-d') : null,
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

        return response()->json([
            'status' => 'success',
            'message' => 'Voucher claimed successfully!',
            'data' => $redemption
        ]);
    }
}
