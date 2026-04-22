@php
    use App\Models\Role;
    use Illuminate\Support\Facades\Auth;

    $user = Auth::user();
    $isAdmin = $user?->hasRole(Role::ADMIN, Role::ADMINISTRATOR);
    $isAdministrator = $user?->hasRole(Role::ADMINISTRATOR);
    $displayName = $isAdmin ? 'Administrator' : $user?->name;
    $initials = collect(explode(' ', (string) $user?->name))->filter()->map(fn ($part) => mb_substr($part, 0, 1))->take(2)->implode('');
    $profilePhotoUrl = $user?->profile_photo_path
        ? asset('storage/' . $user->profile_photo_path).'?v='.$user->updated_at?->timestamp
        : null;
@endphp

<div class="user-dropdown" x-data="{ open: false }" @click.outside="open = false">
    <button class="user-dropdown-trigger" type="button" @click="open = !open" :aria-expanded="open.toString()" aria-haspopup="true">
        <span class="user-avatar" aria-hidden="true">
            @if($profilePhotoUrl)
                <img src="{{ $profilePhotoUrl }}" alt="">
            @else
                <span class="user-avatar-fallback">{{ $initials ?: 'U' }}</span>
            @endif
        </span>
        <span>{{ $displayName }}</span>
        <svg class="user-dropdown-arrow" :class="{ 'open': open }" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
            <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 11.17l3.71-3.94a.75.75 0 1 1 1.08 1.04l-4.25 4.5a.75.75 0 0 1-1.08 0l-4.25-4.5a.75.75 0 0 1 .02-1.06Z" clip-rule="evenodd" />
        </svg>
    </button>

    <div
        class="user-dropdown-menu"
        x-cloak
        x-show="open"
        x-transition:enter="dropdown-enter"
        x-transition:enter-start="dropdown-enter-start"
        x-transition:enter-end="dropdown-enter-end"
        x-transition:leave="dropdown-leave"
        x-transition:leave-start="dropdown-leave-start"
        x-transition:leave-end="dropdown-leave-end"
    >
        <a class="user-dropdown-item" href="{{ route('profile') }}">Profile</a>

        @if($isAdministrator)
            <a class="user-dropdown-item" href="{{ route('settings') }}">Settings</a>
            <a class="user-dropdown-item" href="{{ route('backup-restore.index') }}">Backup & Restore</a>
        @endif

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button class="user-dropdown-item danger" type="submit">Log out</button>
        </form>
    </div>
</div>
