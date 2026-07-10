<?php

namespace Plugins\Promotions\src\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Plugins\Promotions\src\Models\Coupon;
use Plugins\Promotions\src\Models\Promotion;

class PromotionController extends Controller
{
    public function index()
    {
        $promotions = Promotion::query()
            ->withCount('coupons')
            ->latest()
            ->paginate(20);

        return view('plugins.Promotions::admin.index', compact('promotions'));
    }

    public function create()
    {
        $promotion = new Promotion([
            'promotion_type' => 'automatic',
            'discount_type' => 'percentage',
            'applies_to' => 'signup',
            'status' => 'active',
            'priority' => 100,
        ]);

        return view('plugins.Promotions::admin.form', compact('promotion'));
    }

    public function store(Request $request)
    {
        $promotion = Promotion::create($this->validatedPromotion($request));

        return redirect()->route('admin.promotions.edit', $promotion)
            ->with('success', 'Promotion created.');
    }

    public function edit(Promotion $promotion)
    {
        return view('plugins.Promotions::admin.form', compact('promotion'));
    }

    public function update(Request $request, Promotion $promotion)
    {
        $promotion->update($this->validatedPromotion($request, $promotion));

        return back()->with('success', 'Promotion updated.');
    }

    public function destroy(Promotion $promotion)
    {
        $promotion->delete();

        return redirect()->route('admin.promotions.index')->with('success', 'Promotion deleted.');
    }

    public function coupons()
    {
        $coupons = Coupon::query()->with('promotion')->withCount('redemptions')->latest()->paginate(25);
        $promotions = Promotion::query()->where('promotion_type', 'coupon')->orderBy('name')->get();

        return view('plugins.Promotions::admin.coupons', compact('coupons', 'promotions'));
    }

    public function storeCoupon(Request $request)
    {
        $validated = $request->validate([
            'promotion_id' => ['required', 'exists:promotions,id'],
            'code' => ['required', 'string', 'max:80', 'unique:coupons,code'],
            'status' => ['required', 'in:active,inactive'],
            'max_uses' => ['nullable', 'integer', 'min:1'],
            'max_uses_per_client' => ['nullable', 'integer', 'min:1'],
            'min_cart_total' => ['nullable', 'numeric', 'min:0'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
        ]);

        $validated['code'] = strtoupper(trim($validated['code']));
        Coupon::create($validated);

        return back()->with('success', 'Coupon created.');
    }

    protected function validatedPromotion(Request $request, ?Promotion $promotion = null): array
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:promotions,slug,' . ($promotion?->id ?? 'NULL')],
            'description' => ['nullable', 'string'],
            'promotion_type' => ['required', 'in:automatic,coupon,manual'],
            'discount_type' => ['required', 'in:percentage,fixed,free_setup'],
            'discount_value' => ['nullable', 'numeric', 'min:0'],
            'applies_to' => ['required', 'in:signup,renewal,both'],
            'recurring_cycles' => ['nullable', 'integer', 'min:1', 'max:120'],
            'status' => ['required', 'in:active,inactive'],
            'stackable' => ['nullable', 'boolean'],
            'priority' => ['nullable', 'integer', 'min:1', 'max:9999'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'conditions.min_cart_total' => ['nullable', 'numeric', 'min:0'],
            'conditions.item_types' => ['nullable', 'string'],
            'conditions.service_ids' => ['nullable', 'string'],
            'conditions.billing_cycles' => ['nullable', 'array'],
            'conditions.billing_cycles.*' => ['string'],
        ]);

        $conditions = $validated['conditions'] ?? [];
        $conditions['item_types'] = $this->csv($conditions['item_types'] ?? '');
        $conditions['service_ids'] = array_map('intval', $this->csv($conditions['service_ids'] ?? ''));
        $conditions['billing_cycles'] = array_values(array_filter($conditions['billing_cycles'] ?? []));
        if (blank($conditions['min_cart_total'] ?? null)) {
            unset($conditions['min_cart_total']);
        }

        return [
            'name' => $validated['name'],
            'slug' => $validated['slug'] ?: Str::slug($validated['name']),
            'description' => $validated['description'] ?? null,
            'promotion_type' => $validated['promotion_type'],
            'discount_type' => $validated['discount_type'],
            'discount_value' => $validated['discount_type'] === 'free_setup' ? 0 : ($validated['discount_value'] ?? 0),
            'applies_to' => $validated['applies_to'],
            'recurring_cycles' => $validated['recurring_cycles'] ?? null,
            'status' => $validated['status'],
            'stackable' => (bool) ($validated['stackable'] ?? false),
            'priority' => $validated['priority'] ?? 100,
            'starts_at' => $validated['starts_at'] ?? null,
            'ends_at' => $validated['ends_at'] ?? null,
            'conditions' => array_filter($conditions, fn ($value) => !blank($value)),
        ];
    }

    protected function csv(string $value): array
    {
        return collect(explode(',', $value))
            ->map(fn ($item) => trim($item))
            ->filter()
            ->values()
            ->all();
    }
}
