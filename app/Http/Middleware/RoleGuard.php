<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleGuard
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = $request->user();

        if (!$user || !in_array($user->role, $roles)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'error' => [
                        'code' => 'PERMISSION_DENIED',
                        'message' => 'Role tidak diizinkan mengakses resource',
                    ]
                ], 403);
            }
            abort(403, 'Unauthorized.');
        }

        return $next($request);
    }
}
