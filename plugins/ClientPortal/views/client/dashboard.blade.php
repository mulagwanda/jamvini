@extends('themes.default::layouts.frontend')

@section('title', 'Client Area — ' . $client->full_name)

@section('content')
<section class="page-hero">
    <div class="container">
        <div class="breadcrumb"><a href="/">Home</a> / Client Area</div>
        <h1>Welcome back, {{ $client->first_name }} 👋</h1>
        <p>Manage your services, domains and billing all in one place.</p>
    </div>
</section>

<main>
    <div class="container client-wrap">
        {{-- Sidebar --}}
        <aside class="client-side reveal">
            <div class="who">
                <div class="avatar">{{ strtoupper(substr($client->first_name, 0, 1) . substr($client->last_name, 0, 1)) }}</div>
                <div>
                    <div style="font-weight:600">{{ $client->full_name }}</div>
                    <small>{{ $client->email }}</small>
                </div>
            </div>
            <nav class="client-nav">
                <a href="#dashboard" class="active">📊 Dashboard</a>
                <a href="/client/services">🖥️ My Services</a>
                <a href="/client/domains">🌐 Domains</a>
                <a href="/client/orders">🛒 Orders</a>
                <a href="/client/invoices">🧾 Invoices</a>
                <a href="/client/support">🎧 Support</a>
                <a href="/client/account">⚙️ Account</a>
                <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">🚪 Logout</a>
                <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display:none">@csrf</form>
            </nav>
        </aside>

        {{-- Main Content --}}
        <div class="client-main">
            {{-- Stats --}}
            <div class="stat-grid reveal">
                <div class="stat-card"><div class="l">Active Services</div><div class="v">{{ $stats['active_services'] }}</div></div>
                <div class="stat-card"><div class="l">Domains</div><div class="v">{{ $stats['active_domains'] }}</div></div>
                <div class="stat-card"><div class="l">Orders</div><div class="v">{{ $stats['total_orders'] }}</div></div>
                <div class="stat-card"><div class="l">Open Invoices</div><div class="v">{{ $stats['pending_invoices'] }}</div></div>
                <div class="stat-card"><div class="l">Due Balance</div><div class="v">{{ jv_format_money($stats['due_amount'] ?? 0) }}</div></div>
            </div>

            {{-- Services --}}
            <section id="services" class="panel reveal">
                <div class="panel-head">
                    <h3>My Services</h3>
                    <a href="/hosting" class="btn btn-outline">+ New Service</a>
                </div>
                @if($services->count() > 0)
                <table class="ca-table">
                    <thead><tr><th>Product</th><th>Billing</th><th>Next Due</th><th>Status</th></tr></thead>
                    <tbody>
                        @foreach($services as $cs)
                        <tr>
                            <td>
                                <strong>{{ $cs->service->name ?? 'Service #' . $cs->id }}</strong>
                                @if($cs->domain)<br><small style="color:var(--gray-500);">{{ $cs->domain }}</small>@endif
                            </td>
                            <td>{{ jv_format_money($cs->price) }} / {{ $cs->billing_cycle }}</td>
                            <td>{{ $cs->next_due_date ? $cs->next_due_date->format('M d, Y') : '—' }}</td>
                            <td><span class="pill pill-{{ $cs->status === 'active' ? 'ok' : ($cs->status === 'suspended' ? 'bad' : 'warn') }}">{{ ucfirst($cs->status) }}</span></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @else
                <div style="text-align:center;padding:40px;color:var(--gray-400);">No active services. <a href="/hosting">Browse plans</a></div>
                @endif
            </section>

            <div class="row">
                {{-- Domains --}}
                <section id="domains" class="panel reveal">
                    <div class="panel-head">
                        <h3>Domains</h3>
                        <a href="/domains" class="btn btn-outline">Register</a>
                    </div>
                    @if($domains->count() > 0)
                    <table class="ca-table">
                        <thead><tr><th>Domain</th><th>Expires</th><th>Status</th></tr></thead>
                        <tbody>
                            @foreach($domains as $domain)
                            <tr>
                                <td><strong>{{ $domain->domain_name }}</strong></td>
                                <td>{{ $domain->expiry_date ? $domain->expiry_date->format('M d, Y') : '—' }}</td>
                                <td>
                                    @php
                                        $expired = $domain->expiry_date && $domain->expiry_date->isPast();
                                        $soon = $domain->expiry_date && $domain->expiry_date->diffInDays(now()) <= 30;
                                    @endphp
                                    <span class="pill pill-{{ $expired ? 'bad' : ($soon ? 'warn' : 'ok') }}">
                                        {{ $expired ? 'Expired' : ($soon ? 'Expiring' : 'Active') }}
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    @else
                    <div style="text-align:center;padding:40px;color:var(--gray-400)">No domains. <a href="/domains">Search domains</a></div>
                    @endif
                </section>

                {{-- Invoices --}}
                <section id="invoices" class="panel reveal">
                    <div class="panel-head"><h3>Recent Invoices</h3></div>
                    @if($invoices->count() > 0)
                    <table class="ca-table">
                        <thead><tr><th>Invoice</th><th>Amount</th><th>Status</th></tr></thead>
                        <tbody>
                            @foreach($invoices as $invoice)
                            <tr>
                                <td>#{{ $invoice->invoice_number }}</td>
                                <td>{{ jv_format_money($invoice->total) }}</td>
                                <td><span class="pill pill-{{ $invoice->status === 'paid' ? 'ok' : ($invoice->status === 'overdue' ? 'bad' : 'warn') }}">{{ ucfirst($invoice->status) }}</span></td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    @else
                    <div style="text-align:center;padding:40px;color:var(--gray-400)">No invoices yet</div>
                    @endif
                </section>

                {{-- Recent Orders --}}
                <section id="orders" class="panel reveal">
                    <div class="panel-head">
                        <h3>🛒 Recent Orders</h3>
                        <a href="/client/orders" class="btn btn-outline btn-sm">View All</a>
                    </div>
                    @if(isset($recentOrders) && $recentOrders->count() > 0)
                    <table class="ca-table">
                        <thead><tr><th>Order #</th><th>Items</th><th>Total</th><th>Status</th><th>Date</th></tr></thead>
                        <tbody>
                            @foreach($recentOrders as $ord)
                            <tr>
                                <td><a href="/client/orders/{{ $ord->id }}" style="color:var(--primary);font-weight:600;">#{{ $ord->order_number }}</a></td>
                                <td>{{ $ord->items->count() }} item(s)</td>
                                <td><strong>{{ jv_format_money($ord->total) }}</strong></td>
                                <td><span class="pill pill-{{ $ord->status === 'completed' || $ord->status === 'accepted' ? 'ok' : ($ord->status === 'pending' ? 'warn' : 'bad') }}">{{ ucfirst($ord->status) }}</span></td>
                                <td>{{ $ord->created_at->format('M d, Y') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    @else
                    <div style="text-align:center;padding:30px;color:var(--gray-400);">No orders yet. <a href="/hosting">Browse plans</a></div>
                    @endif
                </section>
            </div>

            {{-- Account Details --}}
            <section id="account" class="panel reveal">
                <div class="panel-head"><h3>Account Details</h3></div>
                <div class="row" style="gap:1rem">
                    <div><strong>Name</strong><div style="color:var(--gray-600)">{{ $client->full_name }}</div></div>
                    <div><strong>Email</strong><div style="color:var(--gray-600)">{{ $client->email }}</div></div>
                    <div><strong>Phone</strong><div style="color:var(--gray-600)">{{ $client->phone ?? '—' }}</div></div>
                    <div><strong>Company</strong><div style="color:var(--gray-600)">{{ $client->company_name ?? '—' }}</div></div>
                    <div><strong>Address</strong><div style="color:var(--gray-600)">{{ $client->address ?? '—' }}</div></div>
                    <div><strong>City / Country</strong><div style="color:var(--gray-600)">{{ $client->city ?? '—' }} / {{ $client->country ?? 'Tanzania' }}</div></div>
                </div>
            </section>
        </div>
    </div>
</main>
@endsection
