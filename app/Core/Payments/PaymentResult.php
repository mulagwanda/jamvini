<?php

namespace App\Core\Payments;

class PaymentResult
{
    public function __construct(
        public bool $success,
        public string $status,
        public string $message,
        public ?string $redirectUrl = null,
        public ?string $transactionId = null,
        public ?float $amount = null,
        public array $payload = []
    ) {
    }

    public static function redirect(string $url, string $message = 'Redirecting to payment gateway.', array $payload = []): self
    {
        return new self(true, 'redirect', $message, $url, null, null, $payload);
    }

    public static function completed(string $message, ?string $transactionId = null, ?float $amount = null, array $payload = []): self
    {
        return new self(true, 'completed', $message, null, $transactionId, $amount, $payload);
    }

    public static function pending(string $message, ?string $transactionId = null, ?float $amount = null, array $payload = []): self
    {
        return new self(true, 'pending', $message, null, $transactionId, $amount, $payload);
    }

    public static function failed(string $message, array $payload = []): self
    {
        return new self(false, 'failed', $message, null, null, null, $payload);
    }
}
