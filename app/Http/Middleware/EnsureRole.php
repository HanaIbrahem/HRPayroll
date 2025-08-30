<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class EnsureRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next,...$roles): Response
    {
        
        $user = Auth::user();

        // Not logged in
        if (!$user) {
            return $request->expectsJson()
                ? response()->json(['message' => 'Unauthenticated.'], 401)
                : redirect()->route('login');
        }

        // Role gate (if roles were provided)
        $allowed = array_map('strtolower', $roles);
        if (!empty($allowed) && !in_array($user->role, $allowed, true)) {
            return $request->expectsJson()
                ? response()->json(['message' => 'Forbidden.'], 403)
                : abort(403, 'Forbidden');
        }

        // All roles must be active
        if (!$user->is_active) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return $request->expectsJson()
                ? response()->json(['message' => 'Account is inactive.'], 403)
                : redirect()->route('login')->withErrors([
                    'username' => 'Your account is inactive.',
                ]);
        }

        return $next($request);
    }
}
