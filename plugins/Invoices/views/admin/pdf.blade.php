<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Invoice #{{ $invoice->invoice_number }}</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap');
        
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; font-size: 12px; color: #1e293b; padding: 40px; }
        
        .header { display: flex; justify-content: space-between; margin-bottom: 40px; border-bottom: 2px solid #6C5CE7; padding-bottom: 20px; }
        .company-info h1 { font-size: 22px; color: #6C5CE7; margin-bottom: 4px; }
        .company-info p { font-size: 11px; color: #64748b; }
        .invoice-info { text-align: right; }
        .invoice-info h2 { font-size: 28px; color: #6C5CE7; margin-bottom: 4px; }
        .invoice-info .status { display: inline-block; padding: 4px 12px; border-radius: 20px; font-size: 11px; font-weight: 600; }
        .status-paid { background: #dcfce7; color: #16a34a; }
        .status-sent { background: #fef3c7; color: #d97706; }
        .status-overdue { background: #fee2e2; color: #dc2626; }
        
        .parties { display: flex; justify-content: space-between; margin-bottom: 32px; }
        .party h3 { font-size: 13px; font-weight: 600; margin-bottom: 8px; color: #6C5CE7; }
        .party p { font-size: 11px; color: #475569; line-height: 1.6; }
        
        table { width: 100%; border-collapse: collapse; margin-bottom: 32px; }
        th { background: #f8fafc; padding: 10px 12px; text-align: left; font-size: 10px; font-weight: 600; text-transform: uppercase; color: #64748b; border-bottom: 2px solid #e2e8f0; }
        td { padding: 10px 12px; border-bottom: 1px solid #e2e8f0; font-size: 11px; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        
        .totals { display: flex; justify-content: flex-end; }
        .totals table { width: 280px; }
        .totals td { border: none; padding: 4px 12px; }
        .totals .total-row { font-size: 16px; font-weight: 700; color: #6C5CE7; }
        
        .footer { margin-top: 40px; text-align: center; font-size: 10px; color: #94a3b8; border-top: 1px solid #e2e8f0; padding-top: 16px; }
        .payment-info { margin-top: 24px; padding: 16px; background: #f8fafc; border-radius: 8px; }
        .payment-info h4 { font-size: 11px; color: #64748b; margin-bottom: 8px; }
        .payment-info p { font-size: 10px; color: #475569; }
    </style>
</head>
<body>
    {{-- Header --}}
    <div class="header">
        <div class="company-info">
            <h1>{{ $company['name'] }}</h1>
            <p>{{ $company['address'] }}</p>
            @if($company['phone'])<p>📞 {{ $company['phone'] }}</p>@endif
            <p>✉️ {{ $company['email'] }}</p>
            @if($company['tin'])<p>TIN: {{ $company['tin'] }}</p>@endif
            @if(!empty($company['vrn']))<p>VRN: {{ $company['vrn'] }}</p>@endif
        </div>
        <div class="invoice-info">
            <h2>INVOICE</h2>
            <p>#{{ $invoice->invoice_number }}</p>
            <span class="status status-{{ $invoice->status }}">
                {{ strtoupper($invoice->status) }}
            </span>
        </div>
    </div>
    
    {{-- Parties --}}
    <div class="parties">
        <div class="party">
            <h3>BILL TO</h3>
            <p>
                <strong>{{ $invoice->client->full_name }}</strong><br>
                @if($invoice->client->company_name){{ $invoice->client->company_name }}<br>@endif
                {{ $invoice->client->email }}<br>
                @if($invoice->client->phone){{ $invoice->client->phone }}<br>@endif
                @if($invoice->client->address){{ $invoice->client->address }}<br>@endif
                @if($invoice->client->tin_number)TIN: {{ $invoice->client->tin_number }}@endif
            </p>
        </div>
        <div class="party">
            <h3>DATES</h3>
            <p>
                Invoice Date: {{ jv_format_date($invoice->created_at) }}<br>
                Due Date: {{ $invoice->due_date ? jv_format_date($invoice->due_date) : 'N/A' }}<br>
                @if($invoice->paid_at)
                    Paid Date: {{ jv_format_date($invoice->paid_at) }}
                @endif
            </p>
        </div>
    </div>
    
    {{-- Line Items --}}
    <table>
        <thead>
            <tr>
                <th style="width: 40%;">Description</th>
                <th class="text-center" style="width: 10%;">Qty</th>
                <th class="text-right" style="width: 20%;">Unit Price</th>
                <th class="text-center" style="width: 10%;">Tax</th>
                <th class="text-right" style="width: 20%;">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoice->items as $item)
            <tr>
                <td>
                    {{ $item->description }}
                    @if($item->domain)<br><span style="color:#64748b;">Domain: {{ $item->domain }}</span>@endif
                    @if($item->billing_cycle)<br><span style="color:#64748b;">Cycle: {{ ucfirst(str_replace('_', ' ', $item->billing_cycle)) }}</span>@endif
                    @if($item->period_start || $item->period_end)<br><span style="color:#64748b;">Period: {{ $item->period_start?->format('M d, Y') ?? '-' }} - {{ $item->period_end?->format('M d, Y') ?? '-' }}</span>@endif
                </td>
                <td class="text-center">{{ $item->quantity }}</td>
                <td class="text-right">{{ jv_format_money($item->unit_price) }}</td>
                <td class="text-center">{{ $item->tax_rate }}%</td>
                <td class="text-right"><strong>{{ jv_format_money($item->total) }}</strong></td>
            </tr>
            @endforeach
        </tbody>
    </table>
    
    {{-- Totals --}}
    <div class="totals">
        <table>
            <tr>
                <td>Subtotal</td>
                <td class="text-right">{{ jv_format_money($invoice->subtotal) }}</td>
            </tr>
            @if(($invoice->discount ?? 0) > 0)
            <tr>
                <td>Discount</td>
                <td class="text-right">-{{ jv_format_money($invoice->discount) }}</td>
            </tr>
            @endif
            <tr>
                <td>{{ jv_tax_label() }} ({{ rtrim(rtrim(number_format(jv_tax_rate(), 2), '0'), '.') }}%)</td>
                <td class="text-right">{{ jv_format_money($invoice->tax_amount) }}</td>
            </tr>
            <tr class="total-row">
                <td>TOTAL</td>
                <td class="text-right">{{ jv_format_money($invoice->total) }}</td>
            </tr>
        </table>
    </div>
    
    {{-- Payment Info --}}
    <div class="payment-info">
        <h4>PAYMENT INFORMATION</h4>
        <p>{{ $invoice->payment_terms ?: \App\Models\Setting::get('invoice_footer', 'Payment via M-Pesa, Tigo Pesa, Airtel Money or Bank Transfer') }}</p>
    </div>
    
    {{-- Notes --}}
    @if($invoice->notes)
    <div style="margin-top: 16px; font-size: 11px; color: #64748b;">
        <strong>Notes:</strong> {{ $invoice->notes }}
    </div>
    @endif
    
    {{-- Footer --}}
    <div class="footer">
        {{ \App\Models\Setting::get('invoice_notes', 'Thank you for your business!') }}<br>
        This is a computer-generated invoice and does not require a signature.
    </div>
</body>
</html>
