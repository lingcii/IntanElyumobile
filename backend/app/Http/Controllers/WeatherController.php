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
        $cacheKey = "weather_v2_{$cacheLat}_{$cacheLng}";

        // Cache weather data for 15 minutes (900 seconds)
        $weatherData = Cache::remember($cacheKey, 900, function () use ($lat, $lng) {
            try {
                $response = Http::timeout(6)->get('https://api.open-meteo.com/v1/forecast', [
                    'latitude' => $lat,
                    'longitude' => $lng,
                    'current' => 'temperature_2m,relative_humidity_2m,weather_code,wind_speed_10m,is_day,apparent_temperature',
                    'hourly' => 'temperature_2m,precipitation_probability,weather_code,wind_speed_10m',
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
        $hourly = $weatherData['hourly'] ?? [];
        $code = (int) ($current['weather_code'] ?? 0);
        $temp = (int) round($current['temperature_2m'] ?? 29);
        $feelsLike = (int) round($current['apparent_temperature'] ?? $current['temperature_2m'] ?? 29);
        $humidity = (int) round($current['relative_humidity_2m'] ?? 70);
        $windSpeed = (int) round($current['wind_speed_10m'] ?? 12);
        $conditionMeta = $this->interpretWeatherCode($code, $current['is_day'] ?? 1);

        $uvIndex = isset($daily['uv_index_max'][0]) ? (int) round($daily['uv_index_max'][0]) : 6;
        $todayRainProb = isset($daily['precipitation_probability_max'][0]) ? (int) round($daily['precipitation_probability_max'][0]) : 0;

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

        $smartInsights = $this->buildSmartWeatherInsights($code, $temp, $feelsLike, $windSpeed, $uvIndex, $todayRainProb, $hourly);

        return response()->json([
            'success' => true,
            'location' => $location,
            'is_current_location' => $isCurrentLocation,
            'latitude' => $lat,
            'longitude' => $lng,
            'temperature' => $temp,
            'feels_like' => $feelsLike,
            'humidity' => $humidity,
            'wind_speed' => $windSpeed,
            'uv_index' => $uvIndex,
            'weather_code' => $code,
            'weather_group' => $smartInsights['weather_group'],
            'short_description' => $smartInsights['short_description'],
            'best_time' => $smartInsights['best_time'],
            'alerts' => $smartInsights['alerts'],
            'hourly_slots' => $smartInsights['hourly_slots'],
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
     * Build smart weather category group, short description, best visiting hours from hourly forecast, and weather alerts.
     */
    private function buildSmartWeatherInsights(int $code, int $temp, int $feelsLike, int $windSpeed, int $uvIndex, int $todayRainProb, array $hourly): array
    {
        $isSevereCode = in_array($code, [65, 67, 82, 95, 96, 99], true);
        $isRainyCode = in_array($code, [51, 53, 55, 56, 57, 61, 63, 66, 80, 81], true);
        $isCloudyCode = in_array($code, [3, 45, 48], true);
        $isPartlyCloudyCode = in_array($code, [1, 2], true);

        if ($isSevereCode || $windSpeed >= 40) {
            $weatherGroup = 'severe';
            $shortDesc = 'Severe weather detected — stay indoors and avoid coastal, mountain, and waterfall areas.';
        } elseif ($isRainyCode) {
            $weatherGroup = 'rainy';
            $shortDesc = 'Rainy skies today — ideal for museums, churches, cultural landmarks, and indoor dining.';
        } elseif ($isCloudyCode) {
            $weatherGroup = 'cloudy';
            $shortDesc = 'Cool overcast skies — comfortable for heritage sites, gardens, farms, and sightseeing.';
        } elseif ($isPartlyCloudyCode) {
            $weatherGroup = 'partly_cloudy';
            $shortDesc = 'Pleasant partly cloudy weather — great for nature trails, eco-parks, and outdoor activities.';
        } else {
            $weatherGroup = 'sunny';
            $shortDesc = 'Bright sunny weather — perfect for beaches, coastal viewpoints, parks, and outdoor adventures.';
        }

        // Build Weather Alerts
        $alerts = [];
        if ($weatherGroup === 'severe') {
            $alerts[] = [
                'level' => 'danger',
                'title' => 'Severe Weather Safety Warning',
                'message' => 'Heavy rain, thunderstorms, or strong winds active. Avoid swimming, surfing, mountain hiking, and waterfalls.'
            ];
        } elseif ($weatherGroup === 'rainy' || $todayRainProb >= 65) {
            $alerts[] = [
                'level' => 'warning',
                'title' => 'Wet Weather Advisory',
                'message' => "High chance of rain ({$todayRainProb}%). Trails and coastal rocks may be slippery; bring rain gear or visit indoor sites."
            ];
        }
        if ($windSpeed >= 28 && $weatherGroup !== 'severe') {
            $alerts[] = [
                'level' => 'warning',
                'title' => 'Strong Coastal Winds (' . $windSpeed . ' km/h)',
                'message' => 'Expect stronger waves along San Juan and coastal beaches. Exercise caution during water activities.'
            ];
        }
        if (($uvIndex >= 8 || $feelsLike >= 37) && in_array($weatherGroup, ['sunny', 'partly_cloudy'], true)) {
            $alerts[] = [
                'level' => 'caution',
                'title' => "High UV & Heat Advisory (UV {$uvIndex} · Feels {$feelsLike}°C)",
                'message' => 'Stay hydrated, apply sunscreen, and avoid prolonged direct sun between 11:00 AM and 2:00 PM.'
            ];
        }

        // Compute Best Time to Visit from today's hourly forecast (06:00 to 18:00)
        $bestTime = '7:00 AM – 10:00 AM & 3:30 PM – 5:30 PM (Cooler hours)';
        $hourlySlots = [];
        if (!empty($hourly['time']) && is_array($hourly['time'])) {
            $todayPrefix = now('Asia/Manila')->format('Y-m-d');
            $currentHour = (int) now('Asia/Manila')->format('G');
            $dayCandidates = [];

            foreach ($hourly['time'] as $idx => $isoTime) {
                if (!str_starts_with((string) $isoTime, $todayPrefix)) {
                    continue;
                }
                $hr = (int) substr((string) $isoTime, 11, 2);
                $hTemp = (int) round($hourly['temperature_2m'][$idx] ?? $temp);
                $hRain = (int) round($hourly['precipitation_probability'][$idx] ?? 0);
                $hCode = (int) ($hourly['weather_code'][$idx] ?? 0);
                $hMeta = $this->interpretWeatherCode($hCode, ($hr >= 6 && $hr < 18) ? 1 : 0);

                if ($hr >= max(6, $currentHour) && $hr <= 20 && count($hourlySlots) < 6) {
                    $hourlySlots[] = [
                        'hour_label' => date('g A', strtotime($isoTime)),
                        'temp' => $hTemp,
                        'rain_prob' => $hRain,
                        'icon' => $hMeta['icon'],
                        'condition' => $hMeta['description'],
                    ];
                }

                // Daytime visiting hours (6 AM to 5 PM)
                if ($hr >= 6 && $hr <= 17 && !in_array($hCode, [65, 67, 82, 95, 96, 99], true)) {
                    // Lower score is better: prioritize low rain probability and comfortable temp (~27°C) + avoid midday scorching heat
                    $middayPenalty = ($hr >= 11 && $hr <= 14 && $hTemp >= 31) ? 18 : 0;
                    $score = ($hRain * 1.4) + (abs($hTemp - 27) * 3) + $middayPenalty;
                    $dayCandidates[] = [
                        'hour' => $hr,
                        'temp' => $hTemp,
                        'rain' => $hRain,
                        'score' => $score,
                    ];
                }
            }

            if ($weatherGroup === 'severe') {
                $bestTime = 'Postpone outdoor visits until severe weather clears';
            } elseif (!empty($dayCandidates)) {
                usort($dayCandidates, fn($a, $b) => $a['score'] <=> $b['score']);
                $best = $dayCandidates[0];
                $startHr = $best['hour'];
                $endHr = min(18, $startHr + 2);
                $startFormatted = date('g:00 A', mktime($startHr, 0, 0));
                $endFormatted = date('g:00 A', mktime($endHr, 0, 0));
                $bestTime = "{$startFormatted} – {$endFormatted} (~{$best['temp']}°C, {$best['rain']}% rain)";
            }
        } elseif ($weatherGroup === 'severe') {
            $bestTime = 'Postpone outdoor visits until severe weather clears';
        }

        return [
            'weather_group' => $weatherGroup,
            'short_description' => $shortDesc,
            'best_time' => $bestTime,
            'alerts' => $alerts,
            'hourly_slots' => $hourlySlots,
        ];
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
            'weather_code' => 2,
            'weather_group' => 'partly_cloudy',
            'short_description' => 'Pleasant partly cloudy weather — great for nature trails, eco-parks, and outdoor activities.',
            'best_time' => '7:00 AM – 10:00 AM & 3:30 PM – 5:30 PM (~28°C, 20% rain)',
            'alerts' => [],
            'hourly_slots' => [],
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
