<?php

namespace App\Core\Provisioning;

class ProvisioningResult
{
    public function __construct(
        public readonly bool $success,
        public readonly string $message,
        public readonly array $data = [],
        public readonly bool $manual = false,
    ) {
    }

    public static function success(string $message, array $data = []): self
    {
        return new self(true, $message, $data);
    }

    public static function failed(string $message, array $data = []): self
    {
        return new self(false, $message, $data);
    }

    public static function manual(string $message, bool $success = true, array $data = []): self
    {
        return new self($success, $message, $data, true);
    }

    public function toArray(): array
    {
        return [
            'success' => $this->success,
            'message' => $this->message,
            'data' => $this->data,
            'manual' => $this->manual,
        ];
    }
}
