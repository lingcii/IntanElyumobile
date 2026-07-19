<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    /**
     * POST /api/auth/login
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email'    => 'required|string',
            'password' => 'required|string',
        ]);

        $loginInput = $request->email;
        $user = User::where('email', $loginInput)
            ->orWhere('name', $loginInput)
            ->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['error' => 'Invalid credentials.'], 401);
        }

        if ($user->status !== 'active') {
            return response()->json(['error' => 'Account is inactive.'], 403);
        }

        // Store session
        $request->session()->put('user_id',              $user->id);
        $request->session()->put('user_name',            $user->name);
        $request->session()->put('user_email',           $user->email);
        $request->session()->put('user_role',            $user->role);
        $request->session()->put('user_municipality_id', $user->municipality_id);
        $request->session()->regenerate();

        // Generate an API token for mobile (Bearer token auth)
        $token = \Illuminate\Support\Str::random(60);
        \Illuminate\Support\Facades\DB::table('users')
            ->where('id', $user->id)
            ->update(['last_activity' => now(), 'api_token' => $token]);

        return response()->json([
            'success' => true,
            'token'   => $token,
            'user'    => [
                'id'              => $user->id,
                'name'            => $user->name,
                'email'           => $user->email,
                'role'            => $user->role,
                'municipality_id' => $user->municipality_id,
                'xp'              => $user->xp ?? 0,
                'level'           => $user->level ?? 1,
                'avatar'          => $user->avatar,
            ],
        ]);
    }

    /**
     * POST /api/auth/google
     */
    public function googleLogin(Request $request): JsonResponse
    {
        $email = $request->input('email');
        $name = $request->input('name');
        $google_id = $request->input('google_id');
        $avatar = $request->input('avatar');

        if ($request->has('credential')) {
            $credential = $request->input('credential');
            $parts = explode('.', $credential);
            if (count($parts) === 3) {
                $payload = json_decode(base64_decode(str_replace(['-', '_'], ['+', '/'], $parts[1])));
                if ($payload) {
                    $email = $payload->email ?? null;
                    $name = $payload->name ?? null;
                    $google_id = 'g_' . ($payload->sub ?? '');
                    $avatar = $payload->picture ?? null;
                }
            }
        } else {
            $request->validate([
                'email'     => 'required|email',
                'name'      => 'required|string',
                'google_id' => 'required|string',
                'avatar'    => 'nullable|string',
            ]);
        }

        if (!$email || !$name || !$google_id) {
            return response()->json(['error' => 'Invalid Google authentication data.'], 400);
        }

        $user = User::where('google_id', $google_id)
            ->orWhere('email', $email)
            ->first();

        if ($user) {
            $updated = false;
            if (!$user->google_id) {
                $user->google_id = $google_id;
                $updated = true;
            }
            if ($avatar && !$user->avatar) {
                $user->avatar = $avatar;
                $updated = true;
            }
            if ($updated) {
                $user->save();
            }
        } else {
            $user = User::create([
                'name'              => $name,
                'email'             => $email,
                'password'          => Hash::make(\Illuminate\Support\Str::random(16)),
                'role'              => 'tourist',
                'status'            => 'active',
                'xp'                => 0,
                'level'             => 1,
                'avatar'            => $avatar,
                'google_id'         => $google_id,
            ]);

            // Send welcome confirmation email
            try {
                \Illuminate\Support\Facades\Mail::to($user->email)->send(new \App\Mail\TouristWelcomeMail($user));
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::warning('TouristWelcomeMail failed for Google user #' . $user->id . ': ' . $e->getMessage());
            }
        }

        if ($user->status !== 'active') {
            return response()->json(['error' => 'Account is inactive.'], 403);
        }

        $request->session()->put('user_id',              $user->id);
        $request->session()->put('user_name',            $user->name);
        $request->session()->put('user_email',           $user->email);
        $request->session()->put('user_role',            $user->role);
        $request->session()->put('user_municipality_id', $user->municipality_id);
        $request->session()->regenerate();

        $token = \Illuminate\Support\Str::random(60);
        \Illuminate\Support\Facades\DB::table('users')
            ->where('id', $user->id)
            ->update(['last_activity' => now(), 'api_token' => $token]);

        return response()->json([
            'success' => true,
            'token'   => $token,
            'user'    => [
                'id'              => $user->id,
                'name'            => $user->name,
                'email'           => $user->email,
                'role'            => $user->role,
                'municipality_id' => $user->municipality_id,
                'xp'              => $user->xp ?? 0,
                'level'           => $user->level ?? 1,
                'avatar'          => $user->avatar,
            ],
        ]);
    }

    /**
     * POST /api/auth/forgot-password
     */
    public function sendResetLinkEmail(Request $request): JsonResponse
    {
        $request->validate(['email' => 'required|email']);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'success' => true,
                'message' => 'If your email is registered, we have sent a reset link.'
            ]);
        }

        $token = \Illuminate\Support\Str::random(60);
        $tokenHash = hash('sha256', $token);

        \Illuminate\Support\Facades\DB::table('frontend_password_resets')->where('email', $request->email)->delete();
        \Illuminate\Support\Facades\DB::table('frontend_password_resets')->insert([
            'email'      => $request->email,
            'token_hash' => $tokenHash,
            'expires_at' => now()->addMinutes(60),
            'created_at' => now(),
            'used'       => 0,
        ]);

        try {
            \Illuminate\Support\Facades\Mail::to($user->email)->send(new \App\Mail\PasswordResetMail($user, $token));
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('PasswordResetMail failed for user #' . $user->id . ': ' . $e->getMessage());
            return response()->json(['error' => 'Failed to send email. Please try again later.'], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'Reset link email sent successfully.'
        ]);
    }

    /**
     * POST /api/auth/reset-password
     */
    public function resetPassword(Request $request): JsonResponse
    {
        $request->validate([
            'token'    => 'required|string',
            'email'    => 'required|email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $tokenHash = hash('sha256', $request->token);

        $record = \Illuminate\Support\Facades\DB::table('frontend_password_resets')
            ->where('email', $request->email)
            ->where('token_hash', $tokenHash)
            ->where('used', 0)
            ->first();

        if (!$record) {
            return response()->json(['error' => 'Invalid email or expired token.'], 400);
        }

        if (\Illuminate\Support\Carbon::parse($record->expires_at)->isPast()) {
            \Illuminate\Support\Facades\DB::table('frontend_password_resets')->where('id', $record->id)->delete();
            return response()->json(['error' => 'Token has expired.'], 400);
        }

        $user = User::where('email', $request->email)->first();
        if ($user) {
            $user->password = \Illuminate\Support\Facades\Hash::make($request->password);
            $user->save();
        }

        \Illuminate\Support\Facades\DB::table('frontend_password_resets')->where('id', $record->id)->update(['used' => 1]);

        return response()->json([
            'success' => true,
            'message' => 'Your password has been reset successfully.'
        ]);
    }
}
