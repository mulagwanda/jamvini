@extends('themes.default::layouts.frontend')

@section('title', ($service->service->name ?? 'Service') . ' — Details')

@section('content')
<section class="page-hero">
    <div class="container">
        <div class="breadcrumb"><a href="/">Home</a> / <a href="/client/dashboard">Client Area</a> / <a href="/client/services">Services</a> / {{ $service->service->name ?? 'Details' }}</div>
        <h1>{{ $service->service->name ?? 'Service #'.$service->id }}</h1>
        <p>Manage your service settings and details.</p>
    </div>
</section>

<main class="container" style="padding: 2rem 0;">
    <div class="client-wrap" style="display: grid; grid-template-columns: 260px 1fr; gap: 2rem;">
        <aside class="client-side" style="background:#fff;border:1px solid var(--gray-200);border-radius:18px;padding:1.25rem;height:fit-content;position:sticky;top:90px;">
            <div class="who" style="display:flex;align-items:center;gap:.75rem;padding-bottom:1rem;border-bottom:1px solid var(--gray-200);margin-bottom:1rem;">
                <div class="avatar" style="width:44px;height:44px;border-radius:50%;background:var(--primary);color:#fff;display:grid;place-items:center;font-weight:700;">
                    {{ strtoupper(substr($client->first_name, 0, 1) . substr($client->last_name, 0, 1)) }}
                </div>
                <div><div style="font-weight:600;">{{ $client->full_name }}</div><small>{{ $client->email }}</small></div>
            </div>
            <nav class="client-nav" style="display:flex;flex-direction:column;gap:.25rem;">
                <a href="/client/dashboard">📊 Dashboard</a>
                <a href="/client/services" class="active" style="background:var(--primary);color:#fff;">🖥️ My Services</a>
                <a href="/client/domains">🌐 Domains</a>
                <a href="/client/orders">🛒 Orders</a>
                <a href="/client/invoices">🧾 Invoices</a>
                <a href="/client/account">⚙️ Account</a>
                <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">🚪 Logout</a>
                <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display:none;">@csrf</form>
            </nav>
        </aside>

        <div>
            {{-- Header --}}
            <div style="background:#fff;border-radius:18px;box-shadow:0 2px 8px rgba(0,0,0,.04);padding:1.5rem;margin-bottom:1.5rem;">
                <div style="display:flex;justify-content:space-between;flex-wrap:wrap;gap:1rem;">
                    <div>
                        <h2 style="display:flex;align-items:center;gap:.75rem;margin-bottom:.5rem;">
                            <div style="width:48px;height:48px;border-radius:12px;background:linear-gradient(135deg,var(--primary),var(--primary-light));color:#fff;display:grid;place-items:center;font-size:1.5rem;">🖥️</div>
                            {{ $service->service->name ?? 'Service' }}
                        </h2>
                        <div style="display:flex;gap:1.5rem;flex-wrap:wrap;font-size:.9rem;">
                            @if($service->domain)<div><span style="color:var(--gray-600);">Domain:</span> <strong>{{ $service->domain }}</strong></div>@endif
                            <div><span style="color:var(--gray-600);">Status:</span> <span class="pill pill-{{ $service->status === 'active' ? 'ok' : 'bad' }}">{{ ucfirst($service->status) }}</span></div>
                            <div><span style="color:var(--gray-600);">Next Due:</span> <strong>{{ $service->next_due_date ? $service->next_due_date->format('M d, Y') : '—' }}</strong></div>
                        </div>
                    </div>
                    <div style="display:flex;gap:.5rem;flex-wrap:wrap;">
                        @php
                            $cpanelServer = $service->server ?: ($service->service?->servers?->first(fn ($srv) => (bool) $srv->pivot?->is_default) ?: $service->service?->servers?->first());
                        @endphp
                        @if($service->status === 'active' && $cpanelServer?->type === 'cpanel')
                            <form action="{{ route('client.services.cpanel-login', $service) }}" method="POST" target="_blank" style="margin:0;">
                                @csrf
                                <button class="btn btn-success btn-sm">Open cPanel</button>
                            </form>
                        @endif
                        <a href="/hosting" class="btn btn-primary btn-sm">🔄 Upgrade</a>
                        <a href="/checkout" class="btn btn-success btn-sm">🔄 Renew</a>
                    </div>
                </div>
            </div>

            {{-- Details --}}
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.5rem;">
                <div style="background:#fff;border-radius:18px;box-shadow:0 2px 8px rgba(0,0,0,.04);padding:1.5rem;">
                    <h4 style="margin-bottom:1rem;">📋 Details</h4>
                    <table style="width:100%;">
                        <tr style="border-bottom:1px solid var(--gray-100);"><td style="padding:8px 0;color:var(--gray-600);">Service</td><td style="font-weight:600;">{{ $service->service->name ?? '—' }}</td></tr>
                        <tr style="border-bottom:1px solid var(--gray-100);"><td style="padding:8px 0;color:var(--gray-600);">Group</td><td>{{ $service->service->group->name ?? '—' }}</td></tr>
                        <tr style="border-bottom:1px solid var(--gray-100);"><td style="padding:8px 0;color:var(--gray-600);">Domain</td><td>{{ $service->domain ?? '—' }}</td></tr>
                        <tr style="border-bottom:1px solid var(--gray-100);"><td style="padding:8px 0;color:var(--gray-600);">Registered</td><td>{{ $service->registered_date ? $service->registered_date->format('M d, Y') : $service->created_at->format('M d, Y') }}</td></tr>
                        <tr style="border-bottom:1px solid var(--gray-100);"><td style="padding:8px 0;color:var(--gray-600);">Next Due</td><td style="font-weight:600;">{{ $service->next_due_date ? $service->next_due_date->format('M d, Y') : '—' }}</td></tr>
                    </table>
                </div>

                <div style="background:#fff;border-radius:18px;box-shadow:0 2px 8px rgba(0,0,0,.04);padding:1.5rem;">
                    <h4 style="margin-bottom:1rem;">💰 Billing</h4>
                    <table style="width:100%;">
                        <tr style="border-bottom:1px solid var(--gray-100);"><td style="padding:8px 0;color:var(--gray-600);">Amount</td><td style="font-weight:600;">{{ jv_format_money($service->price) }}</td></tr>
                        <tr style="border-bottom:1px solid var(--gray-100);"><td style="padding:8px 0;color:var(--gray-600);">Cycle</td><td>{{ ucfirst($service->billing_cycle) }}</td></tr>
                        <tr><td style="padding:8px 0;color:var(--gray-600);">Status</td><td><span class="pill pill-{{ $service->status === 'active' ? 'ok' : 'bad' }}">{{ ucfirst($service->status) }}</span></td></tr>
                    </table>
                </div>
            </div>

            {{-- Features --}}
            @if($service->service && is_array($service->service->features) && count($service->service->features) > 0)
            <div style="background:#fff;border-radius:18px;box-shadow:0 2px 8px rgba(0,0,0,.04);padding:1.5rem;margin-top:1.5rem;">
                <h4 style="margin-bottom:1rem;">✅ Features</h4>
                <ul style="columns:2;list-style:none;padding:0;">
                    @foreach($service->service->features as $feature)
                        <li style="padding:4px 0;">✓ {{ $feature }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            @if($service->properties->count() > 0)
            <div style="background:#fff;border-radius:18px;box-shadow:0 2px 8px rgba(0,0,0,.04);padding:1.5rem;margin-top:1.5rem;">
                <h4 style="margin-bottom:1rem;">Service Properties</h4>
                <table style="width:100%;">
                    @foreach($service->properties as $property)
                        <tr style="border-bottom:1px solid var(--gray-100);">
                            <td style="padding:8px 0;color:var(--gray-600);">{{ $property->label ?: ucfirst(str_replace('_', ' ', $property->key)) }}</td>
                            <td style="padding:8px 0;font-weight:600;">{{ $property->is_sensitive ? 'Hidden' : ($property->value ?: '—') }}</td>
                        </tr>
                    @endforeach
                </table>
            </div>
            @endif

            {{-- Notes --}}
            @if($service->notes)
            <div style="background:var(--gray-50);padding:1.5rem;border-radius:12px;margin-top:1.5rem;">
                <h4>📝 Notes</h4>
                <p style="font-size:.9rem;color:var(--gray-600);margin-top:.5rem;">{{ $service->notes }}</p>
            </div>
            @endif
        </div>
    </div>
</main>
@endsection
