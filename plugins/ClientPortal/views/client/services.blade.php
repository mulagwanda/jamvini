@extends('themes.default::layouts.frontend')

@section('title', 'My Services')

@section('content')
<section class="page-hero">
    <div class="container">
        <div class="breadcrumb"><a href="/">Home</a> / <a href="/client/dashboard">Client Area</a> / Services</div>
        <h1>My Services</h1>
        <p>Manage your hosting, domains, and add-ons.</p>
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
            <div class="stats-grid" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:1rem;margin-bottom:1.5rem;">
                <div class="stat-card" style="background:#fff;border:1px solid var(--gray-200);border-radius:12px;padding:1.25rem;"><div style="color:var(--gray-600);font-size:.85rem;">Active</div><div style="font-size:1.6rem;font-weight:700;color:#16a34a;">{{ $services->where('status','active')->count() }}</div></div>
                <div class="stat-card" style="background:#fff;border:1px solid var(--gray-200);border-radius:12px;padding:1.25rem;"><div style="color:var(--gray-600);font-size:.85rem;">Total</div><div style="font-size:1.6rem;font-weight:700;">{{ $services->total() }}</div></div>
            </div>

            <div style="background:#fff;border:1px solid var(--gray-200);border-radius:18px;padding:1.5rem;">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem;">
                    <h3 style="margin:0;">🖥️ My Services</h3>
                    <a href="{{ route('order.services.catalog') }}" class="btn btn-primary btn-sm">+ New Service</a>
                </div>

                @if($services->count() > 0)
                <div style="overflow-x:auto;">
                    <table style="width:100%;border-collapse:collapse;">
                        <thead><tr style="background:var(--gray-50);"><th style="padding:12px;text-align:left;">Service</th><th>Domain</th><th>Next Due</th><th>Amount</th><th>Status</th><th></th></tr></thead>
                        <tbody>
                            @foreach($services as $cs)
                            <tr style="border-bottom:1px solid var(--gray-100);">
                                <td style="padding:12px;">
                                    <strong>{{ $cs->service->name ?? 'Service #'.$cs->id }}</strong>
                                    <div style="font-size:.85rem;color:var(--gray-600);">{{ $cs->service->group->name ?? '' }}</div>
                                </td>
                                <td style="padding:12px;">{{ $cs->domain ?? '—' }}</td>
                                <td style="padding:12px;">{{ $cs->next_due_date ? $cs->next_due_date->format('M d, Y') : '—' }}</td>
                                <td style="padding:12px;font-weight:600;">{{ jv_format_money($cs->price) }}/{{ $cs->billing_cycle }}</td>
                                <td style="padding:12px;"><span class="pill pill-{{ $cs->status === 'active' ? 'ok' : ($cs->status === 'suspended' ? 'bad' : 'warn') }}">{{ ucfirst($cs->status) }}</span></td>
                                <td style="padding:12px;display:flex;gap:6px;justify-content:flex-end;flex-wrap:wrap;">
                                    <a href="{{ route('client.services.show', $cs) }}" class="btn btn-outline btn-sm">Manage</a>
                                    @php
                                        $cpanelServer = $cs->server ?: ($cs->service?->servers?->first(fn ($srv) => (bool) $srv->pivot?->is_default) ?: $cs->service?->servers?->first());
                                    @endphp
                                    @if($cs->status === 'active' && $cpanelServer?->type === 'cpanel')
                                        <form action="{{ route('client.services.cpanel-login', $cs) }}" method="POST" target="_blank" style="margin:0;">
                                            @csrf
                                            <button class="btn btn-primary btn-sm">cPanel</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div style="margin-top:1rem;">{{ $services->links() }}</div>
                @else
                <div style="text-align:center;padding:60px;"><div style="font-size:3rem;">🖥️</div><h3>No services yet</h3><a href="{{ route('order.services.catalog') }}" class="btn btn-primary">Browse Services</a></div>
                @endif
            </div>
        </div>
    </div>
</main>
@endsection
