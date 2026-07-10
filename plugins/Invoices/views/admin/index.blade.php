@extends('themes.default::layouts.admin')

@section('title', 'Invoices')
@section('breadcrumbs')<span class="current">Invoices</span>@endsection

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Invoices</h1>
        <p class="page-subtitle">Manage client billing and payments</p>
    </div>
    <div style="display:flex;gap:8px;flex-wrap:wrap;">
        <form action="{{ route('admin.invoices.refresh-overdue') }}" method="POST">@csrf <button class="btn btn-outline-primary">{{ jv_icon('refresh-cw', '', 16) }} Refresh Overdue</button></form>
        <a href="{{ route('admin.invoices.create') }}" class="btn btn-primary">{{ jv_icon('file-text', '', 16) }} Create Invoice</a>
    </div>
</div>

<div class="kpi-grid">
    <div class="kpi"><div><div class="label">Total</div><div class="value">{{ $stats['total'] }}</div></div><div class="ico blue">{{ jv_icon('file-text') }}</div></div>
    <div class="kpi"><div><div class="label">Outstanding</div><div class="value">{{ jv_format_money($stats['outstanding'] ?? 0) }}</div><div class="delta">{{ $stats['sent'] }} sent</div></div><div class="ico amber">{{ jv_icon('send') }}</div></div>
    <div class="kpi"><div><div class="label">Paid</div><div class="value">{{ $stats['paid'] }}</div><div class="delta">{{ jv_format_money($stats['month_revenue'] ?? 0) }} this month</div></div><div class="ico green">{{ jv_icon('check-circle') }}</div></div>
    <div class="kpi"><div><div class="label">Overdue</div><div class="value">{{ $stats['overdue'] }}</div></div><div class="ico red">{{ jv_icon('triangle-alert') }}</div></div>
    <div class="kpi"><div><div class="label">Due Soon</div><div class="value">{{ $stats['due_soon'] ?? 0 }}</div><div class="delta">{{ $stats['overdue_30'] ?? 0 }} over 30 days</div></div><div class="ico purple">{{ jv_icon('dollar-sign') }}</div></div>
</div>

<div class="dash-card" style="margin-bottom: 1.5rem;">
    <form method="GET" style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap;">
        <input type="text" name="search" class="form-input" placeholder="Invoice #, client, email, client #..." value="{{ request('search') }}" style="width: 320px;">
        <select name="status" class="form-select" style="width: 160px;" onchange="this.form.submit()">
            <option value="">All Status</option>
            <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
            <option value="sent" {{ request('status') === 'sent' ? 'selected' : '' }}>Sent</option>
            <option value="partial" {{ request('status') === 'partial' ? 'selected' : '' }}>Partially Paid</option>
            <option value="paid" {{ request('status') === 'paid' ? 'selected' : '' }}>Paid</option>
            <option value="overdue" {{ request('status') === 'overdue' ? 'selected' : '' }}>Overdue</option>
            <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
        </select>
        <select name="aging" class="form-select" style="width: 170px;" onchange="this.form.submit()">
            <option value="">All Aging</option>
            <option value="7" {{ request('aging') === '7' ? 'selected' : '' }}>Overdue 1-13 days</option>
            <option value="14" {{ request('aging') === '14' ? 'selected' : '' }}>Overdue 14-29 days</option>
            <option value="30" {{ request('aging') === '30' ? 'selected' : '' }}>Overdue 30+ days</option>
        </select>
        @if(request()->anyFilled(['search', 'status', 'aging']))
            <a href="{{ route('admin.invoices.index') }}" class="btn btn-sm btn-outline-danger">Clear</a>
        @endif
    </form>
</div>

<div class="dash-card" style="padding: 0; overflow: hidden;">
    @if($invoices->count() > 0)
    <table class="table" style="margin: 0;">
        <thead><tr><th>Invoice #</th><th>Client</th><th>Total</th><th>Paid</th><th>Status</th><th>Due Date</th><th>Date</th><th class="text-center">Actions</th></tr></thead>
        <tbody>
            @foreach($invoices as $invoice)
            <tr>
                <td><a href="{{ route('admin.invoices.show', $invoice) }}" style="font-weight: 600;">#{{ $invoice->invoice_number }}</a></td>
                <td>
                    <div class="mini-user">
                        <div class="avatar" style="width:28px;height:28px;font-size:.7rem;">{{ strtoupper(substr($invoice->client->first_name,0,1).substr($invoice->client->last_name,0,1)) }}</div>
                        <div><strong>{{ $invoice->client->full_name }}</strong><small>{{ $invoice->client->email }}</small></div>
                    </div>
                </td>
                <td><strong>{{ jv_format_money($invoice->total) }}</strong></td>
                <td><span class="pill pill-{{ $invoice->paid_amount >= $invoice->total ? 'ok' : ($invoice->paid_amount > 0 ? 'info' : 'mute') }}">{{ jv_format_money($invoice->paid_amount) }}</span></td>
                <td><span class="pill pill-{{ $invoice->status === 'paid' ? 'ok' : ($invoice->status === 'overdue' ? 'bad' : ($invoice->status === 'sent' ? 'warn' : 'mute')) }}">{{ ucfirst($invoice->status) }}</span></td>
                <td>
                    {{ jv_format_date($invoice->due_date) }}
                    @if($invoice->is_overdue)<small style="display:block;color:var(--jv-danger);">{{ $invoice->age_bucket }} days overdue</small>@endif
                </td>
                <td>{{ jv_format_date($invoice->created_at) }}</td>
                <td class="text-center">
                    <div class="btn-group" style="gap:4px;justify-content:center;">
                        <a href="{{ route('admin.invoices.show', $invoice) }}" class="btn btn-sm btn-outline-primary">{{ jv_icon('search', '', 16) }}</a>
                        <a href="{{ route('admin.invoices.pdf', $invoice) }}" class="btn btn-sm btn-outline-primary">{{ jv_icon('file', '', 16) }}</a>
                        @if(!in_array($invoice->status, ['paid']))
                            <a href="{{ route('admin.invoices.edit', $invoice) }}" class="btn btn-sm btn-outline-primary">{{ jv_icon('notebook-pen', '', 16) }}</a>
                        @endif
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <div style="padding: 16px 24px; display: flex; justify-content: space-between; align-items: center;">
        <small style="color: var(--jv-gray-500);">Showing {{ $invoices->firstItem() ?? 0 }}–{{ $invoices->lastItem() ?? 0 }} of {{ $invoices->total() }}</small>
        {{ $invoices->appends(request()->query())->links() }}
    </div>
    @else
    <div class="empty-state" style="padding: 60px;"><div class="empty-state-icon">{{ jv_icon('file-text', '', 42) }}</div><div class="empty-state-title">No invoices</div><a href="{{ route('admin.invoices.create') }}" class="btn btn-primary">{{ jv_icon('file-text', '', 16) }} Create First Invoice</a></div>
    @endif
</div>
@endsection
