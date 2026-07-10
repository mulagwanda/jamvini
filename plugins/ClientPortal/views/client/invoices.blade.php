@extends('themes.default::layouts.frontend')

@section('title', 'My Invoices')

@section('content')
<section class="page-hero">
    <div class="container">
        <div class="breadcrumb"><a href="/">Home</a> / <a href="/client/dashboard">Client Area</a> / Invoices</div>
        <h1>My Invoices</h1>
        <p>View and manage your invoices.</p>
    </div>
</section>

<main class="container" style="padding: 2rem 0;">
    <div class="client-wrap" style="display: grid; grid-template-columns: 260px 1fr; gap: 2rem;">
        <aside class="client-side" style="background:#fff;border:1px solid var(--gray-200);border-radius:18px;padding:1.25rem;height:fit-content;position:sticky;top:90px;">
            <div class="who" style="display:flex;align-items:center;gap:.75rem;padding-bottom:1rem;border-bottom:1px solid var(--gray-200);margin-bottom:1rem;">
                <div class="avatar" style="width:44px;height:44px;border-radius:50%;background:var(--primary);color:#fff;display:grid;place-items:center;font-weight:700;">
                    {{ strtoupper(substr($client->first_name, 0, 1) . substr($client->last_name, 0, 1)) }}
                </div>
                <div><div style="font-weight:600;">{{ $client->full_name }}</div><small style="color:var(--gray-600);">{{ $client->email }}</small></div>
            </div>
            <nav class="client-nav" style="display:flex;flex-direction:column;gap:.25rem;">
                <a href="/client/dashboard">📊 Dashboard</a>
                <a href="/client/services">🖥️ My Services</a>
                <a href="/client/domains">🌐 Domains</a>
                <a href="/client/orders">🛒 Orders</a>
                <a href="/client/invoices" class="active" style="background:var(--primary);color:#fff;">🧾 Invoices</a>
                <a href="/client/account">⚙️ Account</a>
                <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">🚪 Logout</a>
                <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display:none;">@csrf</form>
            </nav>
        </aside>

        <div>
            <div class="stats-grid" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:1rem;margin-bottom:1.5rem;">
                <div class="stat-card" style="background:#fff;border:1px solid var(--gray-200);border-radius:12px;padding:1.25rem;"><div style="color:var(--gray-600);font-size:.85rem;">Total</div><div style="font-size:1.6rem;font-weight:700;">{{ $stats['total'] }}</div></div>
                <div class="stat-card" style="background:#fff;border:1px solid var(--gray-200);border-radius:12px;padding:1.25rem;"><div style="color:var(--gray-600);font-size:.85rem;">Paid</div><div style="font-size:1.6rem;font-weight:700;color:#16a34a;">{{ $stats['paid'] }}</div></div>
                <div class="stat-card" style="background:#fff;border:1px solid var(--gray-200);border-radius:12px;padding:1.25rem;"><div style="color:var(--gray-600);font-size:.85rem;">Unpaid</div><div style="font-size:1.6rem;font-weight:700;color:#dc2626;">{{ $stats['unpaid'] }}</div></div>
                <div class="stat-card" style="background:#fff;border:1px solid var(--gray-200);border-radius:12px;padding:1.25rem;"><div style="color:var(--gray-600);font-size:.85rem;">Due</div><div style="font-size:1.6rem;font-weight:700;">{{ jv_format_money($stats['due_amount']) }}</div></div>
            </div>

            <div style="background:#fff;border:1px solid var(--gray-200);border-radius:18px;padding:1.5rem;">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem;">
                    <h3 style="margin:0;">📋 Invoices</h3>
                </div>

                @if($invoices->count() > 0)
                <div style="overflow-x:auto;">
                    <table style="width:100%;border-collapse:collapse;">
                        <thead><tr style="background:var(--gray-50);"><th style="padding:12px;text-align:left;">Invoice #</th><th>Date</th><th>Due Date</th><th>Amount</th><th>Status</th><th style="text-align:right;">Actions</th></tr></thead>
                        <tbody>
                            @foreach($invoices as $inv)
                            <tr style="border-bottom:1px solid var(--gray-100);">
                                <td style="padding:12px;font-weight:600;color:var(--primary);">#{{ $inv->invoice_number }}</td>
                                <td style="padding:12px;">{{ $inv->created_at->format('M d, Y') }}</td>
                                <td style="padding:12px;{{ $inv->due_date && $inv->due_date->isPast() && $inv->status !== 'paid' ? 'color:#ef4444;font-weight:600;' : '' }}">{{ $inv->due_date ? $inv->due_date->format('M d, Y') : '—' }}</td>
                                <td style="padding:12px;font-weight:600;">{{ jv_format_money($inv->total) }}</td>
                                <td style="padding:12px;"><span class="pill pill-{{ $inv->status === 'paid' ? 'ok' : ($inv->status === 'overdue' ? 'bad' : 'warn') }}">{{ ucfirst($inv->status) }}</span></td>
                                <td style="padding:12px;text-align:right;">
                                    <a href="{{ route('client.invoices.show', $inv) }}" class="btn btn-outline btn-sm">View</a>
                                    @if(in_array($inv->status, ['sent', 'overdue', 'partial']))
                                        <a href="/checkout" class="btn btn-primary btn-sm">Pay</a>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div style="margin-top:1rem;">{{ $invoices->links() }}</div>
                @else
                <div style="text-align:center;padding:60px;"><div style="font-size:3rem;">🧾</div><h3>No invoices yet</h3></div>
                @endif
            </div>
        </div>
    </div>
</main>
@endsection