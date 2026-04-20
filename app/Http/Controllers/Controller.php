<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;

abstract class Controller
{
    protected function redirectIfCannotAccess(string $module): ?RedirectResponse
    {
        if (! auth()->user()?->canAccess($module)) {
            return redirect()->route('dashboard')->with('error', 'Access denied for this module.');
        }

        return null;
    }

    protected function redirectIfMissingRole(string ...$roles): ?RedirectResponse
    {
        if (! auth()->user()?->hasRole(...$roles)) {
            return redirect()->route('dashboard')->with('error', 'Access denied for this module.');
        }

        return null;
    }
}
