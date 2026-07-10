<?php

namespace App\Core\Payments;

use App\Core\Hooks\Action;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Plugins\Invoices\src\Models\Invoice;
use Plugins\Invoices\src\Models\Transaction;

class PaymentManager
{
    public function initiate(Invoice $invoice, string $gatewaySlug, array $context = []): PaymentResult
    {
        $gateway = PaymentGatewayRegistry::get($gatewaySlug);

        if (!$gateway || !$gateway->isEnabled() || !$gateway->isConfigured()) {
            return PaymentResult::failed('Payment gateway is not available.');
        }

        $result = $gateway->initiate($invoice, $context);

        Action::do('payment.initiated', [
            'invoice_id' => $invoice->id,
            'gateway' => $gatewaySlug,
            'status' => $result->status,
            'message' => $result->message,
        ]);

        return $result;
    }

    public function record(
        Invoice $invoice,
        float $amount,
        string $gatewaySlug,
        ?string $transactionId = null,
        string $status = 'completed',
        ?string $notes = null,
        array $metadata = [],
        ?int $recordedBy = null
    ): Transaction {
        return DB::transaction(function () use ($invoice, $amount, $gatewaySlug, $transactionId, $status, $notes, $metadata, $recordedBy) {
            $transaction = Transaction::create([
                'invoice_id' => $invoice->id,
                'amount' => $amount,
                'currency' => $invoice->currency ?? \App\Models\Setting::get('currency', 'TZS'),
                'payment_method' => $gatewaySlug,
                'gateway_slug' => $gatewaySlug,
                'gateway_type' => PaymentGatewayRegistry::get($gatewaySlug)?->type() ?? 'offline',
                'transaction_id' => $transactionId ?: strtoupper($gatewaySlug) . '-' . now()->format('YmdHis') . '-' . Str::upper(Str::random(5)),
                'status' => $status,
                'notes' => $notes,
                'metadata' => $metadata,
                'recorded_by' => $recordedBy,
                'paid_at' => $status === 'completed' ? now() : null,
            ]);

            $this->syncInvoiceStatus($invoice->fresh());

            if ($status === 'completed') {
                Action::do('payment.received', $transaction);
            }

            return $transaction;
        });
    }

    public function refund(Transaction $transaction, float $amount, array $context = []): PaymentResult
    {
        $gateway = PaymentGatewayRegistry::get($transaction->gateway_slug ?: $transaction->payment_method);

        if (!$gateway || !$gateway->supportsRefunds()) {
            return PaymentResult::failed('Refunds are not supported by this gateway.');
        }

        $result = $gateway->refund($transaction, $amount, $context);

        if ($result->success) {
            $transaction->increment('refunded_amount', $amount);
            $transaction->update(['refunded_at' => now()]);
            Action::do('payment.refunded', $transaction, $amount);
        }

        return $result;
    }

    public function syncInvoiceStatus(Invoice $invoice): void
    {
        $completed = (float) $invoice->transactions()->where('status', 'completed')->sum('amount');
        $refunded = (float) $invoice->transactions()->where('status', 'completed')->sum('refunded_amount');
        $netPaid = max(0, $completed - $refunded);
        $wasPaid = $invoice->status === 'paid';

        if ($netPaid >= (float) $invoice->total) {
            $invoice->update(['status' => 'paid', 'paid_at' => $invoice->paid_at ?: now()]);
            if (!$wasPaid) {
                Action::do('invoice.paid', $invoice->fresh(['client']));
            }
        } elseif ($netPaid > 0) {
            $invoice->update(['status' => 'partial']);
        } elseif ($invoice->due_date && $invoice->due_date->isPast() && in_array($invoice->status, ['sent', 'overdue'], true)) {
            $invoice->update(['status' => 'overdue']);
        } elseif (!in_array($invoice->status, ['draft', 'void', 'cancelled'], true)) {
            $invoice->update(['status' => 'sent']);
        }
    }
}
