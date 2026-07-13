<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\UserDms;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    /**
     * Authenticate API request and issue token.
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = UserDms::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'INVALID_CREDENTIALS',
                    'message' => 'Email atau password salah',
                ]
            ], 401);
        }

        // Generate Sanctum token
        // Use the role as a token ability
        $token = $user->createToken('auth_token', [$user->role])->plainTextToken;

        // Update last login
        $user->update([
            'last_login_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'token' => $token,
                'role' => $user->role,
                'email' => $user->email,
            ]
        ]);
    }

    /**
     * Terminate the API session / token.
     */
    public function logout(Request $request): JsonResponse
    {
        // Revoke the current token
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully'
        ]);
    }
}
