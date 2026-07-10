<?php

namespace Plugins\SelcomPayment\src\Gateways;

use App\Core\Payments\AbstractPaymentGateway;
use App\Core\Payments\PaymentResult;
use Illuminate\Http\Request;
use Plugins\Invoices\src\Models\Invoice;

class SelcomGateway extends AbstractPaymentGateway
{
    public function slug(): string
    {
        return 'selcom';
    }

    public function name(): string
    {
        return 'Selcom';
    }

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
        return 'Mobile money and card payments through Selcom.';
    }

    public function isEnabled(): bool
    {
        return $this->setting('selcom_enabled', '1') === '1';
    }

    public function isConfigured(): bool
    {
        return filled($this->setting('selcom_vendor_id')) && filled($this->setting('selcom_api_key'));
    }

    public function supportsRefunds(): bool
    {
        return true;
    }

    public function initiate(Invoice $invoice, array $context = []): PaymentResult
    {
        $orderId = 'SEL-' . $invoice->id . '-' . now()->format('YmdHis');

        return PaymentResult::pending('Selcom payment request created.', $orderId, (float) $invoice->remaining_amount, [
            'invoice_id' => $invoice->id,
            'order_id' => $orderId,
            'test_mode' => $this->setting('selcom_test_mode', '1') === '1',
            'method' => $context['method'] ?? null,
        ]);
    }

    public function handleCallback(Request $request): PaymentResult
    {
        $status = strtoupper((string) $request->input('status', $request->input('payment_status', '')));
        $transactionId = $request->input('transaction_id', $request->input('transid'));
        $amount = (float) $request->input('amount', 0);

        if (in_array($status, ['SUCCESS', 'COMPLETED', 'PAID'], true)) {
            return PaymentResult::completed('Selcom payment received.', $transactionId, $amount, $request->all());
        }

        if (in_array($status, ['PENDING', 'PROCESSING'], true)) {
            return PaymentResult::pending('Selcom payment is pending.', $transactionId, $amount, $request->all());
        }

        return PaymentResult::failed('Selcom payment failed or was cancelled.', $request->all());
    }
}
