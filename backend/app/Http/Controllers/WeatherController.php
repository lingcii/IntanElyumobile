<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class WeatherController extends Controller
{
    /**
     * Get real-time weather and 5-day forecast for given latitude and longitude.
     * Automatically resolves current location or defaults to San Fernando, La Union (16.6159, 120.3209).
     */
    public function getWeather(Request $request)
    {
        $lat = floatval($request->query('lat', 16.6159));
        $lng = floatval($request->query('lng', $request->query('lon', 120.3209)));
        $requestedLocation = $request->query('location', 'San Fernando, La Union');
        $isCurrentLocRequested = $request->boolean('is_current_location', str_contains(strtolower($requestedLocation), 'my location'));

        // Resolve location name (via reverse geocoding or nearest municipality)
        $locationMeta = $this->resolveLocationName($lat, $lng, $requestedLocation, $isCurrentLocRequested);
        $location = $locationMeta['name'];
        $isCurrentLocation = $locationMeta['is_current'];

        // Round coordinates to 2 decimal places to maximize cache hits (~1km grid)
        $cacheLat = number_format($lat, 2, '.', '');
        $cacheLng = number_format($lng, 2, '.', '');
        $cacheKey = "weather_{$cacheLat}_{$cacheLng}";

        // Cache weather data for 15 minutes (900 seconds)
        $weatherData = Cache::remember($cacheKey, 900, function () use ($lat, $lng) {
            try {
                $response = Http::timeout(6)->get('https://api.open-meteo.com/v1/forecast', [
                    'latitude' => $lat,
                    'longitude' => $lng,
                    'current' => 'temperature_2m,relative_humidity_2m,weather_code,wind_speed_10m,is_day,apparent_temperature',
                    'daily' => 'weather_code,temperature_2m_max,temperature_2m_min,uv_index_max,precipitation_probability_max',
                    'timezone' => 'Asia/Manila',
                    'forecast_days' => 5
                ]);

                if ($response->successful()) {
                    return $response->json();
                }
            } catch (\Throwable $e) {
                Log::warning('Weather API call failed: ' . $e->getMessage());
            }
            return null;
        });

        if (!$weatherData || !isset($weatherData['current'])) {
            return response()->json($this->getFallbackData($location, $isCurrentLocation));
        }

        $current = $weatherData['current'];
        $daily = $weatherData['daily'] ?? [];
        $code = $current['weather_code'] ?? 0;
        $conditionMeta = $this->interpretWeatherCode($code, $current['is_day'] ?? 1);

        $uvIndex = isset($daily['uv_index_max'][0]) ? round($daily['uv_index_max'][0]) : 6;

        // Process 5-day forecast
        $forecast = [];
        if (isset($daily['time'])) {
            foreach ($daily['time'] as $i => $dateStr) {
                $dayCode = $daily['weather_code'][$i] ?? 0;
                $dayMeta = $this->interpretWeatherCode($dayCode, 1);
                $dayOfWeek = date('D', strtotime($dateStr));
                
                $forecast[] = [
                    'date' => $dateStr,
                    'day' => $dayOfWeek,
                    'temp_max' => round($daily['temperature_2m_max'][$i] ?? 30),
                    'temp_min' => round($daily['temperature_2m_min'][$i] ?? 24),
                    'condition' => $dayMeta['description'],
                    'icon' => $dayMeta['icon'],
                    'fa_icon' => $dayMeta['fa_icon'],
                    'rain_prob' => round($daily['precipitation_probability_max'][$i] ?? 0)
                ];
            }
        }

        return response()->json([
            'success' => true,
            'location' => $location,
            'is_current_location' => $isCurrentLocation,
            'latitude' => $lat,
            'longitude' => $lng,
            'temperature' => round($current['temperature_2m'] ?? 29),
            'feels_like' => round($current['apparent_temperature'] ?? $current['temperature_2m'] ?? 29),
            'humidity' => round($current['relative_humidity_2m'] ?? 70),
            'wind_speed' => round($current['wind_speed_10m'] ?? 12),
            'uv_index' => $uvIndex,
            'condition' => $conditionMeta['description'],
            'icon' => $conditionMeta['icon'],
            'fa_icon' => $conditionMeta['fa_icon'],
            'is_day' => (bool)($current['is_day'] ?? true),
            'forecast' => $forecast,
            'is_fallback' => false,
            'updated_at' => now()->toIso8601String()
        ]);
    }

    /**
     * Resolve requested location to human-readable city/town name and flag if current location.
     */
    private function resolveLocationName(float $lat, float $lng, string $requestedLocation, bool $isCurrentLoc): array
    {
        if (!$isCurrentLoc && $requestedLocation !== 'San Fernando, La Union' && !empty($requestedLocation)) {
            return ['name' => $requestedLocation, 'is_current' => false];
        }

        // Cache reverse geocode result per coordinate grid for 24h
        $cacheKey = "geo_reverse_" . number_format($lat, 3, '.', '') . "_" . number_format($lng, 3, '.', '');
        $resolvedName = Cache::remember($cacheKey, 86400, function () use ($lat, $lng) {
            try {
                $geoRes = Http::timeout(3)
                    ->withHeaders(['User-Agent' => 'IntanElyuTourism/1.0'])
                    ->get('https://nominatim.openstreetmap.org/reverse', [
                        'format' => 'json',
                        'lat' => $lat,
                        'lon' => $lng,
                        'zoom' => 12
                    ]);

                if ($geoRes->successful()) {
                    $address = $geoRes->json('address') ?? [];
                    $town = $address['town'] ?? $address['city'] ?? $address['municipality'] ?? $address['village'] ?? $address['county'] ?? null;
                    $state = $address['state'] ?? $address['region'] ?? 'La Union';

                    if ($town) {
                        return "{$town}, {$state}";
                    }
                }
            } catch (\Throwable $e) {
                Log::debug('Reverse geocode failed: ' . $e->getMessage());
            }

            return $this->getNearestMunicipality($lat, $lng);
        });

        return [
            'name' => $resolvedName,
            'is_current' => true
        ];
    }

    /**
     * Calculate nearest municipality in La Union based on coordinates
     */
    private function getNearestMunicipality(float $lat, float $lng): string
    {
        $municipalities = [
            'San Fernando' => [16.6159, 120.3167],
            'San Juan'     => [16.671123, 120.338487],
            'Bauang'       => [16.5319, 120.3298],
            'Agoo'         => [16.3217, 120.3667],
            'Luna'         => [16.8554, 120.3758],
            'Bangar'       => [16.8942, 120.4245],
            'Naguilian'    => [16.5366, 120.3926],
            'Bacnotan'     => [16.7202, 120.3353],
            'Balaoan'      => [16.8228, 120.4005],
            'Aringay'      => [16.3958, 120.3325],
            'Caba'         => [16.4292, 120.3344],
            'Rosario'      => [16.2286, 120.4850],
            'Pugo'         => [16.3167, 120.4667],
            'Tubao'        => [16.3470, 120.4126],
            'Sto. Tomas'   => [16.2842, 120.3861],
            'Santol'       => [16.7686, 120.4578],
            'Sudipen'      => [16.9031, 120.4700],
            'San Gabriel'  => [16.6711, 120.4050],
            'Bagulin'      => [16.6072, 120.4422],
            'Burgos'       => [16.5183, 120.4578]
        ];

        $closestName = 'San Fernando';
        $minDist = 999999;

        foreach ($municipalities as $name => $coords) {
            $dist = hypot($lat - $coords[0], $lng - $coords[1]);
            if ($dist < $minDist) {
                $minDist = $dist;
                $closestName = $name;
            }
        }

        return "{$closestName}, La Union";
    }

    /**
     * Map WMO Weather Codes to descriptions, emojis, and FontAwesome icons
     */
    private function interpretWeatherCode(int $code, int $isDay = 1): array
    {
        switch ($code) {
            case 0:
                return [
                    'description' => $isDay ? 'Clear Sky' : 'Clear Night',
                    'icon' => $isDay ? '☀️' : '🌙',
                    'fa_icon' => $isDay ? 'fa-sun' : 'fa-moon'
                ];
            case 1:
                return [
                    'description' => 'Mainly Clear',
                    'icon' => $isDay ? '🌤️' : '🌙',
                    'fa_icon' => $isDay ? 'fa-cloud-sun' : 'fa-cloud-moon'
                ];
            case 2:
                return [
                    'description' => 'Partly Cloudy',
                    'icon' => $isDay ? '⛅' : '☁️',
                    'fa_icon' => $isDay ? 'fa-cloud-sun' : 'fa-cloud'
                ];
            case 3:
                return [
                    'description' => 'Overcast',
                    'icon' => '☁️',
                    'fa_icon' => 'fa-cloud'
                ];
            case 45:
            case 48:
                return [
                    'description' => 'Foggy',
                    'icon' => '🌫️',
                    'fa_icon' => 'fa-smog'
                ];
            case 51:
            case 53:
            case 55:
                return [
                    'description' => 'Light Drizzle',
                    'icon' => '🌦️',
                    'fa_icon' => 'fa-cloud-rain'
                ];
            case 61:
            case 63:
                return [
                    'description' => 'Moderate Rain',
                    'icon' => '🌧️',
                    'fa_icon' => 'fa-cloud-showers-heavy'
                ];
            case 65:
                return [
                    'description' => 'Heavy Rain',
                    'icon' => '⛈️',
                    'fa_icon' => 'fa-cloud-showers-water'
                ];
            case 80:
            case 81:
            case 82:
                return [
                    'description' => 'Rain Showers',
                    'icon' => '🌦️',
                    'fa_icon' => 'fa-cloud-sun-rain'
                ];
            case 95:
            case 96:
            case 99:
                return [
                    'description' => 'Thunderstorm',
                    'icon' => '🌩️',
                    'fa_icon' => 'fa-bolt'
                ];
            default:
                return [
                    'description' => 'Partly Cloudy',
                    'icon' => '⛅',
                    'fa_icon' => 'fa-cloud-sun'
                ];
        }
    }

    /**
     * Fallback data in case Open-Meteo service is unreachable
     */
    private function getFallbackData(string $location, bool $isCurrentLoc = false): array
    {
        return [
            'success' => true,
            'location' => $location,
            'is_current_location' => $isCurrentLoc,
            'latitude' => 16.6159,
            'longitude' => 120.3209,
            'temperature' => 29,
            'feels_like' => 31,
            'humidity' => 72,
            'wind_speed' => 14,
            'uv_index' => 6,
            'condition' => 'Partly Cloudy',
            'icon' => '⛅',
            'fa_icon' => 'fa-cloud-sun',
            'is_day' => true,
            'forecast' => [
                ['day' => date('D'), 'temp_max' => 31, 'temp_min' => 25, 'condition' => 'Partly Cloudy', 'icon' => '⛅', 'fa_icon' => 'fa-cloud-sun', 'rain_prob' => 20],
                ['day' => date('D', strtotime('+1 day')), 'temp_max' => 30, 'temp_min' => 24, 'condition' => 'Sunny', 'icon' => '☀️', 'fa_icon' => 'fa-sun', 'rain_prob' => 10],
                ['day' => date('D', strtotime('+2 days')), 'temp_max' => 29, 'temp_min' => 24, 'condition' => 'Rain Showers', 'icon' => '🌦️', 'fa_icon' => 'fa-cloud-sun-rain', 'rain_prob' => 60],
                ['day' => date('D', strtotime('+3 days')), 'temp_max' => 31, 'temp_min' => 25, 'condition' => 'Partly Cloudy', 'icon' => '⛅', 'fa_icon' => 'fa-cloud-sun', 'rain_prob' => 30],
                ['day' => date('D', strtotime('+4 days')), 'temp_max' => 32, 'temp_min' => 26, 'condition' => 'Sunny', 'icon' => '☀️', 'fa_icon' => 'fa-sun', 'rain_prob' => 15],
            ],
            'is_fallback' => true,
            'updated_at' => now()->toIso8601String()
        ];
    }
}
