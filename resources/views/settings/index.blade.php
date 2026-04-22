@extends('layouts.app')

@section('content')
@php
    $logoUrl = $settings->system_logo_path
        ? asset('storage/' . $settings->system_logo_path).'?v='.$settings->updated_at?->timestamp
        : null;
@endphp

<div class="card">
    <h3 style="margin:0 0 6px;">System Settings</h3>
    <div class="muted" style="margin-bottom:20px;">Update the identity shown across the system.</div>

    <form method="POST" action="{{ route('settings.update') }}" enctype="multipart/form-data" data-confirm-save data-save-message="Save these system settings?">
        @csrf
        @method('PATCH')

        <div class="settings-grid">
            <div class="settings-logo-panel">
                <span class="settings-logo-preview" id="system-logo-preview">
                    @if($logoUrl)
                        <img src="{{ $logoUrl }}" alt="">
                    @else
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                            <path d="M4 20h16" stroke-width="1.8" stroke-linecap="round"/>
                            <path d="M6 20V9l6-4 6 4v11" stroke-width="1.8" stroke-linejoin="round"/>
                            <path d="M10 20v-6h4v6" stroke-width="1.8" stroke-linejoin="round"/>
                        </svg>
                    @endif
                </span>
                <label class="settings-logo-link" for="system_logo">Update logo</label>
                <input id="system_logo" class="settings-logo-input" type="file" name="system_logo" accept="image/*">
            </div>

            <div class="settings-fields">
                <div>
                    <label>Name</label>
                    <input type="text" name="system_name" value="{{ old('system_name', $settings->system_name) }}" required>
                </div>

                <div>
                    <label>Sub Header</label>
                    <input type="text" name="system_subheader" value="{{ old('system_subheader', $settings->system_subheader ?? 'Municipality of Lebak') }}" placeholder="Municipality of Lebak">
                </div>

                <div>
                    <label>Description</label>
                    <textarea name="system_description" placeholder="Short description shown on login and system areas.">{{ old('system_description', $settings->system_description) }}</textarea>
                </div>
            </div>
        </div>

        <div class="stack" style="justify-content:flex-end;margin-top:20px;">
            <button class="btn" type="submit" style="width:auto;">Save Settings</button>
        </div>
    </form>
</div>

<style>
    .settings-grid { display:grid; grid-template-columns:180px minmax(0, 1fr); gap:28px; align-items:start; }
    .settings-logo-panel { display:flex; flex-direction:column; align-items:center; gap:10px; padding-top:4px; }
    .settings-logo-preview { display:inline-flex; align-items:center; justify-content:center; width:96px; height:96px; border:1px solid var(--line); border-radius:14px; background:#f8fbf9; color:var(--brand); overflow:hidden; }
    .settings-logo-preview img { width:100%; height:100%; object-fit:cover; display:block; }
    .settings-logo-preview svg { width:42px; height:42px; }
    .settings-logo-link { margin:0; color:var(--brand); cursor:pointer; font-size:.9rem; }
    .settings-logo-input { position:absolute; width:1px; height:1px; opacity:0; pointer-events:none; }
    .settings-fields { display:grid; gap:14px; }
    @media (max-width:760px) {
        .settings-grid { grid-template-columns:1fr; }
        .settings-logo-panel { align-items:flex-start; }
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const input = document.getElementById('system_logo');
        const preview = document.getElementById('system-logo-preview');
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
