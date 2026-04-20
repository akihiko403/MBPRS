@extends('layouts.app')

@section('content')
<style>
    .page-actions { display:flex; justify-content:space-between; align-items:center; gap:16px; margin-bottom:18px; }
    .page-actions .meta { color:var(--muted); }
    .page-actions .stack { align-items:center; }
    .report-action {
        display:inline-flex;
        align-items:center;
        justify-content:center;
        flex:0 0 190px;
        width:190px;
        height:44px;
        min-height:44px;
        padding:0 18px;
        border:none;
        border-radius:12px;
        font:inherit;
        line-height:1.2;
        text-align:center;
        white-space:nowrap;
        appearance:none;
        -webkit-appearance:none;
        box-sizing:border-box;
    }
    .modal-backdrop { position:fixed; inset:0; background:rgba(8, 20, 18, .55); display:none; align-items:center; justify-content:center; padding:24px; z-index:1000; }
    .modal-backdrop.open { display:flex; }
    .modal-card { width:min(900px, 100%); max-height:90vh; overflow:auto; background:#fff; border-radius:22px; border:1px solid var(--line); box-shadow:0 24px 80px rgba(0,0,0,.18); }
    .modal-head { display:flex; justify-content:space-between; align-items:center; gap:16px; padding:22px 24px; border-bottom:1px solid var(--line); position:sticky; top:0; background:#fff; }
    .modal-body { padding:24px; }
    .icon-btn { width:auto; background:transparent; border:1px solid var(--line); color:var(--ink); padding:8px 12px; }
    .filter-actions {
        grid-column:1 / -1;
        display:flex;
        justify-content:flex-end;
        align-items:center;
        gap:10px;
        margin-top:4px;
    }
    .filter-actions .btn,
    .filter-actions a {
        width:auto;
        min-width:0;
        padding:10px 16px;
        border-radius:10px;
        font-size:.95rem;
        line-height:1.2;
    }
    .filter-actions .btn {
        font-weight:600;
    }
</style>

<div class="card">
    <div class="page-actions">
        <div>
            <h3 style="margin:0;">Report Preview</h3>
            <div class="meta">Generate filtered permit reports and export the current result set.</div>
        </div>
        <div class="stack">
            <button class="btn report-action" type="button" data-open-modal="report-filter-modal">Filters</button>
            <a class="btn secondary report-action" href="{{ route('reports.export', request()->query()) }}">Export Excel/CSV</a>
            <button class="btn secondary report-action" onclick="window.print()" type="button">Print Report</button>
        </div>
    </div>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Permit ID</th><th>Owner</th><th>Building Type</th><th>Building Category</th><th>Barangay</th><th>City / Municipality</th><th>Province</th><th>Status</th><th>Created</th></tr></thead>
            <tbody>
            @forelse($records as $record)
                <tr>
                    <td>{{ $record->permit_id }}</td>
                    <td>{{ $record->owner_full_name }}</td>
                    <td>{{ $record->buildingType?->name }}</td>
                    <td>{{ $record->buildingCategory?->name }}</td>
                    <td>{{ $record->barangay }}</td>
                    <td>{{ $record->city_municipality }}</td>
                    <td>{{ $record->province }}</td>
                    <td><span class="badge {{ $record->status }}">{{ $record->status }}</span></td>
                    <td>{{ $record->created_at?->format('M d, Y') }}</td>
                </tr>
            @empty
                <tr><td colspan="9" class="muted">No records matched the selected report filters.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="modal-backdrop" id="report-filter-modal" aria-hidden="true">
    <div class="modal-card">
        <div class="modal-head">
            <div>
                <h3 style="margin:0;">Report Filters</h3>
                <div class="muted">Choose a report type and narrow the preview data.</div>
            </div>
            <button class="icon-btn" type="button" data-close-modal="report-filter-modal">Close</button>
        </div>
        <div class="modal-body">
            <form method="GET" action="{{ route('reports.index') }}" class="form-grid" id="report-filter-form">
                <div><label>Report Type</label><select name="report_type">@foreach($reportTypes as $key => $label)<option value="{{ $key }}" @selected($reportType === $key)>{{ $label }}</option>@endforeach</select></div>
                <div><label>Status</label><select name="status"><option value="">All</option><option value="Pending" @selected(($filters['status'] ?? '') === 'Pending')>Pending</option><option value="Approved" @selected(($filters['status'] ?? '') === 'Approved')>Approved</option><option value="Rejected" @selected(($filters['status'] ?? '') === 'Rejected')>Rejected</option><option value="Returned" @selected(($filters['status'] ?? '') === 'Returned')>Returned</option></select></div>
                <div><label>Month</label><input type="number" name="month" min="1" max="12" value="{{ $filters['month'] ?? '' }}"></div>
                <div><label>Year</label><input type="number" name="year" min="2000" value="{{ $filters['year'] ?? '' }}"></div>
                <div><label>Barangay</label><input name="barangay" value="{{ $filters['barangay'] ?? '' }}"></div>
                <div><label>City / Municipality</label><input name="city_municipality" value="{{ $filters['city_municipality'] ?? '' }}"></div>
                <div><label>Province</label><input name="province" value="{{ $filters['province'] ?? '' }}"></div>
                <div><label>Building Type</label><select name="building_type_id"><option value="">All</option>@foreach($buildingTypes as $type)<option value="{{ $type->id }}" @selected((string)($filters['building_type_id'] ?? '') === (string)$type->id)>{{ $type->name }}</option>@endforeach</select></div>
                <div><label>Building Category</label><select name="building_category_id"><option value="">All</option>@foreach($buildingCategories as $category)<option value="{{ $category->id }}" @selected((string)($filters['building_category_id'] ?? '') === (string)$category->id)>{{ $category->name }}</option>@endforeach</select></div>
                <div><label>Date From</label><input type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}"></div>
                <div><label>Date To</label><input type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}"></div>
                <div class="filter-actions">
                    <a class="btn secondary" href="{{ route('reports.index') }}">Reset</a>
                    <button class="btn" type="submit">Preview</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    const reportFilterForm = document.getElementById('report-filter-form');
    const reportFilterModal = document.getElementById('report-filter-modal');

    if (reportFilterForm && reportFilterModal) {
        reportFilterForm.addEventListener('submit', function () {
            reportFilterModal.classList.remove('open');
        });
    }

    document.addEventListener('click', function (event) {
        const openTrigger = event.target.closest('[data-open-modal]');
        const closeTrigger = event.target.closest('[data-close-modal]');
        if (openTrigger) {
            const modal = document.getElementById(openTrigger.dataset.openModal);
            if (modal) modal.classList.add('open');
        }
        if (closeTrigger) {
            const modal = document.getElementById(closeTrigger.dataset.closeModal);
            if (modal) modal.classList.remove('open');
        }
        if (event.target.classList.contains('modal-backdrop')) {
            event.target.classList.remove('open');
        }
    });
</script>
@endsection





