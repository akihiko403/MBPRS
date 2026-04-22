@extends('layouts.app')

@section('content')
@php
    $initials = collect(explode(' ', $user->name))->filter()->map(fn ($part) => mb_substr($part, 0, 1))->take(2)->implode('') ?: 'U';
    $profilePhotoUrl = $user->profile_photo_path
        ? asset('storage/' . $user->profile_photo_path).'?v='.$user->updated_at?->timestamp
        : null;
@endphp

<div class="card">
    <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" data-confirm-save data-save-message="Save these profile changes?">
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
                            <div class="password-field">
                                <input type="password" name="current_password" autocomplete="current-password" placeholder="Required when changing password">
                                <button class="password-toggle" type="button" data-password-toggle aria-label="Show password">
                                    <svg class="password-toggle-show" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                        <path d="M2.06 12.35a1 1 0 0 1 0-.7C3.42 8.6 6.47 6 12 6s8.58 2.6 9.94 5.65a1 1 0 0 1 0 .7C20.58 15.4 17.53 18 12 18s-8.58-2.6-9.94-5.65Z"/>
                                        <circle cx="12" cy="12" r="3"/>
                                    </svg>
                                    <svg class="password-toggle-hide" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                        <path d="M3 3l18 18"/>
                                        <path d="M10.58 10.58A2 2 0 0 0 12 14a2 2 0 0 0 1.42-.58"/>
                                        <path d="M9.88 5.09A10.94 10.94 0 0 1 12 5c5.05 0 8.27 2.95 9.67 6.2a1 1 0 0 1 0 .8 11.45 11.45 0 0 1-4.3 5.1"/>
                                        <path d="M6.61 6.61A11.46 11.46 0 0 0 2.33 12a1 1 0 0 0 0 .8C3.73 16.05 6.95 19 12 19c1.68 0 3.17-.33 4.46-.92"/>
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <div class="profile-reset-password-wrap">
                            <button class="btn secondary profile-reset-password-btn" type="submit" form="profile-reset-password-form">Reset Password</button>
                        </div>
                    </div>

                    <div class="profile-login-column">
                        <div>
                            <label>New Password</label>
                            <div class="password-field">
                                <input type="password" name="password" autocomplete="new-password" placeholder="Leave blank to keep current password">
                                <button class="password-toggle" type="button" data-password-toggle aria-label="Show password">
                                    <svg class="password-toggle-show" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                        <path d="M2.06 12.35a1 1 0 0 1 0-.7C3.42 8.6 6.47 6 12 6s8.58 2.6 9.94 5.65a1 1 0 0 1 0 .7C20.58 15.4 17.53 18 12 18s-8.58-2.6-9.94-5.65Z"/>
                                        <circle cx="12" cy="12" r="3"/>
                                    </svg>
                                    <svg class="password-toggle-hide" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                        <path d="M3 3l18 18"/>
                                        <path d="M10.58 10.58A2 2 0 0 0 12 14a2 2 0 0 0 1.42-.58"/>
                                        <path d="M9.88 5.09A10.94 10.94 0 0 1 12 5c5.05 0 8.27 2.95 9.67 6.2a1 1 0 0 1 0 .8 11.45 11.45 0 0 1-4.3 5.1"/>
                                        <path d="M6.61 6.61A11.46 11.46 0 0 0 2.33 12a1 1 0 0 0 0 .8C3.73 16.05 6.95 19 12 19c1.68 0 3.17-.33 4.46-.92"/>
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <div>
                            <label>Confirm Password</label>
                            <div class="password-field">
                                <input type="password" name="password_confirmation" autocomplete="new-password" placeholder="Repeat new password">
                                <button class="password-toggle" type="button" data-password-toggle aria-label="Show password">
                                    <svg class="password-toggle-show" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                        <path d="M2.06 12.35a1 1 0 0 1 0-.7C3.42 8.6 6.47 6 12 6s8.58 2.6 9.94 5.65a1 1 0 0 1 0 .7C20.58 15.4 17.53 18 12 18s-8.58-2.6-9.94-5.65Z"/>
                                        <circle cx="12" cy="12" r="3"/>
                                    </svg>
                                    <svg class="password-toggle-hide" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                        <path d="M3 3l18 18"/>
                                        <path d="M10.58 10.58A2 2 0 0 0 12 14a2 2 0 0 0 1.42-.58"/>
                                        <path d="M9.88 5.09A10.94 10.94 0 0 1 12 5c5.05 0 8.27 2.95 9.67 6.2a1 1 0 0 1 0 .8 11.45 11.45 0 0 1-4.3 5.1"/>
                                        <path d="M6.61 6.61A11.46 11.46 0 0 0 2.33 12a1 1 0 0 0 0 .8C3.73 16.05 6.95 19 12 19c1.68 0 3.17-.33 4.46-.92"/>
                                    </svg>
                                </button>
                            </div>
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

<form id="profile-reset-password-form" method="POST" action="{{ route('profile.reset-password') }}" data-confirm-save data-save-title="Reset Password" data-save-message="Reset your password?" data-save-confirm-label="Confirm" style="display:none;">
    @csrf
    @method('PATCH')
</form>

<style>
    .profile-section-grid { display:grid; grid-template-columns:170px minmax(0, 1fr); gap:30px; align-items:start; }
    .profile-photo-panel { display:flex; flex-direction:column; align-items:center; gap:10px; padding-top:4px; }
    .profile-photo-preview { width:92px; height:92px; font-size:1.35rem; }
    .profile-photo-link { margin:0; color:var(--brand); cursor:pointer; font-size:.9rem; }
    .profile-photo-input { position:absolute; width:1px; height:1px; opacity:0; pointer-events:none; }
    .profile-fields, .profile-login-grid { display:grid; grid-template-columns:repeat(2, minmax(0, 1fr)); gap:14px 18px; }
    .profile-login-column { display:grid; gap:14px; align-content:start; }
    .profile-login-grid { max-width:760px; }
    .password-field { position:relative; }
    .password-field input { padding-right:48px; }
    .password-toggle { position:absolute; top:50%; right:10px; transform:translateY(-50%); display:inline-flex; align-items:center; justify-content:center; width:32px; height:32px; padding:0; border:0; background:transparent; color:var(--muted); cursor:pointer; }
    .password-toggle:hover { color:var(--brand); }
    .password-toggle svg { width:18px; height:18px; }
    .password-toggle-hide { display:none; }
    .password-toggle.is-visible .password-toggle-show { display:none; }
    .password-toggle.is-visible .password-toggle-hide { display:block; }
    .profile-reset-password-wrap { display:flex; align-items:end; min-height:100%; }
    .profile-reset-password-btn { width:auto; }
    .profile-status-badge { width:auto; min-height:49px; border:1px solid var(--line); border-radius:12px; align-items:center; padding:11px 14px; }
    @media (max-width:760px) {
        .profile-section-grid, .profile-fields, .profile-login-grid { grid-template-columns:1fr; }
        .profile-photo-panel { align-items:flex-start; }
        .profile-reset-password-wrap { align-items:flex-start; }
        .profile-reset-password-btn { width:100%; }
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

        document.querySelectorAll('[data-password-toggle]').forEach(function (toggle) {
            toggle.addEventListener('click', function () {
                const field = toggle.closest('.password-field');
                const passwordInput = field ? field.querySelector('input') : null;

                if (!passwordInput) {
                    return;
                }

                const isVisible = passwordInput.type === 'text';
                passwordInput.type = isVisible ? 'password' : 'text';
                toggle.classList.toggle('is-visible', !isVisible);
                toggle.setAttribute('aria-label', isVisible ? 'Show password' : 'Hide password');
            });
        });
    });
</script>
@endsection
