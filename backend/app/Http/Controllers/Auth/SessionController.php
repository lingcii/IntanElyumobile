<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SessionController extends Controller
{
    /**
     * GET /api/auth/check
     */
    public function check(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user && $request->bearerToken()) {
            $user = \App\Models\User::where('api_token', $request->bearerToken())->first();
        }
        if (!$user && $request->hasSession() && $request->session()->has('user_id')) {
            $user = \App\Models\User::find($request->session()->get('user_id'));
        }

        if (!$user || ($user->status ?? 'active') !== 'active') {
            return response()->json(['authenticated' => false], 401);
        }

        return response()->json([
            'authenticated' => true,
            'user' => [
                'id'              => $user->id,
                'name'            => $user->name,
                'email'           => $user->email,
                'role'            => $user->role,
                'municipality_id' => $user->municipality_id,
            ],
        ]);
    }
}
