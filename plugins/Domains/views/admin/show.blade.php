@extends('themes.default::layouts.admin')

@section('title', $domain->domain_name . ' — Domain Details')

@section('breadcrumbs')
    <a href="{{ route('admin.domains.index') }}">Domains</a>
    <span class="separator">/</span>
    <span class="current">{{ $domain->domain_name }}</span>
@endsection

@section('content')
<div class="page-header">
    <div style="display: flex; align-items: center; justify-content: space-between;">
        <div>
            <h1 class="page-title">{{ $domain->domain_name }}</h1>
            <p style="color: var(--jv-gray-500);">
                <span class="badge badge-{{ $domain->status === 'active' ? 'success' : 'danger' }} badge-with-dot" style="margin-right: 8px;">
                    {{ ucfirst($domain->status) }}
                </span>
                {{ $domain->tld }} · {{ $domain->registrar ?? 'Manual' }}
            </p>
        </div>
        <a href="{{ route('admin.domains.edit', $domain) }}" class="btn btn-primary">✏️ Edit</a>
    </div>
</div>

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px;">
    <div class="card">
        <div class="card-header"><h3 class="card-title">🌐 Domain Info</h3></div>
        <div class="card-body">
            <table class="table" style="margin: 0;">
                <tr><td style="font-weight: 600; width: 140px;">Domain</td><td>{{ $domain->domain_name }}</td></tr>
                <tr><td style="font-weight: 600;">Client</td><td><a href="{{ route('admin.clients.show', $domain->client) }}">{{ $domain->client->full_name }}</a></td></tr>
                <tr><td style="font-weight: 600;">Registrar</td><td>{{ $domain->registrar ?? 'Manual' }}</td></tr>
                <tr><td style="font-weight: 600;">Status</td><td><span class="badge badge-{{ $domain->status === 'active' ? 'success' : 'danger' }}">{{ ucfirst($domain->status) }}</span></td></tr>
                <tr><td style="font-weight: 600;">Auto Renew</td><td>{{ $domain->auto_renew ? '✅ Yes' : '❌ No' }}</td></tr>
            </table>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header"><h3 class="card-title">📅 Dates & Fees</h3></div>
        <div class="card-body">
            <table class="table" style="margin: 0;">
                <tr><td style="font-weight: 600; width: 140px;">Registered</td><td>{{ $domain->registration_date?->format('M d, Y') ?? '—' }}</td></tr>
                <tr><td style="font-weight: 600;">Expires</td><td><strong class="@if($domain->is_expired) text-danger @endif">{{ $domain->expiry_date?->format('M d, Y') ?? '—' }}</strong></td></tr>
                <tr><td style="font-weight: 600;">Period</td><td>{{ $domain->registration_period }} year(s)</td></tr>
                <tr><td style="font-weight: 600;">Reg. Fee</td><td>{{ jv_format_money($domain->registration_fee) }}</td></tr>
                <tr><td style="font-weight: 600;">Renewal Fee</td><td>{{ jv_format_money($domain->renewal_fee) }}</td></tr>
            </table>
        </div>
    </div>
</div>

@if($domain->notes)
<div class="card">
    <div class="card-header"><h3 class="card-title">📝 Notes</h3></div>
    <div class="card-body">{{ nl2br(e($domain->notes)) }}</div>
</div>
@endif
@endsection