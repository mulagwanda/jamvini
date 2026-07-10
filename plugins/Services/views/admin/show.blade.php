@extends('themes.default::layouts.admin')

@section('title', $service->name . ' — Service Details')
@section('breadcrumbs')<a href="{{ route('admin.services.index') }}">Services</a> <span class="separator">/</span> <span class="current">{{ $service->name }}</span>@endsection

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">{{ $service->name }}</h1>
        <p style="color: var(--jv-gray-500);">
            <span class="pill pill-info" style="margin-right: 8px;">{{ ucfirst($service->type) }}</span>
            <span class="pill pill-{{ $service->is_active ? 'ok' : 'mute' }}">{{ $service->is_active ? 'Visible' : 'Hidden' }}</span>
        </p>
    </div>
    <a href="{{ route('admin.services.edit', $service) }}" class="btn btn-primary">✏️ Edit Service</a>
</div>

<div class="grid-2" style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
    {{-- Details --}}
    <div class="dash-card">
        <div class="dash-card-head"><h3>📋 Details</h3></div>
        <table style="width: 100%;">
            <tr><td style="font-weight: 600; padding: 8px 0; width: 130px;">Name</td><td>{{ $service->name }}</td></tr>
            <tr><td style="font-weight: 600; padding: 8px 0;">Group</td><td>{{ $service->group?->icon }} {{ $service->group?->name ?? '—' }}</td></tr>
            <tr><td style="font-weight: 600; padding: 8px 0;">Type</td><td><span class="pill pill-info">{{ ucfirst($service->type) }}</span></td></tr>
            <tr><td style="font-weight: 600; padding: 8px 0;">Status</td><td><span class="pill pill-{{ $service->is_active ? 'ok' : 'mute' }}">{{ $service->is_active ? 'Active' : 'Hidden' }}</span></td></tr>
            @if($service->description)<tr><td style="font-weight: 600; padding: 8px 0;">Description</td><td>{{ $service->description }}</td></tr>@endif
            @if($service->upgradable)<tr><td style="font-weight: 600; padding: 8px 0;">Upgradable</td><td>✅ Yes</td></tr>@endif
        </table>
    </div>

    {{-- Pricing --}}
    <div class="dash-card">
        <div class="dash-card-head"><h3>💰 Pricing</h3></div>
        @if($service->is_free)
            <div class="alert alert-info" style="margin: 0;"><span class="alert-icon">🎉</span> This is a free service</div>
        @elseif(in_array($service->type, ['domain', 'domains']))
            <p style="color: var(--jv-gray-500);">Pricing configured per TLD. See TLD configuration below.</p>
        @else
            <table style="width: 100%;">
                <tr><td style="font-weight: 600; padding: 8px 0;">Billing Type</td><td>{{ ucfirst($service->billing_type ?? 'recurring') }}</td></tr>
                @if($service->pricing)
                    @foreach($service->pricing as $cycle => $price)
                    <tr><td style="font-weight: 600; padding: 8px 0;">{{ ucfirst(str_replace('_', ' ', $cycle)) }}</td><td><strong>{{ jv_format_money($price) }}</strong></td></tr>
                    @endforeach
                @endif
                @if($service->setup_fee > 0)<tr><td style="font-weight: 600; padding: 8px 0;">Setup Fee</td><td>{{ jv_format_money($service->setup_fee) }}</td></tr>@endif
                @if($service->free_domain_cycles)<tr><td style="font-weight: 600; padding: 8px 0;">Free Domain</td><td>{{ implode(', ', $service->free_domain_cycles) }}</td></tr>@endif
            </table>
        @endif
    </div>
</div>

{{-- Features --}}
@if(!empty($service->features))
<div class="dash-card" style="margin-bottom: 1.5rem;">
    <div class="dash-card-head"><h3>✅ Features</h3></div>
    <ul style="list-style: none; padding: 0; columns: 2; margin: 0;">
        @foreach($service->features as $feature)
            <li style="padding: 6px 0;">✓ {{ $feature }}</li>
        @endforeach
    </ul>
</div>
@endif

{{-- Configurable Options --}}
@if($service->options->count() > 0)
<div class="dash-card" style="margin-bottom: 1.5rem; padding: 0; overflow: hidden;">
    <div class="dash-card-head" style="padding: 1.25rem 1.25rem 0;"><h3>⚙️ Configurable Options</h3></div>
    <table class="table" style="margin: 0;">
        <thead><tr><th>Option</th><th>Type</th><th>Monthly Price</th><th>Status</th></tr></thead>
        <tbody>
            @foreach($service->options as $option)
            <tr>
                <td><strong>{{ $option->name }}</strong></td>
                <td>{{ ucfirst($option->type) }}</td>
                <td>{{ jv_format_money($option->prices['monthly'] ?? 0) }}</td>
                <td><span class="pill pill-{{ $option->is_active ? 'ok' : 'mute' }}">{{ $option->is_active ? 'Active' : 'Hidden' }}</span></td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif

{{-- Domain TLDs --}}
@if(in_array($service->type, ['domain', 'domains']) && $service->tlds && $service->tlds->count() > 0)
<div class="dash-card" style="margin-bottom: 1.5rem; padding: 0; overflow: hidden;">
    <div class="dash-card-head" style="padding: 1.25rem 1.25rem 0;"><h3>🌐 TLD Configuration</h3></div>
    @foreach($service->tlds as $tld)
    <div style="padding: 16px 20px; border-bottom: 1px solid var(--jv-gray-100);">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <strong style="font-size: 1.1rem;">{{ $tld->tld }}</strong>
            @if($tld->auto_register)<span class="pill pill-ok">Auto Register</span>@endif
        </div>
        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px; margin-top: 12px;">
            <div><small style="color: var(--jv-gray-500);">Register</small><br><strong>{{ jv_format_money($tld->pricing->where('years', 1)->first()?->register_price ?? 0) }}</strong></div>
            <div><small style="color: var(--jv-gray-500);">Renewal</small><br><strong>{{ jv_format_money($tld->pricing->where('years', 1)->first()?->renewal_price ?? 0) }}</strong></div>
            <div><small style="color: var(--jv-gray-500);">Transfer</small><br><strong>{{ jv_format_money($tld->pricing->where('years', 1)->first()?->transfer_price ?? 0) }}</strong></div>
        </div>
        <div style="display: flex; gap: 12px; margin-top: 8px; font-size: 0.82rem; color: var(--jv-gray-500);">
            <span>{{ $tld->dns_management ? '✅ DNS' : '—' }}</span>
            <span>{{ $tld->email_forwarding ? '✅ Email' : '—' }}</span>
            <span>{{ $tld->id_protection ? '✅ ID Protect' : '—' }}</span>
            <span>{{ $tld->epp_code ? '✅ EPP' : '—' }}</span>
        </div>
    </div>
    @endforeach
</div>
@endif

{{-- Servers --}}
@if($service->servers->count() > 0)
<div class="dash-card" style="margin-bottom: 1.5rem;">
    <div class="dash-card-head"><h3>🖥️ Provisioning Servers</h3></div>
    @foreach($service->servers as $server)
    <div style="padding: 8px 0; border-bottom: 1px solid var(--jv-gray-100);">
        <strong>{{ $server->name }}</strong> ({{ ucfirst($server->type) }}) — <code>{{ $server->hostname }}</code>
    </div>
    @endforeach
</div>
@endif
@endsection
