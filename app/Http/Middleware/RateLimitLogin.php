<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class RateLimitLogin
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $key = 'login-attempts:' . $request->ip() . '|' . $request->input('email');

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'error' => [
                        'code' => 'LOGIN_LOCKED',
                        'message' => 'Akun/IP terkunci sementara. Coba lagi dalam ' . $seconds . ' detik.',
                    ]
                ], 429);
            }

            return back()->withErrors([
                'email' => 'Terlalu banyak percobaan login. Silakan coba lagi dalam ' . $seconds . ' detik.',
            ]);
        }

        $response = $next($request);

        // If the login was not successful, hit the rate limiter
        // Status code >= 400 or redirected back with validation errors
        $hasErrors = session()->has('errors');
        $isErrorStatus = $response->getStatusCode() >= 400;

        if ($hasErrors || $isErrorStatus) {
            RateLimiter::hit($key, 60); // Lock for 60 seconds
        } else {
            RateLimiter::clear($key);
        }

        return $response;
    }
}
