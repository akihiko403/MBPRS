@extends('layouts.app')

@section('content')
<style>
    .page-actions { display:flex; justify-content:space-between; align-items:center; gap:16px; margin-bottom:18px; }
    .page-actions .meta { color:var(--muted); }
    .modal-backdrop { position:fixed; inset:0; background:rgba(8, 20, 18, .55); display:none; align-items:center; justify-content:center; padding:24px; z-index:1000; }
    .modal-backdrop.open { display:flex; }
    .modal-card { width:min(860px, 100%); max-height:90vh; overflow:auto; background:#fff; border-radius:22px; border:1px solid var(--line); box-shadow:0 24px 80px rgba(0,0,0,.18); }
    .modal-head { display:flex; justify-content:space-between; align-items:center; gap:16px; padding:22px 24px; border-bottom:1px solid var(--line); position:sticky; top:0; background:#fff; }
    .modal-body { padding:24px; }
    .icon-btn { width:auto; background:transparent; border:1px solid var(--line); color:var(--ink); padding:8px 12px; }
    .action-icons { display:flex; gap:8px; align-items:center; flex-wrap:wrap; }
    .action-icon { display:inline-flex; align-items:center; justify-content:center; width:36px; height:36px; padding:0; border-radius:10px; border:1px solid var(--line); background:#fff; color:var(--ink); line-height:1; }
    .action-icon svg { width:16px; height:16px; flex-shrink:0; }
    .action-icon.edit { background:#fff7ed; color:#9a3412; }
    .action-icon.warn { background:#fff7ed; color:#b45309; }
    .action-icon.secondary { background:#eef6f3; color:#1f6f5f; }
    .action-icon.delete { background:#fef2f2; color:#991b1b; }
</style>

<div class="card">
    <div class="page-actions">
        <div>
            <h3 style="margin:0;">User Accounts</h3>
            <div class="meta">Manage system access, roles, account status, and password resets.</div>
        </div>
        <button class="btn" style="width:auto;" type="button" data-open-modal="user-modal">Add User</button>
    </div>

    <div class="table-wrap">
        <table>
            <thead><tr><th>Name</th><th>Username</th><th>Role</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
            @forelse($users as $user)
                <tr>
                    <td>{{ $user->name }}</td>
                    <td>{{ $user->username }}</td>
                    <td>{{ $user->role?->name }}</td>
                    <td><span class="badge {{ $user->is_active ? 'Approved' : 'Rejected' }}">{{ $user->is_active ? 'Active' : 'Inactive' }}</span></td>
                    <td>
                        <div class="action-icons">
                            <a class="action-icon edit" href="{{ route('users.index', ['edit' => $user->id]) }}" title="Edit User" aria-label="Edit User"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="m16.5 3.5 4 4L7 21l-4 1 1-4 12.5-14.5Z"/></svg></a>
                            <form method="POST" action="{{ route('users.toggle', $user) }}" style="display:inline-flex; margin:0;">
                                @csrf
                                @method('PATCH')
                                <button class="action-icon warn" type="submit" title="{{ $user->is_active ? 'Deactivate' : 'Activate' }} User" aria-label="{{ $user->is_active ? 'Deactivate' : 'Activate' }} User"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M12 7v5"/><path d="M12 16h.01"/></svg></button>
                            </form>
                            <form method="POST" action="{{ route('users.reset-password', $user) }}" onsubmit="return confirm('Reset this user password to password123?');" style="display:inline-flex; margin:0;">
                                @csrf
                                @method('PATCH')
                                <button class="action-icon secondary" type="submit" title="Reset Password" aria-label="Reset Password"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12a9 9 0 1 0 3-6.7"/><path d="M3 3v6h6"/></svg></button>
                            </form>
                            <form method="POST" action="{{ route('users.destroy', $user) }}" data-confirm-delete data-confirm-message="Delete this user account?" style="display:inline-flex; margin:0;">
                                @csrf
                                @method('DELETE')
                                <button class="action-icon delete" type="submit" title="Delete User" aria-label="Delete User"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M8 6V4h8v2"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/></svg></button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="muted">No user accounts found.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    {{ $users->links() }}
</div>

<div class="modal-backdrop {{ $editUser || $errors->any() ? 'open' : '' }}" id="user-modal" aria-hidden="true">
    <div class="modal-card">
        <div class="modal-head">
            <div>
                <h3 style="margin:0;">{{ $editUser ? 'Edit User' : 'Add User' }}</h3>
                <div class="muted">Create or update user accounts and role assignments.</div>
            </div>
            <button class="icon-btn" type="button" data-close-modal="user-modal">Close</button>
        </div>
        <div class="modal-body">
            <form method="POST" action="{{ $editUser ? route('users.update', $editUser) : route('users.store') }}" class="grid">
                @csrf
                @if($editUser) @method('PATCH') @endif
                <div><label>Full Name</label><input name="name" value="{{ old('name', $editUser->name ?? '') }}" required></div>
                <div><label>Username</label><input name="username" value="{{ old('username', $editUser->username ?? '') }}" required></div>
                <div><label>Email</label><input type="email" name="email" value="{{ old('email', $editUser->email ?? '') }}"></div>
                <div><label>Password</label><input type="password" name="password" {{ $editUser ? '' : 'required' }}></div>
                <div><label>Role</label><select name="role_id" required>@foreach($roles as $role)<option value="{{ $role->id }}" @selected((string)old('role_id', $editUser->role_id ?? '') === (string)$role->id)>{{ $role->name }}</option>@endforeach</select></div>
                <div><label>Account Status</label><select name="is_active" required><option value="1" @selected((string)old('is_active', $editUser->is_active ?? 1) === '1')>Active</option><option value="0" @selected((string)old('is_active', $editUser->is_active ?? 1) === '0')>Inactive</option></select></div>
                <div class="stack">
                    <button class="btn" style="width:auto;" type="submit">{{ $editUser ? 'Update User' : 'Save User' }}</button>
                    <button class="btn secondary" style="width:auto;" type="button" data-close-modal="user-modal">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('click', function (event) {
        const openTrigger = event.target.closest('[data-open-modal]');
        const closeTrigger = event.target.closest('[data-close-modal]');
        if (openTrigger) {
            const modal = document.getElementById(openTrigger.dataset.openModal);
            if (modal) modal.classList.add('open');
        }
        if (closeTrigger) {
            const modal = document.getElementById(closeTrigger.dataset.closeModal);
            if (modal) {
                modal.classList.remove('open');
                if (window.location.search.includes('edit=')) window.location = '{{ route('users.index') }}';
            }
        }
        if (event.target.classList.contains('modal-backdrop')) {
            event.target.classList.remove('open');
            if (window.location.search.includes('edit=')) window.location = '{{ route('users.index') }}';
        }
    });
</script>
@endsection

