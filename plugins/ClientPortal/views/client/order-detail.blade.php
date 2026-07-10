@extends('themes.default::layouts.frontend')

@section('title', 'Order #' . $order->order_number)

@section('content')
<section class="page-hero no-print">
    <div class="container">
        <div class="breadcrumb"><a href="/">Home</a> / <a href="/client/dashboard">Client Area</a> / <a href="/client/orders">Orders</a> / #{{ $order->order_number }}</div>
        <h1>Order #{{ $order->order_number }}</h1>
        <p>View details of your order, including items, payment, and status.</p>
    </div>
</section>

<main class="container" style="padding: 2rem 0;">
    <div class="client-wrap" style="display: grid; grid-template-columns: 260px 1fr; gap: 2rem;">
        {{-- Sidebar --}}
        <aside class="client-side no-print" style="background:#fff;border:1px solid var(--gray-200);border-radius:18px;padding:1.25rem;height:fit-content;position:sticky;top:90px;">
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
                <a href="/client/dashboard">📊 Dashboard</a>
                <a href="/client/services">🖥️ My Services</a>
                <a href="/client/domains">🌐 Domains</a>
                <a href="/client/orders" class="active" style="background:var(--primary);color:#fff;">🛒 Orders</a>
                <a href="/client/invoices">🧾 Invoices</a>
                <a href="/client/account">⚙️ Account</a>
                <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">🚪 Logout</a>
                <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display:none;">@csrf</form>
            </nav>
        </aside>

        {{-- Main Content --}}
        <div>
            {{-- Header --}}
            <div style="background:#fff;border-radius:18px;box-shadow:0 2px 8px rgba(0,0,0,.04);padding:1.5rem;margin-bottom:1.5rem;">
                <div style="display:flex;justify-content:space-between;flex-wrap:wrap;gap:1.5rem;">
                    <div>
                        <h2 style="display:flex;align-items:center;gap:.75rem;margin-bottom:.5rem;">
                            <div style="width:48px;height:48px;border-radius:12px;background:linear-gradient(135deg,var(--primary),var(--primary-light));color:#fff;display:grid;place-items:center;font-size:1.5rem;">🛒</div>
                            Order #{{ $order->order_number }}
                        </h2>
                        <div style="display:flex;flex-direction:column;gap:.5rem;font-size:.9rem;">
                            <div><span style="color:var(--gray-600);">Date:</span> <strong>{{ $order->created_at->format('M d, Y') }}</strong></div>
                            <div><span style="color:var(--gray-600);">Status:</span>
                                <span class="pill pill-{{ $order->status === 'completed' || $order->status === 'accepted' ? 'ok' : ($order->status === 'pending' ? 'warn' : 'bad') }}">{{ ucfirst($order->status) }}</span>
                            </div>
                            @if($order->invoice)
                            <div><span style="color:var(--gray-600);">Invoice:</span> <strong>#{{ $order->invoice->invoice_number }}</strong></div>
                            @endif
                        </div>
                    </div>
                    <div class="no-print" style="display:flex;gap:.5rem;flex-wrap:wrap;">
                        <a href="/client/orders" class="btn btn-outline btn-sm">← Back</a>
                        <button class="btn btn-light btn-sm" onclick="window.print()">🖨️ Print</button>
                    </div>
                </div>
            </div>

            {{-- Detail Cards --}}
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:1rem;margin-bottom:1.5rem;">
                <div style="background:var(--gray-50);padding:1.25rem;border-radius:12px;">
                    <h4 style="font-size:.85rem;text-transform:uppercase;color:var(--gray-600);margin-bottom:.75rem;">Billing</h4>
                    <div style="font-size:.9rem;line-height:1.8;">
                        <strong>{{ $client->full_name }}</strong><br>
                        @if($client->company_name){{ $client->company_name }}<br>@endif
                        {{ $client->email }}<br>
                        {{ $client->phone ?? '—' }}<br>
                        {{ $client->address ?: '—' }}, {{ $client->city ?: '' }} {{ $client->country ?: 'Tanzania' }}
                    </div>
                </div>
                <div style="background:var(--gray-50);padding:1.25rem;border-radius:12px;">
                    <h4 style="font-size:.85rem;text-transform:uppercase;color:var(--gray-600);margin-bottom:.75rem;">Order Info</h4>
                    <div style="display:flex;flex-direction:column;gap:.5rem;font-size:.9rem;">
                        <div style="display:flex;justify-content:space-between;"><span style="color:var(--gray-600);">Order #</span><strong>#{{ $order->order_number }}</strong></div>
                        <div style="display:flex;justify-content:space-between;"><span style="color:var(--gray-600);">Date</span><span>{{ $order->created_at->format('M d, Y') }}</span></div>
                        <div style="display:flex;justify-content:space-between;"><span style="color:var(--gray-600);">Status</span><span class="pill pill-{{ $order->status === 'completed' ? 'ok' : 'warn' }}">{{ ucfirst($order->status) }}</span></div>
                    </div>
                </div>
            </div>

            {{-- Line Items --}}
            <div style="background:#fff;border-radius:18px;box-shadow:0 2px 8px rgba(0,0,0,.04);padding:1.5rem;margin-bottom:1.5rem;overflow-x:auto;">
                <h4 style="margin-bottom:1rem;">📋 Line Items</h4>
                <table style="width:100%;border-collapse:collapse;">
                    <thead><tr style="background:var(--gray-50);"><th style="padding:12px;text-align:left;">Description</th><th style="text-align:center;">Qty</th><th style="text-align:right;">Unit Price</th><th style="text-align:right;">Total</th></tr></thead>
                    <tbody>
                        @foreach($order->items as $item)
                        <tr style="border-bottom:1px solid var(--gray-100);">
                            <td style="padding:12px;">{{ $item->description }}</td>
                            <td style="text-align:center;padding:12px;">{{ $item->quantity }}</td>
                            <td style="text-align:right;padding:12px;">{{ jv_format_money($item->unit_price) }}</td>
                            <td style="text-align:right;padding:12px;font-weight:600;">{{ jv_format_money($item->total) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Totals --}}
            <div style="background:#fff;border-radius:18px;box-shadow:0 2px 8px rgba(0,0,0,.04);padding:1.5rem;margin-left:auto;max-width:380px;margin-bottom:1.5rem;">
                <div style="display:flex;justify-content:space-between;padding:.5rem 0;"><span>Subtotal</span><strong>{{ jv_format_money($order->subtotal) }}</strong></div>
                <div style="display:flex;justify-content:space-between;padding:.5rem 0;"><span>Tax (18%)</span><strong>{{ jv_format_money($order->tax_amount) }}</strong></div>
                <div style="display:flex;justify-content:space-between;padding:1rem 0 0;margin-top:.5rem;border-top:2px solid var(--gray-200);font-size:1.1rem;font-weight:700;">
                    <span>Total</span><span style="color:var(--primary);">{{ jv_format_money($order->total) }}</span>
                </div>
            </div>

            {{-- Payment Info --}}
            @if($order->invoice && $order->invoice->transactions->count() > 0)
            <div style="background:var(--gray-50);padding:1.5rem;border-radius:12px;margin-bottom:1.5rem;">
                <h4 style="margin-bottom:1rem;">💳 Payment History</h4>
                @foreach($order->invoice->transactions as $txn)
                <div style="display:flex;justify-content:space-between;padding:.5rem 0;font-size:.9rem;border-bottom:1px solid var(--gray-200);">
                    <span>{{ $txn->created_at->format('M d, Y H:i') }}</span>
                    <span class="pill pill-info">{{ ucfirst($txn->payment_method) }}</span>
                    <strong style="color:#16a34a;">{{ jv_format_money($txn->amount) }}</strong>
                </div>
                @endforeach
            </div>
            @endif

            {{-- Notes --}}
            @if($order->notes)
            <div style="background:var(--gray-50);padding:1.5rem;border-radius:12px;">
                <h4 style="margin-bottom:.5rem;">📝 Notes</h4>
                <p style="font-size:.9rem;color:var(--gray-600);">{{ $order->notes }}</p>
            </div>
            @endif
        </div>
    </div>
</main>
@endsection