<?php

namespace Plugins\Services\src\Connectors;

use Plugins\Services\src\Models\Server;

class ManualConnector implements ServerConnector
{
    public function __construct(protected Server $server)
    {
    }

    public function test(): array
    {
        return [
            'success' => true,
            'message' => ucfirst($this->server->type ?: 'manual') . ' provisioning is configured for manual handling.',
            'data' => [],
        ];
    }

    public function packages(): array
    {
        return [
            'success' => false,
            'message' => 'No remote package sync is available for this server type yet.',
            'packages' => [],
        ];
    }

    public function createAccount(array $payload): array
    {
        return [
            'success' => true,
            'message' => ucfirst($this->server->type ?: 'manual') . ' provisioning will be completed manually.',
            'data' => [],
        ];
    }

    public function createLoginSession(array $payload): array
    {
        return [
            'success' => false,
            'message' => 'One-click login is not available for this server type yet.',
            'data' => [],
        ];
    }
}
