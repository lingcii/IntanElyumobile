<?php

namespace App\Services;

use App\Models\TouristSpot;
use App\Models\Vehicle;
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
        // 1. Fetch real-time local fuel price from transportation_routes table (fallback to system_settings)
        $fuelPrice = $customFuelPrice;
        if ($fuelPrice === null) {
            $routeFuelPrice = DB::table('transportation_routes')->whereNotNull('fuel_price')->value('fuel_price');
            if ($routeFuelPrice !== null) {
                $fuelPrice = (float) $routeFuelPrice;
            } else {
                $fuelPriceSetting = DB::table('system_settings')->where('key', 'fuel_price')->value('value');
                $fuelPrice = $fuelPriceSetting !== null ? (float) $fuelPriceSetting : 65.00;
            }
        }

        // 2. Fetch vehicle consumption rate (efficiency in km/L) from vehicles table
        $efficiency = $customFuelEfficiency;
        if ($efficiency === null) {
            $dbVehicleName = $this->mapVehicleToDbName($vehicleType);
            $vehicle = Vehicle::where('name', $dbVehicleName)->first();
            $efficiency = $vehicle && $vehicle->fuel_efficiency_kml > 0 
                ? (float) $vehicle->fuel_efficiency_kml 
                : 12.00; // default to 12 km/L (average private car)
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
        } elseif (in_array($normType, ['jeepney', 'puj_ordinary', 'puj_aircon', 'lutrampco', 'mini_bus', 'van', 'uve'])) {
            $guide = FareGuide::where('status', 'active')
                ->whereIn('vehicle_type', ['MPUJ', 'PUJ_Ordinary', 'PUJ_Aircon', 'Jeepney'])
                ->latest('effective_date')
                ->first();
        } elseif (in_array($normType, ['bus', 'private_bus', 'pub_aircon', 'pub_ordinary'])) {
            $guide = FareGuide::where('status', 'active')
                ->whereIn('vehicle_type', ['PUB_Aircon', 'PUB_Ordinary', 'Bus'])
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

        // 1. Calculate entrance fees of all spots and identify municipalities
        $spots = TouristSpot::with('municipality')->whereIn('id', $destinationIds)->get();
        $entranceFees = (float) $spots->sum('entrance_fee');

        // Extract primary destination municipality (default to San Juan)
        $primaryMuni = $spots->first()?->municipality?->name ?? 'San Juan';

        // 2. Fetch coordinates in itinerary order
        $orderedSpots = collect($destinationIds)->map(function ($id) use ($spots) {
            return $spots->firstWhere('id', $id);
        })->filter()->values();

        $distanceKm = 0.0;
        if ($orderedSpots->count() > 1) {
            $distanceKm = $this->calculateRouteDistance($orderedSpots);
        }

        // 3. Compute transport costs
        $transitFares = 0.00;
        $fuelCost = 0.00;
        
        $modes = array_filter(explode(',', $transportModeString));
        foreach ($modes as $mode) {
            $mode = trim($mode);
            if ($mode === 'own_car') {
                $fuelCost += $this->estimateFuelCost($distanceKm, 'Private Car', $customFuelPrice, $customFuelEfficiency);
            } elseif ($mode === 'taxi') {
                $transitFares += round(40.00 + ($distanceKm * 13.00), 2);
            } elseif ($mode === 'private_bus') {
                $transitFares += $this->estimateTransitFare($distanceKm, 'private_bus', $primaryMuni);
            } else {
                $transitFares += $this->estimateTransitFare($distanceKm, $mode, $primaryMuni);
            }
        }

        // 4. Peak Season Surge Multiplier
        $isPeak = $this->isPeakSeason($travelDate);
        $peakMultiplier = $customPeakMultiplier !== null ? (float)$customPeakMultiplier : ($isPeak ? 1.25 : 1.00);

        $subtotal = $entranceFees + $transitFares + $fuelCost;
        $transitFaresSurged = $transitFares * $peakMultiplier;
        $totalCost = $entranceFees + $transitFaresSurged + $fuelCost;

        $seasonNote = $isPeak 
            ? "🔥 Peak Season Surge Pricing Applied (+".round(($peakMultiplier - 1.0) * 100)."% on transit & high-demand travel during holidays/surfing season)."
            : "Standard Regular Season Pricing.";

        return [
            'entrance_fees'      => round($entranceFees, 2),
            'transit_fares'      => round($transitFaresSurged, 2),
            'base_transit_fares' => round($transitFares, 2),
            'fuel_cost'          => round($fuelCost, 2),
            'subtotal_cost'      => round($subtotal, 2),
            'total_cost'         => round($totalCost, 2),
            'distance_km'        => round($distanceKm, 2),
            'is_peak_season'     => $isPeak,
            'peak_multiplier'    => $peakMultiplier,
            'peak_season_note'   => $seasonNote,
        ];
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
            'tricycle'    => 'Tricycle',
            'jeepney'     => 'PUJ_Ordinary',
            'lutrampco'   => 'PUB_Ordinary',
            'mini_bus'    => 'PUJ_Aircon',
            'private_bus' => 'PUB_Aircon',
            'van'         => 'Van',
            'taxi'        => 'Taxi',
            'own_car'     => 'Private Car',
        ];

        return $map[strtolower($frontendName)] ?? $frontendName;
    }
}
