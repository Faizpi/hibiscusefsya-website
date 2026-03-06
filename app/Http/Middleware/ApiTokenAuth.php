<?php

namespace App\Http\Middleware;

use App\PersonalAccessToken;
use Closure;
use Illuminate\Http\Request;

class ApiTokenAuth
{
    /**
     * Authenticate request via Bearer token for mobile API.
     */
    public function handle(Request $request, Closure $next)
    {
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $hashedToken = hash('sha256', $token);
        $accessToken = PersonalAccessToken::where('token', $hashedToken)->first();

        if (!$accessToken || $accessToken->isExpired()) {
            return response()->json(['message' => 'Token invalid atau sudah expired.'], 401);
        }

        // Update last used
        $accessToken->update(['last_used_at' => now()]);

        // Set the authenticated user
        $user = $accessToken->user;
        if (!$user) {
            return response()->json(['message' => 'User tidak ditemukan.'], 401);
        }

        auth()->setUser($user);
        $request->merge(['api_token_id' => $accessToken->id]);

        return $next($request);
    }
}
