<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\PersonalAccessToken;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    /**
     * Login dan dapatkan API token.
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
            'device_name' => 'nullable|string|max:255',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Email atau password salah.',
            ], 401);
        }

        // Generate token
        $plainToken = Str::random(64);
        $hashedToken = hash('sha256', $plainToken);

        PersonalAccessToken::create([
            'user_id' => $user->id,
            'name' => $request->device_name ?? 'mobile',
            'token' => $hashedToken,
            'expires_at' => now()->addDays(30),
        ]);

        return response()->json([
            'message' => 'Login berhasil.',
            'token' => $plainToken,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'alamat' => $user->alamat,
                'no_telp' => $user->no_telp,
                'gudang_id' => $user->gudang_id,
                'current_gudang_id' => $user->current_gudang_id,
            ],
        ]);
    }

    /**
     * Logout (revoke token).
     */
    public function logout(Request $request)
    {
        $tokenId = $request->get('api_token_id');
        if ($tokenId) {
            PersonalAccessToken::where('id', $tokenId)->delete();
        }

        return response()->json(['message' => 'Logout berhasil.']);
    }

    /**
     * Get current user profile.
     */
    public function profile(Request $request)
    {
        $user = auth()->user();
        $gudang = $user->getCurrentGudang();

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'alamat' => $user->alamat,
                'no_telp' => $user->no_telp,
                'gudang_id' => $user->gudang_id,
                'current_gudang_id' => $user->current_gudang_id,
            ],
            'gudang' => $gudang ? [
                'id' => $gudang->id,
                'nama_gudang' => $gudang->nama_gudang,
                'alamat_gudang' => $gudang->alamat_gudang,
            ] : null,
        ]);
    }
}
