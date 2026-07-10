<?php

namespace App\Core\Contracts;

interface DomainRegistrarInterface
{
    /**
     * Check domain availability.
     */
    public function check(string $domain): array;

    /**
     * Register a new domain.
     */
    public function register(string $domain, int $years, array $nameservers, array $contact): array;

    /**
     * Renew a domain.
     */
    public function renew(string $domain, int $years): array;

    /**
     * Transfer a domain.
     */
    public function transfer(string $domain, string $eppCode, int $years): array;

    /**
     * Get nameservers for a domain.
     */
    public function getNameservers(string $domain): array;

    /**
     * Update nameservers for a domain.
     */
    public function updateNameservers(string $domain, array $nameservers): bool;

    /**
     * Get EPP/Auth code for a domain.
     */
    public function getEppCode(string $domain): string;

    /**
     * Sync domain expiry date from registry.
     */
    public function syncExpiry(string $domain): ?string;

    /**
     * Check if registrar is properly configured with credentials.
     */
    public function isConfigured(): bool;
}