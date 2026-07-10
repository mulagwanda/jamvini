<?php

namespace Plugins\Services\src\Connectors;

interface ServerConnector
{
    public function test(): array;

    public function packages(): array;

    public function createAccount(array $payload): array;

    public function createLoginSession(array $payload): array;
}
