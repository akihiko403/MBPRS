@extends('layouts.app')

@section('content')
<style>
    .page-actions { display:flex; justify-content:space-between; align-items:center; gap:16px; margin-bottom:18px; }
    .page-actions .meta { color:var(--muted); }
    .audit-description { max-width:360px; }
    .audit-url { max-width:280px; word-break:break-word; }
</style>

<div class="card">
    <div class="page-actions">
        <div>
            <h3 style="margin:0;">Audit Log</h3>
            <div class="meta">{{ $logs->total() }} login and record change events.</div>
        </div>
        <form class="card-search" method="GET" action="{{ route('audit-logs.index') }}">
            <input name="search" value="{{ request('search') }}" placeholder="Search user, login, add, update, delete, restore, IP">
            <div class="card-search-actions"><button class="btn" type="submit">Search</button>
                @if(request('search'))<a class="btn secondary" href="{{ route('audit-logs.index') }}">Reset</a>@endif
            </div>
        </form>
    </div>

    <div class="table-wrap">
        <table>
            <thead><tr><th>Date & Time</th><th>User</th><th>Action</th><th>Activity</th><th>Method</th><th>IP Address</th><th>URL</th></tr></thead>
            <tbody>
            @forelse($logs as $log)
                <tr>
                    <td>{{ $log->created_at?->format('M d, Y h:i A') }}</td>
                    <td>
                        {{ $log->user?->name ?? 'Deleted User' }}
                        @if($log->user?->username)<div class="muted">{{ $log->user->username }}</div>@endif
                    </td>
                    <td><span class="badge {{ $log->action === 'login' ? 'Approved' : ($log->action === 'logout' ? 'Rejected' : 'Pending') }}">{{ ucfirst($log->action) }}</span></td>
                    <td class="audit-description">{{ $log->description }}</td>
                    <td>{{ $log->method }}</td>
                    <td>{{ $log->ip_address }}</td>
                    <td class="audit-url">{{ $log->url }}</td>
                </tr>
            @empty
                <tr><td colspan="7" class="muted">No audit logs matched the selected filters.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    {{ $logs->links() }}
</div>
@endsection
