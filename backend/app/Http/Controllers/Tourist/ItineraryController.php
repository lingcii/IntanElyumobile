<?php

namespace App\Http\Controllers\Tourist;

use App\Http\Controllers\Controller;
use App\Models\Itinerary;
use App\Models\ItineraryItem;
use App\Models\TouristSpot;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ItineraryController extends Controller
{
    /**
     * GET /api/tourist/itineraries
     * Returns all saved trips for the authenticated tourist.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $itineraries = Itinerary::where('user_id', $user->id)
            ->with(['items.destination:id,name,photo_url,latitude,longitude,entrance_fee,classification_status'])
            ->orderByDesc('created_at')
            ->get()
            ->map(function ($itinerary) {
                $items = $itinerary->items->map(function ($item) {
                    $dest = $item->destination;
                    $imageUrl = $dest ? $dest->photo_url : null;

                    return [
                        'id'          => $item->id,
                        'is_visited'  => $item->is_visited,
                        'proof_image' => $item->proof_image,
                        'visited_at'  => $item->visited_at,
                        'destination' => $dest ? [
                            'id'           => $dest->id,
                            'name'         => $dest->name,
                            'image'        => $imageUrl,
                            'latitude'     => $dest->latitude,
                            'longitude'    => $dest->longitude,
                            'entrance_fee' => $dest->entrance_fee,
                            'classification_status' => $dest->classification_status,
                        ] : null,
                    ];
                });

                return [
                    'id'             => $itinerary->id,
                    'title'          => $itinerary->title,
                    'trip_date'      => $itinerary->trip_date?->format('Y-m-d'),
                    'budget'         => $itinerary->budget,
                    'total_cost'     => $itinerary->total_cost,
                    'status'         => $itinerary->status,
                    'route_type'     => $itinerary->route_type,
                    'transport_mode' => $itinerary->transport_mode,
                    'items'          => $items,
                ];
            });

        return response()->json(['itineraries' => $itineraries]);
    }

    /**
     * GET /api/tourist/itineraries/{id}
     * Return a single itinerary with items and destinations.
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $itinerary = Itinerary::where('user_id', $user->id)
            ->with(['items.destination:id,name,photo_url,latitude,longitude,entrance_fee,classification_status'])
            ->findOrFail($id);

        $items = $itinerary->items->map(function ($item) {
            $dest = $item->destination;
            $imageUrl = $dest ? $dest->photo_url : null;
            return [
                'id'          => $item->id,
                'is_visited'  => $item->is_visited,
                'proof_image' => $item->proof_image,
                'visited_at'  => $item->visited_at,
                'destination' => $dest ? [
                    'id'           => $dest->id,
                    'name'         => $dest->name,
                    'image'        => $imageUrl,
                    'latitude'     => $dest->latitude,
                    'longitude'    => $dest->longitude,
                    'entrance_fee' => $dest->entrance_fee,
                    'classification_status' => $dest->classification_status,
                ] : null,
            ];
        });

        return response()->json([
            'itinerary' => [
                'id'             => $itinerary->id,
                'title'          => $itinerary->title,
                'trip_date'      => $itinerary->trip_date?->format('Y-m-d'),
                'budget'         => $itinerary->budget,
                'total_cost'     => $itinerary->total_cost,
                'status'         => $itinerary->status,
                'route_type'     => $itinerary->route_type,
                'transport_mode' => $itinerary->transport_mode,
                'items'          => $items,
            ]
        ]);
    }

    /**
     * POST /api/tourist/itineraries
     * Save a draft plan as a named itinerary.
     *
     * Body:
     *   - title (required)
     *   - destinations (array of spot IDs, required)
     *   - trip_date (optional)
     *   - budget (optional)
     *   - transport (optional)
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'title'          => 'required|string|max:255',
            'destinations'   => 'required|array|min:1',
            'destinations.*' => 'integer|exists:tourist_spots,id',
            'trip_date'      => 'nullable|date',
            'budget'         => 'nullable|numeric|min:0',
            'route_type'     => 'nullable|string|max:255',
            'transport_mode' => 'nullable|string|max:255',
        ]);

        $user = $request->user();

        $itinerary = DB::transaction(function () use ($request, $user) {
            // Rough cost estimation based on entrance fees
            $spots = TouristSpot::whereIn('id', $request->destinations)->get();
            $totalFee = $spots->sum('entrance_fee');

            $itinerary = Itinerary::create([
                'user_id'        => $user->id,
                'title'          => $request->title,
                'trip_date'      => $request->trip_date,
                'budget'         => $request->budget,
                'total_cost'     => $totalFee,
                'status'         => 'pending',
                'route_type'     => $request->route_type,
                'transport_mode' => $request->transport_mode,
            ]);

            // Create itinerary items preserving order
            foreach ($request->destinations as $spotId) {
                ItineraryItem::create([
                    'itinerary_id'    => $itinerary->id,
                    'tourist_spot_id' => $spotId,
                ]);
            }

            return $itinerary;
        });

        return response()->json([
            'message'      => 'Trip saved! 🎉',
            'itinerary_id' => $itinerary->id,
        ], 201);
    }

    /**
     * PATCH /api/tourist/itineraries/{id}/complete
     * Mark an itinerary as completed.
     */
    public function markCompleted(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $itinerary = Itinerary::where('user_id', $user->id)->findOrFail($id);

        $itinerary->update(['status' => 'completed']);

        // Cache invalidation — flush stale profile/rank caches
        Cache::forget("rank:user:{$user->id}");
        Cache::forget("profile:trips:{$user->id}");

        return response()->json(['message' => 'Trip marked as completed! 🏁']);
    }

    /**
     * PUT /api/tourist/itineraries/{id}
     * Update an existing itinerary (title, trip_date, budget, destinations).
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $itinerary = Itinerary::where('user_id', $user->id)->findOrFail($id);

        $request->validate([
            'title'          => 'sometimes|string|max:255',
            'trip_date'      => 'nullable|date',
            'budget'         => 'nullable|numeric|min:0',
            'route_type'     => 'nullable|string|max:255',
            'transport_mode' => 'nullable|string|max:255',
        ]);

        $itinerary->update($request->only(['title', 'trip_date', 'budget', 'route_type', 'transport_mode']));

        return response()->json([
            'message'    => 'Trip updated successfully!',
            'itinerary'  => $itinerary->fresh()            ->load(['items.destination:id,name,photo_url,latitude,longitude,entrance_fee,classification_status']),
        ]);
    }

    /**
     * DELETE /api/tourist/itineraries/{id}
     * Delete an itinerary and its items.
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $itinerary = Itinerary::where('user_id', $user->id)->findOrFail($id);

        $itinerary->items()->delete();
        $itinerary->delete();

        // Cache invalidation — flush stale profile/rank caches
        Cache::forget("rank:user:{$user->id}");
        Cache::forget("profile:trips:{$user->id}");

        return response()->json(['message' => 'Trip deleted successfully.']);
    }
}
