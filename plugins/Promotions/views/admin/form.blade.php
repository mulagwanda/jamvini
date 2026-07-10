@extends('themes.default::layouts.admin')

@section('title', $promotion->exists ? 'Edit Promotion' : 'Create Promotion')
@section('breadcrumbs')<a href="{{ route('admin.promotions.index') }}">Promotions</a> / <span class="current">{{ $promotion->exists ? 'Edit' : 'Create' }}</span>@endsection

@section('content')
<div style="display:flex;justify-content:space-between;align-items:flex-start;gap:16px;margin-bottom:22px;">
    <div>
        <h1 class="page-title">{{ $promotion->exists ? 'Edit Promotion' : 'Create Promotion' }}</h1>
        <p class="page-subtitle">Discount rules are separate from coupons. A coupon can activate a promotion rule.</p>
    </div>
    <a href="{{ route('admin.promotions.index') }}" class="btn btn-outline-primary">{{ jv_icon('arrow-left', '', 16) }} Back</a>
</div>

<form method="POST" action="{{ $promotion->exists ? route('admin.promotions.update', $promotion) : route('admin.promotions.store') }}" class="card" style="display:grid;gap:22px;">
    @csrf
    @if($promotion->exists) @method('PUT') @endif

    <div style="display:grid;grid-template-columns:2fr 1fr;gap:16px;">
        <div class="form-group"><label class="form-label">Name *</label><input name="name" class="form-input" value="{{ old('name', $promotion->name) }}" required></div>
        <div class="form-group"><label class="form-label">Slug</label><input name="slug" class="form-input" value="{{ old('slug', $promotion->slug) }}"></div>
    </div>
    <div class="form-group"><label class="form-label">Description</label><textarea name="description" class="form-textarea" rows="2">{{ old('description', $promotion->description) }}</textarea></div>

    <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:16px;">
        <div class="form-group"><label class="form-label">Promotion Type</label><select name="promotion_type" class="form-select"><option value="automatic" @selected(old('promotion_type', $promotion->promotion_type)==='automatic')>Automatic</option><option value="coupon" @selected(old('promotion_type', $promotion->promotion_type)==='coupon')>Coupon Required</option><option value="manual" @selected(old('promotion_type', $promotion->promotion_type)==='manual')>Manual</option></select></div>
        <div class="form-group"><label class="form-label">Discount Type</label><select name="discount_type" class="form-select"><option value="percentage" @selected(old('discount_type', $promotion->discount_type)==='percentage')>Percentage</option><option value="fixed" @selected(old('discount_type', $promotion->discount_type)==='fixed')>Fixed Amount</option><option value="free_setup" @selected(old('discount_type', $promotion->discount_type)==='free_setup')>Free Setup Fee</option></select></div>
        <div class="form-group"><label class="form-label">Value</label><input type="number" step="0.01" min="0" name="discount_value" class="form-input" value="{{ old('discount_value', $promotion->discount_value ?? 0) }}"></div>
        <div class="form-group"><label class="form-label">Applies To</label><select name="applies_to" class="form-select"><option value="signup" @selected(old('applies_to', $promotion->applies_to)==='signup')>Signup</option><option value="renewal" @selected(old('applies_to', $promotion->applies_to)==='renewal')>Renewal</option><option value="both" @selected(old('applies_to', $promotion->applies_to)==='both')>Both</option></select></div>
    </div>

    <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:16px;">
        <div class="form-group"><label class="form-label">Status</label><select name="status" class="form-select"><option value="active" @selected(old('status', $promotion->status)==='active')>Active</option><option value="inactive" @selected(old('status', $promotion->status)==='inactive')>Inactive</option></select></div>
        <div class="form-group"><label class="form-label">Priority</label><input type="number" name="priority" class="form-input" value="{{ old('priority', $promotion->priority ?? 100) }}"></div>
        <div class="form-group"><label class="form-label">Starts At</label><input type="datetime-local" name="starts_at" class="form-input" value="{{ old('starts_at', optional($promotion->starts_at)->format('Y-m-d\TH:i')) }}"></div>
        <div class="form-group"><label class="form-label">Ends At</label><input type="datetime-local" name="ends_at" class="form-input" value="{{ old('ends_at', optional($promotion->ends_at)->format('Y-m-d\TH:i')) }}"></div>
    </div>

    <div class="card" style="background:#f8fafc;">
        <h3 style="margin-top:0;">Conditions</h3>
        @php $conditions = old('conditions', $promotion->conditions ?? []); @endphp
        <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:16px;">
            <div class="form-group"><label class="form-label">Minimum Cart Total</label><input type="number" step="0.01" min="0" name="conditions[min_cart_total]" class="form-input" value="{{ $conditions['min_cart_total'] ?? '' }}"></div>
            <div class="form-group"><label class="form-label">Item Types</label><input name="conditions[item_types]" class="form-input" value="{{ implode(',', $conditions['item_types'] ?? []) }}" placeholder="hosting,ssl,email,domain"></div>
            <div class="form-group"><label class="form-label">Service IDs</label><input name="conditions[service_ids]" class="form-input" value="{{ implode(',', $conditions['service_ids'] ?? []) }}" placeholder="1,2,3"></div>
            <div class="form-group">
                <label class="form-label">Billing Cycles</label>
                <div style="display:flex;gap:10px;flex-wrap:wrap;">
                    @foreach(['monthly','quarterly','semi_annually','annually','one-time','free'] as $cycle)
                        <label class="checkbox-group"><input type="checkbox" name="conditions[billing_cycles][]" value="{{ $cycle }}" @checked(in_array($cycle, $conditions['billing_cycles'] ?? []))> <span>{{ ucfirst(str_replace('_', ' ', $cycle)) }}</span></label>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <label class="checkbox-group"><input type="checkbox" name="stackable" value="1" @checked(old('stackable', $promotion->stackable))> <span>Allow this promotion to stack with other promotions</span></label>

    <div style="display:flex;justify-content:flex-end;gap:10px;">
        <button class="btn btn-primary">{{ jv_icon('save', '', 16) }} Save Promotion</button>
    </div>
</form>
@endsection
