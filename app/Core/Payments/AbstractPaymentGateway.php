<?php

namespace App\Core\Payments;

use App\Core\Contracts\PaymentGatewayInterface;
use Illuminate\Http\Request;
use Plugins\Invoices\src\Models\Invoice;
use Plugins\Invoices\src\Models\Transaction;

abstract class AbstractPaymentGateway implements PaymentGatewayInterface
{
    public function type(): string
    {
        return 'third_party';
    }

    public function icon(): string
    {
        return 'credit-card';
    }

    public function description(): string
    {
        return '';
    }

    public function supportsRefunds(): bool
    {
        return false;
    }

    public function supportsRecurring(): bool
    {
        return false;
    }

    public function supportsAdminCapture(): bool
    {
        return false;
    }

    public function handleCallback(Request $request): PaymentResult
    {
        return PaymentResult::failed('Callbacks are not supported by this gateway.');
    }

    public function refund(Transaction $transaction, float $amount, array $context = []): PaymentResult
    {
        return PaymentResult::failed('Refunds are not supported by this gateway.');
    }

    protected function setting(string $key, mixed $default = null): mixed
    {
        return \App\Models\Setting::get($key, $default);
    }
}
