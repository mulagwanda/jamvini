<?php

namespace Plugins\TzRegistryRegistrar\src\Support;

class TzRegistryException extends \RuntimeException
{
    public function __construct(
        string $message,
        protected ?int $eppCode = null,
        protected ?array $context = null
    ) {
        parent::__construct($message, $eppCode ?? 0);
    }

    public function eppCode(): ?int
    {
        return $this->eppCode;
    }

    public function context(): ?array
    {
        return $this->context;
    }
}
