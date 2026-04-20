<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function markAsRead(Request $request): RedirectResponse
    {
        $request->user()->forceFill([
            'notifications_read_at' => now(),
        ])->save();

        return back();
    }
}
