@extends('themes.default::layouts.admin')

@section('title', 'Selcom Settings')
@section('breadcrumbs')
    <a href="{{ route('admin.selcom.index') }}">Selcom Payments</a>
    <span class="separator">/</span>
    <span class="current">Settings</span>
@endsection

@section('content')
<div class="page-header">
    <h1 class="page-title">Selcom Configuration</h1>
    <p class="page-subtitle">Enter your Selcom API credentials to enable mobile money payments</p>
</div>

<form action="{{ route('admin.selcom.settings') }}" method="POST">
    @csrf
    <div class="card">
        <div class="card-body">
            <div style="display:flex;gap:24px;flex-wrap:wrap;margin-bottom:18px;">
                <label class="toggle-switch">
                    <input type="checkbox" name="selcom_enabled" value="1" {{ \App\Models\Setting::get('selcom_enabled', '1') === '1' ? 'checked' : '' }}>
                    <span class="toggle-slider"></span><span>Enable Selcom</span>
                </label>
                <label class="toggle-switch">
                    <input type="checkbox" name="selcom_test_mode" value="1" {{ \App\Models\Setting::get('selcom_test_mode', '1') === '1' ? 'checked' : '' }}>
                    <span class="toggle-slider"></span><span>Test mode</span>
                </label>
            </div>
            <div class="form-group">
                <label class="form-label" for="vendor_id">Vendor ID</label>
                <input type="text" id="vendor_id" name="vendor_id" class="form-input" 
                       value="{{ old('vendor_id', \App\Models\Setting::get('selcom_vendor_id')) }}" required>
                <div class="form-hint">Provided by Selcom when you register as a merchant</div>
            </div>
            <div class="form-group">
                <label class="form-label" for="api_key">API Key</label>
                <input type="text" id="api_key" name="api_key" class="form-input" 
                       value="{{ old('api_key', \App\Models\Setting::get('selcom_api_key')) }}" required>
            </div>
            <div class="form-group">
                <label class="form-label" for="api_secret">API Secret</label>
                <input type="password" id="api_secret" name="api_secret" class="form-input" 
                       placeholder="Leave blank to keep current secret">
                <div class="form-hint">Store this carefully. It is used to verify gateway requests.</div>
            </div>
        </div>
    </div>
    <div style="display: flex; gap: 12px; justify-content: flex-end;">
        <a href="{{ route('admin.selcom.index') }}" class="btn btn-outline-danger">Cancel</a>
        <button type="submit" class="btn btn-primary btn-lg">{{ jv_icon('check-circle', '', 16) }} Save Configuration</button>
    </div>
</form>
@endsection
