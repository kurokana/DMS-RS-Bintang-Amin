<?php

namespace App\Services;

use App\Models\UserDms;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class LocalAuthService
{
    /**
     * Attempt local authentication.
     */
    public function attemptLogin(string $email, string $password): bool
    {
        $user = UserDms::where('email', $email)->first();

        if (!$user || !Hash::check($password, $user->password)) {
            return false;
        }

        // Standard session login
        Auth::login($user, true);

        // Update last login timestamp
        $user->update([
            'last_login_at' => now(),
        ]);

        return true;
    }

    /**
     * Log the user out of the application.
     */
    public function logout(): void
    {
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
    }
}
