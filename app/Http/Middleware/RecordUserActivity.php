<?php

namespace App\Http\Middleware;

use App\Models\AuditLog;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RecordUserActivity
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($request->user() && $this->shouldRecord($request)) {
            AuditLog::record('activity', $this->describe($request), $request->user(), $request);
        }

        return $response;
    }

    private function shouldRecord(Request $request): bool
    {
        if ($request->routeIs('login.attempt', 'logout', 'notifications.read')) {
            return false;
        }

        return in_array($request->method(), ['POST', 'PATCH', 'PUT', 'DELETE'], true);
    }

    private function describe(Request $request): string
    {
        $routeName = $request->route()?->getName();

        return match ($request->method()) {
            'POST' => 'Added '.($routeName ?: $request->path()),
            'PATCH', 'PUT' => $request->is('*restore*') || str_contains((string) $routeName, 'restore')
                ? 'Restored '.($routeName ?: $request->path())
                : 'Updated '.($routeName ?: $request->path()),
            'DELETE' => 'Deleted '.($routeName ?: $request->path()),
            default => $request->method().' '.($routeName ?: $request->path()),
        };
    }
}
