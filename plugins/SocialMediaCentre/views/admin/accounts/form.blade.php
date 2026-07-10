@extends('themes.default::layouts.admin')

@section('title', $account->exists ? 'Edit Account' : 'Add Account')
@section('breadcrumbs')<a href="{{ route('admin.social.accounts.index') }}">Accounts</a> <span class="separator">/</span> <span class="current">{{ $account->exists ? 'Edit' : 'Add' }}</span>@endsection

@section('content')
<div class="page-header"><h1 class="page-title">{{ $account->exists ? 'Edit Account' : 'Add Account' }}</h1></div>
<form action="{{ $account->exists ? route('admin.social.accounts.update', $account) : route('admin.social.accounts.store') }}" method="POST">
    @csrf
    @if($account->exists) @method('PUT') @endif
    <div class="dash-card">
        <div class="form-grid">
            <div class="form-group"><label class="form-label">Platform</label><select name="platform" class="form-select" required>@foreach($platforms as $value => $label)<option value="{{ $value }}" {{ old('platform', $account->platform) === $value ? 'selected' : '' }}>{{ $label }}</option>@endforeach</select></div>
            <div class="form-group"><label class="form-label">Display Name</label><input name="name" class="form-input" value="{{ old('name', $account->name) }}" required></div>
            <div class="form-group"><label class="form-label">Handle</label><input name="handle" class="form-input" value="{{ old('handle', $account->handle) }}" placeholder="@smartwebhosting"></div>
            <div class="form-group"><label class="form-label">Status</label><select name="status" class="form-select">@foreach(['manual','connected','disabled'] as $status)<option value="{{ $status }}" {{ old('status', $account->status) === $status ? 'selected' : '' }}>{{ ucfirst($status) }}</option>@endforeach</select></div>
        </div>
    </div>
    <div style="display:flex;justify-content:flex-end;gap:10px;margin-top:1rem;">
        <a href="{{ route('admin.social.accounts.index') }}" class="btn btn-outline-danger">Cancel</a>
        <button class="btn btn-primary btn-lg">Save Account</button>
    </div>
</form>
@endsection
