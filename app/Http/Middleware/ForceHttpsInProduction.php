<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ForceHttpsInProduction
{
    public function handle(Request $request, Closure $next): Response
    {
        if (
            strtolower((string) config('app.env')) === 'production'
            && str_starts_with((string) config('app.url'), 'https://')
            && ! $request->secure()
        ) {
            return redirect()->to(preg_replace('/^http:/i', 'https:', $request->fullUrl()), 301);
        }

        return $next($request);
    }
}
