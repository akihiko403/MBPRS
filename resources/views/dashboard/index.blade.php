@extends('layouts.app')

@section('content')
<style>
    .dashboard-stat-label { display:flex; align-items:center; gap:10px; }
    .dashboard-stat-icon { display:inline-flex; align-items:center; justify-content:center; width:34px; height:34px; border-radius:10px; background:#eef6f3; color:var(--brand); border:1px solid #d7e1dd; flex:0 0 auto; }
    .dashboard-stat-icon.warn { background:#fff7ed; color:#d97706; border-color:#fdba74; }
    .dashboard-stat-icon.success { background:#ecfdf5; color:#15803d; border-color:#86efac; }
    .dashboard-stat-icon.danger { background:#fef2f2; color:#b91c1c; border-color:#fca5a5; }
    .dashboard-stat-icon svg { width:18px; height:18px; }
    .analytics-grid { display:grid; grid-template-columns:minmax(260px, .85fr) minmax(0, 1.15fr); gap:12px; margin-top:12px; }
    .analytics-grid .card, .breakdown-grid .card { padding:14px 16px; border-radius:14px; }
    .chart-title { margin:0; font-size:1rem; }
    .chart-meta { margin:2px 0 10px; font-size:.9rem; }
    .status-chart-layout { display:grid; grid-template-columns:140px minmax(0, 1fr); gap:14px; align-items:center; }
    .donut-chart { width:132px; aspect-ratio:1; border-radius:50%; background:conic-gradient(var(--segments)); position:relative; box-shadow:inset 0 0 0 1px var(--line); }
    .donut-chart::after { content:""; position:absolute; inset:25px; border-radius:50%; background:#fff; border:1px solid var(--line); }
    .donut-center { position:absolute; inset:0; display:grid; place-items:center; z-index:1; text-align:center; pointer-events:none; }
    .donut-center strong { font-size:1.35rem; line-height:1; }
    .donut-center span { color:var(--muted); font-size:.72rem; margin-top:3px; display:block; }
    .legend-list { display:grid; gap:6px; }
    .legend-row { display:flex; align-items:center; justify-content:space-between; gap:10px; color:var(--muted); font-size:.9rem; }
    .legend-label { display:inline-flex; align-items:center; gap:7px; color:var(--ink); }
    .legend-dot { width:9px; height:9px; border-radius:50%; background:var(--dot); }
    .bar-chart { display:flex; align-items:end; gap:10px; min-height:162px; padding:4px 4px 0; border-bottom:1px solid var(--line); }
    .bar-item { flex:1; display:grid; align-content:end; justify-items:center; gap:5px; min-width:38px; height:154px; }
    .bar-value { color:var(--muted); font-size:.78rem; min-height:16px; }
    .bar-fill { width:100%; max-width:40px; min-height:7px; height:var(--bar-height); border-radius:7px 7px 0 0; background:linear-gradient(180deg,#2f8a75,#1f6f5f); }
    .bar-label { color:var(--muted); font-size:.78rem; }
    .breakdown-grid { display:grid; grid-template-columns:repeat(2, minmax(0, 1fr)); gap:12px; margin-top:12px; }
    .breakdown-list { display:grid; gap:9px; }
    .breakdown-row { display:grid; gap:5px; }
    .breakdown-head { display:flex; align-items:center; justify-content:space-between; gap:10px; font-size:.86rem; }
    .progress-track { height:7px; border-radius:999px; background:#eef3f0; overflow:hidden; border:1px solid var(--line); }
    .progress-fill { height:100%; width:var(--progress); border-radius:999px; background:var(--brand); }
    @media (max-width:1080px){ .analytics-grid,.breakdown-grid{ grid-template-columns:1fr; } }
    @media (max-width:640px){ .status-chart-layout{ grid-template-columns:1fr; } .donut-chart{ margin:auto; } .bar-chart{ gap:8px; overflow-x:auto; } .bar-item{ min-width:54px; } }
</style>

<div class="analytics-grid">
    <div class="card">
        <h3 class="chart-title">Permit Status Analytics</h3>
        <div class="muted chart-meta">Current distribution of permit records.</div>
        <div class="status-chart-layout">
            <div class="donut-chart" style="--segments: {{ $analytics['donutSegments'] }};">
                <div class="donut-center">
                    <div><strong>{{ $stats['total'] }}</strong><span>Total</span></div>
                </div>
            </div>
            <div class="legend-list">
                @foreach($analytics['statusCounts'] as $status => $count)
                    <div class="legend-row">
                        <span class="legend-label"><span class="legend-dot" style="--dot: {{ $analytics['statusColors'][$status] ?? '#64748b' }}"></span>{{ $status }}</span>
                        <strong>{{ $count }}</strong>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="card">
        <h3 class="chart-title">Monthly Permit Volume</h3>
        <div class="muted chart-meta">New permits encoded during the last six months.</div>
        <div class="bar-chart" aria-label="Monthly permit volume bar chart">
            @foreach($analytics['monthlyCounts'] as $month)
                @php($height = max(7, round(($month['count'] / $analytics['monthlyMax']) * 125)))
                <div class="bar-item">
                    <div class="bar-value">{{ $month['count'] }}</div>
                    <div class="bar-fill" style="--bar-height: {{ $height }}px;"></div>
                    <div class="bar-label">{{ $month['label'] }}</div>
                </div>
            @endforeach
        </div>
    </div>
</div>

<div class="breakdown-grid">
    <div class="card">
        <h3 class="chart-title">Top Building Categories</h3>
        <div class="muted chart-meta">Most used permit categories.</div>
        <div class="breakdown-list">
            @forelse($analytics['categoryBreakdown'] as $item)
                <div class="breakdown-row">
                    <div class="breakdown-head"><span>{{ $item['label'] }}</span><strong>{{ $item['count'] }}</strong></div>
                    <div class="progress-track"><div class="progress-fill" style="--progress: {{ max(4, round(($item['count'] / $analytics['categoryMax']) * 100)) }}%;"></div></div>
                </div>
            @empty
                <div class="muted">No category data yet.</div>
            @endforelse
        </div>
    </div>

    <div class="card">
        <h3 class="chart-title">Top Building Types</h3>
        <div class="muted chart-meta">Most used building types.</div>
        <div class="breakdown-list">
            @forelse($analytics['typeBreakdown'] as $item)
                <div class="breakdown-row">
                    <div class="breakdown-head"><span>{{ $item['label'] }}</span><strong>{{ $item['count'] }}</strong></div>
                    <div class="progress-track"><div class="progress-fill" style="--progress: {{ max(4, round(($item['count'] / $analytics['typeMax']) * 100)) }}%;"></div></div>
                </div>
            @empty
                <div class="muted">No building type data yet.</div>
            @endforelse
        </div>
    </div>
</div>

<div class="card" style="margin-top:20px;">
    <div class="page-actions">
        <div>
            <h3 style="margin:0;">Recently Added Records</h3>
            <div class="muted">Automatic updates based on permit transactions.</div>
        </div>
        <form class="card-search" method="GET" action="{{ route('dashboard') }}">
            <input name="search" value="{{ request('search') }}" placeholder="Search permit ID, owner, type, category, status">
            <div class="card-search-actions">
                <button class="btn" type="submit">Search</button>
                @if(request('search'))<a class="btn secondary" href="{{ route('dashboard') }}">Reset</a>@endif
            </div>
        </form>
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
