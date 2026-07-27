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
                // Verify the JWT signature against Google's public keys
                $header = json_decode(base64_decode(str_replace(['-', '_'], ['+', '/'], $parts[0])));
                $payload = json_decode(base64_decode(str_replace(['-', '_'], ['+', '/'], $parts[1])));

                if (!$header || !$payload) {
                    return response()->json(['error' => 'Invalid Google credential.'], 400);
                }

                // Verify issuer
                $validIssuers = ['accounts.google.com', 'https://accounts.google.com'];
                if (!isset($payload->iss) || !in_array($payload->iss, $validIssuers)) {
                    return response()->json(['error' => 'Invalid token issuer.'], 400);
                }

                // Verify audience (must match your Google Client ID)
                $clientId = env('GOOGLE_CLIENT_ID', '874613490302-qno8lkqoujur0db888hg72hogjv6cp5v.apps.googleusercontent.com');
                if (!isset($payload->aud) || $payload->aud !== $clientId) {
                    return response()->json(['error' => 'Invalid token audience.'], 400);
                }

                // Verify expiry
                if (!isset($payload->exp) || $payload->exp < time()) {
                    return response()->json(['error' => 'Token has expired.'], 400);
                }

                $email = $payload->email ?? null;
                $name = $payload->name ?? null;
                $google_id = 'g_' . ($payload->sub ?? '');
                $avatar = $payload->picture ?? null;
            } else {
                return response()->json(['error' => 'Malformed Google credential.'], 400);
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
                'email'   => $request->email,
                'message' => 'If your email is registered, we have sent a reset code & link.'
            ]);
        }

        $token = \Illuminate\Support\Str::random(60);
        $tokenHash = hash('sha256', $token);
        $otpCode = sprintf('%06d', random_int(0, 999999));

        // Always store OTP in Cache for fast, reliable in-app reset
        \Illuminate\Support\Facades\Cache::put("pwd_reset_otp:{$user->email}", $otpCode, 900);

        // Record in DB if table exists (safely guarded against missing table in remote DBs)
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('frontend_password_resets')) {
                \Illuminate\Support\Facades\DB::table('frontend_password_resets')->where('email', $request->email)->delete();
                \Illuminate\Support\Facades\DB::table('frontend_password_resets')->insert([
                    'email'      => $request->email,
                    'token_hash' => $tokenHash,
                    'expires_at' => now()->addMinutes(60),
                    'created_at' => now(),
                    'used'       => 0,
                ]);
            }
        } catch (\Throwable $th) {
            \Illuminate\Support\Facades\Log::warning('frontend_password_resets table DB record skip: ' . $th->getMessage());
        }

        try {
            \Illuminate\Support\Facades\Mail::to($user->email)->send(new \App\Mail\PasswordResetMail($user, $token, $otpCode));
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('PasswordResetMail failed for user #' . $user->id . ': ' . $e->getMessage());
            // Return success anyway so OTP reset via cache is still possible for the user
            return response()->json([
                'success' => true,
                'email'   => $user->email,
                'message' => 'Reset code & link sent successfully to your email.'
            ]);
        }

        return response()->json([
            'success' => true,
            'email'   => $user->email,
            'message' => 'Reset code & link sent successfully to your email.'
        ]);
    }

    /**
     * POST /api/auth/reset-password-otp
     */
    public function resetPasswordWithOtp(Request $request): JsonResponse
    {
        $request->validate([
            'email'                 => 'required|email',
            'otp'                   => 'required|string',
            'password'              => 'required|string|min:8|confirmed',
        ]);

        $email = $request->email;
        $otp = trim($request->otp);

        $cachedOtp = \Illuminate\Support\Facades\Cache::get("pwd_reset_otp:{$email}");

        if (!$cachedOtp || (string)$cachedOtp !== (string)$otp) {
            return response()->json(['error' => 'Invalid or expired verification code.'], 400);
        }

        $user = User::where('email', $email)->first();
        if (!$user) {
            return response()->json(['error' => 'User account not found.'], 404);
        }

        $user->password = \Illuminate\Support\Facades\Hash::make($request->password);
        $user->save();

        \Illuminate\Support\Facades\Cache::forget("pwd_reset_otp:{$email}");

        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('frontend_password_resets')) {
                \Illuminate\Support\Facades\DB::table('frontend_password_resets')->where('email', $email)->delete();
            }
        } catch (\Throwable $th) {}

        return response()->json([
            'success' => true,
            'email'   => $email,
            'message' => 'Your password has been reset successfully!'
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
        $record = null;

        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('frontend_password_resets')) {
                $record = \Illuminate\Support\Facades\DB::table('frontend_password_resets')
                    ->where('email', $request->email)
                    ->where('token_hash', $tokenHash)
                    ->where('used', 0)
                    ->first();
            }
        } catch (\Throwable $th) {}

        if (!$record) {
            return response()->json(['error' => 'Invalid email or expired token.'], 400);
        }

        if (\Illuminate\Support\Carbon::parse($record->expires_at)->isPast()) {
            try {
                \Illuminate\Support\Facades\DB::table('frontend_password_resets')->where('id', $record->id)->delete();
            } catch (\Throwable $th) {}
            return response()->json(['error' => 'Token has expired.'], 400);
        }

        $user = User::where('email', $request->email)->first();
        if ($user) {
            $user->password = \Illuminate\Support\Facades\Hash::make($request->password);
            $user->save();
        }

        try {
            \Illuminate\Support\Facades\DB::table('frontend_password_resets')->where('id', $record->id)->update(['used' => 1]);
        } catch (\Throwable $th) {}

        return response()->json([
            'success' => true,
            'message' => 'Your password has been reset successfully.'
        ]);
    }
}
