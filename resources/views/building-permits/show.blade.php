@extends('layouts.app')

@section('content')
<div class="card">
    <a class="btn secondary" style="width:auto; margin-bottom:16px;" href="{{ route('building-permits.index') }}">Back to Permit List</a>
    <div class="grid cols-3">
        <div><strong>Permit ID</strong><div>{{ $permit->permit_id }}</div></div>
        <div><strong>Owner</strong><div>{{ $permit->owner_full_name }}</div></div>
        <div><strong>Status</strong><div><span class="badge {{ $permit->status }}">{{ $permit->status }}</span></div></div>
        <div><strong>Building Type</strong><div>{{ $permit->buildingType?->name }}</div></div>
        <div><strong>Building Category</strong><div>{{ $permit->buildingCategory?->name }}</div></div>
        <div><strong>Barangay</strong><div>{{ $permit->barangay }}</div></div>
        <div><strong>City / Municipality</strong><div>{{ $permit->city_municipality }}</div></div>
        <div><strong>Province</strong><div>{{ $permit->province }}</div></div>
        <div><strong>Approved By</strong><div>{{ $permit->approver?->name ?: '—' }}</div></div>
    </div>
    <div style="margin-top:16px;">
        <strong>Documents</strong>
        @if($permit->documents->isNotEmpty())
            <div class="stack" style="margin-top:8px;">
                @foreach($permit->documents as $document)
                    <a href="{{ route('building-permits.documents.download', [$permit, $document]) }}">{{ $document->original_name }}</a>
                @endforeach
            </div>
        @else
            <div>—</div>
        @endif
    </div>
    <div style="margin-top:16px;"><strong>Remarks</strong><div>{{ $permit->remarks ?: '—' }}</div></div>
</div>
@endsection
