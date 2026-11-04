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
     * Returns all active and upcoming vouchers created by Admin in the database.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            // 1. Automatically activate upcoming vouchers whose valid_from has arrived or is null
            try {
                Voucher::where(function($q) {
                    $q->where(DB::raw('LOWER(status)'), 'upcoming');
                })->where(function ($q) {
                    $q->whereNull('valid_from')
                      ->orWhere('valid_from', '<=', now());
                })->update(['status' => 'active']);
            } catch (\Throwable $ignored) {}

            $query = Voucher::with('municipality');

            // 2. Include active & upcoming vouchers created by Admin in Railway DB
            // (Exclude only explicitly deleted, inactive, or archived vouchers)
            $query->where(function($q) {
                $q->whereIn(DB::raw("LOWER(COALESCE(status, 'active'))"), ['active', 'upcoming'])
                  ->orWhereNull('status')
                  ->orWhere('status', '');
            })->where(function($q) {
                $q->whereNotIn(DB::raw("LOWER(COALESCE(status, ''))"), ['inactive', 'disabled', 'archived']);
            });

            $vouchers = $query->latest()->get();

            // Format response for Mobile app compatibility with full LUPTO voucher fields
            $formatted = $vouchers->map(function($v) {
                // Partner establishment resolution (support single field or multi-establishment JSON array)
                $partner = $v->partner_establishment;
                $partnerEstablishments = [];
                if (!empty($v->partner_establishments)) {
                    $estArr = is_array($v->partner_establishments) ? $v->partner_establishments : json_decode($v->partner_establishments, true);
                    if (!empty($estArr) && is_array($estArr)) {
                        $partnerEstablishments = array_values(array_filter($estArr));
                        if (empty($partner) && count($partnerEstablishments) > 0) {
                            $partner = implode(', ', $partnerEstablishments);
                        }
                    }
                }
                if (!empty($partner) && empty($partnerEstablishments)) {
                    $partnerEstablishments = array_values(array_filter(array_map('trim', explode(',', $partner))));
                }
                if (empty($partner)) {
                    $partner = $v->municipality ? $v->municipality->name . ' Tourism' : 'LUPTO Tourism';
                    $partnerEstablishments = [$partner];
                }

                // Municipality resolution (support single id or multi-municipality JSON array)
                $muni = $v->municipality;
                $municipalityNames = [];
                if (!empty($v->municipality_ids)) {
                    $muniIds = is_array($v->municipality_ids) ? $v->municipality_ids : json_decode($v->municipality_ids, true);
                    if (!empty($muniIds) && is_array($muniIds)) {
                        $munis = \App\Models\Municipality::whereIn('id', $muniIds)->pluck('name')->toArray();
                        if (!empty($munis)) {
                            $municipalityNames = $munis;
                        }
                    }
                }
                if (empty($municipalityNames) && $muni) {
                    $municipalityNames = [$muni->name];
                }

                $location = count($municipalityNames) > 0 ? implode(', ', $municipalityNames) . ', La Union' : ($muni ? $muni->name . ', La Union' : 'La Union');

                $category = 'Food & Dining';
                $text = strtolower(($v->voucher_name ?? '') . ' ' . ($partner ?? '') . ' ' . ($v->description ?? '') . ' ' . ($v->terms_and_conditions ?? ''));
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
                if ($v->discount_value !== null && $v->discount_value > 0) {
                    $val = (float) $v->discount_value == (int) $v->discount_value ? (int) $v->discount_value : $v->discount_value;
                    $badge = (str_contains(strtolower($v->discount_type ?? ''), 'percent')) ? "{$val}% OFF" : "₱{$val} OFF";
                } elseif (!empty($v->discount_type) && str_starts_with(strtolower($v->discount_type), 'custom:')) {
                    $badge = strtoupper(trim(substr($v->discount_type, 7)));
                } elseif (!empty($v->discount_type)) {
                    $cleanType = strtoupper(trim(str_replace(['_', '-'], ' ', $v->discount_type)));
                    $badge = $cleanType ?: 'PROMO';
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
                    $muniName = count($municipalityNames) > 0 ? $municipalityNames[0] : ($muni ? $muni->name : null);
                    if (!$muniName) {
                        $partnerLower = strtolower($partner ?? '');
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

                // Expiration type resolution matching LUPTO Admin options
                $expType = strtolower($v->expiration_type ?? 'date');
                $isNoExpiration = in_array($expType, ['no_expiry_claim', 'no_expiry_usable', 'no_expiration_claim', 'no_expiration_usable']);
                $isExpired = false;
                $expiresFormatted = 'No Expiration';
                $expiresIso = $v->expires_at ? $v->expires_at->toIso8601String() : null;

                if ($isNoExpiration) {
                    if (str_contains($expType, 'claim')) {
                        $expiresFormatted = 'Until Fully Claimed';
                    } else {
                        $expiresFormatted = 'No Expiration';
                    }
                    $isExpired = false;
                } else {
                    $isExpired = $v->expires_at ? $v->expires_at->isPast() : false;
                    $expiresFormatted = $v->expires_at ? $v->expires_at->format('M d, Y') : 'No Expiry';
                }

                $isUpcoming = $v->valid_from ? $v->valid_from->isFuture() : false;
                $computedStatus = $isExpired ? 'expired' : ($isUpcoming ? 'upcoming' : 'active');
                $isOutOfStock = ($v->remaining_quantity !== null && $v->remaining_quantity <= 0);
                $isMabanag = str_contains(strtolower($partner ?? ''), 'mabanag') || (isset($v->partner_establishments) && str_contains(strtolower(is_array($v->partner_establishments) ? implode(' ', $v->partner_establishments) : (string) $v->partner_establishments), 'mabanag'));

                return [
                    'id'                       => $v->id,
                    'title'                    => $v->voucher_name,
                    'category'                 => $category,
                    'partner'                  => $partner,
                    'partner_establishments'   => $partnerEstablishments,
                    'location'                 => $location,
                    'municipalities'           => $municipalityNames,
                    'badge'                    => $badge,
                    'discount_type'            => $v->discount_type ?? 'percentage',
                    'discount_value'           => (float) ($v->discount_value ?? 0),
                    'xpCost'                   => (int) ($v->required_points ?: 100),
                    'pointsCost'               => (int) ($v->required_points ?: 100),
                    'points'                   => (int) ($v->required_points ?: 100),
                    'required_points'          => (int) ($v->required_points ?: 100),
                    'code'                     => $v->voucher_code,
                    'valid_from'               => $v->valid_from ? $v->valid_from->toIso8601String() : null,
                    'valid_from_formatted'     => $v->valid_from ? $v->valid_from->format('M d, Y') : null,
                    'created_at'               => $v->created_at ? $v->created_at->toIso8601String() : null,
                    'updated_at'               => $v->updated_at ? $v->updated_at->toIso8601String() : null,
                    'expiration_type'          => $expType,
                    'is_no_expiration'         => $isNoExpiration,
                    'expires'                  => $expiresIso,
                    'expires_formatted'        => $expiresFormatted,
                    'is_expired'               => $isExpired,
                    'is_upcoming'              => $isUpcoming,
                    'is_out_of_stock'          => $isOutOfStock,
                    'id_needed'                => (bool) ($v->id_needed ?? false),
                    'terms_and_conditions'     => $v->terms_and_conditions ?: null,
                    'status'                   => $computedStatus,
                    'description'              => $v->description ?: $v->terms_and_conditions ?: 'Present voucher code at merchant checkout.',
                    'image'                    => $imageUrl,
                    'available_quantity'       => $v->available_quantity,
                    'remaining_quantity'       => $v->remaining_quantity,
                    'redeemed_quantity'        => $v->redeemed_quantity,
                    'is_mabanag'               => $isMabanag,
                ];
            });

            return response()->json([
                'status' => 'success',
                'data'   => $formatted,
                'raw'    => $vouchers
            ])->header('Cache-Control', 'public, max-age=120, stale-while-revalidate=300');
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
            if (!$voucher) {
                return response()->json(['status' => 'error', 'message' => 'Voucher not found.'], 404);
            }

            // Auto-activate if valid_from has arrived or is null
            if (strtolower($voucher->status ?? '') === 'upcoming' && ($voucher->valid_from === null || $voucher->valid_from->isPast())) {
                $voucher->status = 'active';
                $voucher->save();
            }

            $statusLower = strtolower($voucher->status ?? 'active');
            if (in_array($statusLower, ['inactive', 'disabled', 'archived'])) {
                return response()->json(['status' => 'error', 'message' => 'Voucher is inactive or unavailable.'], 404);
            }

            if ($voucher->valid_from && $voucher->valid_from->isFuture()) {
                $timeStr = $voucher->valid_from->format('M d, Y g:i A');
                return response()->json(['status' => 'error', 'message' => "This voucher is upcoming and will be available on {$timeStr}."], 400);
            }

            if ($voucher->expires_at && $voucher->expires_at->isPast()) {
                return response()->json(['status' => 'error', 'message' => 'Voucher has expired.'], 400);
            }

            if ($voucher->remaining_quantity !== null && $voucher->remaining_quantity <= 0) {
                return response()->json(['status' => 'error', 'message' => 'Voucher is fully claimed.'], 400);
            }

            // Enforce one-time claim per tourist account per voucher (ignoring cancelled claims)
            if (\Illuminate\Support\Facades\Schema::hasTable('voucher_redemptions')) {
                $alreadyClaimed = \Illuminate\Support\Facades\DB::table('voucher_redemptions')
                    ->where('user_id', $user->id)
                    ->where('voucher_id', $voucher->id)
                    ->whereIn('status', ['claimed', 'active', 'redeemed', 'used', 'completed'])
                    ->exists();

                if ($alreadyClaimed) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'You have already claimed this voucher. Each voucher is valid for one-time claim only.'
                    ], 400);
                }
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
            $letters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
            do {
                $suffix = '';
                for ($i = 0; $i < 4; $i++) {
                    $suffix .= $letters[random_int(0, 25)];
                }
                $uniqueCode = "{$baseCode}-{$suffix}";
                $attempts++;

                $existsInVoucherTbl = \Illuminate\Support\Facades\Schema::hasTable('voucher_redemptions') &&
                    \Illuminate\Support\Facades\DB::table('voucher_redemptions')->where('redemption_code', $uniqueCode)->exists();
                $existsInPointTbl = \Illuminate\Support\Facades\Schema::hasTable('point_redemptions') &&
                    PointRedemption::where('voucher_code', $uniqueCode)->exists();
            } while (($existsInVoucherTbl || $existsInPointTbl) && $attempts < 10);

            if ($attempts >= 10) {
                $suffix = '';
                for ($i = 0; $i < 4; $i++) {
                    $suffix .= $letters[random_int(0, 25)];
                }
                $uniqueCode = 'ELYU-' . time() . "-{$suffix}";
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

                // A claimed voucher lasts three days, but never beyond the campaign deadline.
                $voucherExpiresAt = now()->addDays(3);
                if ($voucher->expires_at && $voucher->expires_at->isFuture() && $voucher->expires_at->lt($voucherExpiresAt)) {
                    $voucherExpiresAt = $voucher->expires_at->copy();
                }

                // The QR session starts only after the tourist taps Use.
                $qrExpiresAt = null;
                $qrToken = null;

                // 1. Sync with voucher_redemptions (used by web admin / partner scanning)
                if (\Illuminate\Support\Facades\Schema::hasTable('voucher_redemptions')) {
                    try {
                        \Illuminate\Support\Facades\DB::table('voucher_redemptions')->insert([
                            'voucher_id'         => $voucher->id,
                            'user_id'            => $user->id,
                            'redemption_code'    => $uniqueCode,
                            'qr_token'           => $qrToken,
                            'points_used'        => $cost,
                            'status'             => 'claimed',
                            'voucher_expires_at' => $voucherExpiresAt,
                            'qr_expires_at'      => $qrExpiresAt,
                            'claimed_at'         => now(),
                            'redeemed_at'        => null,
                            'cancelled_at'       => null,
                            'created_at'         => now(),
                            'updated_at'         => now(),
                        ]);
                    } catch (\Throwable $e) {
                        \Illuminate\Support\Facades\Log::warning('Could not record into voucher_redemptions: ' . $e->getMessage());
                    }
                }

                // 2. Sync with point_redemptions (used by mobile views & PointRedemption model)
                $pointRedemption = null;
                if (\Illuminate\Support\Facades\Schema::hasTable('point_redemptions')) {
                    try {
                        $pointRedemption = PointRedemption::create([
                            'user_id'            => $user->id,
                            'voucher_id'         => $voucher->id,
                            'type'               => $voucher->voucher_name,
                            'points_cost'        => $cost,
                            'voucher_code'       => $uniqueCode,
                            'qr_token'           => $qrToken,
                            'status'             => 'claimed',
                            'voucher_expires_at' => $voucherExpiresAt,
                            'qr_expires_at'      => $qrExpiresAt,
                            'claimed_at'         => now(),
                        ]);
                    } catch (\Throwable $e) {
                        \Illuminate\Support\Facades\Log::warning('Could not record into point_redemptions: ' . $e->getMessage());
                    }
                }

                if (!$pointRedemption) {
                    $pointRedemption = (object) [
                        'user_id'            => $user->id,
                        'voucher_id'         => $voucher->id,
                        'type'               => $voucher->voucher_name,
                        'points_cost'        => $cost,
                        'voucher_code'       => $uniqueCode,
                        'qr_token'           => $qrToken,
                        'status'             => 'claimed',
                        'voucher_expires_at' => $voucherExpiresAt,
                        'qr_expires_at'      => $qrExpiresAt,
                        'claimed_at'         => now(),
                    ];
                }

                return [
                    'redemption'         => $pointRedemption,
                    'qr_token'           => $qrToken,
                    'voucher_expires_at' => $voucherExpiresAt,
                    'qr_expires_at'      => $qrExpiresAt,
                ];
            });

            // Trigger notification safely
            \App\Models\Notification::createSafely(
                $user->id,
                'favorite_update',
                'Voucher Claimed!',
                "Claimed '{$voucher->voucher_name}'. Tap Use when you are ready to show the 20-minute QR pass.",
                ['action_url' => '/discount']
            );

            try {
                if (\Illuminate\Support\Facades\Schema::hasTable('activity_logs')) {
                    \App\Models\ActivityLog::create([
                        'user_id'    => $user->id,
                        'action'     => 'Voucher Claimed',
                        'details'    => "Claimed '{$voucher->voucher_name}' for {$cost} Points (Code: {$uniqueCode})",
                        'ip_address' => $request->ip() ?? '127.0.0.1',
                    ]);
                }
            } catch (\Throwable $e) {}

            $user->refresh();
            $newPoints = (int) ($user->points ?? max(0, $balance - $cost));
            \Illuminate\Support\Facades\Cache::forget("rank:user:{$user->id}");

            return response()->json([
                'status'             => 'success',
                'message'            => 'Voucher claimed successfully!',
                'new_balance'        => $newPoints,
                'points'             => $newPoints,
                'xp'                 => (int) ($user->xp ?? 0),
                'level'              => (int) ($user->level ?? 1),
                'promo_code'         => $voucher->voucher_code,
                'claim_code'         => $uniqueCode,
                'qr_token'           => $redemption['qr_token'],
                'voucher_expires_at' => $redemption['voucher_expires_at'] ? $redemption['voucher_expires_at']->toIso8601String() : null,
                'qr_expires_at'      => $redemption['qr_expires_at'] ? $redemption['qr_expires_at']->toIso8601String() : null,
                'voucher_status'     => 'claimed',
                'user'               => [
                    'id'     => $user->id,
                    'name'   => $user->name,
                    'points' => $newPoints,
                    'xp'     => (int) ($user->xp ?? 0),
                    'level'  => (int) ($user->level ?? 1),
                ],
                'data'               => $redemption['redemption']
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

    /**
     * GET /api/tourist/redemptions/{code}/status
     * Live status check to see if voucher/merchandise has been scanned & redeemed by partner staff (e.g. Mabanag Hall),
     * cancelled, used, or automatically expired.
     */
    public function checkRedemptionStatus(Request $request, string $code): JsonResponse
    {
        try {
            $user = $request->user();
            $voucherRecord = null;
            $pointRecord = null;

            if (\Illuminate\Support\Facades\Schema::hasTable('voucher_redemptions')) {
                $query = \Illuminate\Support\Facades\DB::table('voucher_redemptions')
                    ->where(function ($q) use ($code) {
                        $q->where('redemption_code', $code)
                          ->orWhere('qr_token', $code);
                    });

                if ($user) {
                    $query->where('user_id', $user->id);
                }

                $voucherRecord = $query->first();
            }

            if (\Illuminate\Support\Facades\Schema::hasTable('point_redemptions')) {
                $query = \Illuminate\Support\Facades\DB::table('point_redemptions')
                    ->where(function ($q) use ($code) {
                        $q->where('voucher_code', $code)
                          ->orWhere('qr_token', $code);
                    });
                if ($user) {
                    $query->where('user_id', $user->id);
                }
                $pointRecord = $query->first();
            }

            if (!$voucherRecord && !$pointRecord) {
                return response()->json([
                    'status' => 'not_found',
                    'message' => 'Redemption record not found.'
                ], 404);
            }

            $record = $voucherRecord ?: $pointRecord;

            // Normalize current status across both tables
            $vStatus = strtolower($voucherRecord->status ?? '');
            $pStatus = strtolower($pointRecord->status ?? '');
            
            $isUsed = in_array($vStatus, ['redeemed', 'used', 'completed']) || in_array($pStatus, ['redeemed', 'used', 'completed']);
            $isActive = !$isUsed && (($vStatus === 'active') || ($pStatus === 'active'));
            $isCancelled = ($vStatus === 'cancelled') || ($pStatus === 'cancelled');
            $isExpired = ($vStatus === 'expired') || ($pStatus === 'expired');

            // Check auto-expiration if not used and not cancelled
            $voucherExpiresAt = $record->voucher_expires_at ? \Carbon\Carbon::parse($record->voucher_expires_at) : null;
            if (!$isUsed && !$isCancelled && $voucherExpiresAt && now()->gte($voucherExpiresAt)) {
                $isExpired = true;
                if ($voucherRecord && $voucherRecord->status !== 'expired') {
                    \Illuminate\Support\Facades\DB::table('voucher_redemptions')->where('id', $voucherRecord->id)->update([
                        'status' => 'expired',
                        'updated_at' => now(),
                    ]);
                }
                if ($pointRecord && $pointRecord->status !== 'expired') {
                    \Illuminate\Support\Facades\DB::table('point_redemptions')->where('id', $pointRecord->id)->update([
                        'status' => 'expired',
                        'updated_at' => now(),
                    ]);
                }
            }

            $qrExpiresAt = $record->qr_expires_at ? \Carbon\Carbon::parse($record->qr_expires_at) : null;
            $activeQrToken = $record->qr_token ?? null;
            if (!$isUsed && !$isCancelled && !$isExpired && $isActive && (!$qrExpiresAt || now()->gte($qrExpiresAt))) {
                $activeQrToken = 'elyu_qr_' . \Illuminate\Support\Str::random(32);
                $qrExpiresAt = $qrExpiresAt ?: now();
                while ($qrExpiresAt->lte(now())) {
                    $qrExpiresAt->addMinutes(20);
                }
                if ($voucherExpiresAt && $qrExpiresAt->gt($voucherExpiresAt)) {
                    $qrExpiresAt = $voucherExpiresAt->copy();
                }
                foreach ([['voucher_redemptions', $voucherRecord], ['point_redemptions', $pointRecord]] as [$table, $row]) {
                    if ($row) {
                        \Illuminate\Support\Facades\DB::table($table)->where('id', $row->id)->update([
                            'status' => 'active',
                            'qr_token' => $activeQrToken,
                            'qr_expires_at' => $qrExpiresAt,
                            'updated_at' => now(),
                        ]);
                    }
                }
            }

            $finalStatus = $isUsed ? 'used' : ($isCancelled ? 'cancelled' : ($isExpired ? 'expired' : ($isActive ? 'active' : 'claimed')));
            $redeemedRecord = in_array($vStatus, ['redeemed', 'used', 'completed']) ? $voucherRecord : ($pointRecord ?: $record);

            $partnerName = 'Official Partner Merchant';
            if (!empty($record->redeemed_by_partner_id)) {
                $partner = \Illuminate\Support\Facades\DB::table('partner_establishments')->where('id', $record->redeemed_by_partner_id)->first();
                if ($partner) {
                    $partnerName = $partner->name;
                }
            } elseif (!empty($record->voucher_id)) {
                $voucherModel = Voucher::find($record->voucher_id);
                if ($voucherModel && !empty($voucherModel->partner_establishment)) {
                    $partnerName = $voucherModel->partner_establishment;
                }
            }

            return response()->json([
                'status'               => 'success',
                'voucher_status'       => $finalStatus,
                'redemption_status'    => $isUsed ? 'redeemed' : $finalStatus,
                'is_redeemed'          => $isUsed,
                'is_used'              => $isUsed,
                'is_cancelled'         => $isCancelled,
                'is_expired'           => $isExpired,
                'voucher_expires_at'   => $voucherExpiresAt ? $voucherExpiresAt->toIso8601String() : null,
                'qr_expires_at'        => $isActive && $qrExpiresAt ? $qrExpiresAt->toIso8601String() : null,
                'qr_token'             => $isActive ? $activeQrToken : null,
                'redeemed_at'          => $redeemedRecord->redeemed_at ?? ($redeemedRecord->updated_at ?? null),
                'cancelled_at'         => $record->cancelled_at ?? null,
                'redeemed_by_partner'  => $partnerName,
                'code'                 => $code
            ])->header('Cache-Control', 'no-store, no-cache, must-revalidate');
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * POST /api/tourist/vouchers/{code}/use
     * Validate validity and start a 20-minute QR session.
     */
    public function useVoucher(Request $request, string $code): JsonResponse
    {
        try {
            $user = $request->user();
            if (!$user) {
                return response()->json(['status' => 'error', 'message' => 'Unauthenticated.'], 401);
            }

            $voucherRecord = null;
            if (\Illuminate\Support\Facades\Schema::hasTable('voucher_redemptions')) {
                $voucherRecord = \Illuminate\Support\Facades\DB::table('voucher_redemptions')
                    ->where('user_id', $user->id)
                    ->where(function ($q) use ($code) {
                        $q->where('redemption_code', $code)
                          ->orWhere('qr_token', $code);
                    })->first();
            }

            $pointRecord = null;
            if (\Illuminate\Support\Facades\Schema::hasTable('point_redemptions')) {
                $pointRecord = \Illuminate\Support\Facades\DB::table('point_redemptions')
                    ->where('user_id', $user->id)
                    ->where(function ($q) use ($code) {
                        $q->where('voucher_code', $code)
                          ->orWhere('qr_token', $code);
                    })->first();
            }

            if (!$voucherRecord && !$pointRecord) {
                return response()->json(['status' => 'error', 'message' => 'Claimed voucher record not found.'], 404);
            }

            $record = $voucherRecord ?: $pointRecord;
            $currentStatus = strtolower($record->status ?? 'claimed');

            if (in_array($currentStatus, ['used', 'redeemed', 'completed'])) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'This voucher has already been used and redeemed.'
                ], 400);
            }

            if ($currentStatus === 'cancelled') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'This voucher claim was cancelled and cannot be used.'
                ], 400);
            }

            // Server-side timestamp validation: Check voucher expiration
            $voucherExpiresAt = $record->voucher_expires_at ? \Carbon\Carbon::parse($record->voucher_expires_at) : null;
            if ($voucherExpiresAt && now()->gte($voucherExpiresAt)) {
                if ($voucherRecord) {
                    \Illuminate\Support\Facades\DB::table('voucher_redemptions')->where('id', $voucherRecord->id)->update([
                        'status' => 'expired',
                        'updated_at' => now(),
                    ]);
                }
                if ($pointRecord) {
                    \Illuminate\Support\Facades\DB::table('point_redemptions')->where('id', $pointRecord->id)->update([
                        'status' => 'expired',
                        'updated_at' => now(),
                    ]);
                }

                return response()->json([
                    'status' => 'error',
                    'voucher_status' => 'expired',
                    'message' => 'This voucher has reached its expiration time and cannot be used.'
                ], 400);
            }

            $now = now();
            $existingQrExpiresAt = $record->qr_expires_at ? \Carbon\Carbon::parse($record->qr_expires_at) : null;
            if ($currentStatus === 'active' && !empty($record->qr_token) && $existingQrExpiresAt && $now->lt($existingQrExpiresAt)) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Voucher QR pass is already active.',
                    'voucher_status' => 'active',
                    'qr_token' => $record->qr_token,
                    'qr_expires_at' => $existingQrExpiresAt->toIso8601String(),
                    'voucher_expires_at' => $voucherExpiresAt ? $voucherExpiresAt->toIso8601String() : null,
                    'code' => $code,
                ]);
            }

            $qrToken = 'elyu_qr_' . \Illuminate\Support\Str::random(32);
            $qrExpiresAt = $now->copy()->addMinutes(20);
            if ($voucherExpiresAt && $qrExpiresAt->gt($voucherExpiresAt)) {
                $qrExpiresAt = $voucherExpiresAt->copy();
            }

            if ($voucherRecord) {
                \Illuminate\Support\Facades\DB::table('voucher_redemptions')->where('id', $voucherRecord->id)->update([
                    'status' => 'active',
                    'qr_token' => $qrToken,
                    'qr_expires_at' => $qrExpiresAt,
                    'updated_at' => $now,
                ]);
            }
            if ($pointRecord) {
                \Illuminate\Support\Facades\DB::table('point_redemptions')->where('id', $pointRecord->id)->update([
                    'status' => 'active',
                    'qr_token' => $qrToken,
                    'qr_expires_at' => $qrExpiresAt,
                    'updated_at' => $now,
                ]);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Voucher QR pass is active for 20 minutes.',
                'voucher_status' => 'active',
                'qr_token' => $qrToken,
                'qr_expires_at' => $qrExpiresAt->toIso8601String(),
                'voucher_expires_at' => $voucherExpiresAt ? $voucherExpiresAt->toIso8601String() : null,
                'code' => $code,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to use voucher: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * POST /api/tourist/vouchers/{code}/cancel
     * Cancel a claimed voucher and refund tourist points.
     */
    public function cancelVoucher(Request $request, string $code): JsonResponse
    {
        try {
            $user = $request->user();
            if (!$user) {
                return response()->json(['status' => 'error', 'message' => 'Unauthenticated.'], 401);
            }

            $voucherRecord = null;
            if (\Illuminate\Support\Facades\Schema::hasTable('voucher_redemptions')) {
                $voucherRecord = \Illuminate\Support\Facades\DB::table('voucher_redemptions')
                    ->where('user_id', $user->id)
                    ->where(function ($q) use ($code) {
                        $q->where('redemption_code', $code)
                          ->orWhere('qr_token', $code);
                    })->first();
            }

            $pointRecord = null;
            if (\Illuminate\Support\Facades\Schema::hasTable('point_redemptions')) {
                $pointRecord = \Illuminate\Support\Facades\DB::table('point_redemptions')
                    ->where('user_id', $user->id)
                    ->where(function ($q) use ($code) {
                        $q->where('voucher_code', $code)
                          ->orWhere('qr_token', $code);
                    })->first();
            }

            if (!$voucherRecord && !$pointRecord) {
                return response()->json(['status' => 'error', 'message' => 'Claimed voucher record not found.'], 404);
            }

            $record = $voucherRecord ?: $pointRecord;
            $currentStatus = strtolower($record->status ?? 'claimed');

            if (in_array($currentStatus, ['used', 'redeemed', 'completed'])) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Cannot cancel a voucher that has already been used.'
                ], 400);
            }

            if ($currentStatus === 'cancelled') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'This voucher claim is already cancelled.'
                ], 400);
            }

            // Server-side timestamp validation: Check voucher expiration
            $voucherExpiresAt = $record->voucher_expires_at ? \Carbon\Carbon::parse($record->voucher_expires_at) : null;
            if ($voucherExpiresAt && now()->gte($voucherExpiresAt)) {
                if ($voucherRecord) {
                    \Illuminate\Support\Facades\DB::table('voucher_redemptions')->where('id', $voucherRecord->id)->update([
                        'status' => 'expired',
                        'updated_at' => now(),
                    ]);
                }
                if ($pointRecord) {
                    \Illuminate\Support\Facades\DB::table('point_redemptions')->where('id', $pointRecord->id)->update([
                        'status' => 'expired',
                        'updated_at' => now(),
                    ]);
                }
                return response()->json([
                    'status' => 'error',
                    'voucher_status' => 'expired',
                    'message' => 'Cannot cancel an expired voucher.'
                ], 400);
            }

            $cost = (int) ($record->points_used ?? ($record->points_cost ?? 0));
            $now = now();

            \Illuminate\Support\Facades\DB::transaction(function () use ($user, $cost, $record, $voucherRecord, $pointRecord, $now) {
                if ($cost > 0) {
                    $user->increment('points', $cost);
                }

                $vId = $record->voucher_id ?? null;
                if ($vId) {
                    $voucherModel = Voucher::find($vId);
                    if ($voucherModel && $voucherModel->remaining_quantity !== null) {
                        $voucherModel->increment('remaining_quantity');
                        if ($voucherModel->redeemed_quantity > 0) {
                            $voucherModel->decrement('redeemed_quantity');
                        }
                    }
                }

                if ($voucherRecord) {
                    \Illuminate\Support\Facades\DB::table('voucher_redemptions')->where('id', $voucherRecord->id)->update([
                        'status' => 'cancelled',
                        'cancelled_at' => $now,
                        'updated_at' => $now,
                    ]);
                }
                if ($pointRecord) {
                    \Illuminate\Support\Facades\DB::table('point_redemptions')->where('id', $pointRecord->id)->update([
                        'status' => 'cancelled',
                        'cancelled_at' => $now,
                        'updated_at' => $now,
                    ]);
                }
            });

            $user->refresh();
            $newPoints = (int) ($user->points ?? 0);
            \Illuminate\Support\Facades\Cache::forget("rank:user:{$user->id}");

            $voucherTitle = $record->reward_name_snapshot ?? ($record->type ?? 'Tourist Voucher');
            try {
                if (\Illuminate\Support\Facades\Schema::hasTable('activity_logs')) {
                    \App\Models\ActivityLog::create([
                        'user_id'    => $user->id,
                        'action'     => 'Voucher Cancelled',
                        'details'    => "Cancelled voucher '{$voucherTitle}' (Code: {$code}), refunded {$cost} Points.",
                        'ip_address' => $request->ip() ?? '127.0.0.1',
                    ]);
                }
            } catch (\Throwable $e) {}

            \App\Models\Notification::createSafely(
                $user->id,
                'favorite_update',
                'Voucher Claim Cancelled',
                "Your claim for '{$voucherTitle}' was cancelled. +{$cost} Points have been refunded.",
                ['action_url' => '/discount']
            );

            return response()->json([
                'status' => 'success',
                'message' => "Voucher claim cancelled successfully. {$cost} Points refunded.",
                'voucher_status' => 'cancelled',
                'refunded_points' => $cost,
                'new_balance' => $newPoints,
                'points' => $newPoints,
                'code' => $code,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to cancel voucher: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * POST /api/tourist/vouchers/{code}/refresh-qr
     * Refresh the 20-minute QR code token without extending the voucher's original expiration.
     */
    public function refreshQrCode(Request $request, string $code): JsonResponse
    {
        try {
            $user = $request->user();
            if (!$user) {
                return response()->json(['status' => 'error', 'message' => 'Unauthenticated.'], 401);
            }

            $voucherRecord = null;
            if (\Illuminate\Support\Facades\Schema::hasTable('voucher_redemptions')) {
                $voucherRecord = \Illuminate\Support\Facades\DB::table('voucher_redemptions')
                    ->where('user_id', $user->id)
                    ->where(function ($q) use ($code) {
                        $q->where('redemption_code', $code)
                          ->orWhere('qr_token', $code);
                    })->first();
            }

            $pointRecord = null;
            if (\Illuminate\Support\Facades\Schema::hasTable('point_redemptions')) {
                $pointRecord = \Illuminate\Support\Facades\DB::table('point_redemptions')
                    ->where('user_id', $user->id)
                    ->where(function ($q) use ($code) {
                        $q->where('voucher_code', $code)
                          ->orWhere('qr_token', $code);
                    })->first();
            }

            if (!$voucherRecord && !$pointRecord) {
                return response()->json(['status' => 'error', 'message' => 'Claimed voucher record not found.'], 404);
            }

            $record = $voucherRecord ?: $pointRecord;
            $currentStatus = strtolower($record->status ?? 'claimed');

            if (in_array($currentStatus, ['used', 'redeemed', 'completed'])) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Voucher is already used and redeemed.'
                ], 400);
            }

            if ($currentStatus === 'cancelled') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Voucher claim was cancelled.'
                ], 400);
            }

            // Server-side timestamp validation: Check voucher expiration
            $voucherExpiresAt = $record->voucher_expires_at ? \Carbon\Carbon::parse($record->voucher_expires_at) : null;
            if ($voucherExpiresAt && now()->gte($voucherExpiresAt)) {
                if ($voucherRecord) {
                    \Illuminate\Support\Facades\DB::table('voucher_redemptions')->where('id', $voucherRecord->id)->update([
                        'status' => 'expired',
                        'updated_at' => now(),
                    ]);
                }
                if ($pointRecord) {
                    \Illuminate\Support\Facades\DB::table('point_redemptions')->where('id', $pointRecord->id)->update([
                        'status' => 'expired',
                        'updated_at' => now(),
                    ]);
                }
                return response()->json([
                    'status' => 'error',
                    'voucher_status' => 'expired',
                    'message' => 'Voucher has reached its expiration time and cannot be refreshed.'
                ], 400);
            }

            // Keep a live QR window unchanged. When it ends, rotate from the
            // previous server timestamp so closing/reopening cannot reset it.
            $existingQrExpiresAt = $record->qr_expires_at ? \Carbon\Carbon::parse($record->qr_expires_at) : null;
            if ($currentStatus === 'active' && $existingQrExpiresAt && $existingQrExpiresAt->gt(now()) && !empty($record->qr_token)) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'QR code is still active.',
                    'qr_token' => $record->qr_token,
                    'qr_expires_at' => $existingQrExpiresAt->toIso8601String(),
                    'voucher_expires_at' => $voucherExpiresAt ? $voucherExpiresAt->toIso8601String() : null,
                    'voucher_status' => 'active',
                    'code' => $code,
                ])->header('Cache-Control', 'no-store, no-cache, must-revalidate');
            }

            $newQrToken = 'elyu_qr_' . \Illuminate\Support\Str::random(32);
            $newQrExpiresAt = $existingQrExpiresAt ?: now();
            while ($newQrExpiresAt->lte(now())) {
                $newQrExpiresAt->addMinutes(20);
            }

            // Never extend beyond voucher's original expiration time
            if ($voucherExpiresAt && $newQrExpiresAt->gt($voucherExpiresAt)) {
                $newQrExpiresAt = $voucherExpiresAt->copy();
            }

            $now = now();
            if ($voucherRecord) {
                \Illuminate\Support\Facades\DB::table('voucher_redemptions')->where('id', $voucherRecord->id)->update([
                    'status' => 'active',
                    'qr_token' => $newQrToken,
                    'qr_expires_at' => $newQrExpiresAt,
                    'updated_at' => $now,
                ]);
            }
            if ($pointRecord) {
                \Illuminate\Support\Facades\DB::table('point_redemptions')->where('id', $pointRecord->id)->update([
                    'status' => 'active',
                    'qr_token' => $newQrToken,
                    'qr_expires_at' => $newQrExpiresAt,
                    'updated_at' => $now,
                ]);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'QR code refreshed successfully!',
                'qr_token' => $newQrToken,
                'qr_expires_at' => $newQrExpiresAt->toIso8601String(),
                'voucher_expires_at' => $voucherExpiresAt ? $voucherExpiresAt->toIso8601String() : null,
                'voucher_status' => 'active',
                'code' => $code,
            ])->header('Cache-Control', 'no-store, no-cache, must-revalidate');
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to refresh QR code: ' . $e->getMessage()
            ], 500);
        }
    }
}
