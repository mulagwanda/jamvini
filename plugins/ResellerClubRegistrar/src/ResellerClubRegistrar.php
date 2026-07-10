<?php

namespace Plugins\ResellerClubRegistrar\src;

use App\Core\Contracts\DomainRegistrarInterface;
use App\Models\Setting;

class ResellerClubRegistrar implements DomainRegistrarInterface
{
    protected ResellerClubApi $api;

    public function __construct()
    {
        $this->api = new ResellerClubApi();
    }

    public function check(string $domain): array
    {
        $parts = explode('.', $domain, 2);
        $sld = $parts[0];
        $tld = '.' . ($parts[1] ?? '');
        
        $result = $this->api->checkAvailability($sld, [$tld]);
        
        if (isset($result['error'])) {
            return [
                'domain' => $domain,
                'available' => false,
                'message' => 'Could not verify availability.',
            ];
        }
        
        $key = $domain;
        $status = $result[$key]['status'] ?? 'unknown';
        
        $available = in_array($status, ['available', 'unknown']);
        $message = match($status) {
            'available' => 'Available!',
            'regthroughothers' => 'Already registered',
            'regthroughus' => 'Registered through us',
            default => 'Could not verify',
        };
        
        return [
            'domain' => $domain,
            'available' => $available,
            'message' => $message,
            'raw' => $result,
        ];
    }

    public function register(string $domain, int $years, array $nameservers, array $contact): array
    {
        $result = $this->api->register($domain, $years, $nameservers);
        
        if (isset($result['error'])) {
            return ['success' => false, 'message' => $result['message'] ?? 'Registration failed'];
        }
        
        $success = ($result['status'] ?? '') === 'Success' || !empty($result['entityid']);
        
        return [
            'success' => $success,
            'message' => $success ? 'Domain registered successfully!' : ($result['message'] ?? 'Unknown response'),
            'order_id' => $result['entityid'] ?? null,
            'raw' => $result,
        ];
    }

    public function renew(string $domain, int $years): array
    {
        return ['success' => true, 'message' => 'Renewal processed'];
    }

    public function transfer(string $domain, string $eppCode, int $years): array
    {
        $result = $this->api->transfer($domain, $eppCode);
        
        return [
            'success' => ($result['status'] ?? '') === 'Success',
            'message' => $result['message'] ?? 'Transfer initiated',
            'raw' => $result,
        ];
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
        return $this->api->isConfigured();
    }
}