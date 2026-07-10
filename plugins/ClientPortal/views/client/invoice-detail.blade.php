@extends('themes.default::layouts.frontend')

@section('title', 'Invoice #' . $invoice->invoice_number)

@section('content')
<section class="page-hero no-print">
    <div class="container">
        <div class="breadcrumb"><a href="/">Home</a> / <a href="/client/dashboard">Client Area</a> / <a href="/client/invoices">Invoices</a> / #{{ $invoice->invoice_number }}</div>
        <h1>Invoice #{{ $invoice->invoice_number }}</h1>
        <p>View and manage your invoice details.</p>
    </div>
</section>

<main class="container" style="padding: 2rem 0;">
    <div class="client-wrap" style="display: grid; grid-template-columns: 260px 1fr; gap: 2rem;">
        <aside class="client-side no-print" style="background:#fff;border:1px solid var(--gray-200);border-radius:18px;padding:1.25rem;height:fit-content;position:sticky;top:90px;">
            <div class="who" style="display:flex;align-items:center;gap:.75rem;padding-bottom:1rem;border-bottom:1px solid var(--gray-200);margin-bottom:1rem;">
                <div class="avatar" style="width:44px;height:44px;border-radius:50%;background:var(--primary);color:#fff;display:grid;place-items:center;font-weight:700;">
                    {{ strtoupper(substr($client->first_name, 0, 1) . substr($client->last_name, 0, 1)) }}
                </div>
                <div><div style="font-weight:600;">{{ $client->full_name }}</div><small>{{ $client->email }}</small></div>
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
            {{-- Header --}}
            <div style="background:#fff;border-radius:18px;box-shadow:0 2px 8px rgba(0,0,0,.04);padding:1.5rem;margin-bottom:1.5rem;">
                <div style="display:flex;justify-content:space-between;flex-wrap:wrap;gap:1.5rem;">
                    <div style="display:flex;align-items:center;gap:.75rem;">
                        <div style="width:40px;height:40px;border-radius:10px;background:linear-gradient(135deg,var(--primary),var(--primary-light));display:grid;place-items:center;color:#fff;font-weight:700;">{{ substr(\App\Models\Setting::get('company_name', 'J'), 0, 1) }}</div>
                        <div style="font-family:'Poppins',sans-serif;font-weight:700;font-size:1.2rem;">{{ \App\Models\Setting::get('company_name', 'JamVini Hosting') }}</div>
                    </div>
                    <div style="text-align:right;">
                        <h2 style="color:var(--primary);margin-bottom:.25rem;">Invoice</h2>
                        <div style="font-size:.9rem;"><span style="color:var(--gray-600);">#:</span> <strong>{{ $invoice->invoice_number }}</strong></div>
                        <div style="font-size:.9rem;"><span style="color:var(--gray-600);">Issued:</span> {{ $invoice->created_at->format('M d, Y') }}</div>
                        <div style="font-size:.9rem;"><span style="color:var(--gray-600);">Due:</span> <strong>{{ $invoice->due_date ? $invoice->due_date->format('M d, Y') : '—' }}</strong></div>
                    </div>
                </div>
            </div>

            {{-- Actions --}}
            <div class="no-print" style="display:flex;gap:.5rem;flex-wrap:wrap;margin-bottom:1.5rem;">
                @if(in_array($invoice->status, ['sent', 'overdue', 'partial']))
                    <a href="/checkout" class="btn btn-primary">💳 Pay Now</a>
                @endif
                <a href="{{ route('admin.invoices.pdf', $invoice) }}" class="btn btn-outline">📥 Download PDF</a>
                <button class="btn btn-light" onclick="window.print()">🖨️ Print</button>
            </div>

            {{-- Bill To + Info --}}
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:1rem;margin-bottom:1.5rem;">
                <div style="background:var(--gray-50);padding:1.25rem;border-radius:12px;">
                    <h4 style="font-size:.85rem;text-transform:uppercase;color:var(--gray-600);margin-bottom:.75rem;">Bill To</h4>
                    <div style="font-size:.9rem;line-height:1.8;"><strong>{{ $client->full_name }}</strong><br>{{ $client->email }}<br>{{ $client->phone ?? '—' }}<br>{{ $client->address ?: '—' }}, {{ $client->city ?: '' }}</div>
                </div>
                <div style="background:var(--gray-50);padding:1.25rem;border-radius:12px;">
                    <h4 style="font-size:.85rem;text-transform:uppercase;color:var(--gray-600);margin-bottom:.75rem;">Payment</h4>
                    <div style="font-size:.9rem;line-height:1.8;">
                        <div><span style="color:var(--gray-600);">Status:</span> <span class="pill pill-{{ $invoice->status === 'paid' ? 'ok' : ($invoice->status === 'overdue' ? 'bad' : 'warn') }}">{{ ucfirst($invoice->status) }}</span></div>
                        <div><span style="color:var(--gray-600);">Paid:</span> <strong style="color:#16a34a;">{{ jv_format_money($invoice->paid_amount) }}</strong></div>
                        <div><span style="color:var(--gray-600);">Balance:</span> <strong style="color:{{ $invoice->remaining_amount > 0 ? '#dc2626' : '#16a34a' }};">{{ jv_format_money($invoice->remaining_amount) }}</strong></div>
                    </div>
                </div>
            </div>

            {{-- Line Items --}}
            <div style="background:#fff;border-radius:18px;box-shadow:0 2px 8px rgba(0,0,0,.04);padding:1.5rem;margin-bottom:1.5rem;overflow-x:auto;">
                <h4 style="margin-bottom:1rem;">📋 Items</h4>
                <table style="width:100%;border-collapse:collapse;">
                    <thead><tr style="background:var(--gray-50);"><th style="padding:12px;text-align:left;">Description</th><th style="text-align:center;">Qty</th><th style="text-align:right;">Unit</th><th style="text-align:right;">Total</th></tr></thead>
                    <tbody>
                        @foreach($invoice->items as $item)
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
                <div style="display:flex;justify-content:space-between;padding:.5rem 0;"><span>Subtotal</span><strong>{{ jv_format_money($invoice->subtotal) }}</strong></div>
                <div style="display:flex;justify-content:space-between;padding:.5rem 0;"><span>Tax</span><strong>{{ jv_format_money($invoice->tax_amount) }}</strong></div>
                <div style="display:flex;justify-content:space-between;padding:1rem 0 0;margin-top:.5rem;border-top:2px solid var(--gray-200);font-size:1.1rem;font-weight:700;"><span>Total</span><span style="color:var(--primary);">{{ jv_format_money($invoice->total) }}</span></div>
            </div>

            {{-- Payment History --}}
            @if($invoice->transactions->count() > 0)
            <div style="background:#fff;border-radius:18px;box-shadow:0 2px 8px rgba(0,0,0,.04);padding:1.5rem;margin-bottom:1.5rem;">
                <h4 style="margin-bottom:1rem;">💳 Payment History</h4>
                <table style="width:100%;border-collapse:collapse;">
                    <thead><tr style="background:var(--gray-50);"><th style="padding:10px;">Date</th><th>Amount</th><th>Method</th><th>Status</th></tr></thead>
                    <tbody>
                        @foreach($invoice->transactions as $txn)
                        <tr style="border-bottom:1px solid var(--gray-100);">
                            <td style="padding:10px;">{{ $txn->created_at->format('M d, Y H:i') }}</td>
                            <td style="padding:10px;color:#16a34a;font-weight:600;">{{ jv_format_money($txn->amount) }}</td>
                            <td style="padding:10px;"><span class="pill pill-info">{{ ucfirst($txn->payment_method) }}</span></td>
                            <td style="padding:10px;"><span class="pill pill-ok">Completed</span></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                <div style="margin-top:1rem;padding-top:1rem;border-top:1px solid var(--gray-100);display:flex;justify-content:space-between;font-weight:600;">
                    <span>Total Paid:</span><span style="color:#16a34a;">{{ jv_format_money($invoice->paid_amount) }}</span>
                </div>
            </div>
            @endif

            {{-- Notes --}}
            @if($invoice->notes)
            <div style="background:var(--gray-50);padding:1.5rem;border-radius:12px;">
                <h4>📝 Notes</h4><p style="font-size:.9rem;color:var(--gray-600);margin-top:.5rem;">{{ $invoice->notes }}</p>
            </div>
            @endif
        </div>
    </div>
</main>
@endsection