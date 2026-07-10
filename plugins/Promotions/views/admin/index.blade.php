@extends('themes.default::layouts.admin')

@section('title', 'Promotions')
@section('breadcrumbs')<span class="current">Promotions</span>@endsection

@section('content')
<div style="display:flex;justify-content:space-between;align-items:flex-start;gap:16px;margin-bottom:22px;">
    <div>
        <h1 class="page-title">Promotions</h1>
        <p class="page-subtitle">Automatic discounts, coupon-backed offers, and manual promotion rules.</p>
    </div>
    <div style="display:flex;gap:8px;flex-wrap:wrap;">
        <a href="{{ route('admin.promotions.coupons') }}" class="btn btn-outline-primary">{{ jv_icon('ticket-percent', '', 16) }} Coupons</a>
        <a href="{{ route('admin.promotions.create') }}" class="btn btn-primary">{{ jv_icon('plus', '', 16) }} Create Promotion</a>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table">
            <thead><tr><th>Name</th><th>Type</th><th>Discount</th><th>Status</th><th>Coupons</th><th>Dates</th><th></th></tr></thead>
            <tbody>
                @forelse($promotions as $promotion)
                    <tr>
                        <td><strong>{{ $promotion->name }}</strong><br><small>{{ $promotion->slug }}</small></td>
                        <td>{{ ucfirst($promotion->promotion_type) }}</td>
                        <td>
                            @if($promotion->discount_type === 'percentage')
                                {{ rtrim(rtrim(number_format($promotion->discount_value, 2), '0'), '.') }}%
                            @elseif($promotion->discount_type === 'fixed')
                                {{ jv_format_money($promotion->discount_value) }}
                            @else
                                Free setup fee
                            @endif
                        </td>
                        <td><span class="badge badge-{{ $promotion->status === 'active' ? 'success' : 'gray' }}">{{ ucfirst($promotion->status) }}</span></td>
                        <td>{{ $promotion->coupons_count }}</td>
                        <td><small>{{ $promotion->starts_at?->format('M d, Y') ?: 'Now' }} - {{ $promotion->ends_at?->format('M d, Y') ?: 'No end' }}</small></td>
                        <td style="text-align:right;"><a href="{{ route('admin.promotions.edit', $promotion) }}" class="btn btn-sm btn-outline-primary">Edit</a></td>
                    </tr>
                @empty
                    <tr><td colspan="7"><div class="empty-state"><div class="empty-state-title">No promotions yet</div><div class="empty-state-desc">Create an automatic discount or coupon promotion.</div></div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $promotions->links() }}
</div>
@endsection
