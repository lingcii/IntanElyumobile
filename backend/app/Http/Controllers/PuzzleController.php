<?php

namespace App\Http\Controllers;

use App\Models\TouristSpot;
use Illuminate\Http\JsonResponse;

class PuzzleController extends Controller
{
    public function spots(): JsonResponse
    {
        try {
            $spots = TouristSpot::activeForTourists()
                ->with(['municipality', 'images'])
                ->orderBy('name')
                ->get();

            $r2PublicBase = rtrim(env('CLOUDFLARE_R2_URL', 'https://pub-268a50c87a9249ccbf90d35e77ddc65b.r2.dev'), '/');

            $resolveImageUrl = function (?string $pUrl) use ($r2PublicBase): ?string {
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
                return $pUrl;
            };

            $formatted = $spots->flatMap(function ($spot) use ($resolveImageUrl) {
                $munName = $spot->municipality ? $spot->municipality->name : 'La Union';
                $imageUrls = collect([$spot->photo_url])
                    ->merge($spot->images->pluck('photo_url'))
                    ->filter()
                    ->map($resolveImageUrl)
                    ->filter()
                    ->unique()
                    ->values();

                return $imageUrls->map(fn ($imageUrl, $imageIndex) => [
                    'id' => $spot->id . '-' . $imageIndex,
                    'spot_id' => $spot->id,
                    'name' => $spot->name,
                    'location' => $munName,
                    'image' => $imageUrl,
                    'desc' => "Rearrange the tiles to reveal the image of {$spot->name} in {$munName}! Solve to earn <strong style=\"color: #38bdf8;\">+100 Points</strong>."
                ]);
            })->values();

            return response()->json([
                'status' => 'success',
                'spots' => $formatted
            ])->header('Cache-Control', 'no-store, no-cache, must-revalidate');
        } catch (\Throwable $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
}
