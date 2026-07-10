@extends('themes.default::layouts.admin')

@section('title', 'Invoice #' . $invoice->invoice_number)
@section('breadcrumbs')<a href="{{ route('admin.invoices.index') }}">Invoices</a> <span class="separator">/</span> <span class="current">#{{ $invoice->invoice_number }}</span>@endsection

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">
            Invoice #{{ $invoice->invoice_number }}
            <span class="pill pill-{{ $invoice->status === 'paid' ? 'ok' : ($invoice->status === 'overdue' ? 'bad' : 'warn') }}" style="margin-left:8px;">{{ ucfirst($invoice->status) }}</span>
        </h1>
        <p class="page-subtitle">{{ $invoice->client->full_name }} · {{ jv_format_date($invoice->created_at) }}</p>
    </div>
    <div style="display: flex; gap: 8px; flex-wrap: wrap;">
        <a href="{{ route('admin.invoices.pdf', $invoice) }}" class="btn btn-outline-primary">📥 Download PDF</a>
        @if($invoice->status === 'draft')
            <form action="{{ route('admin.invoices.mark-sent', $invoice) }}" method="POST">@csrf <button class="btn btn-warning">📤 Mark Sent</button></form>
        @endif
        @if(in_array($invoice->status, ['sent', 'overdue', 'partial']))
            <button class="btn btn-success" onclick="document.getElementById('addPaymentForm').style.display='block'">💳 Record Payment</button>
        @endif
        @if(!in_array($invoice->status, ['paid', 'cancelled']) && $invoice->paid_amount <= 0)
            <form action="{{ route('admin.invoices.void', $invoice) }}" method="POST" data-confirm="Void this invoice?" data-danger="true">@csrf <button class="btn btn-outline-danger">Void</button></form>
        @endif
        @if(!in_array($invoice->status, ['paid', 'cancelled']))
            <a href="{{ route('admin.invoices.edit', $invoice) }}" class="btn btn-primary">✏️ Edit</a>
        @endif
    </div>
</div>

{{-- Meta Cards --}}
<div class="grid-meta" style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 1rem; margin-bottom: 1.5rem;">
    <div class="meta" style="background:#f8fafc;border:1px solid var(--jv-gray-200);border-radius:10px;padding:.85rem 1rem;"><div class="label" style="color:#64748b;font-size:.7rem;text-transform:uppercase;font-weight:600;">Total</div><div class="value" style="font-weight:700;font-size:.95rem;">{{ jv_format_money($invoice->total) }}</div></div>
    <div class="meta" style="background:#f8fafc;border:1px solid var(--jv-gray-200);border-radius:10px;padding:.85rem 1rem;"><div class="label" style="color:#64748b;font-size:.7rem;text-transform:uppercase;font-weight:600;">Paid</div><div class="value" style="font-weight:700;font-size:.95rem;color:#16a34a;">{{ jv_format_money($invoice->paid_amount) }}</div></div>
    <div class="meta" style="background:#f8fafc;border:1px solid var(--jv-gray-200);border-radius:10px;padding:.85rem 1rem;"><div class="label" style="color:#64748b;font-size:.7rem;text-transform:uppercase;font-weight:600;">Remaining</div><div class="value" style="font-weight:700;font-size:.95rem;color:{{ $invoice->remaining_amount > 0 ? '#dc2626' : '#16a34a' }};">{{ jv_format_money($invoice->remaining_amount) }}</div></div>
    <div class="meta" style="background:#f8fafc;border:1px solid var(--jv-gray-200);border-radius:10px;padding:.85rem 1rem;"><div class="label" style="color:#64748b;font-size:.7rem;text-transform:uppercase;font-weight:600;">Due Date</div><div class="value" style="font-weight:700;font-size:.95rem;">{{ jv_format_date($invoice->due_date) }}</div>@if($invoice->external_id)<small>External: {{ $invoice->external_id }}</small>@endif</div>
</div>

{{-- Record Payment Form --}}
<div id="addPaymentForm" style="display: none; margin-bottom: 1.5rem;">
    <div class="dash-card">
        <div class="dash-card-head"><h3>💳 Record Payment</h3></div>
        <form action="{{ route('admin.invoices.record-payment', $invoice) }}" method="POST">
            @csrf
            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px;">
                <div class="form-group" style="margin:0;"><label class="form-label">Amount ({{ \App\Models\Setting::get('currency', 'TZS') }}) *</label><input type="number" name="amount" class="form-input" value="{{ $invoice->remaining_amount }}" step="0.01" required></div>
                <div class="form-group" style="margin:0;"><label class="form-label">Payment Method</label><select name="payment_method" class="form-select"><option value="mpesa">M-Pesa</option><option value="tigo">Tigo Pesa</option><option value="airtel">Airtel Money</option><option value="bank">Bank Transfer</option><option value="cash">Cash</option></select></div>
                <div class="form-group" style="margin:0;"><label class="form-label">Transaction ID</label><input type="text" name="transaction_id" class="form-input" placeholder="Optional"></div>
            </div>
            <div class="form-group" style="margin-top: 12px;"><label class="form-label">Notes</label><input type="text" name="notes" class="form-input" placeholder="Payment notes..."></div>
            <div style="display: flex; gap: 12px; margin-top: 12px;">
                <button type="submit" class="btn btn-success">✅ Record Payment</button>
                <button type="button" class="btn btn-outline-danger" onclick="document.getElementById('addPaymentForm').style.display='none'">Cancel</button>
            </div>
        </form>
    </div>
</div>

<div class="grid-2" style="display: grid; grid-template-columns: 1fr 340px; gap: 1.5rem;">
    <div style="display: flex; flex-direction: column; gap: 1.5rem;">
        {{-- Line Items --}}
        <div class="dash-card" style="padding: 0; overflow: hidden;">
            <div class="dash-card-head" style="padding: 1.25rem 1.25rem 0;"><h3>📋 Line Items</h3></div>
            <table class="a-table" style="width: 100%; border-collapse: collapse;">
                <thead><tr><th style="text-align:left;padding:12px;">Description</th><th style="text-align:right;">Qty</th><th style="text-align:right;">Unit</th><th style="text-align:right;">Tax</th><th style="text-align:right;">Total</th></tr></thead>
                <tbody>
                    @foreach($invoice->items as $item)
                    <tr style="border-bottom:1px solid #f1f5f9;">
                        <td style="padding:12px;">{{ $item->description }}</td>
                        <td style="text-align:right;padding:12px;">{{ $item->quantity }}</td>
                        <td style="text-align:right;padding:12px;">{{ jv_format_money($item->unit_price) }}</td>
                        <td style="text-align:right;padding:12px;">{{ $item->tax_rate }}%</td>
                        <td style="text-align:right;padding:12px;"><strong>{{ jv_format_money($item->total) }}</strong></td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr><td colspan="4" style="text-align:right;padding:12px;font-weight:700;">Subtotal</td><td style="text-align:right;padding:12px;font-weight:700;">{{ jv_format_money($invoice->subtotal) }}</td></tr>
                    @if(($invoice->discount ?? 0) > 0)<tr><td colspan="4" style="text-align:right;padding:12px;">Discount</td><td style="text-align:right;padding:12px;">-{{ jv_format_money($invoice->discount) }}</td></tr>@endif
                    <tr><td colspan="4" style="text-align:right;padding:12px;">{{ jv_tax_label() }} ({{ rtrim(rtrim(number_format(jv_tax_rate(), 2), '0'), '.') }}%)</td><td style="text-align:right;padding:12px;">{{ jv_format_money($invoice->tax_amount) }}</td></tr>
                    <tr style="border-top:2px solid var(--jv-gray-200);"><td colspan="4" style="text-align:right;padding:12px;font-weight:700;font-size:1.05rem;color:var(--jv-primary);">Total</td><td style="text-align:right;padding:12px;font-weight:700;font-size:1.05rem;color:var(--jv-primary);">{{ jv_format_money($invoice->total) }}</td></tr>
                </tfoot>
            </table>
        </div>

        {{-- Transactions History --}}
        @if($invoice->transactions->count() > 0)
        <div class="dash-card" style="padding: 0; overflow: hidden;">
            <div class="dash-card-head" style="padding: 1.25rem 1.25rem 0;"><h3>💳 Payment History</h3></div>
            <table class="table" style="margin: 0;">
                <thead><tr><th>Date</th><th>Amount</th><th>Method</th><th>Transaction ID</th><th>Notes</th></tr></thead>
                <tbody>
                    @foreach($invoice->transactions as $txn)
                    <tr>
                        <td>{{ jv_format_date($txn->created_at) }}</td>
                        <td><strong style="color:#16a34a;">{{ jv_format_money($txn->amount) }}</strong></td>
                        <td><span class="pill pill-info">{{ ucfirst($txn->payment_method) }}</span></td>
                        <td>{{ $txn->transaction_id ?? '—' }}</td>
                        <td>{{ $txn->notes ?? '—' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>

    {{-- Sidebar --}}
    <div style="display: flex; flex-direction: column; gap: 1.5rem;">
        @if(($invoice->client->credit_balance ?? 0) > 0 && $invoice->remaining_amount > 0)
        <div class="dash-card">
            <div class="dash-card-head"><h3>Apply Client Credit</h3></div>
            <p style="font-size:.88rem;color:var(--jv-gray-600);margin-bottom:12px;">Available credit: <strong>{{ jv_format_money($invoice->client->credit_balance) }}</strong></p>
            <form action="{{ route('admin.invoices.apply-credit', $invoice) }}" method="POST">
                @csrf
                <div class="form-group"><label class="form-label">Amount</label><input type="number" name="amount" class="form-input" value="{{ min((float) $invoice->client->credit_balance, (float) $invoice->remaining_amount) }}" max="{{ min((float) $invoice->client->credit_balance, (float) $invoice->remaining_amount) }}" min="1" step="0.01"></div>
                <button class="btn btn-primary btn-sm">Apply Credit</button>
            </form>
        </div>
        @endif
        <div class="dash-card">
            <div class="dash-card-head"><h3>👤 Client</h3><a href="{{ route('admin.clients.show', $invoice->client) }}" class="btn btn-sm btn-outline-primary">View Profile</a></div>
            <div class="client-card" style="display:flex;gap:.8rem;align-items:center;margin-bottom:1rem;">
                <div class="av" style="width:48px;height:48px;border-radius:50%;background:linear-gradient(135deg,#6C5CE7,#a78bfa);color:#fff;display:grid;place-items:center;font-weight:700;">{{ strtoupper(substr($invoice->client->first_name,0,1).substr($invoice->client->last_name,0,1)) }}</div>
                <div><div style="font-weight:700;">{{ $invoice->client->full_name }}</div><small>#{{ $invoice->client->id }}</small></div>
            </div>
            <ul class="info-list" style="list-style:none;padding:0;font-size:.85rem;">
                <li style="display:flex;justify-content:space-between;padding:6px 0;border-bottom:1px dashed #f1f5f9;"><span style="color:#64748b;">Email</span><span style="font-weight:600;">{{ $invoice->client->email }}</span></li>
                <li style="display:flex;justify-content:space-between;padding:6px 0;border-bottom:1px dashed #f1f5f9;"><span style="color:#64748b;">Phone</span><span style="font-weight:600;">{{ $invoice->client->phone ?? '—' }}</span></li>
                <li style="display:flex;justify-content:space-between;padding:6px 0;"><span style="color:#64748b;">TIN</span><span style="font-weight:600;">{{ $invoice->client->tin_number ?? '—' }}</span></li>
            </ul>
        </div>

        @if($invoice->notes)
        <div class="dash-card">
            <div class="dash-card-head"><h3>📝 Notes</h3></div>
            <p style="font-size:.88rem;color:var(--jv-gray-600);">{{ $invoice->notes }}</p>
        </div>
        @endif
        @if($invoice->payment_terms || $invoice->admin_notes || $invoice->source)
        <div class="dash-card">
            <div class="dash-card-head"><h3>Billing Details</h3></div>
            <ul class="info-list" style="list-style:none;padding:0;font-size:.85rem;">
                <li style="display:flex;justify-content:space-between;padding:6px 0;border-bottom:1px dashed #f1f5f9;"><span style="color:#64748b;">Source</span><span style="font-weight:600;">{{ $invoice->source ?? 'admin' }}</span></li>
                <li style="display:flex;justify-content:space-between;padding:6px 0;border-bottom:1px dashed #f1f5f9;"><span style="color:#64748b;">Currency</span><span style="font-weight:600;">{{ $invoice->currency ?? \App\Models\Setting::get('currency', 'TZS') }}</span></li>
                <li style="display:flex;justify-content:space-between;padding:6px 0;"><span style="color:#64748b;">Sent</span><span style="font-weight:600;">{{ $invoice->sent_at?->format('M d, Y H:i') ?? '—' }}</span></li>
            </ul>
            @if($invoice->payment_terms)<p style="font-size:.88rem;color:var(--jv-gray-600);margin-top:12px;">{{ $invoice->payment_terms }}</p>@endif
            @if($invoice->admin_notes)<p style="font-size:.88rem;color:var(--jv-gray-500);margin-top:12px;">Private: {{ $invoice->admin_notes }}</p>@endif
        </div>
        @endif
    </div>
</div>
@endsection
