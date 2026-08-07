<?php

namespace App\Http\Controllers;

use App\Models\TouristSpot;
use Illuminate\Http\JsonResponse;

class PuzzleController extends Controller
{
    public function spots(): JsonResponse
    {
        try {
            $spots = TouristSpot::with('municipality')
                ->whereNotNull('photo_url')
                ->where('photo_url', '!=', '')
                ->where(function($q) {
                    $q->whereNull('status')->orWhere('status', 'approved');
                })
                ->inRandomOrder()
                ->limit(20)
                ->get();

            $r2PublicBase = rtrim(env('CLOUDFLARE_R2_URL', 'https://pub-268a50c87a9249ccbf90d35e77ddc65b.r2.dev'), '/');

            $formatted = $spots->map(function ($spot) use ($r2PublicBase) {
                $pUrl = $spot->photo_url;
                if ($pUrl) {
                    if (preg_match('/(spot_[a-z0-9_]+\.(?:jpg|jpeg|png|webp|gif))/i', $pUrl, $matches)) {
                        $pUrl = $r2PublicBase . '/tourist_spots/' . $matches[1];
                    } else if (str_contains($pUrl, 'file=')) {
                        $parts = parse_url($pUrl);
                        parse_str($parts['query'] ?? '', $query);
                        if (!empty($query['file'])) {
                            $file = ltrim($query['file'], '/');
                            $pUrl = preg_match('/^spot_/i', $file) ? $r2PublicBase . '/tourist_spots/' . $file : '/api/image/' . $file;
                        }
                    } else if (!str_starts_with($pUrl, 'http') && !str_starts_with($pUrl, '/')) {
                        $pUrl = preg_match('/^spot_/i', $pUrl) ? $r2PublicBase . '/tourist_spots/' . $pUrl : '/api/image/' . $pUrl;
                    }
                }
                
                $munName = $spot->municipality ? $spot->municipality->name : 'La Union';

                return [
                    'id' => $spot->id,
                    'name' => $spot->name,
                    'location' => $munName,
                    'image' => $pUrl,
                    'desc' => "Rearrange the tiles to reveal the image of {$spot->name} in {$munName}! Solve to earn <strong style=\"color: #38bdf8;\">+100 Points</strong>."
                ];
            });

            return response()->json([
                'status' => 'success',
                'spots' => $formatted
            ]);
        } catch (\Throwable $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
}
