<?php

namespace Plugins\TzRegistryRegistrar\src\Support;

class EppResponse
{
    public function __construct(
        public readonly bool $success,
        public readonly int $code,
        public readonly string $message,
        public readonly ?string $clientTransactionId,
        public readonly ?string $serverTransactionId,
        public readonly string $xml,
        public readonly array $data = []
    ) {
    }

    public function toArray(bool $includeXml = false): array
    {
        $payload = [
            'success' => $this->success,
            'code' => $this->code,
            'message' => $this->message,
            'client_transaction_id' => $this->clientTransactionId,
            'server_transaction_id' => $this->serverTransactionId,
            'data' => $this->data,
        ];

        if ($includeXml) {
            $payload['xml'] = $this->xml;
        }

        return $payload;
    }
}
