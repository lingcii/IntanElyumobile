<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\EmailVerificationOtpMail;
use App\Mail\TouristWelcomeMail;
use App\Models\User;
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
     * Initiates registration with strict validation & sends 6-digit OTP code to email.
     */
    public function register(Request $request): JsonResponse
    {
        $request->validate([
            'first_name' => 'nullable|string|min:2|max:50',
            'last_name'  => 'nullable|string|min:2|max:50',
            'name'       => 'nullable|string|min:4|max:100',
            'gender'     => 'required|string|in:Male,Female',
            'email'      => ['required', 'email', 'regex:/^[^\s@]+@[^\s@.]+(?:\.[^\s@.]+)*\.[A-Za-z]{2,63}$/', 'unique:users,email'],
            'password'   => 'required|string|min:8|confirmed',
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

        // 3. Password strength check
        if (!preg_match('/[A-Za-z]/', $request->password) || !preg_match('/[0-9]/', $request->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Password must be at least 8 characters and contain both letters and numbers.'
            ], 422);
        }

        $emailKey = strtolower(trim($request->email));

        $otp = (string) random_int(100000, 999999);
        $cacheKey = "pending_reg_{$emailKey}";
        Cache::put($cacheKey, [
            'name'     => $trimmedName,
            'email'    => $emailKey,
            'password' => Hash::make($request->password),
            'gender'   => $request->gender,
            'otp'      => $otp,
        ], now()->addMinutes(10));

        try {
            Mail::to($emailKey)->send(new EmailVerificationOtpMail($trimmedName, $otp));
        } catch (\Throwable $e) {
            Cache::forget($cacheKey);
            Log::error('Registration OTP email failed', ['email' => $emailKey, 'error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'We could not send the verification email. Please try again.'
            ], 500);
        }

        return response()->json([
            'success' => true,
            'requires_otp' => true,
            'email' => $emailKey,
            'message' => 'A 6-digit verification code was sent to your email.',
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

        // Send Welcome Mail
        try {
            Mail::to($user->email)->send(new TouristWelcomeMail($user));
        } catch (\Throwable $e) {}

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
                'created_at'     => $user->created_at ? $user->created_at->toIso8601String() : $now->toIso8601String(),
                'created_at_formatted' => $user->created_at ? $user->created_at->format('M d, Y h:i A') : $now->format('M d, Y h:i A'),
                'date_created'   => $user->created_at ? $user->created_at->format('M d, Y h:i A') : $now->format('M d, Y h:i A'),
            ],
        ], 201);
    }
}
