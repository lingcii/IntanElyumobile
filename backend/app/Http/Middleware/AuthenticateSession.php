<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Ensure a valid session exists for API requests.
 */
class AuthenticateSession
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->session()->has('user_id')) {
            return response()->json(['error' => 'Unauthorized: not logged in'], 401);
        }

        return $next($request);
    }
}
