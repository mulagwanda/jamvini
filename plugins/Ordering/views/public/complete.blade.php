@extends('themes.default::layouts.frontend')

@section('title', 'Order Complete')

@section('content')
<section>
    <div class="container" style="text-align:center;padding:80px 0">
        <div style="font-size:64px;margin-bottom:16px">🎉</div>
        <h1>Order Placed Successfully!</h1>
        <p style="color:var(--gray-600);margin-bottom:24px">
            Your invoice <strong>#{{ $invoice->invoice_number }}</strong> has been created.
        </p>
        <p style="margin-bottom:32px">Total: <strong>{{ jv_format_money($invoice->total) }}</strong></p>
        <div style="display:flex;gap:16px;justify-content:center">
            <a href="/client/dashboard" class="btn btn-primary">👤 Client Portal</a>
            <a href="{{ route('order.domains') }}" class="btn btn-outline">🔍 Search Domains</a>
        </div>
    </div>
</section>
@endsection