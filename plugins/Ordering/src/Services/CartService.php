<?php

namespace Plugins\Ordering\src\Services;

use Illuminate\Support\Facades\Session;

class CartService
{
    protected string $sessionKey = 'ordering_cart';

    public function getCart(): array
    {
        return Session::get($this->sessionKey, ['items' => [], 'total' => 0]);
    }

    public function addItem(array $item): void
    {
        $cart = $this->getCart();
        $item['id'] = uniqid('cart_');
        $cart['items'][] = $item;
        $this->saveCart($cart);
    }

    public function removeItem(string $id): void
    {
        $cart = $this->getCart();
        $cart['items'] = array_filter($cart['items'], fn($item) => $item['id'] !== $id);
        $this->saveCart($cart);
    }

    public function updateItem(string $id, array $changes): ?array
    {
        $cart = $this->getCart();
        $updated = null;

        foreach ($cart['items'] as &$item) {
            if (($item['id'] ?? null) !== $id) {
                continue;
            }

            $item = array_replace_recursive($item, $changes);
            $updated = $item;
            break;
        }

        unset($item);
        $this->saveCart($cart);

        return $updated;
    }

    public function clear(): void
    {
        Session::forget($this->sessionKey);
    }

    public function applyCoupon(string $code): void
    {
        $cart = $this->getCart();
        $cart['coupons'] = array_values(array_unique(array_merge($cart['coupons'] ?? [], [strtoupper(trim($code))])));
        $this->saveCart($cart);
    }

    public function removeCoupon(string $code): void
    {
        $cart = $this->getCart();
        $code = strtoupper(trim($code));
        $cart['coupons'] = array_values(array_filter($cart['coupons'] ?? [], fn ($coupon) => $coupon !== $code));
        $this->saveCart($cart);
    }

    public function coupons(): array
    {
        return $this->getCart()['coupons'] ?? [];
    }

    public function calculation(mixed $client = null): array
    {
        if (class_exists(\Plugins\Promotions\src\Services\PromotionCalculator::class)) {
            return app(\Plugins\Promotions\src\Services\PromotionCalculator::class)
                ->calculate($this->getItems(), $client, $this->coupons());
        }

        $subtotal = $this->total();
        $taxRate = function_exists('jv_tax_rate') ? jv_tax_rate() : 0;
        $taxAmount = $subtotal * ($taxRate / 100);

        return [
            'subtotal' => $subtotal,
            'discounts' => [],
            'discount_total' => 0,
            'taxable_total' => $subtotal,
            'tax_rate' => $taxRate,
            'tax_amount' => $taxAmount,
            'grand_total' => $subtotal + $taxAmount,
            'messages' => [],
        ];
    }

    public function count(): int
    {
        return count($this->getCart()['items']);
    }

    public function total(): float
    {
        $cart = $this->getCart();
        $total = 0;
        foreach ($cart['items'] as $item) {
            $total += $item['price'] ?? 0;
        }
        return $total;
    }

    public function getItems(): array
    {
        return $this->getCart()['items'];
    }

    protected function saveCart(array $cart): void
    {
        $cart['total'] = array_sum(array_column($cart['items'], 'price'));
        $cart['coupons'] = $cart['coupons'] ?? [];
        Session::put($this->sessionKey, $cart);
    }
}
