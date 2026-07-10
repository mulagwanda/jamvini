@extends('themes.default::layouts.admin')

@section('title', 'Offline Payments')
@section('breadcrumbs')<span class="current">Offline Payments</span>@endsection

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Offline Payments</h1>
        <p class="page-subtitle">Configure cash and bank deposit instructions for customers.</p>
    </div>
</div>

<form action="{{ route('admin.offline-payments.settings.save') }}" method="POST">
    @csrf
    <div class="grid-2">
        <div class="dash-card">
            <div class="dash-card-head"><h3>{{ jv_icon('landmark', '', 20) }} Bank Deposit</h3></div>
            <label class="toggle-switch" style="margin-bottom:16px;">
                <input type="checkbox" name="offline_bank_enabled" value="1" {{ $settings['bank_enabled'] === '1' ? 'checked' : '' }}>
                <span class="toggle-slider"></span><span>Enable bank deposit</span>
            </label>
            <div class="form-group">
                <label class="form-label">Customer Instructions</label>
                <textarea class="form-textarea" name="offline_bank_instructions" rows="8">{{ old('offline_bank_instructions', $settings['bank_instructions']) }}</textarea>
            </div>
        </div>
        <div class="dash-card">
            <div class="dash-card-head"><h3>{{ jv_icon('banknote', '', 20) }} Cash</h3></div>
            <label class="toggle-switch" style="margin-bottom:16px;">
                <input type="checkbox" name="offline_cash_enabled" value="1" {{ $settings['cash_enabled'] === '1' ? 'checked' : '' }}>
                <span class="toggle-slider"></span><span>Enable cash payments</span>
            </label>
            <div class="form-group">
                <label class="form-label">Customer Instructions</label>
                <textarea class="form-textarea" name="offline_cash_instructions" rows="8">{{ old('offline_cash_instructions', $settings['cash_instructions']) }}</textarea>
            </div>
        </div>
    </div>
    <div style="display:flex;justify-content:flex-end;margin-top:16px;">
        <button class="btn btn-primary btn-lg">{{ jv_icon('check-circle', '', 16) }} Save Offline Payment Settings</button>
    </div>
</form>
@endsection
