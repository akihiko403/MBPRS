@extends('layouts.app')

@section('content')
<style>
    .dashboard-stat-label { display:flex; align-items:center; gap:10px; }
    .dashboard-stat-icon { display:inline-flex; align-items:center; justify-content:center; width:34px; height:34px; border-radius:10px; background:#eef6f3; color:var(--brand); border:1px solid #d7e1dd; flex:0 0 auto; }
    .dashboard-stat-icon.warn { background:#fff7ed; color:#d97706; border-color:#fdba74; }
    .dashboard-stat-icon.success { background:#ecfdf5; color:#15803d; border-color:#86efac; }
    .dashboard-stat-icon.danger { background:#fef2f2; color:#b91c1c; border-color:#fca5a5; }
    .dashboard-stat-icon svg { width:18px; height:18px; }
</style>
<div class="grid cols-4">
    <div class="card hero">
        <div class="dashboard-stat-label muted">
            <span class="dashboard-stat-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 7h18"/><path d="M6 3h12v18H6z"/><path d="M8 11h8"/><path d="M8 15h5"/></svg></span>
            <span>Total Building Permit Records</span>
        </div>
        <div class="stat">{{ $stats['total'] }}</div>
    </div>
    <div class="card hero">
        <div class="dashboard-stat-label muted">
            <span class="dashboard-stat-icon warn" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 3"/></svg></span>
            <span>Pending Applications</span>
        </div>
        <div class="stat">{{ $stats['pending'] }}</div>
    </div>
    <div class="card hero">
        <div class="dashboard-stat-label muted">
            <span class="dashboard-stat-icon success" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg></span>
            <span>Approved Permits</span>
        </div>
        <div class="stat">{{ $stats['approved'] }}</div>
    </div>
    <div class="card hero">
        <div class="dashboard-stat-label muted">
            <span class="dashboard-stat-icon danger" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M15 9 9 15"/><path d="m9 9 6 6"/></svg></span>
            <span>Rejected / Returned</span>
        </div>
        <div class="stat">{{ $stats['returned_or_rejected'] }}</div>
    </div>
</div>

<div class="card" style="margin-top:20px;">
    <div style="display:flex; justify-content:space-between; gap:16px; align-items:center; margin-bottom:12px;">
        <div>
            <h3 style="margin:0;">Recently Added Records</h3>
            <div class="muted">Automatic updates based on permit transactions.</div>
        </div>
    </div>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Permit ID</th><th>Owner</th><th>Building Type</th><th>Building Category</th><th>Status</th><th>Created</th></tr></thead>
            <tbody>
            @forelse ($recentPermits as $permit)
                <tr>
                    <td>{{ $permit->permit_id }}</td>
                    <td>{{ $permit->owner_full_name }}</td>
                    <td>{{ $permit->buildingType?->name }}</td>
                    <td>{{ $permit->buildingCategory?->name }}</td>
                    <td><span class="badge {{ $permit->status }}">{{ $permit->status }}</span></td>
                    <td>{{ $permit->created_at?->format('M d, Y h:i A') }}</td>
                </tr>
            @empty
                <tr><td colspan="6" class="muted">No permit records yet.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection


