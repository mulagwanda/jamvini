<?php

namespace Plugins\Promotions\src\Services;

use App\Models\Setting;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Plugins\Promotions\src\Models\Coupon;
use Plugins\Promotions\src\Models\CouponRedemption;
use Plugins\Promotions\src\Models\Promotion;

class PromotionCalculator
{
    public function calculate(array $items, mixed $client = null, array $couponCodes = []): array
    {
        $subtotal = $this->subtotal($items);
        $context = [
            'items' => $items,
            'client' => $client,
            'subtotal' => $subtotal,
        ];

        $discounts = [];
        $messages = [];

        foreach ($this->automaticPromotions() as $promotion) {
            $discount = $this->discountForPromotion($promotion, null, $context);
            if ($discount) {
                $discounts[] = $discount;
            }
        }

        foreach (array_unique(array_filter(array_map(fn ($code) => strtoupper(trim((string) $code)), $couponCodes))) as $code) {
            $coupon = Coupon::query()->with('promotion')->where('code', $code)->first();
            $validation = $this->validateCoupon($coupon, $context);
            if (!$validation['valid']) {
                $messages[] = ['code' => $code, 'type' => 'error', 'message' => $validation['message']];
                continue;
            }

            $discount = $this->discountForPromotion($coupon->promotion, $coupon, $context);
            if (!$discount) {
                $messages[] = ['code' => $code, 'type' => 'error', 'message' => 'Coupon is not valid for the selected items.'];
                continue;
            }

            $discounts[] = $discount;
            $messages[] = ['code' => $code, 'type' => 'success', 'message' => 'Coupon applied.'];
        }

        $discounts = $this->resolveStacking($discounts, $subtotal);
        $discountTotal = min($subtotal, array_sum(array_column($discounts, 'amount')));
        $taxableTotal = max(0, $subtotal - $discountTotal);
        $taxRate = function_exists('jv_tax_rate') ? jv_tax_rate() : 0;
        $taxAmount = $taxableTotal * ($taxRate / 100);

        return [
            'subtotal' => $subtotal,
            'discounts' => $discounts,
            'discount_total' => $discountTotal,
            'taxable_total' => $taxableTotal,
            'tax_rate' => $taxRate,
            'tax_amount' => $taxAmount,
            'grand_total' => $taxableTotal + $taxAmount,
            'messages' => $messages,
        ];
    }

    public function validateCoupon(?Coupon $coupon, array $context): array
    {
        if (!$coupon || $coupon->trashed()) {
            return ['valid' => false, 'message' => 'Coupon was not found.'];
        }

        if ($coupon->status !== 'active' || !$coupon->promotion || $coupon->promotion->status !== 'active') {
            return ['valid' => false, 'message' => 'Coupon is not active.'];
        }

        if ($coupon->promotion->promotion_type !== 'coupon') {
            return ['valid' => false, 'message' => 'Coupon is attached to a non-coupon promotion.'];
        }

        $now = now();
        if (($coupon->starts_at && $coupon->starts_at->gt($now)) || ($coupon->ends_at && $coupon->ends_at->lt($now))) {
            return ['valid' => false, 'message' => 'Coupon is outside its valid date range.'];
        }

        if (($coupon->promotion->starts_at && $coupon->promotion->starts_at->gt($now)) || ($coupon->promotion->ends_at && $coupon->promotion->ends_at->lt($now))) {
            return ['valid' => false, 'message' => 'Promotion is outside its valid date range.'];
        }

        if ($coupon->min_cart_total !== null && $context['subtotal'] < (float) $coupon->min_cart_total) {
            return ['valid' => false, 'message' => 'Cart total is below the coupon minimum.'];
        }

        if ($coupon->max_uses !== null && $coupon->redemptions()->where('status', 'redeemed')->count() >= $coupon->max_uses) {
            return ['valid' => false, 'message' => 'Coupon usage limit has been reached.'];
        }

        $clientId = $context['client']?->id ?? null;
        if ($clientId && $coupon->max_uses_per_client !== null) {
            $usedByClient = $coupon->redemptions()
                ->where('client_id', $clientId)
                ->where('status', 'redeemed')
                ->count();
            if ($usedByClient >= $coupon->max_uses_per_client) {
                return ['valid' => false, 'message' => 'Coupon has already been used by this client.'];
            }
        }

        return ['valid' => true, 'message' => 'Coupon is valid.'];
    }

    public function record(array $calculation, mixed $order = null, mixed $invoice = null, mixed $client = null): void
    {
        if (!Schema::hasTable('coupon_redemptions')) {
            return;
        }

        foreach ($calculation['discounts'] ?? [] as $discount) {
            if (Schema::hasTable('order_discounts') && $order) {
                DB::table('order_discounts')->insert([
                    'order_id' => $order->id,
                    'promotion_id' => $discount['promotion_id'] ?? null,
                    'coupon_id' => $discount['coupon_id'] ?? null,
                    'code' => $discount['code'] ?? null,
                    'label' => $discount['label'],
                    'discount_type' => $discount['discount_type'],
                    'amount' => $discount['amount'],
                    'metadata' => json_encode($discount),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            if (Schema::hasTable('invoice_discounts') && $invoice) {
                DB::table('invoice_discounts')->insert([
                    'invoice_id' => $invoice->id,
                    'promotion_id' => $discount['promotion_id'] ?? null,
                    'coupon_id' => $discount['coupon_id'] ?? null,
                    'code' => $discount['code'] ?? null,
                    'label' => $discount['label'],
                    'discount_type' => $discount['discount_type'],
                    'amount' => $discount['amount'],
                    'metadata' => json_encode($discount),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            if (!empty($discount['coupon_id'])) {
                CouponRedemption::create([
                    'coupon_id' => $discount['coupon_id'],
                    'promotion_id' => $discount['promotion_id'] ?? null,
                    'client_id' => $client?->id ?? $order?->client_id ?? $invoice?->client_id ?? null,
                    'order_id' => $order?->id,
                    'invoice_id' => $invoice?->id,
                    'code' => $discount['code'] ?? null,
                    'discount_amount' => $discount['amount'],
                    'currency' => Setting::get('currency', 'TZS'),
                    'status' => 'redeemed',
                    'ip_address' => request()?->ip(),
                    'metadata' => $discount,
                    'redeemed_at' => now(),
                ]);
            }
        }
    }

    protected function automaticPromotions(): Collection
    {
        if (!Schema::hasTable('promotions')) {
            return collect();
        }

        return Promotion::query()
            ->active()
            ->where('promotion_type', 'automatic')
            ->orderBy('priority')
            ->orderByDesc('discount_value')
            ->get();
    }

    protected function discountForPromotion(Promotion $promotion, ?Coupon $coupon, array $context): ?array
    {
        $eligibleItems = $this->eligibleItems($context['items'], $promotion->conditions ?? [], $context);
        if ($eligibleItems->isEmpty()) {
            return null;
        }

        $eligibleSubtotal = $eligibleItems->sum(fn ($item) => (float) ($item['price'] ?? 0));
        $amount = match ($promotion->discount_type) {
            'percentage' => $eligibleSubtotal * ((float) $promotion->discount_value / 100),
            'fixed' => min($eligibleSubtotal, (float) $promotion->discount_value),
            'free_setup' => $eligibleItems->sum(fn ($item) => (float) data_get($item, 'details.setup_fee', 0)),
            default => 0,
        };

        $amount = round(max(0, $amount), 2);
        if ($amount <= 0) {
            return null;
        }

        return [
            'promotion_id' => $promotion->id,
            'coupon_id' => $coupon?->id,
            'code' => $coupon?->code,
            'label' => $coupon ? "{$promotion->name} ({$coupon->code})" : $promotion->name,
            'promotion_type' => $promotion->promotion_type,
            'discount_type' => $promotion->discount_type,
            'amount' => $amount,
            'stackable' => (bool) $promotion->stackable,
            'priority' => (int) $promotion->priority,
            'applies_to' => $promotion->applies_to,
            'eligible_item_ids' => $eligibleItems->pluck('id')->filter()->values()->all(),
        ];
    }

    protected function eligibleItems(array $items, array $conditions, array $context): Collection
    {
        $items = collect($items);

        if (!$this->contextMatches($conditions, $context)) {
            return collect();
        }

        if (!empty($conditions['billing_cycles'])) {
            $cycles = array_map('strval', $conditions['billing_cycles']);
            $items = $items->filter(fn ($item) => in_array((string) data_get($item, 'details.billing_cycle'), $cycles, true));
        }

        if (!empty($conditions['item_types'])) {
            $types = array_map('strval', $conditions['item_types']);
            $items = $items->filter(fn ($item) => in_array((string) ($item['type'] ?? ''), $types, true));
        }

        if (!empty($conditions['service_ids'])) {
            $serviceIds = array_map('intval', $conditions['service_ids']);
            $items = $items->filter(fn ($item) => in_array((int) data_get($item, 'details.service_id'), $serviceIds, true));
        }

        return $items->values();
    }

    protected function contextMatches(array $conditions, array $context): bool
    {
        if (!empty($conditions['min_cart_total']) && $context['subtotal'] < (float) $conditions['min_cart_total']) {
            return false;
        }

        $client = $context['client'];
        if (!empty($conditions['client_group_ids'])) {
            $groupIds = array_map('intval', $conditions['client_group_ids']);
            if (!$client || !in_array((int) ($client->group_id ?? 0), $groupIds, true)) {
                return false;
            }
        }

        return true;
    }

    protected function resolveStacking(array $discounts, float $subtotal): array
    {
        $stackable = array_values(array_filter($discounts, fn ($discount) => $discount['stackable']));
        $exclusive = array_values(array_filter($discounts, fn ($discount) => !$discount['stackable']));

        usort($exclusive, fn ($a, $b) => $b['amount'] <=> $a['amount']);
        $resolved = array_merge($stackable, array_slice($exclusive, 0, 1));
        usort($resolved, fn ($a, $b) => ($a['priority'] <=> $b['priority']) ?: ($b['amount'] <=> $a['amount']));

        $remaining = $subtotal;
        foreach ($resolved as &$discount) {
            $discount['amount'] = min($discount['amount'], max(0, $remaining));
            $remaining -= $discount['amount'];
        }

        return array_values(array_filter($resolved, fn ($discount) => $discount['amount'] > 0));
    }

    protected function subtotal(array $items): float
    {
        return array_sum(array_map(fn ($item) => (float) ($item['price'] ?? 0), $items));
    }
}
