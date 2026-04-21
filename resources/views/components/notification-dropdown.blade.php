@php
    $user = auth()->user();
    $today = now()->startOfDay();
    $readAt = $user?->notifications_read_at;
    $unreadSince = $readAt && $readAt->isToday() ? $readAt : $today;
    $changeMethods = ['POST', 'PATCH', 'PUT', 'DELETE'];
    $changesQuery = fn () => \App\Models\AuditLog::query()
        ->whereIn('method', $changeMethods)
        ->whereNotIn('action', ['login', 'logout'])
        ->where('description', 'not like', 'Viewed %');

    $notifications = $changesQuery()
        ->with('user')
        ->latest()
        ->limit(8)
        ->get();

    $recentCount = $changesQuery()
        ->where('created_at', '>=', $unreadSince)
        ->count();

    $canViewAuditLog = $user?->canAccess('audit-logs');
    $notificationText = function ($notification): array {
        $description = $notification->description ?: 'System record changed.';

        return match (true) {
            str_contains($description, 'users.store') => ['New User Added', 'A user account was created.'],
            str_contains($description, 'users.update') => ['User Account Updated', 'A user account was changed.'],
            str_contains($description, 'users.toggle') => ['User Status Updated', 'A user account status was changed.'],
            str_contains($description, 'profile.update') => ['Profile Updated', 'An account profile was changed.'],
            str_contains($description, 'settings.update') => ['System Settings Updated', 'System identity details were changed.'],
            str_contains($description, 'backup-restore.restore') => ['Database Restored', 'A database backup was restored.'],
            str_contains($description, 'building-categories.store') => ['Building Category Added', 'A new building category was added.'],
            str_contains($description, 'building-categories.update') => ['Building Category Updated', 'A building category was changed.'],
            str_contains($description, 'building-types.store') => ['Building Type Added', 'A new building type was added.'],
            str_contains($description, 'building-types.update') => ['Building Type Updated', 'A building type was changed.'],
            str_contains($description, 'building-permits.store') => ['Building Permit Added', 'A new building permit was added.'],
            str_contains($description, 'building-permits.update') => ['Building Permit Updated', 'A building permit was changed.'],
            str_contains($description, 'permit-approvals.update-status') => ['Permit Status Updated', 'A permit was approved, rejected, or returned.'],
            $notification->method === 'DELETE' => ['Record Deleted', 'A record was moved to trash or permanently deleted.'],
            in_array($notification->method, ['PATCH', 'PUT'], true) => ['Record Updated', 'A system record was changed.'],
            $notification->method === 'POST' => ['New Record Added', 'A new system record was created.'],
            default => ['System Change', $description],
        };
    };
@endphp

<div class="notification-dropdown" x-data="{ open: false }" @click.outside="open = false">
    <button class="notification-trigger" type="button" @click="open = !open" :aria-expanded="open.toString()" aria-label="Notifications">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 17H9m10-1.8c-.86-.94-1.5-1.5-1.5-4.2a5.5 5.5 0 0 0-11 0c0 2.7-.64 3.26-1.5 4.2-.42.46-.1 1.3.52 1.3h12.96c.62 0 .94-.84.52-1.3Z" />
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M13.5 19a1.8 1.8 0 0 1-3 0" />
        </svg>
        @if($recentCount > 0)
            <span class="notification-badge">{{ $recentCount > 9 ? '9+' : $recentCount }}</span>
        @endif
    </button>

    <div
        class="notification-menu"
        x-cloak
        x-show="open"
        x-transition:enter="dropdown-enter"
        x-transition:enter-start="dropdown-enter-start"
        x-transition:enter-end="dropdown-enter-end"
        x-transition:leave="dropdown-leave"
        x-transition:leave-start="dropdown-leave-start"
        x-transition:leave-end="dropdown-leave-end"
    >
        <div class="notification-head">
            <div>
                <strong>Notifications</strong>
                <span>{{ $recentCount }} new today</span>
            </div>
            @if($recentCount > 0)
                <form method="POST" action="{{ route('notifications.read') }}">
                    @csrf
                    <button class="notification-clear" type="submit">Clear</button>
                </form>
            @endif
        </div>

        @forelse($notifications as $notification)
            @php([$notificationTitle, $notificationDescription] = $notificationText($notification))
            <a class="notification-item" href="{{ $canViewAuditLog ? route('audit-logs.index', ['search' => $notification->description ?: $notification->action]) : '#' }}">
                <span class="notification-dot" aria-hidden="true"></span>
                <span>
                    <strong>{{ $notificationTitle }}</strong>
                    <small>{{ $notificationDescription }}</small>
                    <em>{{ $notification->user?->name ?? 'System' }} &middot; {{ $notification->created_at->diffForHumans() }}</em>
                </span>
            </a>
        @empty
            <div class="notification-empty">No new changes yet.</div>
        @endforelse
    </div>
</div>
