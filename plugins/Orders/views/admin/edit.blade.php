@extends('themes.default::layouts.admin')

@section('title', 'Edit Order #' . $order->order_number)
@section('breadcrumbs')<a href="{{ route('admin.orders.index') }}">Orders</a> <span class="separator">/</span> <a href="{{ route('admin.orders.show', $order) }}">#{{ $order->order_number }}</a> <span class="separator">/</span> <span class="current">Edit</span>@endsection

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Edit Order #{{ $order->order_number }}</h1>
        <p class="page-subtitle">Update order metadata and provisioning notes</p>
    </div>
    <a href="{{ route('admin.orders.show', $order) }}" class="btn btn-outline-primary">Back to Order</a>
</div>

<form action="{{ route('admin.orders.update', $order) }}" method="POST">
    @csrf @method('PUT')

    <div style="display:grid;grid-template-columns:minmax(0,1fr) 340px;gap:1.5rem;align-items:start;">
        <div style="display:grid;gap:1.5rem;">
            <div class="dash-card">
                <div class="dash-card-head"><h3>Order Details</h3></div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                    <div class="form-group">
                        <label class="form-label">Client</label>
                        <select name="client_id" class="form-select" required>
                            @foreach($clients as $client)
                                <option value="{{ $client->id }}" {{ old('client_id', $order->client_id) == $client->id ? 'selected' : '' }}>{{ $client->full_name }} ({{ $client->email }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group"><label class="form-label">Payment Method</label><input type="text" name="payment_method" class="form-input" value="{{ old('payment_method', $order->payment_method) }}"></div>
                    <div class="form-group"><label class="form-label">Source</label><input type="text" name="source" class="form-input" value="{{ old('source', $order->source) }}"></div>
                    <div class="form-group"><label class="form-label">External / WHMCS ID</label><input type="text" name="external_id" class="form-input" value="{{ old('external_id', $order->external_id) }}"></div>
                </div>
            </div>

            <div class="dash-card">
                <div class="dash-card-head"><h3>Notes</h3></div>
                <div class="form-group"><label class="form-label">Order Notes</label><textarea name="notes" class="form-textarea" rows="4">{{ old('notes', $order->notes) }}</textarea></div>
                <div class="form-group"><label class="form-label">Client Notes</label><textarea name="client_notes" class="form-textarea" rows="4">{{ old('client_notes', $order->client_notes) }}</textarea></div>
                <div class="form-group"><label class="form-label">Private Admin / Provisioning Notes</label><textarea name="admin_notes" class="form-textarea" rows="5">{{ old('admin_notes', $order->admin_notes) }}</textarea></div>
            </div>
        </div>

        <aside class="dash-card">
            <div class="dash-card-head"><h3>Status</h3></div>
            <div class="form-group">
                <label class="form-label">Provisioning Status</label>
                <select name="provisioning_status" class="form-select">
                    @foreach(['not_started' => 'Not Started', 'pending' => 'Pending', 'in_progress' => 'In Progress', 'completed' => 'Completed', 'failed' => 'Failed', 'cancelled' => 'Cancelled'] as $value => $label)
                        <option value="{{ $value }}" {{ old('provisioning_status', $order->provisioning_status) === $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div style="display:grid;gap:8px;font-size:.9rem;color:var(--jv-gray-600);">
                <div><strong>Order:</strong> {{ ucfirst($order->status) }}</div>
                <div><strong>Total:</strong> {{ jv_format_money($order->total) }}</div>
                <div><strong>Invoice:</strong> {{ $order->invoice?->invoice_number ?? '-' }}</div>
            </div>
        </aside>
    </div>

    <div style="display:flex;gap:12px;justify-content:flex-end;margin-top:1.5rem;">
        <a href="{{ route('admin.orders.show', $order) }}" class="btn btn-outline-danger">Cancel</a>
        <button class="btn btn-primary btn-lg">Update Order</button>
    </div>
</form>
@endsection
