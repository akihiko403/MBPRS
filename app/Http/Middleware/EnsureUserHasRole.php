<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        if (! $user->is_active) {
            auth()->logout();

            return redirect()->route('login')->withErrors([
                'username' => 'This account is inactive.',
            ]);
        }

        if ($roles !== [] && ! $user->hasRole(...$roles)) {
            return redirect()->route('dashboard')->with('error', 'Access denied for this module.');
        }

        return $next($request);
    }
}
