@extends('layouts.app')

@section('content')
<style>
    .page-actions { display:flex; justify-content:space-between; align-items:center; gap:16px; margin-bottom:18px; }
    .page-actions .meta { color:var(--muted); }
    .modal-backdrop { position:fixed; inset:0; background:rgba(8, 20, 18, .55); display:none; align-items:center; justify-content:center; padding:24px; z-index:1000; }
    .modal-backdrop.open { display:flex; }
    .modal-card { width:min(920px, 100%); max-height:90vh; overflow:auto; background:#fff; border-radius:22px; border:1px solid var(--line); box-shadow:0 24px 80px rgba(0,0,0,.18); }
    .modal-head { display:flex; justify-content:space-between; align-items:center; gap:16px; padding:22px 24px; border-bottom:1px solid var(--line); position:sticky; top:0; background:#fff; }
    .modal-body { padding:24px; }
    .icon-btn { width:auto; background:transparent; border:1px solid var(--line); color:var(--ink); padding:8px 12px; }
    .action-icons { display:flex; gap:8px; align-items:center; }
    .action-icon { display:inline-flex; align-items:center; justify-content:center; width:36px; height:36px; padding:0; border-radius:10px; border:1px solid var(--line); background:#fff; color:var(--ink); font-size:14px; font-weight:600; line-height:1; }
    .action-icon svg { width:16px; height:16px; flex-shrink:0; }
    .action-icon.view { background:#eef6f3; }
    .action-icon.edit { background:#fff7ed; color:#9a3412; }
    .action-icon.delete { background:#fef2f2; color:#991b1b; }
    .upload-box { display:flex; flex-wrap:wrap; align-items:center; gap:10px; min-height:52px; padding:10px 12px; border:1px solid var(--line); border-radius:14px; background:#fff; }
    .upload-box input[type=file] { position:absolute; width:1px; height:1px; padding:0; margin:-1px; overflow:hidden; clip:rect(0,0,0,0); white-space:nowrap; border:0; }
    .upload-trigger { display:inline-flex; align-items:center; justify-content:center; width:auto; min-height:38px; padding:8px 14px; border:1px solid #cbd5e1; border-radius:12px; background:#f8fafc; color:var(--ink); font-size:14px; font-weight:600; cursor:pointer; }
    .selected-files { display:flex; flex:1 1 240px; flex-wrap:wrap; gap:8px; margin:0; padding-left:0; list-style:none; }
    .selected-file-item { display:inline-flex; align-items:center; gap:8px; max-width:220px; padding:8px 10px 8px 12px; border:1px solid #cbd5e1; border-radius:12px; background:#f8fafc; color:var(--ink); font-size:12px; overflow:hidden; box-shadow:inset 0 0 0 1px rgba(255,255,255,.55); }
    .selected-file-name { min-width:0; flex:1 1 auto; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
    .selected-file-remove { display:inline-flex; align-items:center; justify-content:center; flex:0 0 auto; width:18px; height:18px; border:0; border-radius:999px; background:#fee2e2; color:#b91c1c; font-size:13px; font-weight:700; line-height:1; padding:0; cursor:pointer; }
    .document-grid { display:grid; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); gap:12px; margin-top:8px; }
    .document-card {
        display:flex;
        flex-direction:column;
        align-items:flex-start;
        min-height:138px;
        padding:12px;
        border:1px solid var(--line);
        border-radius:14px;
        background:linear-gradient(180deg,#ffffff,#f8fbfa);
        box-shadow:0 8px 18px rgba(17,24,39,.04);
    }
    .document-card-name {
        display:-webkit-box;
        margin:0 0 4px;
        font-size:.98rem;
        font-weight:600;
        line-height:1.35;
        word-break:break-word;
        -webkit-line-clamp:2;
        -webkit-box-orient:vertical;
        overflow:hidden;
    }
    .document-card-meta { margin:0 0 10px; color:var(--muted); font-size:.82rem; }
    .document-card-actions {
        display:flex;
        justify-content:flex-start;
        align-items:center;
        gap:8px;
        flex-wrap:nowrap;
        margin-top:auto;
    }
    .document-card-actions .btn {
        display:inline-flex;
        align-items:center;
        justify-content:center;
        flex:0 0 96px;
        width:96px;
        height:40px;
        min-width:96px;
        padding:0 12px;
        text-align:center;
        white-space:nowrap;
    }
    .document-list {
        display:grid;
        gap:8px;
        margin-top:6px;
    }
    .document-list-item {
        display:flex;
        justify-content:space-between;
        align-items:center;
        gap:12px;
        padding:8px 10px;
        border:1px solid var(--line);
        border-radius:10px;
        background:#f8fbfa;
    }
    .document-list-info {
        min-width:0;
        flex:1 1 auto;
    }
    .document-list-name {
        margin:0 0 2px;
        font-size:.92rem;
        font-weight:600;
        line-height:1.3;
        overflow:hidden;
        text-overflow:ellipsis;
        white-space:nowrap;
    }
    .document-list-meta {
        margin:0;
        color:var(--muted);
        font-size:.78rem;
        line-height:1.2;
    }
    .document-list-actions {
        display:flex;
        align-items:center;
        gap:6px;
        flex:0 0 auto;
    }
    .document-action-btn {
        border-radius:12px;
    }
    .document-list-actions .btn {
        display:inline-flex;
        align-items:center;
        justify-content:center;
        width:88px;
        min-width:88px;
        height:36px;
        padding:0 10px;
        white-space:nowrap;
    }
    .preview-modal-card { width:min(1080px, 100%); }
    .document-preview-shell { display:grid; gap:16px; }
    .document-preview-frame { width:100%; min-height:68vh; border:1px solid var(--line); border-radius:16px; background:#f6faf8; }
    .document-preview-empty { display:grid; place-items:center; min-height:340px; padding:32px; border:1px dashed #cbd5d1; border-radius:16px; background:#f8fbfa; text-align:center; }
    .document-preview-empty p { margin:0 0 14px; color:var(--muted); }
    .document-preview-tools { display:flex; justify-content:flex-end; gap:10px; flex-wrap:wrap; }
    .document-preview-tools .btn { width:auto; min-width:0; }
    .header-actions { display:flex; justify-content:flex-end; gap:10px; align-items:center; }
    .trash-header-btn { display:inline-flex; align-items:center; justify-content:center; width:46px; height:46px; min-width:46px; padding:0; border-radius:10px; }
    .trash-header-btn svg { width:18px; height:18px; }
</style>

<div class="card">
    <div class="page-actions">
        <div>
            <h3 style="margin:0;">Building Permit Records</h3>
            <div class="meta">A simplified permit repository view focused on encoded records.</div>
        </div>
        <div class="header-actions">
            @if(auth()->user()->canDeleteRecords())
                <button class="btn secondary trash-header-btn" type="button" data-open-modal="permit-trash-modal" title="Deleted Building Permits" aria-label="Deleted Building Permits"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M8 6V4h8v2"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/></svg></button>
            @endif
            @unless($readOnly)
                <button class="btn" style="width:auto;" type="button" data-open-modal="permit-modal">Add Building Permit</button>
            @endunless
        </div>
        <form class="card-search" method="GET" action="{{ route('building-permits.index') }}">
            @foreach(request()->except(['search', 'page', 'edit', 'show']) as $key => $value)
                <input type="hidden" name="{{ $key }}" value="{{ $value }}">
            @endforeach
            <input name="search" value="{{ request('search') }}" placeholder="Search permit ID, owner, type, category, status">
            <div class="card-search-actions"><button class="btn" type="submit">Search</button>
            </div>@if(request('search'))<a class="btn secondary" href="{{ route('building-permits.index', request()->except(['search', 'page', 'edit', 'show'])) }}">Reset</a>@endif
        </form>
    </div>

    <div class="table-wrap">
        <table>
            <thead><tr><th>Permit ID</th><th>Owner</th><th>Building Type</th><th>Building Category</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
            @forelse($permits as $permit)
                <tr>
                    <td>{{ $permit->permit_id }}</td>
                    <td>{{ $permit->owner_full_name }}</td>
                    <td>{{ $permit->buildingType?->name }}</td>
                    <td>{{ $permit->buildingCategory?->name }}</td>
                    <td><span class="badge {{ $permit->status }}">{{ $permit->status }}</span></td>
                    <td>
                        <div class="action-icons">
                            <a class="action-icon view" href="{{ route('building-permits.index', ['show' => $permit->id]) }}" title="View Details" aria-label="View Details"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg></a>
                            @unless($readOnly)
                                <a class="action-icon edit" href="{{ route('building-permits.index', ['edit' => $permit->id]) }}" title="Edit Permit" aria-label="Edit Permit"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="m16.5 3.5 4 4L7 21l-4 1 1-4 12.5-14.5Z"/></svg></a>
                                @if(auth()->user()->canDeleteRecords())
                                <form method="POST" action="{{ route('building-permits.destroy', $permit) }}" data-confirm-delete data-confirm-message="Delete this building permit?" style="display:inline-flex; margin:0;">
                                    @csrf
                                    @method('DELETE')
                                    <button class="action-icon delete" type="submit" title="Delete Permit" aria-label="Delete Permit"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M8 6V4h8v2"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/></svg></button>
                                </form>
                                @endif
                            @endunless
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="muted">No permit records found.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    {{ $permits->links() }}
</div>

@if(auth()->user()->canDeleteRecords())
<div class="modal-backdrop" id="permit-trash-modal" aria-hidden="true">
    <div class="modal-card trash-modal-card">
        <div class="modal-head">
            <div>
                <h3 style="margin:0;">Deleted Building Permits</h3>
                <div class="muted">Restore deleted permits or permanently remove records.</div>
            </div>
            <button class="icon-btn" type="button" data-close-modal="permit-trash-modal">Close</button>
        </div>
        <div class="modal-body">
            <div class="table-wrap">
                <table>
                    <thead><tr><th>Permit ID</th><th>Owner</th><th>Status</th><th>Deleted</th><th>Actions</th></tr></thead>
                    <tbody>
                    @forelse($deletedPermits as $permit)
                        <tr>
                            <td>{{ $permit->permit_id }}</td>
                            <td>{{ $permit->owner_full_name }}</td>
                            <td><span class="badge {{ $permit->status }}">{{ $permit->status }}</span></td>
                            <td>{{ $permit->deleted_at?->format('M d, Y h:i A') }}</td>
                            <td>
                                <div class="trash-actions">
                                    <form method="POST" action="{{ route('building-permits.restore', $permit->id) }}" style="display:inline-flex; margin:0;">
                                        @csrf
                                        @method('PATCH')
                                        <button class="btn secondary trash-icon-btn" type="submit" title="Restore" aria-label="Restore"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12a9 9 0 1 0 3-6.7"/><path d="M3 3v6h6"/></svg></button>
                                    </form>
                                    <form method="POST" action="{{ route('building-permits.force-delete', $permit->id) }}" data-confirm-delete data-confirm-message="Permanently delete this building permit? This cannot be undone." style="display:inline-flex; margin:0;">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn danger trash-icon-btn" type="submit" title="Delete Forever" aria-label="Delete Forever"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M8 6V4h8v2"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/></svg></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="muted">No deleted building permits found.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endif
@if($selectedPermit)
<div class="modal-backdrop open" id="permit-view-modal" aria-hidden="true">
    <div class="modal-card">
        <div class="modal-head">
            <div>
                <h3 style="margin:0;">Permit Details</h3>
                <div class="muted">Review the full details and status history of the selected permit.</div>
            </div>
            <button class="icon-btn" type="button" data-close-modal="permit-view-modal">Close</button>
        </div>
        <div class="modal-body">
            <div class="grid cols-3">
                <div><strong>Permit ID</strong><div>{{ $selectedPermit->permit_id }}</div></div>
                <div><strong>Owner</strong><div>{{ $selectedPermit->owner_full_name }}</div></div>
                <div><strong>Status</strong><div><span class="badge {{ $selectedPermit->status }}">{{ $selectedPermit->status }}</span></div></div>
                <div><strong>Building Type</strong><div>{{ $selectedPermit->buildingType?->name }}</div></div>
                <div><strong>Building Category</strong><div>{{ $selectedPermit->buildingCategory?->name }}</div></div>
                <div><strong>Barangay</strong><div>{{ $selectedPermit->barangay }}</div></div>
                <div><strong>City / Municipality</strong><div>{{ $selectedPermit->city_municipality }}</div></div>
                <div><strong>Province</strong><div>{{ $selectedPermit->province }}</div></div>
            </div>
            <div style="margin-top:16px;">
                <strong>Documents</strong>
                @if($selectedPermit->documents->isNotEmpty())
                    <div class="document-grid">
                        @foreach($selectedPermit->documents as $document)
                            @php
                                $mime = $document->mime_type ?? '';
                                $canPreview = \Illuminate\Support\Str::startsWith($mime, ['image/', 'text/']) || in_array($mime, ['application/pdf'], true);
                            @endphp
                            <div class="document-card">
                                <p class="document-card-name">{{ $document->original_name }}</p>
                                <p class="document-card-meta">{{ strtoupper(pathinfo($document->original_name, PATHINFO_EXTENSION) ?: 'FILE') }} · {{ number_format(($document->size ?? 0) / 1024, 1) }} KB</p>
                                <div class="document-card-actions">
                                    <button
                                        class="btn secondary"
                                        type="button"
                                        data-open-document-preview
                                        data-preview-url="{{ route('building-permits.documents.preview', [$selectedPermit, $document]) }}"
                                        data-download-url="{{ route('building-permits.documents.download', [$selectedPermit, $document]) }}"
                                        data-document-name="{{ $document->original_name }}"
                                        data-previewable="{{ $canPreview ? 'true' : 'false' }}"
                                    >View</button>
                                    <a class="btn secondary document-action-btn" href="{{ route('building-permits.documents.download', [$selectedPermit, $document]) }}">Download</a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div>No documents uploaded.</div>
                @endif
            </div>
            <div style="margin-top:16px;"><strong>Remarks</strong><div>{{ $selectedPermit->remarks ?: 'No remarks provided.' }}</div></div>
            <h4>Status History</h4>
            <div class="table-wrap"><table><thead><tr><th>Date</th><th>From</th><th>To</th><th>By</th><th>Remarks</th></tr></thead><tbody>@foreach($selectedPermit->statusLogs as $log)<tr><td>{{ $log->created_at?->format('M d, Y h:i A') }}</td><td>{{ $log->old_status ?: '—' }}</td><td>{{ $log->new_status }}</td><td>{{ $log->actor?->name ?: 'System' }}</td><td>{{ $log->remarks ?: '—' }}</td></tr>@endforeach</tbody></table></div>
        </div>
    </div>
</div>
@endif

@unless($readOnly)
<div class="modal-backdrop {{ $editPermit || $errors->any() ? 'open' : '' }}" id="permit-modal" aria-hidden="true">
    <div class="modal-card">
        <div class="modal-head">
            <div>
                <h3 style="margin:0;">{{ $editPermit ? 'Edit Building Permit' : 'Add Building Permit' }}</h3>
                <div class="muted">Fill in the permit details and save to the repository.</div>
            </div>
            <button class="icon-btn" type="button" data-close-modal="permit-modal">Close</button>
        </div>
        <div class="modal-body">
            <form method="POST" action="{{ $editPermit ? route('building-permits.update', $editPermit) : route('building-permits.store') }}" class="grid" enctype="multipart/form-data">
                @csrf
                @if($editPermit) @method('PATCH') @endif
                <div><label>Permit ID</label><input value="{{ $editPermit?->permit_id ?? $nextPermitId }}" readonly></div>
                <div class="form-grid">
                    <div><label>Owner Last Name</label><input name="owner_last_name" value="{{ old('owner_last_name', $editPermit?->owner_last_name ?? '') }}" required></div>
                    <div><label>Owner First Name</label><input name="owner_first_name" value="{{ old('owner_first_name', $editPermit?->owner_first_name ?? '') }}" required></div>
                    <div><label>Owner Middle Name</label><input name="owner_middle_name" value="{{ old('owner_middle_name', $editPermit?->owner_middle_name ?? '') }}"></div>
                    <div><label>Suffix</label><input name="owner_suffix" value="{{ old('owner_suffix', $editPermit?->owner_suffix ?? '') }}"></div>
                    <div><label>Building Type</label><select name="building_type_id" required><option value="">Select</option>@foreach($buildingTypes as $type)<option value="{{ $type->id }}" @selected((string)old('building_type_id', $editPermit?->building_type_id ?? '') === (string)$type->id)>{{ $type->name }}</option>@endforeach</select></div>
                    <div><label>Building Category</label><select name="building_category_id" required><option value="">Select</option>@foreach($buildingCategories as $category)<option value="{{ $category->id }}" @selected((string)old('building_category_id', $editPermit?->building_category_id ?? '') === (string)$category->id)>{{ $category->name }}</option>@endforeach</select></div>
                    <div><label>Barangay</label><input name="barangay" value="{{ old('barangay', $editPermit?->barangay ?? '') }}" required></div>
                    <div><label>City / Municipality</label><input name="city_municipality" value="{{ old('city_municipality', $editPermit?->city_municipality ?? '') }}" required></div>
                    <div><label>Province</label><input name="province" value="{{ old('province', $editPermit?->province ?? '') }}" required></div>
                </div>
                <div>
                    <label>Documents</label>
                    <div class="upload-box">
                        <input id="permit-documents-input" type="file" name="documents[]" multiple>
                        <label for="permit-documents-input" class="upload-trigger">Choose Files</label>
                        <ul id="selected-documents-list" class="selected-files"></ul>
                    </div>
                    <small class="muted">You can upload up to 20 documents per permit. Max 10 MB each.</small>
                </div>
                @if($editPermit && $editPermit->documents->isNotEmpty())
                    <div>
                        <label>Existing Documents</label>
                        <div class="document-list">
                            @foreach($editPermit->documents as $document)
                                @php
                                    $mime = $document->mime_type ?? '';
                                    $canPreview = \Illuminate\Support\Str::startsWith($mime, ['image/', 'text/']) || in_array($mime, ['application/pdf'], true);
                                @endphp
                                <div class="document-list-item">
                                    <div class="document-list-info">
                                        <p class="document-list-name">{{ $document->original_name }}</p>
                                        <p class="document-list-meta">{{ strtoupper(pathinfo($document->original_name, PATHINFO_EXTENSION) ?: 'FILE') }} · {{ number_format(($document->size ?? 0) / 1024, 1) }} KB</p>
                                    </div>
                                    <div class="document-list-actions">
                                        <button
                                            class="btn secondary document-action-btn"
                                            type="button"
                                            data-open-document-preview
                                            data-preview-url="{{ route('building-permits.documents.preview', [$editPermit, $document]) }}"
                                            data-download-url="{{ route('building-permits.documents.download', [$editPermit, $document]) }}"
                                            data-document-name="{{ $document->original_name }}"
                                            data-previewable="{{ $canPreview ? 'true' : 'false' }}"
                                        >View</button>
                                        <a class="btn secondary document-action-btn" href="{{ route('building-permits.documents.download', [$editPermit, $document]) }}">Download</a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
                <div><label>Remarks</label><textarea name="remarks">{{ old('remarks', $editPermit?->remarks ?? '') }}</textarea></div>
                <div class="stack">
                    <button class="btn" style="width:auto;" type="submit">{{ $editPermit ? 'Update Permit' : 'Save Permit' }}</button>
                    <button class="btn secondary" style="width:auto;" type="button" data-close-modal="permit-modal">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endunless

<div class="modal-backdrop" id="document-preview-modal" aria-hidden="true">
    <div class="modal-card preview-modal-card">
        <div class="modal-head">
            <div>
                <h3 id="document-preview-title" style="margin:0;">Document Preview</h3>
                <div class="muted">Preview the attached file or download it if your browser cannot display it.</div>
            </div>
            <button class="icon-btn" type="button" data-close-modal="document-preview-modal">Close</button>
        </div>
        <div class="modal-body">
            <div class="document-preview-shell">
                <div id="document-preview-frame-wrap">
                    <iframe id="document-preview-frame" class="document-preview-frame" src="about:blank" title="Document Preview"></iframe>
                </div>
                <div id="document-preview-empty" class="document-preview-empty" hidden>
                    <div>
                        <p>This file type cannot be previewed directly in the browser.</p>
                        <a class="btn" id="document-preview-download-fallback" href="#">Download File</a>
                    </div>
                </div>
                <div class="document-preview-tools">
                    <a class="btn secondary" id="document-preview-open-tab" href="#" target="_blank" rel="noopener">Open in New Tab</a>
                    <a class="btn" id="document-preview-download" href="#">Download</a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('click', function (event) {
        const openTrigger = event.target.closest('[data-open-modal]');
        const closeTrigger = event.target.closest('[data-close-modal]');
        const documentTrigger = event.target.closest('[data-open-document-preview]');

        if (documentTrigger) {
            const modal = document.getElementById('document-preview-modal');
            const title = document.getElementById('document-preview-title');
            const frame = document.getElementById('document-preview-frame');
            const frameWrap = document.getElementById('document-preview-frame-wrap');
            const emptyState = document.getElementById('document-preview-empty');
            const downloadLink = document.getElementById('document-preview-download');
            const fallbackDownloadLink = document.getElementById('document-preview-download-fallback');
            const openTabLink = document.getElementById('document-preview-open-tab');
            const previewUrl = documentTrigger.dataset.previewUrl;
            const downloadUrl = documentTrigger.dataset.downloadUrl;
            const documentName = documentTrigger.dataset.documentName || 'Document Preview';
            const previewable = documentTrigger.dataset.previewable === 'true';

            if (modal && title && frame && frameWrap && emptyState && downloadLink && fallbackDownloadLink && openTabLink) {
                title.textContent = documentName;
                downloadLink.href = downloadUrl;
                fallbackDownloadLink.href = downloadUrl;
                openTabLink.href = previewUrl;

                if (previewable) {
                    frame.src = previewUrl;
                    frameWrap.hidden = false;
                    emptyState.hidden = true;
                } else {
                    frame.src = 'about:blank';
                    frameWrap.hidden = true;
                    emptyState.hidden = false;
                }

                modal.classList.add('open');
            }

            return;
        }

        if (openTrigger) {
            const modal = document.getElementById(openTrigger.dataset.openModal);
            if (modal) modal.classList.add('open');
        }

        if (closeTrigger) {
            const modal = document.getElementById(closeTrigger.dataset.closeModal);
            if (modal) {
                modal.classList.remove('open');

                if (modal.id === 'document-preview-modal') {
                    const frame = document.getElementById('document-preview-frame');
                    if (frame) frame.src = 'about:blank';
                    return;
                }

                if ((modal.id === 'permit-modal' || modal.id === 'permit-view-modal') && (window.location.search.includes('edit=') || window.location.search.includes('show='))) {
                    window.location = '{{ route('building-permits.index') }}';
                }
            }
        }

        if (event.target.classList.contains('modal-backdrop')) {
            event.target.classList.remove('open');

            if (event.target.id === 'document-preview-modal') {
                const frame = document.getElementById('document-preview-frame');
                if (frame) frame.src = 'about:blank';
                return;
            }

            if ((event.target.id === 'permit-modal' || event.target.id === 'permit-view-modal') && (window.location.search.includes('edit=') || window.location.search.includes('show='))) {
                window.location = '{{ route('building-permits.index') }}';
            }
        }
    });

    const documentsInput = document.getElementById('permit-documents-input');
    const selectedDocumentsList = document.getElementById('selected-documents-list');

    if (documentsInput && selectedDocumentsList) {
        const selectedFiles = new DataTransfer();
        const compactFileName = (fileName) => {
            if (fileName.length <= 28) {
                return fileName;
            }

            const extensionIndex = fileName.lastIndexOf('.');
            const extension = extensionIndex > 0 ? fileName.slice(extensionIndex) : '';
            const baseName = extensionIndex > 0 ? fileName.slice(0, extensionIndex) : fileName;

            return `${baseName.slice(0, 20)}...${extension}`;
        };

        const syncSelectedDocuments = () => {
            selectedDocumentsList.innerHTML = '';

            Array.from(selectedFiles.files).forEach((file, index) => {
                const item = document.createElement('li');
                item.className = 'selected-file-item';

                const name = document.createElement('span');
                name.className = 'selected-file-name';
                name.textContent = compactFileName(file.name);
                name.title = file.name;

                const removeButton = document.createElement('button');
                removeButton.type = 'button';
                removeButton.className = 'selected-file-remove';
                removeButton.setAttribute('aria-label', `Remove ${file.name}`);
                removeButton.textContent = 'x';
                removeButton.addEventListener('click', () => {
                    const updatedFiles = new DataTransfer();

                    Array.from(selectedFiles.files).forEach((selectedFile, selectedIndex) => {
                        if (selectedIndex !== index) {
                            updatedFiles.items.add(selectedFile);
                        }
                    });

                    selectedFiles.items.clear();
                    Array.from(updatedFiles.files).forEach((updatedFile) => {
                        selectedFiles.items.add(updatedFile);
                    });

                    documentsInput.files = selectedFiles.files;
                    syncSelectedDocuments();
                });

                item.appendChild(name);
                item.appendChild(removeButton);
                selectedDocumentsList.appendChild(item);
            });
        };

        documentsInput.addEventListener('change', () => {
            Array.from(documentsInput.files).forEach((file) => {
                if (selectedFiles.files.length >= 20) {
                    return;
                }

                const alreadySelected = Array.from(selectedFiles.files).some((selectedFile) => (
                    selectedFile.name === file.name
                    && selectedFile.size === file.size
                    && selectedFile.lastModified === file.lastModified
                ));

                if (alreadySelected) {
                    return;
                }

                selectedFiles.items.add(file);
            });

            documentsInput.files = selectedFiles.files;
            syncSelectedDocuments();
        });
    }
</script>
@endsection







