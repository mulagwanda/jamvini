<?php

namespace App\Core\Payments;

use App\Core\Contracts\PaymentGatewayInterface;

class PaymentGatewayRegistry
{
    protected static array $gateways = [];

    public static function register(string $slug, string|PaymentGatewayInterface $gateway): void
    {
        self::$gateways[$slug] = $gateway;
    }

    public static function all(): array
    {
        return collect(self::$gateways)
            ->mapWithKeys(fn ($gateway, $slug) => [$slug => self::resolve($gateway)])
            ->filter()
            ->all();
    }

    public static function enabled(): array
    {
        return collect(self::all())
            ->filter(fn (PaymentGatewayInterface $gateway) => $gateway->isEnabled() && $gateway->isConfigured())
            ->all();
    }

    public static function get(string $slug): ?PaymentGatewayInterface
    {
        return isset(self::$gateways[$slug]) ? self::resolve(self::$gateways[$slug]) : null;
    }

    public static function remove(string $slug): void
    {
        unset(self::$gateways[$slug]);
    }

    protected static function resolve(string|PaymentGatewayInterface $gateway): ?PaymentGatewayInterface
    {
        if ($gateway instanceof PaymentGatewayInterface) {
            return $gateway;
        }

        if (!class_exists($gateway)) {
            return null;
        }

        $instance = app($gateway);

        return $instance instanceof PaymentGatewayInterface ? $instance : null;
    }
}
