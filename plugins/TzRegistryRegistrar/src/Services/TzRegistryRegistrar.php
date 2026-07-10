<?php

namespace Plugins\TzRegistryRegistrar\src\Services;

use App\Core\ActivityLogger;
use App\Core\Contracts\DomainRegistrarInterface;
use App\Core\Hooks\Action;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Plugins\Domains\src\Models\Domain;
use Plugins\Domains\src\Models\DomainTld;
use Plugins\TzRegistryRegistrar\src\Models\DomainRegistrarOperation;
use Plugins\TzRegistryRegistrar\src\Support\EppResponse;
use Plugins\TzRegistryRegistrar\src\Support\TzRegistryException;

class TzRegistryRegistrar implements DomainRegistrarInterface
{
    public const SLUG = 'tznic';

    public function check(string $domain): array
    {
        $domain = $this->normalizeDomain($domain);
        $cacheKey = 'tznic:epp-check:' . $domain;

        return Cache::remember($cacheKey, (int) Setting::get('tznic_availability_cache_seconds', '300'), function () use ($domain) {
            try {
                $result = $this->client()->checkDomains([$domain])[$domain] ?? null;

                return [
                    'domain' => $domain,
                    'available' => (bool) ($result['available'] ?? false),
                    'message' => $result['message'] ?? (($result['available'] ?? false) ? 'Available' : 'Not available'),
                    'source' => 'tznic-epp',
                    'raw' => $this->safeRaw($result['response'] ?? []),
                ];
            } catch (\Throwable $e) {
                $this->logOperation($domain, 'check', false, $e->getMessage());

                return [
                    'domain' => $domain,
                    'available' => false,
                    'message' => 'Could not verify availability with tzNIC right now.',
                    'source' => 'tznic-epp',
                    'error' => $e->getMessage(),
                ];
            }
        });
    }

    public function register(string $domain, int $years, array $nameservers, array $contact): array
    {
        $domain = $this->normalizeDomain($domain);
        $operation = $this->startOperation($domain, 'register', [
            'years' => $years,
            'nameservers' => $nameservers,
            'contact' => $this->maskContact($contact),
        ]);

        try {
            $client = $this->client();
            $contactId = $this->ensureContact($client, $domain, $contact);
            $response = $client->createDomain($domain, $years, $this->nameservers($nameservers), [
                'registrant' => $contactId,
                'admin' => $contactId,
                'tech' => $contactId,
                'billing' => $contactId,
            ]);

            $this->completeOperation($operation, $response);
            $this->upsertLocalDomain($domain, [
                'registrar' => self::SLUG,
                'registration_date' => $this->parseDate($response->data['created_date'] ?? null) ?? now(),
                'expiry_date' => $this->parseDate($response->data['expiry_date'] ?? null) ?? now()->addYears($years),
                'registration_period' => $years,
                'nameservers' => $this->nameservers($nameservers),
                'status' => 'active',
                'registrar_statuses' => [],
                'registrar_lock' => false,
                'registrar_meta' => [
                    'contact_id' => $contactId,
                    'auth_code_stored' => !empty($response->data['auth_code']),
                ],
            ]);

            Action::do('tznic.domain_registered', $domain, $response->toArray());

            return [
                'success' => true,
                'message' => 'Domain registered successfully through tzNIC.',
                'domain' => $domain,
                'expiry_date' => $response->data['expiry_date'] ?? null,
                'raw' => $this->safeRaw($response->toArray()),
            ];
        } catch (\Throwable $e) {
            $this->failOperation($operation, $e);

            return [
                'success' => false,
                'message' => $this->publicError($e),
                'domain' => $domain,
            ];
        }
    }

    public function renew(string $domain, int $years): array
    {
        $domain = $this->normalizeDomain($domain);
        $local = Domain::where('domain_name', $domain)->first();
        $operation = $this->startOperation($domain, 'renew', ['years' => $years]);

        try {
            $response = $this->client()->renewDomain($domain, $years, $local?->expiry_date?->format('Y-m-d'));
            $this->completeOperation($operation, $response);

            if ($local) {
                $local->update([
                    'expiry_date' => $this->parseDate($response->data['expiry_date'] ?? null) ?? $local->expiry_date?->copy()->addYears($years),
                    'status' => 'active',
                    'last_synced_at' => now(),
                ]);
            }

            Action::do('tznic.domain_renewed', $domain, $response->toArray());

            return [
                'success' => true,
                'message' => 'Domain renewed successfully through tzNIC.',
                'expiry_date' => $response->data['expiry_date'] ?? null,
                'raw' => $this->safeRaw($response->toArray()),
            ];
        } catch (\Throwable $e) {
            $this->failOperation($operation, $e);
            return ['success' => false, 'message' => $this->publicError($e)];
        }
    }

    public function transfer(string $domain, string $eppCode, int $years): array
    {
        $domain = $this->normalizeDomain($domain);
        $operation = $this->startOperation($domain, 'transfer', [
            'years' => $years,
            'epp_code_provided' => $eppCode !== '',
        ]);

        try {
            $response = $this->client()->transferDomain($domain, $eppCode, $years);
            $this->completeOperation($operation, $response);
            Action::do('tznic.domain_transferred', $domain, $response->toArray());

            return [
                'success' => true,
                'message' => 'Domain transfer request submitted to tzNIC.',
                'raw' => $this->safeRaw($response->toArray()),
            ];
        } catch (\Throwable $e) {
            $this->failOperation($operation, $e);
            return ['success' => false, 'message' => $this->publicError($e)];
        }
    }

    public function getNameservers(string $domain): array
    {
        return $this->client()->infoDomain($this->normalizeDomain($domain))['nameservers'] ?? [];
    }

    public function updateNameservers(string $domain, array $nameservers): bool
    {
        $domain = $this->normalizeDomain($domain);
        $operation = $this->startOperation($domain, 'update_nameservers', ['nameservers' => $nameservers]);

        try {
            $info = $this->client()->infoDomain($domain);
            $old = $info['nameservers'] ?? [];
            $new = $this->nameservers($nameservers);

            if ($old === $new) {
                $operation->update(['status' => 'success', 'message' => 'Nameservers already up to date.', 'completed_at' => now()]);
                return true;
            }

            $response = $this->client()->updateNameservers($domain, $old, $new);
            $this->completeOperation($operation, $response);

            Domain::where('domain_name', $domain)->update([
                'nameservers' => $new,
                'last_synced_at' => now(),
            ]);

            return true;
        } catch (\Throwable $e) {
            $this->failOperation($operation, $e);
            return false;
        }
    }

    public function getEppCode(string $domain): string
    {
        $info = $this->client()->infoDomain($this->normalizeDomain($domain));
        return (string) ($info['auth_code'] ?? '');
    }

    public function syncExpiry(string $domain): ?string
    {
        $domain = $this->normalizeDomain($domain);

        try {
            $info = $this->client()->infoDomain($domain);
            $expiry = $this->parseDate($info['expiry_date'] ?? null);

            Domain::where('domain_name', $domain)->update([
                'expiry_date' => $expiry,
                'nameservers' => $info['nameservers'] ?? [],
                'registrar_statuses' => $info['statuses'] ?? [],
                'registrar_domain_id' => $info['registrar_domain_id'] ?? null,
                'registrar_lock' => in_array('clientTransferProhibited', $info['statuses'] ?? [], true),
                'last_synced_at' => now(),
                'registrar_meta' => [
                    'registrant' => $info['registrant'] ?? null,
                    'last_info_response' => $this->safeRaw($info['response'] ?? []),
                ],
            ]);

            Action::do('tznic.domain_synced', $domain, $info);

            return $expiry?->format('Y-m-d');
        } catch (\Throwable $e) {
            $this->logOperation($domain, 'sync_expiry', false, $e->getMessage());
            return null;
        }
    }

    public function syncAllDomains(int $limit = 100): array
    {
        $domains = Domain::where('registrar', self::SLUG)
            ->orWhere(fn ($query) => $query->whereNull('registrar')->whereIn('tld', $this->supportedTlds()))
            ->orderByRaw('last_synced_at IS NULL DESC')
            ->orderBy('last_synced_at')
            ->limit($limit)
            ->get();

        $synced = 0;
        $failed = 0;

        foreach ($domains as $domain) {
            $this->syncExpiry($domain->domain_name) ? $synced++ : $failed++;
        }

        return compact('synced', 'failed');
    }

    public function setTransferLock(string $domain, bool $locked): bool
    {
        $domain = $this->normalizeDomain($domain);
        $operation = $this->startOperation($domain, $locked ? 'lock' : 'unlock');

        try {
            $response = $this->client()->setTransferLock($domain, $locked);
            $this->completeOperation($operation, $response);
            Domain::where('domain_name', $domain)->update(['registrar_lock' => $locked, 'last_synced_at' => now()]);

            return true;
        } catch (\Throwable $e) {
            $this->failOperation($operation, $e);
            return false;
        }
    }

    public function syncPricing(): array
    {
        $source = trim((string) Setting::get('tznic_pricing_json', ''));
        $updated = 0;

        if ($source === '') {
            $this->logPricingSync('failed', 0, 'No pricing JSON configured.');
            return ['success' => false, 'updated' => 0, 'message' => 'No pricing JSON configured.'];
        }

        $payload = json_decode($source, true);

        if (!is_array($payload)) {
            $this->logPricingSync('failed', 0, 'Pricing JSON is invalid.');
            return ['success' => false, 'updated' => 0, 'message' => 'Pricing JSON is invalid.'];
        }

        foreach ($payload as $row) {
            $tld = '.' . ltrim(strtolower((string) ($row['tld'] ?? '')), '.');
            if (!in_array($tld, $this->supportedTlds(), true)) {
                continue;
            }

            $domainTld = DomainTld::where('tld', $tld)->first();

            if (!$domainTld) {
                continue;
            }

            $domainTld->update(['registrar_slug' => self::SLUG]);
            $pricing = $domainTld->pricing()->firstOrNew(['years' => (int) ($row['years'] ?? 1)]);
            $pricing->fill([
                'register_price' => (float) ($row['register'] ?? $row['registration'] ?? 0),
                'renewal_price' => (float) ($row['renewal'] ?? 0),
                'transfer_price' => (float) ($row['transfer'] ?? 0),
                'is_active' => true,
            ])->save();

            $updated++;
        }

        $message = "{$updated} TLD price row(s) updated.";
        $this->logPricingSync('success', $updated, $message, $payload);

        return ['success' => true, 'updated' => $updated, 'message' => $message];
    }

    public function isConfigured(): bool
    {
        return Setting::get('tznic_enabled', '0') === '1'
            && filled(Setting::get('tznic_host'))
            && filled(Setting::get('tznic_username'))
            && filled(Setting::get('tznic_password'))
            && filled(Setting::get('tznic_certificate_path'));
    }

    public function supportedTlds(): array
    {
        return ['.tz', '.co.tz', '.or.tz', '.go.tz', '.ac.tz', '.ne.tz', '.sc.tz', '.me.tz', '.hotel.tz', '.mobi.tz', '.info.tz', '.tv.tz'];
    }

    protected function client(): TzRegistryEppClient
    {
        if (!$this->isConfigured()) {
            throw new TzRegistryException('tzNIC registrar is not fully configured.');
        }

        return new TzRegistryEppClient(
            host: Setting::get('tznic_host', 'epp.tznic.or.tz'),
            port: (int) Setting::get('tznic_port', '700'),
            username: Setting::get('tznic_username', ''),
            password: Setting::get('tznic_password', ''),
            certificatePath: Setting::get('tznic_certificate_path', ''),
            privateKeyPath: Setting::get('tznic_private_key_path') ?: null,
            privateKeyPassphrase: Setting::get('tznic_private_key_passphrase') ?: null,
            verifyPeer: Setting::get('tznic_verify_peer', '1') === '1',
            timeout: (int) Setting::get('tznic_timeout', '30'),
            minimumInterval: (float) Setting::get('tznic_rate_limit_seconds', '0.5'),
            logXml: Setting::get('tznic_log_xml', '0') === '1',
        );
    }

    protected function ensureContact(TzRegistryEppClient $client, string $domain, array $contact): string
    {
        if (!empty($contact['tznic_contact_id'])) {
            return $contact['tznic_contact_id'];
        }

        $response = $client->createContact([
            'id' => 'JV-' . strtoupper(Str::slug($domain, '-')) . '-' . strtoupper(Str::random(5)),
            'name' => trim(($contact['first_name'] ?? '') . ' ' . ($contact['last_name'] ?? '')) ?: ($contact['name'] ?? 'Domain Owner'),
            'org' => $contact['company'] ?? $contact['company_name'] ?? '',
            'email' => $contact['email'] ?? Setting::get('company_email', 'admin@example.com'),
            'phone' => $contact['phone'] ?? Setting::get('company_phone', '+255000000000'),
            'street1' => $contact['address'] ?? 'N/A',
            'city' => $contact['city'] ?? 'Dar es Salaam',
            'province' => $contact['state'] ?? $contact['region'] ?? '',
            'postal' => $contact['postcode'] ?? '00000',
            'country' => $this->countryCode($contact['country'] ?? 'TZ'),
        ]);

        return $response->data['contact_id'];
    }

    protected function startOperation(string $domain, string $operation, array $payload = []): DomainRegistrarOperation
    {
        return DomainRegistrarOperation::create([
            'domain_id' => Domain::where('domain_name', $domain)->value('id'),
            'registrar_slug' => self::SLUG,
            'domain_name' => $domain,
            'operation' => $operation,
            'status' => 'pending',
            'request_payload' => $payload,
        ]);
    }

    protected function completeOperation(DomainRegistrarOperation $operation, EppResponse $response): void
    {
        $operation->update([
            'status' => 'success',
            'client_transaction_id' => $response->clientTransactionId,
            'server_transaction_id' => $response->serverTransactionId,
            'message' => $response->message,
            'response_payload' => $this->safeRaw($response->toArray()),
            'completed_at' => now(),
        ]);
    }

    protected function failOperation(DomainRegistrarOperation $operation, \Throwable $e): void
    {
        $operation->update([
            'status' => 'failed',
            'message' => $e->getMessage(),
            'response_payload' => $e instanceof TzRegistryException ? $this->safeRaw($e->context() ?? []) : null,
            'completed_at' => now(),
        ]);

        ActivityLogger::log('error', 'Domain', $operation->domain_id, "tzNIC {$operation->operation} failed for {$operation->domain_name}: {$e->getMessage()}");
    }

    protected function logOperation(string $domain, string $operation, bool $success, string $message): void
    {
        DomainRegistrarOperation::create([
            'domain_id' => Domain::where('domain_name', $domain)->value('id'),
            'registrar_slug' => self::SLUG,
            'domain_name' => $domain,
            'operation' => $operation,
            'status' => $success ? 'success' : 'failed',
            'message' => $message,
            'completed_at' => now(),
        ]);
    }

    protected function logPricingSync(string $status, int $updated, string $message, array $payload = []): void
    {
        if (!Schema::hasTable('domain_pricing_sync_logs')) {
            return;
        }

        DB::table('domain_pricing_sync_logs')->insert([
            'registrar_slug' => self::SLUG,
            'status' => $status,
            'updated_count' => $updated,
            'message' => $message,
            'payload' => $payload ? json_encode($payload) : null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    protected function upsertLocalDomain(string $domain, array $attributes): void
    {
        if (!Schema::hasTable('domains')) {
            return;
        }

        Domain::where('domain_name', $domain)->update($attributes);
    }

    protected function nameservers(array $nameservers): array
    {
        $fallback = [
            Setting::get('domain_default_ns1', 'ns1.jamvini.co.tz'),
            Setting::get('domain_default_ns2', 'ns2.jamvini.co.tz'),
            Setting::get('domain_default_ns3', ''),
            Setting::get('domain_default_ns4', ''),
        ];

        return collect($nameservers ?: $fallback)
            ->map(fn ($nameserver) => strtolower(trim((string) $nameserver)))
            ->filter()
            ->unique()
            ->take(4)
            ->values()
            ->all();
    }

    protected function parseDate(?string $date): ?Carbon
    {
        if (!$date) {
            return null;
        }

        try {
            return Carbon::parse($date);
        } catch (\Throwable) {
            return null;
        }
    }

    protected function normalizeDomain(string $domain): string
    {
        return strtolower(trim($domain, ". \t\n\r\0\x0B"));
    }

    protected function countryCode(string $country): string
    {
        $country = strtoupper(trim($country));
        return match ($country) {
            'TANZANIA', 'UNITED REPUBLIC OF TANZANIA' => 'TZ',
            default => strlen($country) === 2 ? $country : 'TZ',
        };
    }

    protected function publicError(\Throwable $e): string
    {
        if ($e instanceof TzRegistryException && $e->eppCode()) {
            return "tzNIC returned {$e->eppCode()}: {$e->getMessage()}";
        }

        return $e->getMessage();
    }

    protected function maskContact(array $contact): array
    {
        return [
            'name' => trim(($contact['first_name'] ?? '') . ' ' . ($contact['last_name'] ?? '')),
            'email' => isset($contact['email']) ? Str::mask($contact['email'], '*', 2, max(3, strlen($contact['email']) - 6)) : null,
            'phone_present' => !empty($contact['phone']),
            'company' => $contact['company'] ?? $contact['company_name'] ?? null,
        ];
    }

    protected function safeRaw(array $payload): array
    {
        unset($payload['xml']);

        if (isset($payload['data']['auth_code'])) {
            $payload['data']['auth_code'] = '***';
        }

        return $payload;
    }
}
