<?php

namespace App\Http\Controllers;

use App\Models\Merchandise;
use App\Models\MerchReservation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MerchandiseController extends Controller
{
    // === MOBILE APP / TOURIST ENDPOINTS ===

    public function index(Request $request)
    {
        // Fetch all merch that has stock > 0
        $items = Merchandise::where('stock', '>', 0)->get();
        return response()->json([
            'status' => 'success',
            'data' => $items
        ]);
    }

    public function reserve(Request $request)
    {
        $request->validate([
            'merchandise_id' => 'required|integer|exists:merchandises,id',
        ]);

        $user = $request->user();

        $result = DB::transaction(function () use ($request, $user) {
            // Lock the merchandise row to prevent race conditions
            $merch = Merchandise::where('id', $request->merchandise_id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($merch->stock <= 0) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'This item is out of stock.'
                ], 400);
            }

            if ($user->xp < $merch->price_xp) {
                return response()->json([
                    'status' => 'error',
                    'message' => "Insufficient XP. You need {$merch->price_xp} XP but only have {$user->xp} XP."
                ], 400);
            }

            // Deduct XP and decrement stock atomically
            $user->xp -= $merch->price_xp;
            $user->save();

            $merch->stock -= 1;
            $merch->save();

            $reservation = MerchReservation::create([
                'user_id' => $user->id,
                'merchandise_id' => $merch->id,
                'status' => 'pending'
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Reservation successful.',
                'data' => $reservation,
                'new_xp' => $user->xp
            ]);
        });

        return $result;
    }
}
