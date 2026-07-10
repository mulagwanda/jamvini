@extends('themes.default::layouts.admin')

@section('title', 'Selcom Payments')
@section('breadcrumbs')<span class="current">Selcom Payments</span>@endsection

@section('content')
<div class="page-header">
    <div style="display: flex; justify-content: space-between;">
        <div>
            <h1 class="page-title">Selcom Payment Gateway</h1>
            <p class="page-subtitle">Accept M-Pesa, Tigo Pesa, and Airtel Money payments</p>
        </div>
        <a href="{{ route('admin.selcom.settings') }}" class="btn btn-primary">{{ jv_icon('settings', '', 16) }} Configure</a>
    </div>
</div>

<div class="card">
    <div class="card-header"><h3 class="card-title">{{ jv_icon('credit-card', '', 20) }} Configuration Status</h3></div>
    <div class="card-body">
        @if($config['is_configured'])
            <div class="alert alert-success">
                <span class="alert-icon">{{ jv_icon('check-circle', '', 20) }}</span>
                <span>Selcom is configured and ready to accept payments.</span>
            </div>
            <table class="table">
                <tr><td style="font-weight: 600;">Vendor ID</td><td>{{ $config['vendor_id'] }}</td></tr>
                <tr><td style="font-weight: 600;">API Key</td><td>{{ substr($config['api_key'], 0, 8) }}...</td></tr>
                <tr><td style="font-weight: 600;">Status</td><td><span class="badge badge-success">{{ $config['enabled'] === '1' ? 'Enabled' : 'Disabled' }}</span></td></tr>
                <tr><td style="font-weight: 600;">Mode</td><td>{{ $config['test_mode'] === '1' ? 'Test' : 'Live' }}</td></tr>
            </table>
        @else
            <div class="alert alert-warning">
                <span class="alert-icon">{{ jv_icon('triangle-alert', '', 20) }}</span>
                <span>Selcom is not configured. Please add your API credentials.</span>
            </div>
            <a href="{{ route('admin.selcom.settings') }}" class="btn btn-primary">Configure Now</a>
        @endif
    </div>
</div>

<div class="card">
    <div class="card-header"><h3 class="card-title">{{ jv_icon('layout-dashboard', '', 20) }} Payment Methods Available</h3></div>
    <div class="card-body">
        <div class="stats-grid">
            <div class="stat-card success">
                <div class="stat-card-header"><div class="stat-card-icon">{{ jv_icon('credit-card') }}</div></div>
                <div class="stat-card-value">M-Pesa</div>
                <div class="stat-card-label">Vodacom</div>
            </div>
            <div class="stat-card info">
                <div class="stat-card-header"><div class="stat-card-icon">{{ jv_icon('credit-card') }}</div></div>
                <div class="stat-card-value">Tigo Pesa</div>
                <div class="stat-card-label">Tigo</div>
            </div>
            <div class="stat-card warning">
                <div class="stat-card-header"><div class="stat-card-icon">{{ jv_icon('credit-card') }}</div></div>
                <div class="stat-card-value">Airtel Money</div>
                <div class="stat-card-label">Airtel</div>
            </div>
        </div>
    </div>
</div>
@endsection
