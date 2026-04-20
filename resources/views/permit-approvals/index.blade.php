@extends('layouts.app')

@section('content')
<style>
    .page-actions { display:flex; justify-content:space-between; align-items:center; gap:16px; margin-bottom:14px; }
    .page-actions .meta { color:var(--muted); }
    .approval-table th,
    .approval-table td { vertical-align:middle; }
    .approval-table th:nth-child(1), .approval-table td:nth-child(1) { width:14%; }
    .approval-table th:nth-child(2), .approval-table td:nth-child(2) { width:18%; }
    .approval-table th:nth-child(3), .approval-table td:nth-child(3) { width:10%; }
    .approval-table th:nth-child(4), .approval-table td:nth-child(4) { width:13%; }
    .approval-table th:nth-child(5), .approval-table td:nth-child(5) { width:9%; }
    .approval-table th:nth-child(6), .approval-table td:nth-child(6) { width:36%; }
    .approval-table td { padding-top:12px; padding-bottom:12px; }
    .compact-form { display:grid; gap:12px; grid-template-columns:150px minmax(180px, 1fr) 180px; align-items:center; }
    .compact-form select,
    .compact-form input,
    .compact-form button { margin:0; min-height:46px; }
    .compact-form button { width:100%; }
    @media (max-width:920px){
        .compact-form { grid-template-columns:1fr; }
    }
</style>

<div class="card">
    <div class="page-actions">
        <div>
            <h3 style="margin:0;">Pending Building Permits</h3>
            <div class="meta">Review and update pending permit applications.</div>
        </div>
    </div>

    <div class="table-wrap">
        <table class="approval-table">
            <thead><tr><th>Permit ID</th><th>Owner</th><th>Building Type</th><th>Building Category</th><th>Status</th><th>Approval Action</th></tr></thead>
            <tbody>
            @forelse($pendingPermits as $permit)
                <tr>
                    <td>{{ $permit->permit_id }}</td>
                    <td>{{ $permit->owner_full_name }}</td>
                    <td>{{ $permit->buildingType?->name }}</td>
                    <td>{{ $permit->buildingCategory?->name }}</td>
                    <td><span class="badge {{ $permit->status }}">{{ $permit->status }}</span></td>
                    <td>
                        <form method="POST" action="{{ route('permit-approvals.update-status', $permit) }}" class="compact-form">
                            @csrf
                            @method('PATCH')
                            <select name="status"><option>Approved</option><option>Rejected</option><option>Returned</option></select>
                            <input name="remarks" placeholder="Remarks">
                            <button class="btn secondary" type="submit">Apply Update</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="muted">No pending permits for approval.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    {{ $pendingPermits->links() }}
</div>
@endsection
