<?php

namespace Plugins\ManualRegistrar\src;

use App\Core\Contracts\DomainRegistrarInterface;

class ManualRegistrar implements DomainRegistrarInterface
{
    public function check(string $domain): array
    {
        return [
            'domain' => $domain,
            'available' => false,
            'message' => 'Manual check required',
        ];
    }

    public function register(string $domain, int $years, array $nameservers, array $contact): array
    {
        return [
            'success' => true,
            'message' => 'Domain flagged for manual registration',
            'domain' => $domain,
        ];
    }

    public function renew(string $domain, int $years): array
    {
        return ['success' => true, 'message' => 'Manual renewal required'];
    }

    public function transfer(string $domain, string $eppCode, int $years): array
    {
        return ['success' => true, 'message' => 'Manual transfer required'];
    }

    public function getNameservers(string $domain): array
    {
        return [];
    }

    public function updateNameservers(string $domain, array $nameservers): bool
    {
        return true;
    }

    public function getEppCode(string $domain): string
    {
        return '';
    }

    public function syncExpiry(string $domain): ?string
    {
        return null;
    }

    public function isConfigured(): bool
    {
        return true;
    }
}