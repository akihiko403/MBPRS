<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserManagementController extends Controller
{
    public function index(Request $request): View|RedirectResponse
    {
        if ($redirect = $this->redirectIfCannotAccess('users')) {
            return $redirect;
        }

        return view('users.index', [
            'title' => 'User Management',
            'subtitle' => 'Manage user accounts, assigned roles, and access to system modules.',
            'users' => User::query()
                ->with('role')
                ->when($request->input('search'), function ($query, string $search): void {
                    $query->where(function ($query) use ($search): void {
                        $query
                            ->where('name', 'like', "%{$search}%")
                            ->orWhere('username', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%")
                            ->orWhereHas('role', fn ($query) => $query->where('name', 'like', "%{$search}%"));
                    });
                })
                ->latest()
                ->paginate(10)
                ->withQueryString(),
            'roles' => Role::query()->allowed()->orderBy('name')->get(),
            'editUser' => $request->query('edit') ? User::query()->find($request->query('edit')) : null,
            'deletedUsers' => User::query()->onlyTrashed()->with('role')->latest('deleted_at')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        if ($redirect = $this->redirectIfCannotAccess('users')) {
            return $redirect;
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', 'unique:users,username'],
            'email' => ['nullable', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'role_id' => ['required', Rule::exists('roles', 'id')->where(fn ($query) => $query->whereIn('slug', Role::allowedSlugs()))],
            'is_active' => ['required', 'boolean'],
        ]);

        User::query()->create($validated);

        return back()->with('success', 'User account added successfully.');
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        if ($redirect = $this->redirectIfCannotAccess('users')) {
            return $redirect;
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', Rule::unique('users', 'username')->ignore($user->id)],
            'email' => ['nullable', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:8'],
            'role_id' => ['required', Rule::exists('roles', 'id')->where(fn ($query) => $query->whereIn('slug', Role::allowedSlugs()))],
            'is_active' => ['required', 'boolean'],
        ]);

        if (blank($validated['password'])) {
            unset($validated['password']);
        }

        $user->update($validated);

        return redirect()->route('users.index')->with('success', 'User account updated successfully.');
    }

    public function destroy(User $user): RedirectResponse
    {
        if ($redirect = $this->redirectIfCannotAccess('users')) {
            return $redirect;
        }

        if (! auth()->user()->canDeleteRecords()) {
            return back()->with('error', 'Only administrators can delete records.');
        }

        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        $user->delete();

        return back()->with('success', 'User account moved to trash.');
    }

    public function restore(Request $request, int $id): RedirectResponse
    {
        if ($redirect = $this->redirectIfCannotAccess('users')) {
            return $redirect;
        }

        if (! $request->user()->canDeleteRecords()) {
            return back()->with('error', 'Only administrators can restore records.');
        }

        User::query()->onlyTrashed()->findOrFail($id)->restore();

        return back()->with('success', 'User account restored successfully.');
    }

    public function forceDelete(Request $request, int $id): RedirectResponse
    {
        if ($redirect = $this->redirectIfCannotAccess('users')) {
            return $redirect;
        }

        if (! $request->user()->canDeleteRecords()) {
            return back()->with('error', 'Only administrators can permanently delete records.');
        }

        User::query()->onlyTrashed()->findOrFail($id)->forceDelete();

        return back()->with('success', 'User account permanently deleted.');
    }

    public function toggle(User $user): RedirectResponse
    {
        if ($redirect = $this->redirectIfCannotAccess('users')) {
            return $redirect;
        }

        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot deactivate your own account.');
        }

        $user->update(['is_active' => ! $user->is_active]);

        return back()->with('success', 'User account status updated.');
    }

    public function resetPassword(User $user): RedirectResponse
    {
        if ($redirect = $this->redirectIfCannotAccess('users')) {
            return $redirect;
        }

        $user->forceFill(['password' => Hash::make('password123')])->save();

        return back()->with('success', 'Password reset to password123.');
    }
}
