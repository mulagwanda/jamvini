<?php

namespace Plugins\OfflinePayments\src\Gateways;

use App\Core\Payments\AbstractPaymentGateway;
use App\Core\Payments\PaymentResult;
use Plugins\Invoices\src\Models\Invoice;

class CashPaymentGateway extends AbstractPaymentGateway
{
    public function slug(): string
    {
        return 'cash';
    }

    public function name(): string
    {
        return 'Cash';
    }

    public function type(): string
    {
        return 'offline';
    }

    public function icon(): string
    {
        return 'banknote';
    }

    public function description(): string
    {
        return 'Customer pays in person and an admin records the payment.';
    }

    public function isEnabled(): bool
    {
        return $this->setting('offline_cash_enabled', '1') === '1';
    }

    public function isConfigured(): bool
    {
        return true;
    }

    public function initiate(Invoice $invoice, array $context = []): PaymentResult
    {
        return PaymentResult::pending('Cash payment selected. Payment will be confirmed by staff.', null, (float) $invoice->remaining_amount, [
            'instructions' => $this->setting('offline_cash_instructions', 'Please visit our office to complete payment.'),
        ]);
    }
}
