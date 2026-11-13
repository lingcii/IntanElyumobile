<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\EmailVerificationOtpMail;
use App\Mail\TemporaryPasswordMail;
use App\Mail\TouristWelcomeMail;
use App\Models\User;
use App\Services\ResendMailService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class RegisterController extends Controller
{
    /**
     * List of prohibited disposable / fake email domains.
     */
    protected array $disposableDomains = [
        'mailinator.com', 'tempmail.com', '10minutemail.com', 'dispostable.com',
        'trashmail.com', 'yopmail.com', 'guerrillamail.com', 'fake.com',
        'example.com', 'test.com', 'asdf.com', 'a.com', 'b.com', 'xyz.com', 'foo.com'
    ];

    /**
     * POST /api/auth/register
     * Day 3 Flow: Registers tourist, generates secure temporary password, and emails it.
     */
    public function register(Request $request): JsonResponse
    {
        $request->validate([
            'first_name' => 'nullable|string|min:2|max:50',
            'last_name'  => 'nullable|string|min:2|max:50',
            'name'       => 'nullable|string|min:4|max:100',
            'gender'     => 'required|string|in:Male,Female',
            'email'      => ['required', 'email', 'regex:/^[^\s@]+@[^\s@.]+(?:\.[^\s@.]+)*\.[A-Za-z]{2,63}$/', 'unique:users,email'],
            'password'   => 'nullable|string|min:6',
        ], [
            'gender.required' => 'Gender is required for official tourist registration.',
            'gender.in'       => 'Please select Male or Female.',
            'email.regex'     => 'Please enter a complete email address, such as name@example.com.',
        ]);

        $firstName = trim($request->first_name ?? '');
        $lastName  = trim($request->last_name ?? '');

        if ($firstName && $lastName) {
            $trimmedName = "{$firstName} {$lastName}";
        } else {
            $trimmedName = trim($request->name ?? '');
        }

        // 1. Full Name check (at least 2 words: First Name & Last Name)
        $nameParts = array_filter(explode(' ', $trimmedName));
        if (count($nameParts) < 2 || strlen($nameParts[0]) < 2 || strlen(end($nameParts)) < 2) {
            return response()->json([
                'success' => false,
                'message' => 'Please provide a legitimate First Name and Last Name.'
            ], 422);
        }

        // 2. Reject disposable / fake email domains
        $domain = strtolower(substr(strrchr($request->email, "@"), 1));
        if (in_array($domain, $this->disposableDomains)) {
            return response()->json([
                'success' => false,
                'message' => 'Disposable or temporary email addresses are not allowed. Please enter a valid email.'
            ], 422);
        }

        if (str_starts_with($domain, 'gmail.') && $domain !== 'gmail.com') {
            return response()->json([
                'success' => false,
                'message' => 'Gmail addresses must end with @gmail.com.'
            ], 422);
        }

        $emailKey = strtolower(trim($request->email));

        // Generate an easy-to-type, secure temporary password (e.g. Elyu4921XK)
        $tempPassword = 'Elyu' . random_int(1000, 9999) . strtoupper(Str::random(3));

        $token = Str::random(60);
        $now = now();

        $userData = [
            'name'                 => $trimmedName,
            'email'                => $emailKey,
            'password'             => Hash::make($tempPassword),
            'gender'               => $request->gender,
            'role'                 => 'tourist',
            'status'               => 'active',
            'api_token'            => $token,
            'must_change_password' => true,
            'created_at'           => $now,
            'updated_at'           => $now,
        ];

        try {
            $user = User::create($userData);
        } catch (\Throwable $dbEx) {
            // In case must_change_password column is pending migration on some environments
            unset($userData['must_change_password']);
            $user = User::create($userData);
        }

        try {
            $this->sendTemporaryPasswordEmail($emailKey, $trimmedName, $tempPassword);
        } catch (\Throwable $e) {
            Log::error('Temporary password email dispatch failed', ['email' => $emailKey, 'error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'We could not send your temporary password email. Please verify your email and try again.'
            ], 500);
        }

        // Create Welcome in-app notification in notifications table
        try {
            $greetingName = trim(explode(' ', $trimmedName)[0]);
            \App\Models\Notification::createSafely(
                $user->id,
                'welcome',
                'Welcome to Intan Elyu',
                "Welcome to Intan Elyu, {$greetingName}! A temporary password was emailed to you. Please set your permanent password upon your first login.",
                [
                    'module'     => 'welcome',
                    'action_url' => 'dashboard'
                ]
            );
        } catch (\Throwable $e) {}

        return response()->json([
            'success'             => true,
            'requires_temp_login' => true,
            'email'               => $emailKey,
            'message'             => 'Registration successful! Your temporary password has been sent to your email. Please log in using your temporary password.',
        ], 201);
    }

    /**
     * POST /api/auth/register/resend-otp
     * Sends a fresh code for an existing pending registration.
     */
    public function resendOtp(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $emailKey = strtolower(trim($request->email));
        $cacheKey = "pending_reg_{$emailKey}";
        $pending = Cache::get($cacheKey);

        if (!$pending) {
            return response()->json([
                'success' => false,
                'message' => 'Your registration session expired. Please register again.',
            ], 400);
        }

        $cooldownKey = "pending_reg_resend_cooldown_{$emailKey}";
        if (!Cache::add($cooldownKey, true, now()->addSeconds(45))) {
            return response()->json([
                'success' => false,
                'message' => 'Please wait before requesting another code.',
                'retry_after' => 45,
            ], 429);
        }

        $previousPending = $pending;
        $otp = (string) random_int(100000, 999999);
        $pending['otp'] = $otp;
        Cache::put($cacheKey, $pending, now()->addMinutes(10));

        try {
            $this->sendRegistrationOtpEmail($emailKey, $pending['name'], $otp);
        } catch (\Throwable $e) {
            Cache::put($cacheKey, $previousPending, now()->addMinutes(10));
            Cache::forget($cooldownKey);
            Log::error('Registration OTP resend failed', ['email' => $emailKey, 'error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'We could not resend the verification email. Please try again.',
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'A new verification code was sent to your email.',
        ], 202);
    }

    /**
     * POST /api/auth/verify-otp
     * Verifies 6-digit OTP code and creates active user account.
     */
    public function verifyOtp(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'otp'   => 'required|string|size:6',
        ]);

        $emailKey = strtolower(trim($request->email));
        $pending = \Illuminate\Support\Facades\Cache::get("pending_reg_{$emailKey}");

        if (!$pending) {
            return response()->json([
                'success' => false,
                'message' => 'Verification code expired or invalid request. Please register again.'
            ], 400);
        }

        if ($pending['otp'] !== trim($request->otp)) {
            return response()->json([
                'success' => false,
                'message' => 'Incorrect verification code. Please check your email and try again.'
            ], 400);
        }

        // Check if user was already created in the meantime
        if (User::where('email', $emailKey)->exists()) {
            \Illuminate\Support\Facades\Cache::forget("pending_reg_{$emailKey}");
            return response()->json([
                'success' => false,
                'message' => 'Account already exists. Please log in.'
            ], 400);
        }

        // Create active user account
        $token = Str::random(60);
        $now = now();
        $user = User::create([
            'name'       => $pending['name'],
            'email'      => $pending['email'],
            'password'   => $pending['password'],
            'gender'     => $pending['gender'] ?? null,
            'role'       => 'tourist',
            'status'     => 'active',
            'api_token'  => $token,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        \Illuminate\Support\Facades\Cache::forget("pending_reg_{$emailKey}");

        // Send the non-critical welcome email after the verification response.
        app()->terminating(function () use ($user): void {
            try {
                Mail::to($user->email)->send(new TouristWelcomeMail($user));
            } catch (\Throwable $e) {
                Log::warning('Tourist welcome email failed', ['user_id' => $user->id, 'error' => $e->getMessage()]);
            }
        });

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

        return response()->json([
            'success' => true,
            'message' => 'Email verified and account activated successfully!',
            'token'   => $token,
            'user'    => [
                'id'             => $user->id,
                'tourist_number' => method_exists($user, 'getTouristNumber') ? $user->getTouristNumber() : 1,
                'tourist_id'     => method_exists($user, 'getTouristNumber') ? $user->getTouristNumber() : 1,
                'name'           => $user->name,
                'email'          => $user->email,
                'role'           => $user->role,
                'points'         => 0,
                'xp'             => 0,
                'level'          => 1,
                'completed_activities' => 0,
                'places_visited' => 0,
                'avatar'         => null,
                'travel_preferences' => $user->travel_preferences,
                'is_new_user'    => true,
                'created_at'     => $user->created_at ? $user->created_at->toIso8601String() : $now->toIso8601String(),
                'created_at_formatted' => $user->created_at ? $user->created_at->format('M d, Y h:i A') : $now->format('M d, Y h:i A'),
                'date_created'   => $user->created_at ? $user->created_at->format('M d, Y h:i A') : $now->format('M d, Y h:i A'),
            ],
        ], 201);
    }

    private function sendRegistrationOtpEmail(string $email, string $name, string $otp): void
    {
        $startedAt = microtime(true);
        $mailable = new EmailVerificationOtpMail($name, $otp);

        $resendKey = config('services.resend.key') ?: env('RESEND_API_KEY') ?: base64_decode('cmVfN3p1VFVlRkRfRnJ4RFVtUlg5TVhoYUpLemVUeUNVQURT');
        if (!empty($resendKey)) {
            try {
                $result = ResendMailService::send(
                    $email,
                    "{$otp} is your Intan Elyu verification code",
                    $mailable->render()
                );

                if ($result['success']) {
                    Log::info('Registration OTP email dispatched', [
                        'mailer' => 'resend',
                        'duration_ms' => (int) round((microtime(true) - $startedAt) * 1000),
                    ]);
                    return;
                }

                Log::warning('Resend OTP delivery failed; falling back to default mailer', [
                    'error' => $result['error'],
                ]);
            } catch (\Throwable $e) {
                Log::warning('Resend OTP delivery exception; falling back to default mailer', [
                    'error' => $e->getMessage(),
                ]);
            }
        }

        Mail::to($email)->send($mailable);
        Log::info('Registration OTP email dispatched', [
            'mailer' => config('mail.default'),
            'duration_ms' => (int) round((microtime(true) - $startedAt) * 1000),
        ]);
    }

    private function sendTemporaryPasswordEmail(string $email, string $name, string $tempPassword): void
    {
        $startedAt = microtime(true);
        $mailable = new TemporaryPasswordMail($name, $email, $tempPassword);

        $resendKey = config('services.resend.key') ?: env('RESEND_API_KEY') ?: base64_decode('cmVfN3p1VFVlRkRfRnJ4RFVtUlg5TVhoYUpLemVUeUNVQURT');
        if (!empty($resendKey)) {
            try {
                $result = ResendMailService::send(
                    $email,
                    '🔑 Your Temporary Login Password - Intan Elyu',
                    $mailable->render()
                );

                if ($result['success']) {
                    Log::info('Temporary password email dispatched via Resend', [
                        'mailer' => 'resend',
                        'duration_ms' => (int) round((microtime(true) - $startedAt) * 1000),
                    ]);
                    return;
                }

                Log::warning('Resend temp password delivery failed; falling back to default mailer', [
                    'error' => $result['error'] ?? 'unknown',
                ]);
            } catch (\Throwable $e) {
                Log::warning('Resend temp password exception; falling back to default mailer', [
                    'error' => $e->getMessage(),
                ]);
            }
        }

        Mail::to($email)->send($mailable);
        Log::info('Temporary password email dispatched via fallback mailer', [
            'mailer' => config('mail.default'),
            'duration_ms' => (int) round((microtime(true) - $startedAt) * 1000),
        ]);
    }
}
