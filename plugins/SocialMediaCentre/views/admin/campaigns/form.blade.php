@extends('themes.default::layouts.admin')

@section('title', $campaign->exists ? 'Edit Campaign' : 'New Campaign')
@section('breadcrumbs')<a href="{{ route('admin.social.campaigns.index') }}">Campaigns</a> <span class="separator">/</span> <span class="current">{{ $campaign->exists ? 'Edit' : 'New' }}</span>@endsection

@section('content')
<div class="page-header"><h1 class="page-title">{{ $campaign->exists ? 'Edit Campaign' : 'New Campaign' }}</h1></div>
<form action="{{ $campaign->exists ? route('admin.social.campaigns.update', $campaign) : route('admin.social.campaigns.store') }}" method="POST">
    @csrf
    @if($campaign->exists) @method('PUT') @endif
    <div class="dash-card">
        <div class="form-grid">
            <div class="form-group"><label class="form-label">Name</label><input name="name" class="form-input" value="{{ old('name', $campaign->name) }}" required></div>
            <div class="form-group"><label class="form-label">Goal</label><input name="goal" class="form-input" value="{{ old('goal', $campaign->goal) }}" placeholder="Sell domains, promote VPS, launch new website package"></div>
            <div class="form-group"><label class="form-label">Start Date</label><input type="date" name="starts_at" class="form-input" value="{{ old('starts_at', $campaign->starts_at?->format('Y-m-d')) }}"></div>
            <div class="form-group"><label class="form-label">End Date</label><input type="date" name="ends_at" class="form-input" value="{{ old('ends_at', $campaign->ends_at?->format('Y-m-d')) }}"></div>
            <div class="form-group"><label class="form-label">Status</label><select name="status" class="form-select">@foreach(['active','paused','completed'] as $status)<option value="{{ $status }}" {{ old('status', $campaign->status) === $status ? 'selected' : '' }}>{{ ucfirst($status) }}</option>@endforeach</select></div>
        </div>
        <div class="form-group"><label class="form-label">Notes</label><textarea name="notes" class="form-textarea" rows="5">{{ old('notes', $campaign->notes) }}</textarea></div>
    </div>
    <div style="display:flex;justify-content:flex-end;gap:10px;margin-top:1rem;">
        <a href="{{ route('admin.social.campaigns.index') }}" class="btn btn-outline-danger">Cancel</a>
        <button class="btn btn-primary btn-lg">Save Campaign</button>
    </div>
</form>
@endsection
