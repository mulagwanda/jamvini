<?php

namespace Plugins\OfflinePayments\src\Gateways;

use App\Core\Payments\AbstractPaymentGateway;
use App\Core\Payments\PaymentResult;
use Plugins\Invoices\src\Models\Invoice;

class BankDepositGateway extends AbstractPaymentGateway
{
    public function slug(): string
    {
        return 'bank_deposit';
    }

    public function name(): string
    {
        return 'Bank Deposit';
    }

    public function type(): string
    {
        return 'offline';
    }

    public function icon(): string
    {
        return 'landmark';
    }

    public function description(): string
    {
        return 'Customer deposits or transfers funds to your bank account.';
    }

    public function isEnabled(): bool
    {
        return $this->setting('offline_bank_enabled', '1') === '1';
    }

    public function isConfigured(): bool
    {
        return trim((string) $this->setting('offline_bank_instructions', '')) !== '';
    }

    public function initiate(Invoice $invoice, array $context = []): PaymentResult
    {
        return PaymentResult::pending('Bank deposit instructions are shown to the customer.', null, (float) $invoice->remaining_amount, [
            'instructions' => $this->setting('offline_bank_instructions', ''),
        ]);
    }
}
