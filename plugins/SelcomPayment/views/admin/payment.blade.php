@extends('themes.default::layouts.frontend')

@section('title', 'Selcom Payment')

@section('content')
<section style="padding:4rem 0;">
    <div class="container" style="max-width:720px;">
        <div style="background:#fff;border-radius:16px;box-shadow:0 2px 8px rgba(0,0,0,.05);padding:2rem;text-align:center;">
            <div style="font-size:48px;color:var(--primary);margin-bottom:1rem;">{{ jv_icon('credit-card', '', 48) }}</div>
            <h1>Selcom Payment Request</h1>
            <p style="color:var(--gray-600);margin:10px 0 24px;">
                Invoice <strong>#{{ $invoice->invoice_number }}</strong> for <strong>{{ jv_format_money($invoice->remaining_amount) }}</strong>
            </p>
            <div style="text-align:left;background:#f8fafc;border-radius:12px;padding:1rem;margin-bottom:1.5rem;">
                <div><strong>Reference:</strong> {{ $paymentData['order_id'] ?? 'Pending' }}</div>
                <div><strong>Status:</strong> {{ ucfirst($paymentData['status'] ?? 'pending') }}</div>
                @if(!empty($paymentData['test_mode']))
                    <div><strong>Mode:</strong> Test</div>
                @endif
            </div>
            <p style="color:var(--gray-600);font-size:.92rem;">
                Live redirect/STK push details will use your Selcom merchant API configuration.
            </p>
        </div>
    </div>
</section>
@endsection
