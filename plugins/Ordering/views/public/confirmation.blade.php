@extends('themes.default::layouts.frontend')

@section('title', 'Order Confirmed')

@section('content')
<section style="padding: 5rem 0;">
    <div class="container" style="text-align: center; max-width: 600px;">
        <div style="font-size: 64px; margin-bottom: 16px;">🎉</div>
        <h1>Order Placed Successfully!</h1>
        <p style="color: var(--gray-600); font-size: 1.05rem; margin-bottom: 8px;">
            Your order <strong>#{{ $order->order_number }}</strong> has been received.
        </p>
        <p style="color: var(--gray-500); margin-bottom: 32px;">
            Status: <span class="pill pill-{{ $order->status === 'accepted' ? 'ok' : 'warn' }}">{{ ucfirst($order->status) }}</span>
            @if($order->invoice)
                · Invoice: <strong>#{{ $order->invoice->invoice_number }}</strong>
            @endif
        </p>

        <div style="background: #fff; border-radius: 16px; padding: 24px; box-shadow: 0 2px 8px rgba(0,0,0,.04); margin-bottom: 24px; text-align: left;">
    <h4 style="margin-bottom: 12px;">📋 Order Summary</h4>
    @foreach($order->items as $item)
    <div style="display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid var(--gray-100); font-size: .9rem;">
        <span>
            {{ $item->description }}
            <span class="pill pill-{{ $item->type === 'domain' ? 'info' : ($item->type === 'domain_transfer' ? 'warn' : 'ok') }}" style="margin-left: 8px; font-size: .7rem;">
                {{ $item->type === 'domain_transfer' ? 'Transfer' : ucfirst($item->type) }}
            </span>
        </span>
        <strong>{{ jv_format_money($item->total) }}</strong>
    </div>
    @endforeach
    <div style="display: flex; justify-content: space-between; padding: 12px 0; margin-top: 8px; border-top: 2px solid var(--gray-200); font-weight: 700; font-size: 1.1rem;">
        <span>Total</span>
        <span style="color: var(--primary);">{{ jv_format_money($order->total) }}</span>
    </div>
</div>

<div style="display: flex; gap: 12px; justify-content: center; flex-wrap: wrap;">
    @auth
        <a href="/client/dashboard" class="btn btn-primary">👤 Client Portal</a>
    @else
        <a href="/login" class="btn btn-primary">🔐 Sign In to Manage</a>
    @endif

    <a href="/client/invoices" class="btn btn-outline">📄 View Invoices</a>
    <a href="/client/services" class="btn btn-outline">📦 My Services</a>

    @if($order->invoice)
        <a href="{{ route('admin.invoices.show', $order->invoice) }}" class="btn btn-outline">🧾 View Invoice #{{ $order->invoice->invoice_number }}</a>
    @endif
</div>
    </div>
</section>
@endsection