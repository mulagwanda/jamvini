@extends('themes.default::layouts.frontend')

@section('title', 'Shopping Cart')

@section('content')
<section class="page-hero">
    <div class="container">
        <div class="breadcrumb"><a href="/">Home</a> / Cart</div>
        <h1>Shopping Cart</h1>
    </div>
</section>

<section>
    <div class="container">
@if(count($items) > 0)
        @if($errors->any())
            <div style="background:#fef2f2;border:1px solid #fecaca;color:#991b1b;border-radius:12px;padding:12px 16px;margin-bottom:16px;">
                {{ $errors->first() }}
            </div>
        @endif
        @if(session('success'))
            <div style="background:#ecfdf5;border:1px solid #bbf7d0;color:#166534;border-radius:12px;padding:12px 16px;margin-bottom:16px;">
                {{ session('success') }}
            </div>
        @endif
        <div class="card" style="background:#fff;border-radius:16px;padding:0;box-shadow:var(--shadow-sm);overflow:hidden;">
            <table style="width:100%;border-collapse:collapse;">
                <thead>
                    <tr style="border-bottom:2px solid var(--gray-200);">
                        <th style="padding:16px;text-align:left;">Item</th>
                        <th style="padding:16px;text-align:right;">Price</th>
                        <th style="padding:16px;text-align:right;"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($items as $item)
                    <tr style="border-bottom:1px solid var(--gray-100);">
                        <td style="padding:16px;">
                            <strong>{{ $item['name'] }}</strong>
                            <span class="pill pill-{{ $item['type'] === 'domain' ? 'info' : ($item['type'] === 'domain_transfer' ? 'warn' : ($item['type'] === 'domain_addon' ? 'mute' : 'ok')) }}" style="margin-left:8px;">
                                {{ $item['type'] === 'domain_transfer' ? 'Transfer' : ($item['type'] === 'domain_addon' ? 'Addon' : ucfirst($item['type'])) }}
                            </span>
                            @if(!empty($item['details']))
                                <div style="font-size:0.82rem;color:var(--gray-500);margin-top:6px;line-height:1.5;">
                                    @if(!empty($item['details']['domain']))
                                        <div>🌐 Domain: <strong>{{ $item['details']['domain'] }}</strong></div>
                                    @endif
                                    @if(!empty($item['details']['years']))
                                        <div>📅 Period: <strong>{{ $item['details']['years'] }} year(s)</strong></div>
                                    @endif
                                    @if(!empty($item['details']['billing_cycle']))
                                        <div>{{ jv_icon('calendar-days', '', 13) }} Billing: <strong>{{ ucfirst(str_replace('_', ' ', $item['details']['billing_cycle'])) }}</strong></div>
                                    @endif
                                    @if(($item['details']['setup_fee'] ?? 0) > 0)
                                        <div>{{ jv_icon('info', '', 13) }} Setup fee: <strong>{{ jv_format_money($item['details']['setup_fee']) }}</strong></div>
                                    @endif
                                    @if(($item['details']['renewal_price'] ?? 0) > 0)
                                        <div>{{ jv_icon('refresh-cw', '', 13) }} Renewal: <strong>{{ jv_format_money($item['details']['renewal_price']) }}</strong> / {{ ucfirst(str_replace('_', ' ', $item['details']['billing_cycle'] ?? 'cycle')) }}</div>
                                    @endif
                                    @if(!empty($item['details']['cycle_savings']))
                                        <div style="color:#15803d;">{{ jv_icon('badge-dollar-sign', '', 13) }} {{ $item['details']['cycle_savings'] }}</div>
                                    @endif
                                    @if(!empty($item['details']['nameservers']))
                                        <div>🔧 Nameservers: <strong>{{ implode(', ', array_filter($item['details']['nameservers'])) ?: 'Default' }}</strong></div>
                                    @endif
                                    @if(!empty($item['details']['epp_code']))
                                        <div>🔑 EPP Code: <strong>{{ $item['details']['epp_code'] }}</strong></div>
                                    @endif
                                    @if(!empty($item['details']['dns_management']))
                                        <div>✅ Includes DNS Management</div>
                                    @endif
                                    @if(!empty($item['details']['configurable_options']))
                                        @foreach($item['details']['configurable_options'] as $option)
                                            <div>{{ jv_icon('sliders-horizontal', '', 13) }} {{ $option['name'] ?? 'Option' }}: <strong>{{ is_bool($option['value'] ?? null) ? (($option['value'] ?? false) ? 'Yes' : 'No') : ($option['value'] ?? '') }}</strong>@if(($option['price'] ?? 0) > 0) (+{{ jv_format_money($option['price']) }})@endif</div>
                                        @endforeach
                                    @endif
                                    @if(!empty($item['details']['addons']))
                                        @foreach($item['details']['addons'] as $addon)
                                            <div>{{ jv_icon('plus-circle', '', 13) }} Addon: <strong>{{ $addon['name'] ?? 'Addon' }}</strong>@if(($addon['price'] ?? 0) > 0) (+{{ jv_format_money($addon['price']) }})@endif</div>
                                        @endforeach
                                    @endif
                                    @if(!empty($item['details']['custom_fields']))
                                        @foreach($item['details']['custom_fields'] as $field)
                                            <div>{{ jv_icon('clipboard-list', '', 13) }} {{ $field['label'] ?? 'Field' }}: <strong>{{ $field['value'] ?? '' }}</strong></div>
                                        @endforeach
                                    @endif
                                </div>
                            @endif
                        </td>
                        <td style="text-align:right;padding:16px;">
                            @if(!empty($item['details']['available_cycles']) && count($item['details']['available_cycles']) > 1)
                                <form action="{{ route('order.cart.update') }}" method="POST" style="display:grid;gap:6px;justify-items:end;margin-bottom:8px;">
                                    @csrf
                                    <input type="hidden" name="id" value="{{ $item['id'] }}">
                                    <select name="billing_cycle" class="form-select" onchange="this.form.submit()" style="min-width:150px;">
                                        @foreach($item['details']['available_cycles'] as $cycle)
                                            <option value="{{ $cycle }}" {{ ($item['details']['billing_cycle'] ?? '') === $cycle ? 'selected' : '' }}>{{ ucfirst(str_replace('_', ' ', $cycle)) }}</option>
                                        @endforeach
                                    </select>
                                </form>
                            @endif
                            <strong>{{ jv_format_money($item['price']) }}</strong>
                        </td>
                        <td style="text-align:right;padding:16px;">
                            <form action="{{ route('order.cart.remove') }}" method="POST">
                                @csrf
                                <input type="hidden" name="id" value="{{ $item['id'] }}">
                                <button class="btn btn-outline btn-sm" style="color:#ef4444;border-color:#ef4444;">✕</button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            <div style="display:grid;grid-template-columns:1fr auto;gap:14px;align-items:start;padding:18px 24px;background:#fff;border-top:1px solid var(--gray-100);">
                <form action="{{ route('order.cart.coupon') }}" method="POST" style="display:flex;gap:8px;flex-wrap:wrap;">
                    @csrf
                    <input name="code" class="form-input" placeholder="Coupon code" style="max-width:220px;text-transform:uppercase;">
                    <button class="btn btn-outline">{{ jv_icon('ticket-percent', '', 16) }} Apply Coupon</button>
                </form>
                @if(!empty($coupons))
                    <div style="display:flex;gap:8px;flex-wrap:wrap;justify-content:flex-end;">
                        @foreach($coupons as $coupon)
                            <form action="{{ route('order.cart.coupon.remove') }}" method="POST">
                                @csrf
                                <input type="hidden" name="code" value="{{ $coupon }}">
                                <button class="badge badge-success" style="border:0;cursor:pointer;">{{ $coupon }} ×</button>
                            </form>
                        @endforeach
                    </div>
                @endif
            </div>
            <div style="display:flex;justify-content:space-between;align-items:center;padding:20px 24px;background:#f8fafc;border-top:2px solid var(--gray-200);">
                <div>
                    <div style="display:grid;gap:4px;">
                        <span>Subtotal: <strong>{{ jv_format_money($calculation['subtotal'] ?? $total) }}</strong></span>
                        @if(($calculation['discount_total'] ?? 0) > 0)
                            <span style="color:#15803d;">Discount: <strong>-{{ jv_format_money($calculation['discount_total']) }}</strong></span>
                            @foreach(($calculation['discounts'] ?? []) as $discount)
                                <small style="color:#15803d;">{{ $discount['label'] }} saved {{ jv_format_money($discount['amount']) }}</small>
                            @endforeach
                        @endif
                        <span style="font-size:1.2rem;font-weight:700;">Total before tax: {{ jv_format_money($calculation['taxable_total'] ?? $total) }}</span>
                        <span style="color:var(--gray-600);">+ {{ jv_tax_label() }} at checkout</span>
                    </div>
                </div>
                <div style="display:flex;gap:10px;flex-wrap:wrap;justify-content:flex-end;">
                    <a href="{{ route('order.services.catalog') }}" class="btn btn-outline">Add More Services</a>
                    <a href="{{ route('order.checkout') }}" class="btn btn-primary btn-lg">Proceed to Checkout →</a>
                </div>
            </div>
        </div>
        @else
        <div style="text-align:center;padding:60px;">
            <div style="font-size:64px;">🛒</div>
            <h2>Your cart is empty</h2>
            <p style="color:var(--gray-600);margin-bottom:24px;">
                Browse our <a href="{{ route('order.domains') }}">domains</a> or 
                <a href="{{ route('order.services.catalog') }}">services</a>
            </p>
        </div>
        @endif

{{-- Upsell Services --}}
@php
    $cartDomains = collect($items)->filter(fn($i) => in_array($i['type'], ['domain', 'domain_transfer']));
    $cartModules = collect($items)->map(fn($i) => $i['type'])->unique();
    
    $hasHosting = $cartModules->contains('hosting');
    $hasSsl = $cartModules->contains('ssl');
    $hasEmail = $cartModules->contains('email');
    $hasDomain = $cartDomains->isNotEmpty();
    
    $upsellServices = collect();
    
    if (!$hasHosting && class_exists(\Plugins\Services\src\Models\Service::class)) {
        $hostingService = \Plugins\Services\src\Models\Service::where('is_active', true)
            ->whereHas('group', fn($q) => $q->where('module', 'hosting'))
            ->orderBy('amount')
            ->first();
        if ($hostingService) $upsellServices->push($hostingService);
    }
    
    if (!$hasSsl && class_exists(\Plugins\Services\src\Models\Service::class)) {
        $sslService = \Plugins\Services\src\Models\Service::where('is_active', true)
            ->whereHas('group', fn($q) => $q->where('module', 'ssl'))
            ->first();
        if ($sslService) $upsellServices->push($sslService);
    }
    
    if (!$hasEmail && class_exists(\Plugins\Services\src\Models\Service::class)) {
        $emailService = \Plugins\Services\src\Models\Service::where('is_active', true)
            ->whereHas('group', fn($q) => $q->where('module', 'email'))
            ->first();
        if ($emailService) $upsellServices->push($emailService);
    }
@endphp

@if($upsellServices->isNotEmpty())
<div style="margin-top: 3rem;">
    <div class="section-head" style="text-align: center; margin-bottom: 2rem;">
        <span class="eyebrow">Add to Your Order</span>
        <h2>Complete your setup</h2>
        <p>Add these services to get the most out of your purchase</p>
    </div>
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem;">
        @foreach($upsellServices as $svc)
        @php 
            $price = $svc->pricing['monthly'] ?? $svc->pricing['annually'] ?? $svc->amount ?? 0;
            $icon = match($svc->group?->module) {
                'hosting' => '🖥️', 'ssl' => '🔒', 'email' => '📧',
                default => '📦'
            };
        @endphp
        <div class="feature-card" style="background:#fff;text-align:center;padding:1.5rem;border:2px dashed #e5e7eb;border-radius:16px;transition:all .2s;">
            <div style="font-size:2rem;margin-bottom:8px;">{{ $icon }}</div>
            <h4 style="margin-bottom:4px;">{{ $svc->name }}</h4>
            <p style="color:var(--gray-500);font-size:.85rem;margin-bottom:12px;">{{ $svc->group?->name ?? '' }}</p>
            <div style="font-size:1.3rem;font-weight:700;color:var(--primary);margin-bottom:4px;">
                {{ jv_format_money($price) }}
            </div>
            <small style="color:var(--gray-500);">/{{ $svc->billing_cycle ?? 'mo' }}</small>
            <button class="btn btn-outline btn-sm btn-block add-upsell" style="margin-top:12px;"
                    data-name="{{ $svc->name }}" data-price="{{ $price }}"
                    data-type="{{ $svc->group?->module ?? 'hosting' }}"
                    data-details="{{ json_encode(['service_id' => $svc->id, 'billing_cycle' => $svc->billing_cycle ?? 'monthly']) }}">
                + Add to Cart
            </button>
        </div>
        @endforeach
    </div>
</div>
@endif

    </div>




</section>
@endsection
@push('scripts')
<script>
document.querySelectorAll('.add-upsell').forEach(btn => {
    btn.addEventListener('click', async function() {
        let details = JSON.parse(this.dataset.details || '{}');
        if (this.dataset.type === 'hosting') {
            const domainPrompt = await Swal.fire({
                title: 'Domain for this hosting',
                text: 'Enter the domain that will be hosted on this account.',
                input: 'text',
                inputPlaceholder: 'example.com',
                confirmButtonText: 'Add to Cart',
                showCancelButton: true,
                confirmButtonColor: '#6C5CE7',
                inputValidator: (value) => {
                    const domain = (value || '').trim().toLowerCase();
                    if (!domain) return 'Domain name is required for hosting.';
                    if (!/^[a-z0-9][a-z0-9-]*(\.[a-z0-9][a-z0-9-]*)+$/i.test(domain)) {
                        return 'Enter a complete domain, for example example.com';
                    }
                    return null;
                }
            });

            if (!domainPrompt.isConfirmed) return;
            details.domain = domainPrompt.value.trim().toLowerCase();
        }

        const data = {
            type: this.dataset.type,
            name: this.dataset.name,
            price: parseFloat(this.dataset.price),
            details: details
        };
        const res = await fetch('{{ route('order.cart.add') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify(data)
        });
        if (res.ok) {
            location.reload();
            return;
        }
        const error = await res.json().catch(() => ({}));
        Swal.fire('Could not add service', error.message || 'Please choose the service from the services page.', 'error');
    });
});
</script>
@endpush
