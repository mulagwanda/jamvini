@extends('themes.default::layouts.admin')

@section('title', 'Orders')
@section('breadcrumbs')<span class="current">Orders</span>@endsection

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Orders</h1>
        <p class="page-subtitle">Manage client orders and auto-generate invoices</p>
    </div>
    <a href="{{ route('admin.orders.create') }}" class="btn btn-primary">{{ jv_icon('shopping-cart', '', 16) }} New Order</a>
</div>

<div class="kpi-grid">
    <div class="kpi"><div><div class="label">Total</div><div class="value">{{ $stats['total'] }}</div></div><div class="ico blue">{{ jv_icon('shopping-cart') }}</div></div>
    <div class="kpi"><div><div class="label">Pending</div><div class="value">{{ $stats['pending'] }}</div><div class="delta">{{ jv_format_money($stats['pending_amount'] ?? 0) }}</div></div><div class="ico amber">{{ jv_icon('hourglass') }}</div></div>
    <div class="kpi"><div><div class="label">Accepted</div><div class="value">{{ $stats['accepted'] }}</div></div><div class="ico green">{{ jv_icon('check-circle') }}</div></div>
    <div class="kpi"><div><div class="label">Needs Provisioning</div><div class="value">{{ $stats['needs_provisioning'] ?? 0 }}</div><div class="delta">{{ jv_format_money($stats['month_total'] ?? 0) }} this month</div></div><div class="ico purple">{{ jv_icon('sparkles') }}</div></div>
</div>

{{-- Filters --}}
<div class="dash-card" style="margin-bottom: 1.5rem;">
    <form method="GET" style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap;">
        <input type="text" name="search" class="form-input" placeholder="Search order #, client, email, WHMCS ID..." value="{{ request('search') }}" style="width: 320px;">
        <select name="status" class="form-select" style="width: 160px;" onchange="this.form.submit()">
            <option value="">All Status</option>
            <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
            <option value="accepted" {{ request('status') === 'accepted' ? 'selected' : '' }}>Accepted</option>
            <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
            <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
            <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
        </select>
        <select name="provisioning_status" class="form-select" style="width: 190px;" onchange="this.form.submit()">
            <option value="">All Provisioning</option>
            @foreach(['not_started' => 'Not Started', 'pending' => 'Pending', 'in_progress' => 'In Progress', 'completed' => 'Completed', 'failed' => 'Failed', 'cancelled' => 'Cancelled'] as $value => $label)
                <option value="{{ $value }}" {{ request('provisioning_status') === $value ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
        @if(request()->anyFilled(['search', 'status', 'provisioning_status']))
            <a href="{{ route('admin.orders.index') }}" class="btn btn-sm btn-outline-danger">Clear</a>
        @endif
    </form>
</div>

{{-- Orders Table --}}
<div class="dash-card" style="padding: 0; overflow: hidden;">
    @if($orders->count() > 0)
    <table class="table" style="margin: 0;">
        <thead><tr><th>Order #</th><th>Client</th><th>Total</th><th>Status</th><th>Provisioning</th><th>Invoice</th><th>Date</th><th class="text-center">Actions</th></tr></thead>
        <tbody>
            @foreach($orders as $order)
            <tr>
                <td><a href="{{ route('admin.orders.show', $order) }}" style="font-weight: 600;">#{{ $order->order_number }}</a>@if($order->external_id)<small style="display:block;color:var(--jv-gray-500);">External: {{ $order->external_id }}</small>@endif</td>
                <td><div class="mini-user"><div class="avatar" style="width:28px;height:28px;font-size:.7rem;">{{ strtoupper(substr($order->client->first_name,0,1).substr($order->client->last_name,0,1)) }}</div><div><strong>{{ $order->client->full_name }}</strong><small>{{ $order->client->email }}</small></div></div></td>
                <td><strong>{{ jv_format_money($order->total) }}</strong></td>
                <td><span class="pill pill-{{ $order->status === 'accepted' || $order->status === 'completed' ? 'ok' : ($order->status === 'rejected' || $order->status === 'cancelled' ? 'bad' : 'warn') }}">{{ ucfirst($order->status) }}</span></td>
                <td><span class="pill pill-info">{{ ucfirst(str_replace('_', ' ', $order->provisioning_status ?? 'not_started')) }}</span></td>
                <td>@if($order->invoice)<a href="{{ route('admin.invoices.show', $order->invoice) }}" class="pill pill-info">#{{ $order->invoice->invoice_number }}</a>@else <span class="pill pill-mute">—</span> @endif</td>
                <td>{{ jv_format_date($order->created_at) }}</td>
                <td class="text-center">
                    <div class="btn-group" style="gap:4px;justify-content:center;">
                        <a href="{{ route('admin.orders.show', $order) }}" class="btn btn-sm btn-outline-primary">{{ jv_icon('search', '', 16) }}</a>
                        @if($order->status === 'pending')
                            <form action="{{ route('admin.orders.accept', $order) }}" method="POST" style="display:inline;">@csrf <button class="btn btn-sm btn-success">{{ jv_icon('check-circle', '', 16) }}</button></form>
                            <form action="{{ route('admin.orders.reject', $order) }}" method="POST" style="display:inline;" data-confirm="Reject this order?" data-danger="true">@csrf <button class="btn btn-sm btn-outline-danger">{{ jv_icon('x', '', 16) }}</button></form>
                        @endif
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <div style="padding: 16px 24px; display: flex; justify-content: space-between; align-items: center;">
        <small style="color: var(--jv-gray-500);">Showing {{ $orders->firstItem() ?? 0 }}–{{ $orders->lastItem() ?? 0 }} of {{ $orders->total() }}</small>
        {{ $orders->appends(request()->query())->links() }}
    </div>
    @else
    <div class="empty-state" style="padding: 60px;">
        <div class="empty-state-icon">{{ jv_icon('shopping-cart', '', 42) }}</div>
        <div class="empty-state-title">{{ request()->anyFilled(['search','status']) ? 'No orders match your filters' : 'No orders yet' }}</div>
        @if(!request()->anyFilled(['search','status']))
            <a href="{{ route('admin.orders.create') }}" class="btn btn-primary">{{ jv_icon('shopping-cart', '', 16) }} Create First Order</a>
        @endif
    </div>
    @endif
</div>
@endsection
