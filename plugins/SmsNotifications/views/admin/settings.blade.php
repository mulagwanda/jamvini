@extends('themes.default::layouts.admin')

@section('title', 'SMS Settings')
@section('breadcrumbs')
    <a href="{{ route('admin.sms.index') }}">SMS Notifications</a>
    <span class="separator">/</span>
    <span class="current">Settings</span>
@endsection

@section('content')
<div class="page-header">
    <h1 class="page-title">SMS Configuration</h1>
    <p class="page-subtitle">Set up your SMS provider to send automated notifications</p>
</div>

<form action="{{ route('admin.sms.settings') }}" method="POST">
    @csrf
    <div class="card">
        <div class="card-body">
            <div class="form-group">
                <label class="form-label" for="provider">SMS Provider</label>
                <select id="provider" name="provider" class="form-select">
                    <option value="Africa's Talking" {{ \App\Models\Setting::get('sms_provider') === "Africa's Talking" ? 'selected' : '' }}>Africa's Talking</option>
                    <option value="Beem" {{ \App\Models\Setting::get('sms_provider') === 'Beem' ? 'selected' : '' }}>Beem (formerly Telerivet)</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label" for="api_key">API Key</label>
                <input type="text" id="api_key" name="api_key" class="form-input" 
                       value="{{ old('api_key', \App\Models\Setting::get('sms_api_key')) }}" required>
            </div>
            <div class="form-group">
                <label class="form-label" for="sender_id">Sender ID</label>
                <input type="text" id="sender_id" name="sender_id" class="form-input" 
                       value="{{ old('sender_id', \App\Models\Setting::get('sms_sender_id', 'JamVini')) }}" maxlength="11" required>
                <div class="form-hint">Max 11 characters — appears as the sender name</div>
            </div>
        </div>
    </div>
    <div style="display: flex; gap: 12px; justify-content: flex-end;">
        <a href="{{ route('admin.sms.index') }}" class="btn btn-outline-danger">Cancel</a>
        <button type="submit" class="btn btn-primary btn-lg">💾 Save Configuration</button>
    </div>
</form>
@endsection