<?php

namespace App\Http\Controllers;

use App\Models\PartnerEstablishment;
use App\Models\Voucher;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PartnerMerchantController extends Controller
{
    /**
     * GET /api/public/partner-merchants
     * Returns approved partner merchants with spotlights like Mabanag Hall.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $partners = PartnerEstablishment::with('municipality')
                ->where(function ($q) {
                    $q->where('status', 'active')
                      ->orWhereNull('status');
                })
                ->where(function ($q) {
                    $q->where('partnership_status', 'approved')
                      ->orWhereNull('partnership_status');
                })
                ->orderByRaw("CASE WHEN LOWER(name) LIKE '%mabanag%' THEN 0 ELSE 1 END")
                ->latest()
                ->get();

            $formatted = $partners->map(function ($p) {
                $isMabanag = str_contains(strtolower($p->name), 'mabanag');

                $address = $p->city ? ($p->city . ', ' . ($p->province ?: 'La Union')) : ($p->municipality ? $p->municipality->name . ', La Union' : 'La Union');
                if ($isMabanag) {
                    $address = 'City Plaza, San Fernando City, La Union';
                }

                $operatingHours = $isMabanag ? 'Monday - Sunday • 8:00 AM - 5:00 PM' : 'Daily • 9:00 AM - 6:00 PM';
                $redemptionDesk = $isMabanag ? 'Mabanag Hall Tourism Reception Desk' : 'Main Cashier / Front Desk';

                // Count linked active vouchers
                $voucherCount = 0;
                try {
                    $voucherCount = DB::table('partner_establishment_voucher')
                        ->join('vouchers', 'partner_establishment_voucher.voucher_id', '=', 'vouchers.id')
                        ->where('partner_establishment_voucher.partner_establishment_id', $p->id)
                        ->where(function ($q) {
                            $q->where('vouchers.status', 'active')
                              ->orWhereNull('vouchers.status');
                        })
                        ->count();
                } catch (\Throwable $e) {}

                if ($voucherCount === 0 && $isMabanag) {
                    $voucherCount = Voucher::where(function ($q) {
                        $q->where('partner_establishment', 'LIKE', '%Mabanag%')
                          ->orWhere('partner_establishments', 'LIKE', '%Mabanag%');
                    })->count();
                }

                return [
                    'id'                  => $p->id,
                    'name'                => $p->name,
                    'category'            => $isMabanag ? 'Official Partner Merchant' : ($p->category ?: 'Partner Establishment'),
                    'description'         => $p->description ?: ($isMabanag ? 'Historic Mabanag Hall in San Fernando City, La Union - Official Partner Merchant offering exclusive discounts & voucher rewards.' : null),
                    'municipality_id'     => $p->municipality_id,
                    'municipality_name'   => $p->municipality ? $p->municipality->name : ($p->city ?: 'La Union'),
                    'address'             => $address,
                    'operating_hours'     => $operatingHours,
                    'redemption_desk'     => $redemptionDesk,
                    'contact_number'      => $p->contact_number ?: '(072) 888-2451',
                    'email'               => $p->email ?: 'tourism@launion.gov.ph',
                    'logo'                => $p->logo ?: 'https://pub-268a50c87a9249ccbf90d35e77ddc65b.r2.dev/logo/SAN-FERNANDO.png',
                    'is_mabanag_hub'      => $isMabanag,
                    'is_verified'         => true,
                    'active_deals_count'  => $voucherCount,
                ];
            });

            return response()->json([
                'status' => 'success',
                'data'   => $formatted
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Failed to load partner merchants.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    /**
     * GET /api/public/partner-merchants/{id}
     */
    public function show($id): JsonResponse
    {
        try {
            $p = PartnerEstablishment::with('municipality')->find($id);
            if (!$p) {
                return response()->json(['status' => 'error', 'message' => 'Partner merchant not found.'], 404);
            }

            $isMabanag = str_contains(strtolower($p->name), 'mabanag');

            // Find vouchers linked to this partner
            $vouchers = Voucher::where(function ($q) use ($p, $isMabanag) {
                $q->whereHas('partnerEstablishments', function ($pe) use ($p) {
                    $pe->where('partner_establishments.id', $p->id);
                })
                ->orWhere('partner_establishment', 'LIKE', '%' . $p->name . '%')
                ->orWhere('partner_establishments', 'LIKE', '%' . $p->name . '%');

                if ($isMabanag) {
                    $q->orWhere('partner_establishment', 'LIKE', '%Mabanag%');
                }
            })->where(function ($q) {
                $q->whereIn(DB::raw('LOWER(COALESCE(status, "active"))'), ['active', 'upcoming'])
                  ->orWhereNull('status');
            })->get();

            return response()->json([
                'status' => 'success',
                'data' => [
                    'id'                => $p->id,
                    'name'              => $p->name,
                    'category'          => $isMabanag ? 'Official Partner Merchant' : ($p->category ?: 'Partner Establishment'),
                    'description'       => $p->description,
                    'address'           => $isMabanag ? 'City Plaza, San Fernando City, La Union' : ($p->city ?: 'La Union'),
                    'operating_hours'   => $isMabanag ? 'Monday - Sunday • 8:00 AM - 5:00 PM' : 'Daily • 9:00 AM - 6:00 PM',
                    'redemption_desk'   => $isMabanag ? 'Mabanag Hall Tourism Reception Desk' : 'Main Cashier',
                    'contact_number'    => $p->contact_number ?: '(072) 888-2451',
                    'is_mabanag_hub'    => $isMabanag,
                    'is_verified'       => true,
                    'vouchers'          => $vouchers
                ]
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve partner merchant details.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
