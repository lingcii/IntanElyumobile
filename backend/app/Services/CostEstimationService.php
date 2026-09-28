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
     * Calculate fuel cost based on distance, efficiency, and real-time local fuel price.
     */
    public function estimateFuelCost(float $distanceKm, string $vehicleType, ?float $customFuelPrice = null, ?float $customFuelEfficiency = null): float
    {
        // 1. Fetch real-time local fuel price from system_settings or transportation_routes table
        $fuelPrice = $customFuelPrice;
        if ($fuelPrice === null) {
            $fuelPrice = 65.00;
            try {
                $fuelPriceSetting = DB::table('system_settings')->where('key', 'fuel_price')->value('value');
                if ($fuelPriceSetting !== null) {
                    $fuelPrice = (float) $fuelPriceSetting;
                }
            } catch (\Throwable $e) {}

            try {
                $routeFuelPrice = DB::table('transportation_routes')->whereNotNull('fuel_price')->value('fuel_price');
                if ($routeFuelPrice !== null) {
                    $fuelPrice = (float) $routeFuelPrice;
                }
            } catch (\Throwable $e) {}
        }

        // 2. Fetch vehicle consumption rate (efficiency in km/L)
        $efficiency = $customFuelEfficiency;
        if ($efficiency === null) {
            $dbVehicleName = $this->mapVehicleToDbName($vehicleType);
            $efficiencyMap = [
                'Tricycle'    => 25.00,
                'Jeepney'     => 8.00,
                'MPUJ'        => 8.00,
                'Bus'         => 4.00,
                'PUB_Aircon'  => 4.00,
                'Van'         => 10.00,
                'Taxi'        => 12.00,
                'Motorcycle'  => 35.00,
                'Private Car' => 12.00,
                'Own Car'     => 12.00,
            ];
            $efficiency = $efficiencyMap[$dbVehicleName] ?? 12.00;
        }

        if ($efficiency <= 0) {
            return 0.0;
        }

        // Formula: (Distance / Efficiency) * Fuel Price
        return ($distanceKm / $efficiency) * $fuelPrice;
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
            $muni = $municipality ? trim(strtolower($municipality)) : 'san juan';
            
            // 1. Try to find active tricycle guide matching requested municipality (e.g. San Juan)
            $guide = FareGuide::where('status', 'active')
                ->where('vehicle_type', 'Tricycle')
                ->where(function ($q) use ($muni) {
                    $q->whereRaw('LOWER(region) LIKE ?', ["%{$muni}%"])
                      ->orWhereRaw('LOWER(title) LIKE ?', ["%{$muni}%"]);
                })
                ->latest('effective_date')
                ->first();

            // 2. Default to San Juan (Guide #29) as primary tourism hub if not found
            if (!$guide) {
                $guide = FareGuide::where('status', 'active')
                    ->where('vehicle_type', 'Tricycle')
                    ->where(function ($q) {
                        $q->whereRaw('LOWER(region) LIKE ?', ['%san juan%'])
                          ->orWhereRaw('LOWER(title) LIKE ?', ['%san juan%']);
                    })
                    ->first() ?? FareGuide::find(29);
            }

            // 3. Fallback to any active tricycle guide
            if (!$guide) {
                $guide = FareGuide::where('status', 'active')
                    ->where('vehicle_type', 'Tricycle')
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
                // San Juan base ₱16.32 + ₱2/km fallback
                return round(16.32 + (max(0, $distanceKm - 1.7) * 2.00), 2);
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
        ?float $customPeakMultiplier = null
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

        // 3. Compute transport costs leg-by-leg with municipal boundary awareness
        $transitFares = 0.00;
        $fuelCost = 0.00;
        $legBreakdowns = [];
        $boundaryCrossingsCount = 0;

        $modes = array_filter(explode(',', $transportModeString));
        if (empty($modes)) {
            $modes = ['jeepney'];
        }

        foreach ($modes as $modeIndex => $mode) {
            $mode = trim($mode);
            $norm = strtolower($mode);
            $isPrivate = in_array($norm, ['own_car', 'car', 'motorcycle']);
            
            foreach ($legs as $legIdx => $leg) {
                $isFirstLeg = ($legIdx === 0 && $modeIndex === 0);
                $crosses = (bool) ($leg['crosses_boundary'] ?? false);
                if ($crosses && $modeIndex === 0) {
                    $boundaryCrossingsCount++;
                }

                $distKm = (float) ($leg['distance_km'] ?? 1.0);
                $muniA = $leg['origin_muni'] ?? $primaryMuni;
                $muniB = $leg['dest_muni'] ?? $primaryMuni;

                $priceA = 0.00;
                $estimateB = 0.00;
                $legFare = 0.00;

                if ($crosses) {
                    $distA = round($distKm / 2.0, 2);
                    $distB = round(max(0.1, $distKm - $distA), 2);

                    if ($norm === 'own_car' || $norm === 'car') {
                        $costA = $this->estimateFuelCost($distA, 'Private Car', $customFuelPrice, $customFuelEfficiency);
                        $costB = $this->estimateFuelCost($distB, 'Private Car', $customFuelPrice, $customFuelEfficiency);
                        $priceA = round($costA, 2);
                        $estimateB = round($costB, 2);
                        $legFare = round($priceA + $estimateB, 2);
                        $fuelCost += $legFare;
                    } elseif ($norm === 'motorcycle') {
                        $costA = $this->estimateFuelCost($distA, 'Motorcycle', $customFuelPrice, 35.00);
                        $costB = $this->estimateFuelCost($distB, 'Motorcycle', $customFuelPrice, 35.00);
                        $priceA = round($costA, 2);
                        $estimateB = round($costB, 2);
                        $legFare = round($priceA + $estimateB, 2);
                        $fuelCost += $legFare;
                    } elseif ($norm === 'taxi') {
                        $base = $isFirstLeg ? 40.00 : 0.00;
                        $priceA = round($base + ($distA * 13.00), 2);
                        $estimateB = round($distB * 13.00, 2);
                        $legFare = round($priceA + $estimateB, 2);
                        $transitFares += $legFare;
                    } elseif ($norm === 'tricycle' || $norm === 'trike') {
                        // Tricycle boundary transfer: local matrix A for segment A + local matrix B for segment B
                        $fareA = $this->estimateTransitFare($distA, 'tricycle', $muniA);
                        $fareB = $this->estimateTransitFare($distB, 'tricycle', $muniB);
                        $priceA = round($fareA, 2);
                        $estimateB = round($fareB, 2);
                        $legFare = round($priceA + $estimateB, 2);
                        $transitFares += $legFare;
                    } else {
                        // Inter-municipal Public Transit (MPUJ, TPUJ, PUB Aircon, PUB Ordinary, Bus, Van)
                        // Shows price in origin boundary A, and the incremental remaining estimate in boundary B
                        $targetMode = ($norm === 'private_bus') ? 'pub_aircon' : $mode;
                        $fareA = $this->estimateTransitFare($distA, $targetMode, $muniA);
                        $totalLegTransit = $this->estimateTransitFare($distKm, $targetMode, $muniA);
                        $priceA = round($fareA, 2);
                        $estimateB = max(0.00, round($totalLegTransit - $priceA, 2));
                        $legFare = round($priceA + $estimateB, 2);
                        $transitFares += $legFare;
                    }
                } else {
                    // Single municipal boundary leg
                    if ($norm === 'own_car' || $norm === 'car') {
                        $cost = $this->estimateFuelCost($distKm, 'Private Car', $customFuelPrice, $customFuelEfficiency);
                        $priceA = round($cost, 2);
                        $estimateB = 0.00;
                        $legFare = $priceA;
                        $fuelCost += $legFare;
                    } elseif ($norm === 'motorcycle') {
                        $cost = $this->estimateFuelCost($distKm, 'Motorcycle', $customFuelPrice, 35.00);
                        $priceA = round($cost, 2);
                        $estimateB = 0.00;
                        $legFare = $priceA;
                        $fuelCost += $legFare;
                    } elseif ($norm === 'taxi') {
                        $base = $isFirstLeg ? 40.00 : 0.00;
                        $priceA = round($base + ($distKm * 13.00), 2);
                        $estimateB = 0.00;
                        $legFare = $priceA;
                        $transitFares += $legFare;
                    } elseif ($norm === 'tricycle' || $norm === 'trike') {
                        $priceA = round($this->estimateTransitFare($distKm, 'tricycle', $muniA), 2);
                        $estimateB = 0.00;
                        $legFare = $priceA;
                        $transitFares += $legFare;
                    } else {
                        $targetMode = ($norm === 'private_bus') ? 'pub_aircon' : $mode;
                        $priceA = round($this->estimateTransitFare($distKm, $targetMode, $muniA), 2);
                        $estimateB = 0.00;
                        $legFare = $priceA;
                        $transitFares += $legFare;
                    }
                }

                $legBreakdowns[] = [
                    'leg_index'        => $leg['leg_index'],
                    'from_spot'        => $leg['from_spot'],
                    'to_spot'          => $leg['to_spot'],
                    'from_spot_id'     => $leg['from_spot_id'],
                    'to_spot_id'       => $leg['to_spot_id'],
                    'distance_km'      => $distKm,
                    'crosses_boundary' => $crosses,
                    'vehicle_mode'     => $mode,
                    'is_private'       => $isPrivate,
                    'origin_boundary'  => $muniA,
                    'origin_price'     => $priceA,
                    'next_boundary'    => $muniB,
                    'next_estimate'    => $estimateB,
                    'leg_total'        => $legFare,
                ];
            }
        }

        // 4. Peak Season Surge Multiplier
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
        $coords = $orderedSpots->map(function ($spot) {
            return "{$spot->longitude},{$spot->latitude}";
        })->implode(';');

        $osrmLegs = [];
        try {
            $response = Http::timeout(3)->get("https://router.project-osrm.org/route/v1/driving/{$coords}", [
                'overview'   => 'false',
                'geometries' => 'geojson'
            ]);

            if ($response->successful() && isset($response->json()['routes'][0]['legs'])) {
                $osrmLegs = $response->json()['routes'][0]['legs'];
            }
        } catch (\Throwable $e) {
            Log::warning("OSRM API failed in calculateRouteLegs, using Haversine: " . $e->getMessage());
        }

        $legs = [];
        for ($i = 0; $i < $count - 1; $i++) {
            $spotA = $orderedSpots[$i];
            $spotB = $orderedSpots[$i + 1];

            $distKm = 0.0;
            if (isset($osrmLegs[$i]['distance'])) {
                $distKm = round(((float) $osrmLegs[$i]['distance']) / 1000.0, 2);
            } else {
                $rawDist = $this->haversine(
                    (float) $spotA->latitude,
                    (float) $spotA->longitude,
                    (float) $spotB->latitude,
                    (float) $spotB->longitude
                );
                // 1.25 factor for realistic road curves
                $distKm = max(0.5, round(($rawDist / 1000.0) * 1.25, 2));
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

        try {
            // OSRM Public Driving Router API
            $response = Http::timeout(3)->get("https://router.project-osrm.org/route/v1/driving/{$coords}", [
                'overview' => 'false',
                'geometries' => 'geojson'
            ]);

            if ($response->successful() && isset($response->json()['routes'][0]['distance'])) {
                return (float) ($response->json()['routes'][0]['distance'] / 1000.0); // meters to km
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
