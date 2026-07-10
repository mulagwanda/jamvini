@extends('themes.default::layouts.frontend')

@section('title', 'My Orders')

@section('content')
<section class="page-hero">
    <div class="container">
        <div class="breadcrumb"><a href="/">Home</a> / <a href="/client/dashboard">Client Area</a> / Orders</div>
        <h1>My Orders</h1>
        <p>View and manage your orders and purchases.</p>
    </div>
</section>

<main class="container" style="padding: 2rem 0;">
    <div class="client-wrap" style="display: grid; grid-template-columns: 260px 1fr; gap: 2rem;">
        {{-- Sidebar --}}
        <aside class="client-side" style="background:#fff;border:1px solid var(--gray-200);border-radius:18px;padding:1.25rem;height:fit-content;position:sticky;top:90px;">
            <div class="who" style="display:flex;align-items:center;gap:.75rem;padding-bottom:1rem;border-bottom:1px solid var(--gray-200);margin-bottom:1rem;">
                <div class="avatar" style="width:44px;height:44px;border-radius:50%;background:var(--primary);color:#fff;display:grid;place-items:center;font-weight:700;">
                    {{ strtoupper(substr($client->first_name, 0, 1) . substr($client->last_name, 0, 1)) }}
                </div>
                <div>
                    <div style="font-weight:600;">{{ $client->full_name }}</div>
                    <small style="color:var(--gray-600);">{{ $client->email }}</small>
                </div>
            </div>
            <nav class="client-nav" style="display:flex;flex-direction:column;gap:.25rem;">
                <a href="/client/dashboard" style="padding:.6rem .75rem;border-radius:8px;color:var(--dark);text-decoration:none;font-weight:500;">📊 Dashboard</a>
                <a href="/client/services" style="padding:.6rem .75rem;border-radius:8px;color:var(--dark);text-decoration:none;font-weight:500;">🖥️ My Services</a>
                <a href="/client/domains" style="padding:.6rem .75rem;border-radius:8px;color:var(--dark);text-decoration:none;font-weight:500;">🌐 Domains</a>
                <a href="/client/orders" class="active" style="padding:.6rem .75rem;border-radius:8px;background:var(--primary);color:#fff;text-decoration:none;font-weight:500;">🛒 Orders</a>
                <a href="/client/invoices" style="padding:.6rem .75rem;border-radius:8px;color:var(--dark);text-decoration:none;font-weight:500;">🧾 Invoices</a>
                <a href="/client/account" style="padding:.6rem .75rem;border-radius:8px;color:var(--dark);text-decoration:none;font-weight:500;">⚙️ Account</a>
                <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();" style="padding:.6rem .75rem;border-radius:8px;color:var(--dark);text-decoration:none;font-weight:500;">🚪 Logout</a>
                <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display:none;">@csrf</form>
            </nav>
        </aside>

        {{-- Main --}}
        <div>
            {{-- Stats --}}
            <div class="stats-grid" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:1rem;margin-bottom:1.5rem;">
                <div class="stat-card" style="background:#fff;border:1px solid var(--gray-200);border-radius:12px;padding:1.25rem;">
                    <div style="color:var(--gray-600);font-size:.85rem;text-transform:uppercase;">Total Orders</div>
                    <div style="font-family:'Poppins',sans-serif;font-size:1.6rem;font-weight:700;">{{ $stats['total'] }}</div>
                </div>
                <div class="stat-card" style="background:#fff;border:1px solid var(--gray-200);border-radius:12px;padding:1.25rem;">
                    <div style="color:var(--gray-600);font-size:.85rem;text-transform:uppercase;">Completed</div>
                    <div style="font-family:'Poppins',sans-serif;font-size:1.6rem;font-weight:700;color:#16a34a;">{{ $stats['completed'] }}</div>
                </div>
                <div class="stat-card" style="background:#fff;border:1px solid var(--gray-200);border-radius:12px;padding:1.25rem;">
                    <div style="color:var(--gray-600);font-size:.85rem;text-transform:uppercase;">Pending</div>
                    <div style="font-family:'Poppins',sans-serif;font-size:1.6rem;font-weight:700;color:#d97706;">{{ $stats['pending'] }}</div>
                </div>
                <div class="stat-card" style="background:#fff;border:1px solid var(--gray-200);border-radius:12px;padding:1.25rem;">
                    <div style="color:var(--gray-600);font-size:.85rem;text-transform:uppercase;">Total Spent</div>
                    <div style="font-family:'Poppins',sans-serif;font-size:1.6rem;font-weight:700;">{{ jv_format_money($stats['total_spent']) }}</div>
                </div>
            </div>

            {{-- Orders Panel --}}
            <div style="background:#fff;border:1px solid var(--gray-200);border-radius:18px;padding:1.5rem;">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem;">
                    <h3 style="margin:0;">📋 Order List</h3>
                    <a href="/hosting" class="btn btn-primary btn-sm">+ New Order</a>
                </div>

                @if($orders->count() > 0)
                <div style="overflow-x:auto;">
                    <table style="width:100%;border-collapse:collapse;">
                        <thead>
                            <tr style="background:var(--gray-50);">
                                <th style="padding:12px;text-align:left;font-size:.8rem;text-transform:uppercase;color:var(--gray-600);">Order #</th>
                                <th style="padding:12px;text-align:left;font-size:.8rem;text-transform:uppercase;color:var(--gray-600);">Date</th>
                                <th style="padding:12px;text-align:left;font-size:.8rem;text-transform:uppercase;color:var(--gray-600);">Items</th>
                                <th style="padding:12px;text-align:left;font-size:.8rem;text-transform:uppercase;color:var(--gray-600);">Amount</th>
                                <th style="padding:12px;text-align:left;font-size:.8rem;text-transform:uppercase;color:var(--gray-600);">Status</th>
                                <th style="padding:12px;text-align:left;font-size:.8rem;text-transform:uppercase;color:var(--gray-600);">Invoice</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($orders as $order)
                            <tr style="border-bottom:1px solid var(--gray-100);">
                                <td style="padding:12px;font-weight:600;color:var(--primary);"><a href="/client/orders/{{ $order->id }}" class="pill pill-info">#{{ $order->order_number }}</a></td>
                                <td style="padding:12px;">{{ $order->created_at->format('M d, Y') }}</td>
                                <td style="padding:12px;">
                                    @foreach($order->items->take(2) as $item)
                                        <div style="font-size:.9rem;">{{ $item->description }}</div>
                                    @endforeach
                                    @if($order->items->count() > 2)
                                        <div style="font-size:.8rem;color:var(--gray-500);">+{{ $order->items->count() - 2 }} more</div>
                                    @endif
                                </td>
                                <td style="padding:12px;font-weight:600;">{{ jv_format_money($order->total) }}</td>
                                <td style="padding:12px;">
                                    <span class="pill pill-{{ $order->status === 'completed' || $order->status === 'accepted' ? 'ok' : ($order->status === 'pending' ? 'warn' : ($order->status === 'cancelled' || $order->status === 'rejected' ? 'bad' : 'info')) }}">
                                        {{ ucfirst($order->status) }}
                                    </span>
                                </td>
                                <td style="padding:12px;">
                                    @if($order->invoice)
                                        <a href="/client/invoices" class="pill pill-info">#{{ $order->invoice->invoice_number }}</a>
                                    @else
                                        <span style="color:var(--gray-400);">—</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div style="margin-top:1rem;">{{ $orders->links() }}</div>
                @else
                <div style="text-align:center;padding:60px;">
                    <div style="font-size:3rem;margin-bottom:1rem;">🛒</div>
                    <h3>No orders yet</h3>
                    <p style="color:var(--gray-600);margin-bottom:1.5rem;">Browse our hosting plans or domains to get started!</p>
                    <a href="/hosting" class="btn btn-primary">Browse Hosting</a>
                    <a href="/domains" class="btn btn-outline" style="margin-left:.5rem;">Register Domain</a>
                </div>
                @endif
            </div>
        </div>
    </div>
</main>
@endsection