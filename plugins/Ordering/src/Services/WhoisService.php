<?php

namespace Plugins\Ordering\src\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use App\Core\Registries\RegistrarRegistry;

class WhoisService
{
    protected array $whoisServers = [
        'com' => 'whois.verisign-grs.com',
        'net' => 'whois.verisign-grs.com',
        'org' => 'whois.pir.org',
        'io' => 'whois.nic.io',
        'co' => 'whois.nic.co',
        'africa' => 'whois.nic.africa',
        'co.tz' => 'whois.tznic.or.tz',
        'or.tz' => 'whois.tznic.or.tz',
        'go.tz' => 'whois.tznic.or.tz',
        'ac.tz' => 'whois.tznic.or.tz',
        'me' => 'whois.nic.me',
        'xyz' => 'whois.nic.xyz',
        'online' => 'whois.nic.online',
        'store' => 'whois.nic.store',
        'site' => 'whois.nic.site',
        'tech' => 'whois.nic.tech',
        'dev' => 'whois.nic.dev',
        'app' => 'whois.nic.google',
    ];

    /**
     * Check domain availability.
     */
    public function check(string $domain): array
    {
        $domain = $this->normalizeDomain($domain);
        $domainParts = $this->resolveDomainParts($domain);

        if (!$domainParts) {
            return $this->result($domain, false, 'Enter a complete domain, for example mybusiness.co.tz');
        }

        $tld = $domainParts['tld'];
        $sld = $domainParts['sld'];
        $tldConfig = $domainParts['config'];

        if (strlen($sld) < 2) {
            return $this->result($domain, false, 'Domain name too short');
        }

        if (!$tldConfig) {
            return $this->result($domain, false, 'We do not register .' . $tld . ' domains');
        }

        // Check cache
        $cacheKey = "whois:{$domain}";
        if (Cache::has($cacheKey)) {
            $cached = Cache::get($cacheKey);
            $cached['tld_config'] = $this->formatTldConfig($tldConfig);
            return $cached;
        }

        $registrarResult = $this->checkViaRegistrar($domain, $tldConfig);

        if ($registrarResult) {
            $available = $registrarResult['available'];
            $message = $registrarResult['message'] ?? ($available ? 'Available!' : 'Already registered');
        } else {
            $server = $this->whoisServers[$tld] ?? null;

            if ($server) {
                try {
                    $response = $this->queryWhois($domain, $server);
                    $available = $this->isAvailable($response, $tld);
                } catch (\Exception $e) {
                    $available = false;
                }
            } else {
                $available = $this->checkRdapAvailability($domain);
            }

            $message = $available ? 'Available!' : 'Already registered';
        }

        $data = $this->result($domain, $available, $message);
        $data['tld_config'] = $this->formatTldConfig($tldConfig);
        
        Cache::put($cacheKey, $data, 3600);
        
        return $data;
    }

    protected function checkViaRegistrar(string $domain, $tldConfig): ?array
    {
        $registrarSlug = $tldConfig->registrar_slug ?: get_registrar_for_tld($tldConfig->tld);

        if (!$registrarSlug) {
            return null;
        }

        $config = RegistrarRegistry::get($registrarSlug);

        if (empty($config['class']) || !class_exists($config['class'])) {
            return null;
        }

        try {
            $registrar = app($config['class']);
            if (!method_exists($registrar, 'isConfigured') || !$registrar->isConfigured()) {
                return null;
            }

            $result = $registrar->check($domain);

            return [
                'available' => (bool) ($result['available'] ?? false),
                'message' => $result['message'] ?? null,
            ];
        } catch (\Throwable) {
            return null;
        }
    }

    protected function legacyCheck(string $domain, string $tld): bool
    {
        $server = $this->whoisServers[$tld] ?? null;

        if ($server) {
            try {
                $response = $this->queryWhois($domain, $server);
                return $this->isAvailable($response, $tld);
            } catch (\Exception $e) {
                return false;
            }
        }

        return $this->checkRdapAvailability($domain);
    }

    /**
     * Bulk check multiple domains.
     */
    public function bulkCheck(array $domains): array
    {
        $results = [];
        foreach ($domains as $domain) {
            $results[$domain] = $this->check($domain);
        }
        return $results;
    }

    /**
     * Get domain suggestions.
     */
    public function suggestions(string $query): array
    {
        $name = $this->extractSearchName($query);
        $suggestions = [];

        if (strlen($name) < 2) {
            return [];
        }

        // Get all active TLDs from our database
        $activeTlds = \Plugins\Domains\src\Models\DomainTld::where('is_active', true)
            ->with(['pricing', 'addons'])
            ->get();

        foreach ($activeTlds as $tldConfig) {
            $tld = ltrim($tldConfig->tld, '.');
            $fullDomain = $name . '.' . $tld;
            $result = $this->check($fullDomain);
            if ($result['available']) {
                $suggestions[] = $result;
            }
        }

        return $suggestions;
    }

    /**
     * Query a WHOIS server directly.
     */
    protected function queryWhois(string $domain, string $server): string
    {
        $fp = @fsockopen($server, 43, $errno, $errstr, 10);
        
        if (!$fp) {
            throw new \Exception("Cannot connect to WHOIS server: {$errstr}");
        }

        fwrite($fp, $domain . "\r\n");
        
        $response = '';
        while (!feof($fp)) {
            $response .= fread($fp, 8192);
        }
        fclose($fp);

        return $response;
    }

    /**
     * Try RDAP lookup as fallback.
     */
    protected function checkRdap(string $domain): array
    {
        $url = "https://rdap.org/domain/" . $domain;
        
        try {
            $context = stream_context_create([
                'http' => ['timeout' => 10, 'header' => "Accept: application/json\r\n"]
            ]);
            
            $response = @file_get_contents($url, false, $context);
            
            if ($response) {
                $data = json_decode($response, true);
                $available = empty($data['name']);
                return $this->result($domain, $available, $available ? 'Domain appears available' : 'Domain is registered');
            }
        } catch (\Exception $e) {
            // Both methods failed
        }

        return $this->result($domain, false, 'Could not verify. Please try again.');
    }

    /**
     * Determine if domain is available from WHOIS response.
     */
    protected function isAvailable(string $response, string $tld): bool
    {
        $response = strtolower($response);

        // Common "available" patterns
        $availablePatterns = [
            'no match for',
            'not found',
            'no data found',
            'domain not found',
            'no entries found',
            'status: free',
            'status: available',
            'no matching record',
            'the queried object does not exist',
            'domain does not exist',
        ];

        foreach ($availablePatterns as $pattern) {
            if (str_contains($response, $pattern)) {
                return true;
            }
        }

        // If we get a meaningful response, it's likely registered
        if (strlen($response) > 50) {
            return false;
        }

        return true;
    }

    /**
     * Format result.
     */
    protected function result(string $domain, bool $available, string $message): array
    {
        $parts = $this->resolveDomainParts($domain);
        
        return [
            'domain' => $domain,
            'sld' => $parts['sld'] ?? explode('.', $domain)[0] ?? '',
            'tld' => $parts['tld'] ?? '',
            'available' => $available,
            'message' => $message,
            'checked_at' => now()->toDateTimeString(),
        ];
    }

    private function checkRdapAvailability(string $domain): bool
{
    try {
        $context = stream_context_create([
            'http' => ['timeout' => 10, 'header' => "Accept: application/json\r\n"]
        ]);
        $response = @file_get_contents("https://rdap.org/domain/" . $domain, false, $context);
        if ($response) {
            $data = json_decode($response, true);
            return empty($data['name']);
        }
    } catch (\Exception $e) {}
    return false;
}

private function formatTldConfig($tldConfig): ?array
{
    if (!$tldConfig) return null;

    return [
        'tld' => $tldConfig->tld,
        'service_id' => $tldConfig->service_id,
        'registrar_slug' => $tldConfig->registrar_slug,  // ← ADD THIS
        'pricing' => $tldConfig->pricing->map(fn($p) => [
            'years' => $p->years,
            'register' => $p->register_price,
            'renewal' => $p->renewal_price,
            'transfer' => $p->transfer_price,
        ])->toArray(),
        'addons' => $tldConfig->addons->map(fn($a) => [
            'name' => $a->name,
            'price' => $a->price,
            'type' => $a->type,
        ])->toArray(),
        'auto_register' => $tldConfig->auto_register,
    ];
}

private function normalizeDomain(string $domain): string
{
    $domain = strtolower(trim($domain));
    $domain = preg_replace('#^https?://#', '', $domain);
    $domain = preg_replace('#/.*$#', '', $domain);
    $domain = preg_replace('/^www\./', '', $domain);
    $domain = preg_replace('/\s+/', '', $domain);

    return trim((string) $domain, '.');
}

private function extractSearchName(string $query): string
{
    $query = $this->normalizeDomain($query);
    $parts = $this->resolveDomainParts($query);

    if ($parts) {
        return $parts['sld'];
    }

    return explode('.', $query)[0] ?? $query;
}

private function resolveDomainParts(string $domain): ?array
{
    if (!str_contains($domain, '.')) {
        return null;
    }

    $configs = \Plugins\Domains\src\Models\DomainTld::where('is_active', true)
        ->with(['pricing', 'addons'])
        ->get()
        ->sortByDesc(fn ($config) => strlen(ltrim($config->tld, '.')));

    foreach ($configs as $config) {
        $tld = strtolower(ltrim($config->tld, '.'));

        if (!str_ends_with($domain, '.' . $tld)) {
            continue;
        }

        $sld = substr($domain, 0, -1 * (strlen($tld) + 1));

        if ($sld === '' || str_contains($sld, '.')) {
            continue;
        }

        return [
            'sld' => $sld,
            'tld' => $tld,
            'config' => $config,
        ];
    }

    $parts = explode('.', $domain, 2);

    return [
        'sld' => $parts[0] ?? '',
        'tld' => $parts[1] ?? '',
        'config' => null,
    ];
}

/**
 * Check if a domain is eligible for transfer.
 */
public function checkTransferEligibility(string $domain): array
{
    $result = $this->check($domain);
    
    // Domain must be registered (not available)
    if ($result['available']) {
        $result['transfer_eligible'] = false;
        $result['transfer_message'] = 'Domain is not registered. Use Register instead.';
        return $result;
    }
    
    // Must have a TLD config
    if (empty($result['tld_config'])) {
        $result['transfer_eligible'] = false;
        $result['transfer_message'] = 'We do not support transfers for this TLD.';
        return $result;
    }
    
    // Check if TLD supports transfers (has transfer pricing)
    $hasTransferPricing = false;
    foreach ($result['tld_config']['pricing'] as $p) {
        if (($p['transfer'] ?? 0) > 0) {
            $hasTransferPricing = true;
            break;
        }
    }
    
    if (!$hasTransferPricing) {
        $result['transfer_eligible'] = false;
        $result['transfer_message'] = 'This TLD does not support transfers yet.';
        return $result;
    }
    
    $result['transfer_eligible'] = true;
    $result['transfer_message'] = 'Domain is eligible for transfer.';
    return $result;
}
}
