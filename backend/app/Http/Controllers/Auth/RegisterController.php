<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\TouristWelcomeMail;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
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
            'email'      => 'required|email|unique:users,email',
            'password'   => 'required|string|min:8|confirmed',
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

        // 3. Password strength check
        if (!preg_match('/[A-Za-z]/', $request->password) || !preg_match('/[0-9]/', $request->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Password must be at least 8 characters and contain both letters and numbers.'
            ], 422);
        }

        $emailKey = strtolower(trim($request->email));

        // Create active user account directly
        $token = Str::random(60);
        $user = User::create([
            'name'      => $trimmedName,
            'email'     => $emailKey,
            'password'  => Hash::make($request->password),
            'role'      => 'tourist',
            'status'    => 'active',
            'api_token' => $token,
        ]);

        // Send Welcome Mail
        try {
            Mail::to($user->email)->send(new TouristWelcomeMail($user));
        } catch (\Throwable $e) {}

        return response()->json([
            'success' => true,
            'token'   => $token,
            'user'    => [
                'id'     => $user->id,
                'name'   => $user->name,
                'email'  => $user->email,
                'role'   => $user->role,
                'xp'     => 0,
                'level'  => 1,
                'avatar' => null,
            ],
            'message' => 'Account created successfully! Welcome to Intan Elyu!',
        ], 201);
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
        $user = User::create([
            'name'      => $pending['name'],
            'email'     => $pending['email'],
            'password'  => $pending['password'],
            'role'      => 'tourist',
            'status'    => 'active',
            'api_token' => $token,
        ]);

        \Illuminate\Support\Facades\Cache::forget("pending_reg_{$emailKey}");

        // Send Welcome Mail
        try {
            Mail::to($user->email)->send(new TouristWelcomeMail($user));
        } catch (\Throwable $e) {}

        return response()->json([
            'success' => true,
            'message' => 'Email verified and account activated successfully!',
            'token'   => $token,
            'user'    => [
                'id'     => $user->id,
                'name'   => $user->name,
                'email'  => $user->email,
                'role'   => $user->role,
                'xp'     => 0,
                'level'  => 1,
                'avatar' => null,
            ],
        ], 201);
    }
}
