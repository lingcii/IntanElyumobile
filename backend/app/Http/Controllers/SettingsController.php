<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class SettingsController extends Controller
{
    /**
     * GET /api/{role}/settings/profile
     */
    public function profile(Request $request): JsonResponse
    {
        $user = User::with('municipality:id,name')
            ->findOrFail((int) $request->session()->get('user_id'));

        return response()->json(['success' => true, 'user' => $user->makeHidden('password')]);
    }

    /**
     * PUT /api/{role}/settings/profile
     */
    public function updateProfile(Request $request): JsonResponse
    {
        $id = (int) $request->session()->get('user_id');
        $request->validate([
            'name'  => 'required|string|max:255',
            'email' => "required|email|unique:users,email,{$id}",
        ]);

        $user = User::findOrFail($id);
        $user->update(['name' => $request->name, 'email' => $request->email]);

        // Keep session in sync
        $request->session()->put('user_name',  $user->name);
        $request->session()->put('user_email', $user->email);

        return response()->json(['success' => true, 'message' => 'Profile updated.']);
    }

    /**
     * PUT /api/{role}/settings/password
     */
    public function updatePassword(Request $request): JsonResponse
    {
        $request->validate([
            'current_password' => 'required|string',
            'new_password'     => 'required|string|min:6|confirmed',
        ]);

        $user = User::findOrFail((int) $request->session()->get('user_id'));

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json(['error' => 'Current password is incorrect.'], 422);
        }

        $user->update(['password' => Hash::make($request->new_password)]);

        return response()->json(['success' => true, 'message' => 'Password updated.']);
    }
}
