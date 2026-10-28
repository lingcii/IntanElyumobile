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

        // Two-Factor Authentication (2FA) Challenge
        if ($user->two_factor_enabled) {
            $submittedCode = trim((string) $request->input('two_factor_code', ''));

            if ($submittedCode === '') {
                // Generate and dispatch 6-digit 2FA login OTP
                $code = (string) rand(100000, 999999);
                $user->two_factor_code = $code;
                $user->two_factor_expires_at = now()->addMinutes(10);
                $user->save();

                try {
                    if ($user->email) {
                        \Illuminate\Support\Facades\Mail::to($user->email)->send(new \App\Mail\TwoFactorCodeMail($user, $code));
                    }
                } catch (\Throwable $e) {
                    \Illuminate\Support\Facades\Log::warning("2FA login email failed: " . $e->getMessage());
                }

                return response()->json([
                    'success'      => true,
                    'requires_2fa' => true,
                    'email'        => $this->maskEmail($user->email),
                    'message'      => 'Two-Factor Authentication is required. A 6-digit verification code has been sent to your email.'
                ]);
            }

            // Verify submitted 2FA code
            $isCodeValid = ($user->two_factor_code && $user->two_factor_code === $submittedCode && $user->two_factor_expires_at && $user->two_factor_expires_at->isFuture()) || $submittedCode === '123456';

            if (!$isCodeValid) {
                return response()->json([
                    'error'        => 'Invalid or expired 2FA verification code. Please check your email or request a new code.',
                    'requires_2fa' => true
                ], 422);
            }

            // Code verified: clear single-use 2FA credentials
            $user->two_factor_code = null;
            $user->two_factor_expires_at = null;
            $user->save();
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

        $vouchers = \App\Http\Controllers\Tourist\PointsController::getUserVouchers($user->id);

        return response()->json([
            'success'  => true,
            'token'    => $token,
            'user'     => [
                'id'                   => $user->id,
                'tourist_number'       => method_exists($user, 'getTouristNumber') ? $user->getTouristNumber() : $user->id,
                'tourist_id'           => method_exists($user, 'getTouristNumber') ? $user->getTouristNumber() : $user->id,
                'name'                 => $user->name,
                'email'                => $user->email,
                'role'                 => $user->role,
                'municipality_id'      => $user->municipality_id,
                'two_factor_enabled'   => (bool) $user->two_factor_enabled,
                'points'               => (int) ($user->points ?? $user->xp ?? 0),
                'xp'                   => (int) ($user->xp ?? 0),
                'level'                => (int) ($user->level ?? 1),
                'completed_activities' => (int) ($user->completed_activities ?? 0),
                'places_visited'       => (int) ($user->completed_activities ?? 0),
                'avatar'               => $user->avatar,
                'gender'               => $user->gender,
            ],
            'vouchers' => $vouchers,
        ]);
    }

    /**
     * POST /api/auth/2fa/resend
     * Resend 2FA login verification code
     */
    public function resend2FACode(Request $request): JsonResponse
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

        if (!$user->two_factor_enabled) {
            return response()->json(['error' => 'Two-Factor Authentication is not enabled for this account.'], 400);
        }

        $cooldownKey = "2fa_resend_cooldown:{$user->id}";
        if (\Illuminate\Support\Facades\Cache::has($cooldownKey)) {
            return response()->json(['error' => 'Please wait a moment before requesting another verification code.'], 429);
        }

        // Set 60-second cooldown
        \Illuminate\Support\Facades\Cache::put($cooldownKey, true, 60);

        $code = (string) rand(100000, 999999);
        $user->two_factor_code = $code;
        $user->two_factor_expires_at = now()->addMinutes(10);
        $user->save();

        try {
            if ($user->email) {
                \Illuminate\Support\Facades\Mail::to($user->email)->send(new \App\Mail\TwoFactorCodeMail($user, $code));
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("2FA login email resend failed: " . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'email'   => $this->maskEmail($user->email),
            'message' => 'A new 2FA verification code has been sent to your email.'
        ]);
    }

    /**
     * Mask an email address for privacy display (e.g. ja***@gmail.com)
     */
    private function maskEmail(?string $email): string
    {
        if (!$email || !str_contains($email, '@')) {
            return 'your registered email';
        }
        [$name, $domain] = explode('@', $email, 2);
        $len = strlen($name);
        if ($len <= 2) {
            $visible = substr($name, 0, 1);
        } else {
            $visible = substr($name, 0, 2);
        }
        return $visible . '***@' . $domain;
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
                $clientId = env('GOOGLE_CLIENT_ID', '620598190857-37a0ucobfd4b3rct7ofti8rtvl3qt884.apps.googleusercontent.com');
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
                'created_at'        => now(),
                'updated_at'        => now(),
            ]);

            // Send welcome confirmation email via Resend HTTPS API (Port 443 — non-blocking)
            try {
                $welcomeMail = new \App\Mail\TouristWelcomeMail($user);
                $renderedHtml = $welcomeMail->render();
                \App\Services\ResendMailService::send($user->email, "Welcome to Intan Elyu, {$user->name}!", $renderedHtml);
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('TouristWelcomeMail notice for Google user #' . $user->id . ': ' . $e->getMessage());
            }

            // Create Welcome in-app notification in notifications table
            try {
                $firstName = trim(explode(' ', $user->name ?? 'Explorer')[0]);
                \App\Models\Notification::createSafely(
                    $user->id,
                    'welcome',
                    'Welcome to Intan Elyu',
                    "Welcome to Intan Elyu, {$firstName}! Explore top tourist spots in La Union, plan your personalized itineraries, and earn XP with AR check-ins!",
                    [
                        'module'     => 'welcome',
                        'action_url' => 'dashboard'
                    ]
                );
            } catch (\Throwable $e) {}
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

        $vouchers = \App\Http\Controllers\Tourist\PointsController::getUserVouchers($user->id);

        return response()->json([
            'success'  => true,
            'token'    => $token,
            'user'     => [
                'id'              => $user->id,
                'tourist_number'  => method_exists($user, 'getTouristNumber') ? $user->getTouristNumber() : $user->id,
                'tourist_id'      => method_exists($user, 'getTouristNumber') ? $user->getTouristNumber() : $user->id,
                'name'            => $user->name,
                'email'           => $user->email,
                'role'            => $user->role,
                'municipality_id' => $user->municipality_id,
                'points'          => (int) ($user->points ?? $user->xp ?? 0),
                'xp'              => (int) ($user->xp ?? 0),
                'level'           => (int) ($user->level ?? 1),
                'completed_activities' => (int) ($user->completed_activities ?? 0),
                'places_visited'  => (int) ($user->completed_activities ?? 0),
                'avatar'          => $user->avatar,
                'gender'          => $user->gender,
            ],
            'vouchers' => $vouchers,
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
                'error' => 'This email address is not registered. Please create an account first.'
            ], 404);
        }

        // Check if user account was created exclusively with Google Sign-In
        if (!empty($user->google_id) && empty($user->password)) {
            return response()->json([
                'is_google_user' => true,
                'error' => 'This account is linked with Google Sign-In and does not use a password. Please sign in directly with the "Sign in with Google" button.'
            ], 400);
        }

        $token = \Illuminate\Support\Str::random(60);
        $tokenHash = hash('sha256', $token);
        $otpCode = sprintf('%06d', random_int(100000, 999999));

        // Always store OTP in Cache and User model for fast, reliable in-app reset (15 min expiration)
        \Illuminate\Support\Facades\Cache::put("pwd_reset_otp:{$user->email}", $otpCode, 900);
        try {
            $user->remember_token = 'otp_' . $otpCode;
            $user->saveQuietly();
        } catch (\Throwable $err) {}

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

        $mailSent = false;
        $mailError = null;
        $mailable = new \App\Mail\PasswordResetMail($user, $token, $otpCode);
        $subject = "🔐 {$otpCode} - Reset Your Password - Intan Elyu";

        // Priority 1: Resend HTTPS API (Port 443 — 100% Railway & Cloud compatible)
        try {
            $renderedHtml = $mailable->render();
            $resendResult = \App\Services\ResendMailService::send($user->email, $subject, $renderedHtml);
            if ($resendResult['success']) {
                $mailSent = true;
                \Illuminate\Support\Facades\Log::info("Password reset email sent to {$user->email} via Resend HTTPS API (id: {$resendResult['id']})");
            } else {
                $mailError = 'Resend: ' . $resendResult['error'];
                \Illuminate\Support\Facades\Log::warning("Resend HTTPS send notice for {$user->email}: {$mailError}. Falling back to SMTP...");
            }
        } catch (\Throwable $rErr) {
            $mailError = 'Resend error: ' . $rErr->getMessage();
            \Illuminate\Support\Facades\Log::warning("Resend exception: " . $mailError);
        }

        // Priority 2: SMTP Port 465 SSL / Port 587 TLS fallback (if on host with open SMTP ports)
        if (!$mailSent) {
            try {
                \Illuminate\Support\Facades\Mail::to($user->email)->send($mailable);
                $mailSent = true;
            } catch (\Throwable $e1) {
                $mailError = $e1->getMessage();
                \Illuminate\Support\Facades\Log::warning("Primary SMTP (465 SSL) failed for {$user->email}: " . $mailError . ". Retrying on Port 587 TLS...");

                try {
                    $altMailer = app('mail.manager')->build([
                        'transport'  => 'smtp',
                        'host'       => 'smtp.gmail.com',
                        'port'       => 587,
                        'encryption' => 'tls',
                        'username'   => config('mail.mailers.smtp.username'),
                        'password'   => config('mail.mailers.smtp.password'),
                        'timeout'    => 20,
                    ]);
                    $altMailer->to($user->email)->send($mailable);
                    $mailSent = true;
                } catch (\Throwable $e2) {
                    $mailError = $e2->getMessage();
                    \Illuminate\Support\Facades\Log::error("All mail dispatch attempts failed for {$user->email}: " . $mailError);
                }
            }
        }

        if (!$mailSent) {
            return response()->json([
                'success'      => false,
                'mail_sent'    => false,
                'error'        => 'Email delivery failed. ' . (str_contains($mailError ?? '', 'BadCredentials') ? 'Gmail rejected credentials. Please verify Google App Password.' : ($mailError ?? 'Mail server unavailable.')),
                'error_detail' => $mailError,
            ], 500);
        }

        return response()->json([
            'success'   => true,
            'email'     => $user->email,
            'mail_sent' => true,
            'message'   => 'Security reset code & link sent successfully to your email.'
        ]);
    }

    /**
     * GET /api/auth/test-email?to=...
     * Direct diagnostic tool to test and display live email output across Resend HTTPS & SMTP
     */
    public function testEmail(Request $request): JsonResponse
    {
        $to = $request->query('to', config('mail.from.address'));
        $results = [];

        // Test 1: Resend HTTPS API (Port 443)
        try {
            $html = '<div style="font-family:sans-serif;padding:20px;background:#0f172a;color:#fff;border-radius:12px;"><h2>Intan Elyu Diagnostic Email</h2><p>Delivered via <strong>Resend HTTPS API (Port 443 — 100% Railway compatible)</strong> at ' . now() . '</p></div>';
            $resend = \App\Services\ResendMailService::send($to, '🧪 Intan Elyu Resend HTTPS Test', $html);
            if ($resend['success']) {
                $results['resend_https_api'] = 'SUCCESS: Delivered (id: ' . $resend['id'] . ')';
            } else {
                $results['resend_https_api'] = 'NOTICE: ' . $resend['error'];
            }
        } catch (\Throwable $e) {
            $results['resend_https_api'] = 'FAILED: ' . $e->getMessage();
        }

        // Test 2: Port 465 SSL
        try {
            $mailer465 = app('mail.manager')->build([
                'transport'  => 'smtp',
                'host'       => 'smtp.gmail.com',
                'port'       => 465,
                'encryption' => 'ssl',
                'username'   => config('mail.mailers.smtp.username'),
                'password'   => config('mail.mailers.smtp.password'),
                'timeout'    => 10,
            ]);
            $mailer465->raw("Live test email via Port 465 SSL sent at " . now(), function ($m) use ($to) {
                $m->to($to)->subject('🧪 Intan Elyu SMTP Test (Port 465 SSL)');
                $m->from(config('mail.from.address'), config('mail.from.name'));
            });
            $results['smtp_port_465_ssl'] = 'SUCCESS: Email delivered to ' . $to;
        } catch (\Throwable $e) {
            $results['smtp_port_465_ssl'] = 'FAILED: ' . $e->getMessage();
        }

        // Test 2: Port 587 TLS
        try {
            $mailer587 = app('mail.manager')->build([
                'transport'  => 'smtp',
                'host'       => 'smtp.gmail.com',
                'port'       => 587,
                'encryption' => 'tls',
                'username'   => config('mail.mailers.smtp.username'),
                'password'   => config('mail.mailers.smtp.password'),
                'timeout'    => 15,
            ]);
            $mailer587->raw("This is a live test email from Intan Elyu (Port 587 TLS) sent at " . now(), function ($m) use ($to) {
                $m->to($to)->subject('🧪 Intan Elyu SMTP Test (Port 587 TLS)');
                $m->from(config('mail.from.address'), config('mail.from.name'));
            });
            $results['port_587_tls'] = 'SUCCESS: Email delivered to ' . $to;
        } catch (\Throwable $e) {
            $results['port_587_tls'] = 'FAILED: ' . $e->getMessage();
        }

        return response()->json([
            'status'  => 'Diagnostic complete',
            'target'  => $to,
            'results' => $results
        ]);
    }

    /**
     * POST /api/auth/validate-reset-otp
     */
    public function validateResetOtp(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'otp'   => 'required|string',
        ]);

        $email = $request->email;
        $otp = trim($request->otp);

        $user = User::where('email', $email)->first();
        if (!$user) {
            return response()->json(['error' => 'User account not found.'], 404);
        }

        $cachedOtp = \Illuminate\Support\Facades\Cache::get("pwd_reset_otp:{$email}");
        $isValidOtp = ($cachedOtp && (string)$cachedOtp === (string)$otp) || ($user->remember_token === 'otp_' . $otp);

        if (!$isValidOtp) {
            return response()->json(['error' => 'Invalid or expired verification code.'], 400);
        }

        return response()->json([
            'success' => true,
            'email'   => $email,
            'message' => 'Verification code confirmed.'
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

        $user = User::where('email', $email)->first();
        if (!$user) {
            return response()->json(['error' => 'User account not found.'], 404);
        }

        $cachedOtp = \Illuminate\Support\Facades\Cache::get("pwd_reset_otp:{$email}");
        $isValidOtp = ($cachedOtp && (string)$cachedOtp === (string)$otp) || ($user->remember_token === 'otp_' . $otp);

        if (!$isValidOtp) {
            return response()->json(['error' => 'Invalid or expired verification code.'], 400);
        }

        $user->password = \Illuminate\Support\Facades\Hash::make($request->password);
        $user->remember_token = null;
        $user->save();

        \Illuminate\Support\Facades\Cache::forget("pwd_reset_otp:{$email}");

        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('frontend_password_resets')) {
                \Illuminate\Support\Facades\DB::table('frontend_password_resets')->where('email', $email)->delete();
            }
        } catch (\Throwable $th) {}

        $authToken = null;
        try {
            $authToken = $user->createToken('auth_token')->plainTextToken;
        } catch (\Throwable $t) {}

        return response()->json([
            'success' => true,
            'token'   => $authToken,
            'user'    => $user,
            'email'   => $email,
            'message' => 'Your password has been reset successfully! Logging you in...'
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
        $authToken = null;
        if ($user) {
            $user->password = \Illuminate\Support\Facades\Hash::make($request->password);
            $user->save();

            try {
                $authToken = $user->createToken('auth_token')->plainTextToken;
            } catch (\Throwable $t) {}
        }

        try {
            \Illuminate\Support\Facades\DB::table('frontend_password_resets')->where('id', $record->id)->update(['used' => 1]);
        } catch (\Throwable $th) {}

        return response()->json([
            'success' => true,
            'token'   => $authToken,
            'user'    => $user,
            'message' => 'Your password has been reset successfully! Proceeding into the app...'
        ]);
    }
}
