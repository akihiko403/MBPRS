<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\SystemSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function __invoke(): View|RedirectResponse
    {
        if ($redirect = $this->redirectIfMissingRole(Role::ADMINISTRATOR)) {
            return $redirect;
        }

        return view('settings.index', [
            'title' => 'Settings',
            'subtitle' => 'Manage system-level configuration and administrator options.',
            'settings' => SystemSetting::current(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        if ($redirect = $this->redirectIfMissingRole(Role::ADMINISTRATOR)) {
            return $redirect;
        }

        $settings = SystemSetting::current();

        $validated = $request->validate([
            'system_name' => ['required', 'string', 'max:255'],
            'system_description' => ['nullable', 'string', 'max:1000'],
            'system_logo' => ['nullable', 'image', 'max:2048'],
        ]);

        $settings->fill([
            'system_name' => $validated['system_name'],
            'system_description' => $validated['system_description'] ?? null,
        ]);

        if ($request->hasFile('system_logo')) {
            if ($settings->system_logo_path) {
                Storage::disk('public')->delete($settings->system_logo_path);
            }

            $settings->system_logo_path = $request->file('system_logo')->store('system', 'public');
        }

        $settings->save();

        return back()->with('success', 'System settings updated successfully.');
    }
}
