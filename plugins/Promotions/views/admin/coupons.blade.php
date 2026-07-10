@extends('themes.default::layouts.admin')

@section('title', 'Coupons')
@section('breadcrumbs')<a href="{{ route('admin.promotions.index') }}">Promotions</a> / <span class="current">Coupons</span>@endsection

@section('content')
<div style="display:flex;justify-content:space-between;align-items:flex-start;gap:16px;margin-bottom:22px;">
    <div>
        <h1 class="page-title">Coupons</h1>
        <p class="page-subtitle">Coupons are codes that activate coupon-type promotion rules.</p>
    </div>
    <a href="{{ route('admin.promotions.index') }}" class="btn btn-outline-primary">{{ jv_icon('badge-percent', '', 16) }} Promotions</a>
</div>

<form method="POST" action="{{ route('admin.promotions.coupons.store') }}" class="card" style="display:grid;gap:16px;margin-bottom:22px;">
    @csrf
    <h3 style="margin:0;">Create Coupon</h3>
    <div style="display:grid;grid-template-columns:1.3fr 1fr 1fr 1fr;gap:14px;">
        <div class="form-group"><label class="form-label">Promotion</label><select name="promotion_id" class="form-select" required>@foreach($promotions as $promotion)<option value="{{ $promotion->id }}">{{ $promotion->name }}</option>@endforeach</select></div>
        <div class="form-group"><label class="form-label">Code</label><input name="code" class="form-input" placeholder="KARIBU20" required></div>
        <div class="form-group"><label class="form-label">Max Uses</label><input type="number" name="max_uses" class="form-input" min="1"></div>
        <div class="form-group"><label class="form-label">Per Client</label><input type="number" name="max_uses_per_client" class="form-input" min="1" value="1"></div>
    </div>
    <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:14px;">
        <div class="form-group"><label class="form-label">Minimum Cart</label><input type="number" name="min_cart_total" class="form-input" min="0" step="0.01"></div>
        <div class="form-group"><label class="form-label">Starts At</label><input type="datetime-local" name="starts_at" class="form-input"></div>
        <div class="form-group"><label class="form-label">Ends At</label><input type="datetime-local" name="ends_at" class="form-input"></div>
        <div class="form-group"><label class="form-label">Status</label><select name="status" class="form-select"><option value="active">Active</option><option value="inactive">Inactive</option></select></div>
    </div>
    <div><button class="btn btn-primary">{{ jv_icon('plus', '', 16) }} Add Coupon</button></div>
</form>

<div class="card">
    <div class="table-responsive">
        <table class="table">
            <thead><tr><th>Code</th><th>Promotion</th><th>Status</th><th>Uses</th><th>Limits</th><th>Dates</th></tr></thead>
            <tbody>
                @forelse($coupons as $coupon)
                    <tr>
                        <td><strong>{{ $coupon->code }}</strong></td>
                        <td>{{ $coupon->promotion?->name ?? 'Deleted promotion' }}</td>
                        <td><span class="badge badge-{{ $coupon->status === 'active' ? 'success' : 'gray' }}">{{ ucfirst($coupon->status) }}</span></td>
                        <td>{{ $coupon->redemptions_count }}{{ $coupon->max_uses ? ' / ' . $coupon->max_uses : '' }}</td>
                        <td><small>Per client: {{ $coupon->max_uses_per_client ?: 'Unlimited' }}<br>Min cart: {{ $coupon->min_cart_total ? jv_format_money($coupon->min_cart_total) : 'None' }}</small></td>
                        <td><small>{{ $coupon->starts_at?->format('M d, Y') ?: 'Now' }} - {{ $coupon->ends_at?->format('M d, Y') ?: 'No end' }}</small></td>
                    </tr>
                @empty
                    <tr><td colspan="6"><div class="empty-state"><div class="empty-state-title">No coupons yet</div><div class="empty-state-desc">Create a coupon-type promotion first, then add a coupon code.</div></div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $coupons->links() }}
</div>
@endsection
