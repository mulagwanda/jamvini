<?php

namespace App\Core\Contracts;

use App\Core\Payments\PaymentResult;
use Illuminate\Http\Request;
use Plugins\Invoices\src\Models\Invoice;
use Plugins\Invoices\src\Models\Transaction;

interface PaymentGatewayInterface
{
    public function slug(): string;

    public function name(): string;

    public function type(): string;

    public function icon(): string;

    public function description(): string;

    public function isEnabled(): bool;

    public function isConfigured(): bool;

    public function supportsRefunds(): bool;

    public function supportsRecurring(): bool;

    public function supportsAdminCapture(): bool;

    public function initiate(Invoice $invoice, array $context = []): PaymentResult;

    public function handleCallback(Request $request): PaymentResult;

    public function refund(Transaction $transaction, float $amount, array $context = []): PaymentResult;
}
