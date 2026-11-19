<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LogoutController extends Controller
{
    /**
     * POST /api/auth/logout
     */
    public function logout(Request $request): JsonResponse
    {
        $bearerToken = $request->bearerToken() ?: $request->header('X-API-TOKEN');
        if ($bearerToken) {
            try {
                DB::table('users')->where('api_token', $bearerToken)->update(['api_token' => null]);
            } catch (\Throwable $e) {}
        }

        $sessionUserId = $request->session()->get('user_id');
        if ($sessionUserId) {
            try {
                DB::table('users')->where('id', $sessionUserId)->update(['api_token' => null]);
            } catch (\Throwable $e) {}
        }

        $request->session()->flush();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json(['success' => true, 'message' => 'Logged out successfully.']);
    }
}
