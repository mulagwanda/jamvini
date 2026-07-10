@extends('themes.default::layouts.frontend')

@section('title', 'Checkout')

@section('content')
@php
    $taxRate = $calculation['tax_rate'] ?? jv_tax_rate();
    $taxAmount = $calculation['tax_amount'] ?? ($total * ($taxRate / 100));
    $grandTotal = $calculation['grand_total'] ?? ($total + $taxAmount);
    $discountTotal = $calculation['discount_total'] ?? 0;
@endphp
<section class="page-hero">
    <div class="container">
        <div class="breadcrumb"><a href="/">Home</a> / <a href="{{ route('order.cart') }}">Cart</a> / Checkout</div>
        <h1>Checkout</h1>
        <p>Complete your order in just a few steps.</p>
    </div>
</section>

<main class="container" style="padding: 2rem 0;">
    <div class="checkout-wrap" style="display: grid; grid-template-columns: 1.5fr 1fr; gap: 2rem;">
        {{-- Left: Checkout Form --}}
        <div class="checkout-main" style="background:#fff;border-radius:18px;box-shadow:0 2px 8px rgba(0,0,0,.04);padding:2rem;">
            
            {{-- Steps --}}
            <div style="display:flex;justify-content:center;gap:1rem;margin-bottom:2rem;flex-wrap:wrap;">
                <div class="step active" style="display:flex;align-items:center;gap:.5rem;color:var(--primary);">
                    <div style="width:32px;height:32px;border-radius:50%;background:var(--primary);color:#fff;display:grid;place-items:center;font-weight:600;">1</div>
                    <span style="font-weight:600;">Your Details</span>
                </div>
                <div style="width:40px;height:2px;background:var(--gray-200);margin-top:16px;"></div>
                <div class="step" style="display:flex;align-items:center;gap:.5rem;color:var(--gray-500);">
                    <div style="width:32px;height:32px;border-radius:50%;background:var(--gray-200);display:grid;place-items:center;font-weight:600;">2</div>
                    <span style="font-weight:600;">Payment</span>
                </div>
                <div style="width:40px;height:2px;background:var(--gray-200);margin-top:16px;"></div>
                <div class="step" style="display:flex;align-items:center;gap:.5rem;color:var(--gray-500);">
                    <div style="width:32px;height:32px;border-radius:50%;background:var(--gray-200);display:grid;place-items:center;font-weight:600;">3</div>
                    <span style="font-weight:600;">Confirm</span>
                </div>
            </div>

            @auth
            <div style="background:#dcfce7;border-radius:10px;padding:16px;margin-bottom:1.5rem;display:flex;align-items:center;gap:12px;">
                <span style="font-size:1.5rem;">👋</span>
                <div>
                    <strong>Welcome back, {{ auth()->user()->full_name ?? auth()->user()->name }}!</strong>
                    <p style="color:#16a34a;font-size:.85rem;margin:0;">Your details are pre-filled from your account.</p>
                </div>
            </div>
            @endif

            <form action="{{ route('order.place') }}" method="POST">
                @csrf

                <h3 style="margin-bottom:1rem;">📍 Your Information</h3>
                
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                    <div class="form-group">
                        <label class="form-label">First Name *</label>
                        <input type="text" name="first_name" class="form-input" value="{{ old('first_name', auth()->user()->first_name ?? '') }}" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Last Name *</label>
                        <input type="text" name="last_name" class="form-input" value="{{ old('last_name', auth()->user()->last_name ?? '') }}" required>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Email Address *</label>
                    <input type="email" name="email" class="form-input" value="{{ old('email', auth()->user()->email ?? '') }}" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Company (Optional)</label>
                    <input type="text" name="company_name" class="form-input" value="{{ old('company_name', auth()->user()->company_name ?? '') }}">
                </div>

                <div style="display:grid;grid-template-columns:140px 1fr;gap:12px;">
                    <div class="form-group">
                        <label class="form-label">Country</label>
                        <select name="country_code" id="countryCode" class="form-select" onchange="updatePhonePrefix()">
                            <option value="255" selected>🇹🇿 +255</option>
                            <option value="254">🇰🇪 +254</option>
                            <option value="256">🇺🇬 +256</option>
                            <option value="250">🇷🇼 +250</option>
                            <option value="1">🇺🇸 +1</option>
                            <option value="44">🇬🇧 +44</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Phone Number *</label>
                        <input type="tel" name="phone" id="phoneNumber" class="form-input" value="{{ old('phone', auth()->user()->phone ?? '') }}" placeholder="712345678" required>
                        <div class="form-hint" id="phoneDisplay" style="font-size:.8rem;color:var(--gray-500);">+255 712345678</div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Address</label>
                    <textarea name="address" class="form-textarea" rows="2" placeholder="Street, area...">{{ old('address', auth()->user()->address ?? '') }}</textarea>
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                    <div class="form-group">
                        <label class="form-label">City</label>
                        <input type="text" name="city" class="form-input" value="{{ old('city', auth()->user()->city ?? '') }}" placeholder="Dar es Salaam">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Country</label>
                        <input type="text" name="country" class="form-input" value="{{ old('country', auth()->user()->country ?? 'Tanzania') }}">
                    </div>
                </div>

                @if(!auth()->check())
                <h3 style="margin:1.5rem 0 1rem;">🔐 Create Account Password</h3>
                <p style="color:var(--gray-600);font-size:.9rem;margin-bottom:1rem;">Create a password to access your client portal and manage services.</p>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                    <div class="form-group">
                        <label class="form-label">Password *</label>
                        <input type="password" name="password" class="form-input" required minlength="8">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Confirm Password *</label>
                        <input type="password" name="password_confirmation" class="form-input" required>
                    </div>
                </div>
                @endif

                {{-- Payment Method --}}
                <h3 style="margin:1.5rem 0 1rem;">{{ jv_icon('credit-card', '', 20) }} Payment Method</h3>
                <div style="display:grid;gap:12px;">
                    @forelse($paymentGateways as $gateway)
                    <label class="payment-option {{ $loop->first ? 'selected' : '' }}" style="border:2px solid {{ $loop->first ? 'var(--primary)' : 'var(--gray-200)' }};border-radius:12px;padding:1rem;cursor:pointer;display:flex;align-items:center;gap:1rem;background:{{ $loop->first ? 'rgba(108,92,231,.04)' : '#fff' }};">
                        <input type="radio" name="payment_method" value="{{ $gateway->slug() }}" {{ $loop->first ? 'checked' : '' }} style="display:none;" onchange="selectPayment(this)">
                        <div style="width:48px;height:48px;border-radius:12px;background:var(--gray-100);display:grid;place-items:center;font-size:1.5rem;color:var(--primary);">{{ jv_icon($gateway->icon(), '', 24) }}</div>
                        <div style="flex:1;"><div style="font-weight:600;">{{ $gateway->name() }}</div><div style="font-size:.85rem;color:var(--gray-600);">{{ $gateway->description() }}</div></div>
                        <div style="font-size:.85rem;color:var(--gray-500);">{{ ucwords(str_replace('_', ' ', $gateway->type())) }}</div>
                    </label>
                    @empty
                    <div style="padding:1rem;border:1px solid var(--gray-200);border-radius:12px;color:var(--gray-600);">
                        No payment methods are currently available. Please contact support.
                    </div>
                    @endforelse
                </div>

                <div class="form-group" style="margin-top:1rem;">
                    <label class="form-label">Order Notes (Optional)</label>
                    <textarea name="notes" class="form-textarea" rows="2" placeholder="Any special instructions...">{{ old('notes') }}</textarea>
                </div>

                <div style="margin-top:1.5rem;">
                    <label class="checkbox-group" style="margin-bottom:8px;">
                        <input type="checkbox" required>
                        <span>I agree to the <a href="#" style="color:var(--primary);">Terms of Service</a></span>
                    </label>
                </div>

                <button type="submit" class="btn btn-primary btn-lg btn-block" style="margin-top:1rem;width:100%;padding:1rem;" {{ empty($paymentGateways) ? 'disabled' : '' }}>
                    {{ jv_icon('check-circle', '', 18) }} Place Order
                </button>
            </form>
        </div>

        {{-- Right: Order Summary --}}
        <aside class="order-summary" style="background:#fff;border-radius:18px;box-shadow:0 2px 8px rgba(0,0,0,.04);padding:1.5rem;height:fit-content;position:sticky;top:90px;">
            <h3 style="margin-bottom:1rem;">📋 Order Summary</h3>
            @foreach($items as $item)
            <div style="display:flex;justify-content:space-between;padding:.75rem 0;border-bottom:1px solid var(--gray-100);font-size:.95rem;">
                <span>{{ $item['name'] }}</span>
                <strong>{{ jv_format_money($item['price']) }}</strong>
            </div>
            @endforeach
            <div style="display:flex;justify-content:space-between;padding:.75rem 0;border-bottom:1px solid var(--gray-100);">
                <span>Subtotal</span>
                <strong>{{ jv_format_money($calculation['subtotal'] ?? $total) }}</strong>
            </div>
            @if($discountTotal > 0)
                @foreach(($calculation['discounts'] ?? []) as $discount)
                    <div style="display:flex;justify-content:space-between;padding:.75rem 0;border-bottom:1px solid var(--gray-100);color:#15803d;">
                        <span>{{ $discount['label'] }}</span>
                        <strong>-{{ jv_format_money($discount['amount']) }}</strong>
                    </div>
                @endforeach
            @endif
            <div style="display:flex;justify-content:space-between;padding:.75rem 0;border-bottom:1px solid var(--gray-100);">
                <span>{{ jv_tax_label() }} ({{ rtrim(rtrim(number_format($taxRate, 2), '0'), '.') }}%)</span>
                <strong>{{ jv_format_money($taxAmount) }}</strong>
            </div>
            <div style="display:flex;justify-content:space-between;padding:1rem 0 0;margin-top:.5rem;border-top:2px solid var(--gray-200);font-size:1.1rem;font-weight:700;">
                <span style="color:var(--dark);">Total</span>
                <span style="color:var(--primary);">{{ jv_format_money($grandTotal) }}</span>
            </div>
            <p style="text-align:center;margin-top:1rem;">
                <a href="{{ route('order.cart') }}" style="color:var(--primary);font-size:.9rem;">← Edit Cart</a>
            </p>
        </aside>
    </div>
</main>
@endsection

@push('scripts')
<script>
function updatePhonePrefix() {
    const code = document.getElementById('countryCode').value;
    const phone = document.getElementById('phoneNumber').value.replace(/\D/g, '');
    document.getElementById('phoneDisplay').textContent = '+' + code + ' ' + (phone || '712345678');
    document.getElementById('phoneNumber').setAttribute('data-code', code);
}

function selectPayment(radio) {
    document.querySelectorAll('.payment-option').forEach(opt => {
        opt.style.border = '2px solid var(--gray-200)';
        opt.style.background = '#fff';
    });
    const option = radio.closest('.payment-option');
    option.style.border = '2px solid var(--primary)';
    option.style.background = 'rgba(108,92,231,.04)';
}

// Auto-update phone display
document.getElementById('phoneNumber').addEventListener('input', function() {
    const code = document.getElementById('countryCode').value;
    const clean = this.value.replace(/\D/g, '');
    document.getElementById('phoneDisplay').textContent = '+' + code + ' ' + (clean || '712345678');
});

// Select payment method on label click
document.querySelectorAll('.payment-option').forEach(opt => {
    opt.addEventListener('click', function() {
        const radio = this.querySelector('input[type="radio"]');
        radio.checked = true;
        selectPayment(radio);
    });
});
</script>
@endpush
