@extends('layouts.app')

@section('content')
@php
    $initials = collect(explode(' ', $user->name))->filter()->map(fn ($part) => mb_substr($part, 0, 1))->take(2)->implode('') ?: 'U';
    $profilePhotoUrl = $user->profile_photo_path
        ? asset('storage/' . $user->profile_photo_path).'?v='.$user->updated_at?->timestamp
        : null;
@endphp

<div class="card">
    <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data">
        @csrf
        @method('PATCH')

        <div style="display:grid;gap:24px;">
            <section>
                <h3 style="margin:0 0 6px;">Account Info</h3>
                <div class="muted" style="margin-bottom:20px;">Public information about your account.</div>

                <div class="profile-section-grid">
                    <div class="profile-photo-panel">
                        <span class="user-avatar profile-photo-preview" id="profile-photo-preview">
                            @if($profilePhotoUrl)
                                <img src="{{ $profilePhotoUrl }}" alt="">
                            @else
                                <span class="user-avatar-fallback">{{ $initials }}</span>
                            @endif
                        </span>
                        <label class="profile-photo-link" for="profile_photo">Update image</label>
                        <input id="profile_photo" class="profile-photo-input" type="file" name="profile_photo" accept="image/*">
                    </div>

                    <div class="profile-fields">
                        <div>
                            <label>Full Name</label>
                            <input type="text" name="name" value="{{ old('name', $user->name) }}" required>
                        </div>

                        <div>
                            <label>Email</label>
                            <input type="email" name="email" value="{{ old('email', $user->email) }}" placeholder="No email provided">
                        </div>

                        <div>
                            <label>Role</label>
                            <input type="text" value="{{ $user->role?->name }}" disabled>
                        </div>

                        <div>
                            <label>Status</label>
                            <span class="badge profile-status-badge {{ $user->is_active ? 'Approved' : 'Rejected' }}">{{ $user->is_active ? 'Active' : 'Inactive' }}</span>
                        </div>
                    </div>
                </div>
            </section>

            <div style="height:1px;background:var(--line);"></div>

            <section>
                <h3 style="margin:0 0 6px;">Login Info</h3>
                <div class="muted" style="margin-bottom:20px;">Credentials used to sign in to the system.</div>

                <div class="profile-login-grid">
                    <div class="profile-login-column">
                        <div>
                            <label>Username</label>
                            <input type="text" value="{{ $user->username }}" disabled>
                        </div>

                        <div>
                            <label>Current Password</label>
                            <input type="password" name="current_password" autocomplete="current-password" placeholder="Required when changing password">
                        </div>
                    </div>

                    <div class="profile-login-column">
                        <div>
                            <label>New Password</label>
                            <input type="password" name="password" autocomplete="new-password" placeholder="Leave blank to keep current password">
                        </div>

                        <div>
                            <label>Confirm Password</label>
                            <input type="password" name="password_confirmation" autocomplete="new-password" placeholder="Repeat new password">
                        </div>
                    </div>
                </div>
            </section>

            <div class="stack" style="justify-content:flex-end;">
                <button class="btn" type="submit" style="width:auto;">Save Changes</button>
            </div>
        </div>
    </form>
</div>

<style>
    .profile-section-grid { display:grid; grid-template-columns:170px minmax(0, 1fr); gap:30px; align-items:start; }
    .profile-photo-panel { display:flex; flex-direction:column; align-items:center; gap:10px; padding-top:4px; }
    .profile-photo-preview { width:92px; height:92px; font-size:1.35rem; }
    .profile-photo-link { margin:0; color:var(--brand); cursor:pointer; font-size:.9rem; }
    .profile-photo-input { position:absolute; width:1px; height:1px; opacity:0; pointer-events:none; }
    .profile-fields, .profile-login-grid { display:grid; grid-template-columns:repeat(2, minmax(0, 1fr)); gap:14px 18px; }
    .profile-login-column { display:grid; gap:14px; align-content:start; }
    .profile-login-grid { max-width:760px; }
    .profile-status-badge { width:auto; min-height:49px; border:1px solid var(--line); border-radius:12px; align-items:center; padding:11px 14px; }
    @media (max-width:760px) {
        .profile-section-grid, .profile-fields, .profile-login-grid { grid-template-columns:1fr; }
        .profile-photo-panel { align-items:flex-start; }
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const input = document.getElementById('profile_photo');
        const preview = document.getElementById('profile-photo-preview');
        let previewUrl = null;

        if (!input || !preview) {
            return;
        }

        input.addEventListener('change', function () {
            const file = input.files && input.files[0];

            if (!file) {
                return;
            }

            if (previewUrl) {
                URL.revokeObjectURL(previewUrl);
            }

            previewUrl = URL.createObjectURL(file);
            preview.innerHTML = '';

            const image = document.createElement('img');
            image.src = previewUrl;
            image.alt = '';
            preview.appendChild(image);
        });
    });
</script>
@endsection
