<?php

namespace App\Services;

use App\Models\TouristSpot;
use App\Models\FareGuide;
use App\Models\FareMatrix;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CostEstimationService
{
    /**
     * Calculate fuel cost based on distance and fuel price (Disabled - Personal vehicles have no fare).
     */
    public function estimateFuelCost(float $distanceKm, string $vehicleType, ?float $customFuelPrice = null, ?float $customFuelEfficiency = null): float
    {
        // Fuel costing has been removed; personal/private vehicles have 0 fare.
        return 0.0;
    }

    /**
     * Calculate public transit fare based on distance and the LGU-verified database fare matrices.
     */
    public function estimateTransitFare(float $distanceKm, string $vehicleType, ?string $municipality = null): float
    {
        $dbType = $this->mapVehicleToDbName($vehicleType);
        $normType = strtolower($vehicleType);
        
        $guide = null;

        if (in_array($normType, ['tricycle', 'trike'])) {
            $muni = $municipality ? trim(strtolower($municipality)) : null;
            
            // Only resolve tricycle guide if the requested municipality actually has an active tricycle matrix in LUPTO
            if ($muni) {
                $cleanMuni = trim(preg_replace('/^(municipality of|city of)\s+/i', '', $muni));
                $cleanMuni = trim(preg_replace('/,\s*la\s*union$/i', '', $cleanMuni));

                $guide = FareGuide::where('status', 'active')
                    ->where('vehicle_type', 'Tricycle')
                    ->where(function ($q) use ($cleanMuni) {
                        $q->whereRaw('LOWER(region) LIKE ?', ["%{$cleanMuni}%"])
                          ->orWhereRaw('LOWER(title) LIKE ?', ["%{$cleanMuni}%"]);
                    })
                    ->latest('effective_date')
                    ->first();
            }
        } elseif (in_array($normType, ['pub_aircon'])) {
            $guide = FareGuide::where('status', 'active')
                ->where('vehicle_type', 'PUB_Aircon')
                ->latest('effective_date')
                ->first()
                ?? FareGuide::where('status', 'active')->whereIn('vehicle_type', ['PUB_Aircon', 'Bus'])->first();
        } elseif (in_array($normType, ['pub_ordinary', 'pub_regular'])) {
            $guide = FareGuide::where('status', 'active')
                ->whereIn('vehicle_type', ['PUB_Ordinary', 'PUB_Regular'])
                ->latest('effective_date')
                ->first()
                ?? FareGuide::where('status', 'active')->whereIn('vehicle_type', ['PUB_Ordinary', 'Bus'])->first();
        } elseif (in_array($normType, ['bus', 'private_bus'])) {
            $guide = FareGuide::where('status', 'active')
                ->whereIn('vehicle_type', ['PUB_Aircon', 'PUB_Ordinary', 'Bus'])
                ->latest('effective_date')
                ->first();
        } elseif (in_array($normType, ['mpuj'])) {
            $guide = FareGuide::where('status', 'active')
                ->where('vehicle_type', 'MPUJ')
                ->latest('effective_date')
                ->first();
        } elseif (in_array($normType, ['tpuj'])) {
            $guide = FareGuide::where('status', 'active')
                ->whereIn('vehicle_type', ['TPUJ', 'PUJ_Ordinary'])
                ->latest('effective_date')
                ->first();
        } elseif (in_array($normType, ['uve', 'van', 'mini_bus'])) {
            $guide = FareGuide::where('status', 'active')
                ->whereIn('vehicle_type', ['UVE', 'UV Express', 'Van'])
                ->latest('effective_date')
                ->first()
                ?? FareGuide::where('status', 'active')->whereIn('vehicle_type', ['MPUJ', 'PUJ_Ordinary'])->first();
        } elseif (in_array($normType, ['jeepney', 'puj_ordinary', 'puj_aircon', 'lutrampco'])) {
            $guide = FareGuide::where('status', 'active')
                ->whereIn('vehicle_type', ['MPUJ', 'TPUJ', 'PUJ_Ordinary', 'PUJ_Aircon', 'Jeepney'])
                ->latest('effective_date')
                ->first();
        } else {
            $guide = FareGuide::where('status', 'active')
                ->where('vehicle_type', $dbType)
                ->latest('effective_date')
                ->first();
        }

        if ($guide) {
            // Stage ceiling lookup: find the stage bracket where distance_km >= calculated distance
            $matrixEntry = FareMatrix::where('fare_guide_id', $guide->id)
                ->where('distance_km', '>=', $distanceKm)
                ->orderBy('distance_km', 'asc')
                ->first();

            if ($matrixEntry) {
                return (float) $matrixEntry->regular_fare;
            }

            // If distance exceeds all matrix steps, take the max step and calculate scaling
            $maxEntry = FareMatrix::where('fare_guide_id', $guide->id)
                ->orderByDesc('distance_km')
                ->first();

            if ($maxEntry) {
                $maxDist = (float) $maxEntry->distance_km;
                $maxFare = (float) $maxEntry->regular_fare;
                $extraDist = max(0, $distanceKm - $maxDist);
                $perKmRate = (strcasecmp($guide->vehicle_type, 'Tricycle') === 0) ? 2.00 : 1.80;
                return round($maxFare + ($extraDist * $perKmRate), 2);
            }
        }

        // Dynamic fallbacks if no database guide is configured
        switch ($normType) {
            case 'taxi':
                return round(40.00 + ($distanceKm * 13.00), 2);
            case 'mini_bus':
            case 'van':
            case 'uve':
                return round(25.00 + (max(0, $distanceKm - 4) * 2.50), 2);
            case 'lutrampco':
                return round(14.00 + (max(0, $distanceKm - 4) * 2.20), 2);
            case 'jeepney':
                return round(13.00 + (max(0, $distanceKm - 4) * 1.80), 2);
            case 'bus':
            case 'private_bus':
                return round(15.00 + (max(0, $distanceKm - 5) * 2.20), 2);
            case 'tricycle':
            case 'trike':
                return 0.00;
            default:
                return 0.00;
        }
    }

    /**
     * Check if a given travel date (or current date) falls within La Union Peak Tourism Season.
     * Peak Season Months:
     * - March to May (Summer Beach & Water Tourism)
     * - October to January (Surfing Season & Holidays)
     * - Weekends (Saturday & Sunday surge)
     */
    public function isPeakSeason(?string $dateString = null): bool
    {
        try {
            $date = $dateString ? \Carbon\Carbon::parse($dateString) : now();
        } catch (\Throwable $e) {
            $date = now();
        }

        $month = (int) $date->format('n');
        $isWeekend = $date->isWeekend();

        return in_array($month, [1, 3, 4, 5, 10, 11, 12], true) || $isWeekend;
    }

    /**
     * Get the dynamic peak multiplier for pricing (1.25x during peak season).
     */
    public function getPeakMultiplier(?string $dateString = null): float
    {
        return $this->isPeakSeason($dateString) ? 1.25 : 1.00;
    }

    /**
     * Estimate all costs (fuel, transit, entrance fees, peak season multipliers) for a given sequence of destinations and transport modes.
     */
    public function estimateItineraryCosts(
        array $destinationIds, 
        string $transportModeString, 
        ?float $customFuelPrice = null, 
        ?float $customFuelEfficiency = null,
        ?string $travelDate = null,
        ?float $customPeakMultiplier = null,
        ?array $perLegModes = null
    ): array {
        if (empty($destinationIds)) {
            return [
                'entrance_fees'      => 0.00,
                'transit_fares'      => 0.00,
                'fuel_cost'          => 0.00,
                'subtotal_cost'      => 0.00,
                'total_cost'         => 0.00,
                'distance_km'        => 0.00,
                'is_peak_season'     => false,
                'peak_multiplier'    => 1.00,
                'peak_season_note'   => 'Standard Regular Season Pricing',
            ];
        }

        // 1. Calculate entrance fees & environmental fees of all spots and identify municipalities
        $spots = TouristSpot::with('municipality')->whereIn('id', $destinationIds)->get();
        $entranceFees = (float) $spots->sum('entrance_fee');
        $environmentalFees = (float) $spots->sum('environmental_fee');
        $siteFeesTotal = $entranceFees + $environmentalFees;

        // Extract primary destination municipality (default to San Juan)
        $primaryMuni = $spots->first()?->municipality?->name ?? 'San Juan';

        // 2. Fetch coordinates in itinerary order and build sequential legs
        $orderedSpots = collect($destinationIds)->map(function ($id) use ($spots) {
            return $spots->firstWhere('id', $id);
        })->filter()->values();

        $legs = $this->calculateRouteLegs($orderedSpots);
        $totalDistanceKm = (float) collect($legs)->sum('distance_km');

        // 3. If No Vehicle Selected or empty transport mode, zero out transit fares and fuel
        $isNoVehicle = empty($transportModeString)
            || stripos($transportModeString, 'no_vehicle') !== false
            || stripos($transportModeString, 'no vehicle') !== false;

        if ($isNoVehicle) {
            $subtotalCost = $siteFeesTotal;
            $peakMultiplier = $customPeakMultiplier ?? $this->getPeakMultiplier($travelDate);
            $totalCost = round($subtotalCost * $peakMultiplier, 2);

            return [
                'entrance_fees'      => round($entranceFees, 2),
                'environmental_fees' => round($environmentalFees, 2),
                'transit_fares'      => 0.00,
                'fuel_cost'          => 0.00,
                'subtotal_cost'      => round($subtotalCost, 2),
                'total_cost'         => $totalCost,
                'distance_km'        => round($totalDistanceKm, 2),
                'is_peak_season'     => $this->isPeakSeason($travelDate),
                'peak_multiplier'    => $peakMultiplier,
                'peak_season_note'   => $this->isPeakSeason($travelDate) ? 'Peak Season Dynamic Rate (1.25x)' : 'Standard Regular Season Pricing',
                'boundary_crossings' => 0,
                'legs'               => $legs,
                'leg_breakdowns'     => [],
                'transport_mode'     => 'No Vehicle Selected',
            ];
        }

        // 4. Compute transport costs leg-by-leg with Point-to-Point awareness
        $transitFares = 0.00;
        $fuelCost = 0.00;
        $legBreakdowns = [];
        $boundaryCrossingsCount = 0;

        $globalNorm = strtolower(trim($transportModeString));
        $isGlobalOwnCar = in_array($globalNorm, ['own_car', 'car', 'private car']);
        $isGlobalMotorcycle = in_array($globalNorm, ['motorcycle', 'motor']);

        foreach ($legs as $legIdx => $leg) {
            $crosses = (bool) ($leg['crosses_boundary'] ?? false);
            if ($crosses) {
                $boundaryCrossingsCount++;
            }

            $distKm = (float) ($leg['distance_km'] ?? 1.0);
            $muniA = $leg['origin_muni'] ?? $primaryMuni;
            $muniB = $leg['dest_muni'] ?? $primaryMuni;

            // Resolve target spot for accessibility checks
            $destSpot = $orderedSpots->firstWhere('id', $leg['to_spot_id']);
            $isSpotPrivateInaccessible = $destSpot && ($destSpot->accessible_by_private_vehicle === 0 || $destSpot->accessible_by_private_vehicle === false);

            // Determine vehicle for this specific leg:
            // 1) Explicit per-leg override if provided
            // 2) Global Own Car / Motorcycle if tourist chose private transport for whole trip
            // 3) Smart Hybrid Transit Auto-Recommendation
            $legMode = null;
            if (!empty($perLegModes)) {
                if (isset($perLegModes[$legIdx])) {
                    $rawEntry = $perLegModes[$legIdx];
                    $legMode = is_array($rawEntry) ? ($rawEntry['vehicle'] ?? $rawEntry['transport_mode'] ?? null) : (string)$rawEntry;
                } elseif (isset($perLegModes[$leg['to_spot_id']])) {
                    $rawEntry = $perLegModes[$leg['to_spot_id']];
                    $legMode = is_array($rawEntry) ? ($rawEntry['vehicle'] ?? $rawEntry['transport_mode'] ?? null) : (string)$rawEntry;
                }
            }

            if (!$legMode) {
                if ($isGlobalOwnCar) {
                    $legMode = 'own_car';
                } elseif ($isGlobalMotorcycle) {
                    $legMode = 'motorcycle';
                } else {
                    // Smart Transit Auto-Recommender:
                    // If spot is inaccessible by car (e.g. Tangadan Falls) and within same municipality: Tricycle
                    // If intra-municipal short trip (<= 3.5km): Tricycle
                    // If inter-municipal highway or longer trip: Modern Jeepney (MPUJ)
                    if ($isSpotPrivateInaccessible && !$crosses) {
                        $legMode = 'tricycle';
                    } elseif (!$crosses && $distKm <= 3.5) {
                        $legMode = 'tricycle';
                    } else {
                        $legMode = 'mpuj';
                    }
                }
            }

            $normLegMode = strtolower(trim($legMode));
            $isPrivate = in_array($normLegMode, ['own_car', 'car', 'motorcycle']);
            $priceA = 0.00;
            $estimateB = 0.00;
            $legFare = 0.00;
            $warningNotice = null;

            if ($normLegMode === 'own_car' || $normLegMode === 'car') {
                $legFare = 0.00;
                if ($isSpotPrivateInaccessible) {
                    $warningNotice = '⚠️ Inaccessible by Private Car. Trailhead parking only; prepare to hike or ride local specialized tricycle.';
                }
            } elseif ($normLegMode === 'motorcycle') {
                $legFare = 0.00;
            } elseif ($normLegMode === 'taxi') {
                $base = ($legIdx === 0) ? 40.00 : 0.00;
                $legFare = round($base + ($distKm * 13.00), 2);
                $transitFares += $legFare;
            } elseif ($normLegMode === 'tricycle' || $normLegMode === 'trike') {
                if ($crosses) {
                    $distA = round($distKm / 2.0, 2);
                    $distB = round(max(0.1, $distKm - $distA), 2);
                    $fareA = $this->estimateTransitFare($distA, 'tricycle', $muniA);
                    $fareB = $this->estimateTransitFare($distB, 'tricycle', $muniB);
                    $priceA = round($fareA, 2);
                    $estimateB = round($fareB, 2);
                    $legFare = round($priceA + $estimateB, 2);
                } else {
                    $priceA = round($this->estimateTransitFare($distKm, 'tricycle', $muniA), 2);
                    $legFare = $priceA;
                }
                $transitFares += $legFare;
            } else {
                // Public Transit (MPUJ, TPUJ, PUB Aircon, PUB Ordinary, Van)
                $targetMode = ($normLegMode === 'private_bus') ? 'pub_aircon' : $normLegMode;
                if ($crosses) {
                    $distA = round($distKm / 2.0, 2);
                    $distB = round(max(0.1, $distKm - $distA), 2);
                    $fareA = $this->estimateTransitFare($distA, $targetMode, $muniA);
                    $totalLegTransit = $this->estimateTransitFare($distKm, $targetMode, $muniA);
                    $priceA = round($fareA, 2);
                    $estimateB = max(0.00, round($totalLegTransit - $priceA, 2));
                    $legFare = round($priceA + $estimateB, 2);
                } else {
                    $priceA = round($this->estimateTransitFare($distKm, $targetMode, $muniA), 2);
                    $legFare = $priceA;
                }
                $transitFares += $legFare;
            }

            $legBreakdowns[] = [
                'leg_index'                     => $leg['leg_index'],
                'from_spot'                     => $leg['from_spot'],
                'to_spot'                       => $leg['to_spot'],
                'from_spot_id'                  => $leg['from_spot_id'],
                'to_spot_id'                    => $leg['to_spot_id'],
                'distance_km'                   => $distKm,
                'crosses_boundary'              => $crosses,
                'vehicle_mode'                  => $legMode,
                'is_private'                    => $isPrivate,
                'accessible_by_private_vehicle' => !$isSpotPrivateInaccessible,
                'warning'                       => $warningNotice,
                'origin_boundary'               => $muniA,
                'origin_price'                  => $priceA,
                'next_boundary'                 => $muniB,
                'next_estimate'                 => $estimateB,
                'leg_total'                     => $legFare,
            ];
        }

        // 5. Peak Season Surge Multiplier
        $isPeak = $this->isPeakSeason($travelDate);
        $peakMultiplier = $customPeakMultiplier !== null ? (float)$customPeakMultiplier : ($isPeak ? 1.25 : 1.00);

        $subtotal = $siteFeesTotal + $transitFares + $fuelCost;
        $transitFaresSurged = $transitFares * $peakMultiplier;
        $totalCost = $siteFeesTotal + $transitFaresSurged + $fuelCost;

        $seasonNote = $isPeak 
            ? "🔥 Peak Season Surge Pricing Applied (+".round(($peakMultiplier - 1.0) * 100)."% on transit & high-demand travel during holidays/surfing season)."
            : "Standard Regular Season Pricing.";

        return [
            'entrance_fees'            => round($entranceFees, 2),
            'environmental_fees'       => round($environmentalFees, 2),
            'site_fees'                => round($siteFeesTotal, 2),
            'transit_fares'            => round($transitFaresSurged, 2),
            'base_transit_fares'       => round($transitFares, 2),
            'fuel_cost'                => round($fuelCost, 2),
            'subtotal_cost'            => round($subtotal, 2),
            'total_cost'               => round($totalCost, 2),
            'distance_km'              => round($totalDistanceKm, 2),
            'is_peak_season'           => $isPeak,
            'peak_multiplier'          => $peakMultiplier,
            'peak_season_note'         => $seasonNote,
            'legs'                     => $legBreakdowns,
            'boundary_crossings_count' => $boundaryCrossingsCount,
        ];
    }

    /**
     * Build sequential route legs with municipal boundaries and OSRM/Haversine leg distances.
     */
    public function calculateRouteLegs($orderedSpots): array
    {
        $count = $orderedSpots->count();
        if ($count === 0) {
            return [];
        }

        if ($count === 1) {
            $spot = $orderedSpots->first();
            $muni = $spot->municipality?->name ?? 'San Juan';
            return [
                [
                    'leg_index'        => 1,
                    'from_spot'        => $spot->name,
                    'to_spot'          => $spot->name,
                    'from_spot_id'     => $spot->id,
                    'to_spot_id'       => $spot->id,
                    'distance_km'      => 2.00,
                    'origin_muni'      => $muni,
                    'dest_muni'        => $muni,
                    'crosses_boundary' => false,
                ]
            ];
        }

        // Multiple spots: N-1 legs
        $allValid = $orderedSpots->every(function ($spot) {
            $lat = (float) $spot->latitude;
            $lng = (float) $spot->longitude;
            return ($lat > 15.0 && $lat < 18.0 && $lng > 119.0 && $lng < 122.0);
        });

        $osrmLegs = [];
        if ($allValid) {
            $coords = $orderedSpots->map(function ($spot) {
                return "{$spot->longitude},{$spot->latitude}";
            })->implode(';');

            $cacheKey = 'osrm_legs_' . md5($coords);
            $osrmLegs = \Illuminate\Support\Facades\Cache::remember($cacheKey, 86400, function () use ($coords) {
                try {
                    $response = Http::timeout(3)->get("https://router.project-osrm.org/route/v1/driving/{$coords}", [
                        'overview'   => 'false',
                        'geometries' => 'geojson'
                    ]);

                    if ($response->successful() && isset($response->json()['routes'][0]['legs'])) {
                        return $response->json()['routes'][0]['legs'];
                    }
                } catch (\Throwable $e) {
                    Log::warning("OSRM API failed in calculateRouteLegs, using Haversine: " . $e->getMessage());
                }
                return [];
            });
        }

        $legs = [];
        for ($i = 0; $i < $count - 1; $i++) {
            $spotA = $orderedSpots[$i];
            $spotB = $orderedSpots[$i + 1];

            $distKm = 2.0;
            if (isset($osrmLegs[$i]['distance'])) {
                $distKm = round(((float) $osrmLegs[$i]['distance']) / 1000.0, 2);
            } else {
                $latA = (float) $spotA->latitude;
                $lngA = (float) $spotA->longitude;
                $latB = (float) $spotB->latitude;
                $lngB = (float) $spotB->longitude;

                if ($latA > 15.0 && $latA < 18.0 && $lngA > 119.0 && $lngA < 122.0 &&
                    $latB > 15.0 && $latB < 18.0 && $lngB > 119.0 && $lngB < 122.0) {
                    $rawDist = $this->haversine($latA, $lngA, $latB, $lngB);
                    // 1.25 factor for realistic road curves
                    $distKm = max(0.5, round(($rawDist / 1000.0) * 1.25, 2));
                }
            }

            $muniA = $spotA->municipality?->name ?? 'San Juan';
            $muniB = $spotB->municipality?->name ?? 'San Juan';

            // Clean municipality names for comparison
            $cleanA = trim(preg_replace('/^(municipality of|city of)\s+/i', '', $muniA));
            $cleanB = trim(preg_replace('/^(municipality of|city of)\s+/i', '', $muniB));
            $crosses = (strcasecmp($cleanA, $cleanB) !== 0);

            $legs[] = [
                'leg_index'        => $i + 1,
                'from_spot'        => $spotA->name,
                'to_spot'          => $spotB->name,
                'from_spot_id'     => $spotA->id,
                'to_spot_id'       => $spotB->id,
                'distance_km'      => $distKm,
                'origin_muni'      => $muniA,
                'dest_muni'        => $muniB,
                'crosses_boundary' => $crosses,
            ];
        }

        return $legs;
    }

    /**
     * Call OSRM API to get precise routing distance, with Haversine fallback.
     */
    private function calculateRouteDistance($spots): float
    {
        $coords = $spots->map(function ($spot) {
            return "{$spot->longitude},{$spot->latitude}";
        })->implode(';');

        $cacheKey = 'osrm_dist_' . md5($coords);
        $cachedDist = \Illuminate\Support\Facades\Cache::get($cacheKey);
        if ($cachedDist !== null) {
            return (float) $cachedDist;
        }

        try {
            // OSRM Public Driving Router API
            $response = Http::timeout(3)->get("https://router.project-osrm.org/route/v1/driving/{$coords}", [
                'overview' => 'false',
                'geometries' => 'geojson'
            ]);

            if ($response->successful() && isset($response->json()['routes'][0]['distance'])) {
                $distKm = (float) ($response->json()['routes'][0]['distance'] / 1000.0); // meters to km
                \Illuminate\Support\Facades\Cache::put($cacheKey, $distKm, 86400); // 24 hrs
                return $distKm;
            }
        } catch (\Exception $e) {
            Log::warning("OSRM API failed, falling back to Haversine distance chain: " . $e->getMessage());
        }

        // Fallback: Haversine distance summation between sequence points
        $totalDistance = 0.0;
        for ($i = 0; $i < count($spots) - 1; $i++) {
            $totalDistance += $this->haversine(
                (float) $spots[$i]->latitude,
                (float) $spots[$i]->longitude,
                (float) $spots[$i+1]->latitude,
                (float) $spots[$i+1]->longitude
            );
        }

        return $totalDistance / 1000.0; // meters to km
    }

    /**
     * Haversine formula helper.
     */
    private function haversine(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadius = 6371000; // meters

        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat / 2) * sin($dLat / 2)
           + cos(deg2rad($lat1)) * cos(deg2rad($lat2))
           * sin($dLng / 2) * sin($dLng / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    /**
     * Map frontend transport options to database vehicle types.
     */
    private function mapVehicleToDbName(string $frontendName): string
    {
        $map = [
            'tricycle'     => 'Tricycle',
            'mpuj'         => 'MPUJ',
            'tpuj'         => 'TPUJ',
            'pub_aircon'   => 'PUB_Aircon',
            'pub_ordinary' => 'PUB_Ordinary',
            'pub_regular'  => 'PUB_Regular',
            'jeepney'      => 'MPUJ',
            'lutrampco'    => 'MPUJ',
            'mini_bus'     => 'UVE',
            'private_bus'  => 'PUB_Aircon',
            'bus'          => 'PUB_Aircon',
            'uve'          => 'UVE',
            'van'          => 'Van',
            'taxi'         => 'Taxi',
            'motorcycle'   => 'Motorcycle',
            'own_car'      => 'Private Car',
        ];

        return $map[strtolower($frontendName)] ?? $frontendName;
    }
}
