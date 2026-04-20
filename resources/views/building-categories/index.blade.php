@extends('layouts.app')

@section('content')
<style>
    .page-actions { display:flex; justify-content:space-between; align-items:center; gap:16px; margin-bottom:18px; }
    .page-actions .meta { color:var(--muted); }
    .modal-backdrop { position:fixed; inset:0; background:rgba(8, 20, 18, .55); display:none; align-items:center; justify-content:center; padding:24px; z-index:1000; }
    .modal-backdrop.open { display:flex; }
    .modal-card { width:min(720px, 100%); max-height:90vh; overflow:auto; background:#fff; border-radius:22px; border:1px solid var(--line); box-shadow:0 24px 80px rgba(0,0,0,.18); }
    .modal-head { display:flex; justify-content:space-between; align-items:center; gap:16px; padding:22px 24px; border-bottom:1px solid var(--line); position:sticky; top:0; background:#fff; }
    .modal-body { padding:24px; }
    .icon-btn { width:auto; background:transparent; border:1px solid var(--line); color:var(--ink); padding:8px 12px; }
    .action-icons { display:flex; gap:8px; align-items:center; }
    .action-icon { display:inline-flex; align-items:center; justify-content:center; width:36px; height:36px; padding:0; border-radius:10px; border:1px solid var(--line); background:#fff; color:var(--ink); font-size:14px; font-weight:600; line-height:1; }
    .action-icon svg { width:16px; height:16px; flex-shrink:0; }
    .action-icon.view { background:#eef6f3; }
    .action-icon.edit { background:#fff7ed; color:#9a3412; }
    .action-icon.delete { background:#fef2f2; color:#991b1b; }
    .records-table { table-layout:fixed; }
    .records-table th:nth-child(1), .records-table td:nth-child(1) { width:26%; }
    .records-table th:nth-child(2), .records-table td:nth-child(2) { width:50%; }
    .records-table th:nth-child(3), .records-table td:nth-child(3) { width:24%; text-align:left; vertical-align:middle; }
    .records-table .action-icons { justify-content:flex-start; }
    .records-table td { word-break:break-word; }
</style>

<div class="card">
    <div class="page-actions">
        <div>
            <h3 style="margin:0;">Building Category Records</h3>
            <div class="meta">Repository of building categories used in permit records.</div>
        </div>
        <button class="btn" style="width:auto;" type="button" data-open-modal="category-modal">Add Building Category</button>
    </div>

    <div class="table-wrap">
        <table class="records-table">
            <thead><tr><th>Name</th><th>Description</th><th>Actions</th></tr></thead>
            <tbody>
            @forelse($items as $item)
                <tr>
                    <td>{{ $item->name }}</td>
                    <td>{{ $item->description ?: '—' }}</td>
                    <td>
                        <div class="action-icons">
                            <a class="action-icon view" href="{{ route('building-categories.index', ['show' => $item->id]) }}" title="View Building Category" aria-label="View Building Category"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg></a>
                            <a class="action-icon edit" href="{{ route('building-categories.index', ['edit' => $item->id]) }}" title="Edit Building Category" aria-label="Edit Building Category"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="m16.5 3.5 4 4L7 21l-4 1 1-4 12.5-14.5Z"/></svg></a>
                            <form method="POST" action="{{ route('building-categories.destroy', $item) }}" data-confirm-delete data-confirm-message="Delete this building category?" style="display:inline-flex; margin:0;">
                                @csrf
                                @method('DELETE')
                                <button class="action-icon delete" type="submit" title="Delete Building Category" aria-label="Delete Building Category"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M8 6V4h8v2"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/></svg></button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="3" class="muted">No building categories available.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    {{ $items->links() }}
</div>

@if($selectedItem)
<div class="modal-backdrop open" id="category-view-modal" aria-hidden="true">
    <div class="modal-card">
        <div class="modal-head">
            <div>
                <h3 style="margin:0;">Building Category Details</h3>
                <div class="muted">Review the selected building category record.</div>
            </div>
            <button class="icon-btn" type="button" data-close-modal="category-view-modal">Close</button>
        </div>
        <div class="modal-body">
            <div class="grid cols-2">
                <div><strong>Name</strong><div>{{ $selectedItem->name }}</div></div>
                <div><strong>Description</strong><div>{{ $selectedItem->description ?: 'No description provided.' }}</div></div>
            </div>
        </div>
    </div>
</div>
@endif

<div class="modal-backdrop {{ $editItem || $errors->any() ? 'open' : '' }}" id="category-modal" aria-hidden="true">
    <div class="modal-card">
        <div class="modal-head">
            <div>
                <h3 style="margin:0;">{{ $editItem ? 'Edit Building Category' : 'Add Building Category' }}</h3>
                <div class="muted">Maintain the list of building categories used in permits.</div>
            </div>
            <button class="icon-btn" type="button" data-close-modal="category-modal">Close</button>
        </div>
        <div class="modal-body">
            <form method="POST" action="{{ $editItem ? route('building-categories.update', $editItem) : route('building-categories.store') }}" class="grid">
                @csrf
                @if($editItem) @method('PATCH') @endif
                <div><label>Name</label><input name="name" value="{{ old('name', $editItem->name ?? '') }}" required></div>
                <div><label>Description</label><input name="description" value="{{ old('description', $editItem->description ?? '') }}"></div>
                <div class="stack">
                    <button class="btn" style="width:auto;" type="submit">{{ $editItem ? 'Update' : 'Save' }}</button>
                    <button class="btn secondary" style="width:auto;" type="button" data-close-modal="category-modal">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('click', function (event) {
        const openTrigger = event.target.closest('[data-open-modal]');
        const closeTrigger = event.target.closest('[data-close-modal]');
        if (openTrigger) {
            const modal = document.getElementById(openTrigger.dataset.openModal);
            if (modal) modal.classList.add('open');
        }
        if (closeTrigger) {
            const modal = document.getElementById(closeTrigger.dataset.closeModal);
            if (modal) {
                modal.classList.remove('open');
                if (window.location.search.includes('edit=') || window.location.search.includes('show=')) window.location = '{{ route('building-categories.index') }}';
            }
        }
        if (event.target.classList.contains('modal-backdrop')) {
            event.target.classList.remove('open');
            if (window.location.search.includes('edit=') || window.location.search.includes('show=')) window.location = '{{ route('building-categories.index') }}';
        }
    });
</script>
@endsection






