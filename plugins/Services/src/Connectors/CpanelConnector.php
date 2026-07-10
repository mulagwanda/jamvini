<?php

namespace Plugins\Services\src\Connectors;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use InvalidArgumentException;
use Plugins\Services\src\Models\Server;

class CpanelConnector implements ServerConnector
{
    public function __construct(protected Server $server)
    {
    }

    public function test(): array
    {
        return $this->request('version');
    }

    public function packages(): array
    {
        $result = $this->request('listpkgs');

        if (!$result['success']) {
            return [
                'success' => false,
                'message' => $result['message'],
                'packages' => [],
            ];
        }

        $packages = collect(data_get($result, 'data.response.data.pkg', []))
            ->filter(fn ($package) => is_array($package) && !empty($package['name']))
            ->map(fn ($package) => [
                'name' => (string) $package['name'],
                'display_name' => (string) ($package['name'] ?? ''),
                'limits' => [
                    'quota' => $package['QUOTA'] ?? null,
                    'bandwidth' => $package['BWLIMIT'] ?? null,
                    'ftp_accounts' => $package['MAXFTP'] ?? null,
                    'email_accounts' => $package['MAXPOP'] ?? null,
                    'databases' => $package['MAXSQL'] ?? null,
                    'addon_domains' => $package['MAXADDON'] ?? null,
                    'parked_domains' => $package['MAXPARK'] ?? null,
                    'subdomains' => $package['MAXSUB'] ?? null,
                    'feature_list' => $package['FEATURELIST'] ?? null,
                ],
            ])
            ->values()
            ->all();

        return [
            'success' => true,
            'message' => count($packages) . ' WHM package(s) found.',
            'packages' => $packages,
        ];
    }

    public function createAccount(array $payload): array
    {
        $params = array_filter([
            'domain' => $payload['domain'] ?? null,
            'username' => $payload['username'] ?? null,
            'password' => $payload['password'] ?? null,
            'contactemail' => $payload['email'] ?? null,
            'plan' => $payload['package'] ?? null,
            'cgi' => 1,
            'useregns' => 0,
        ], fn ($value) => $value !== null && $value !== '');

        foreach (['domain', 'username', 'password', 'plan'] as $required) {
            if (empty($params[$required])) {
                return [
                    'success' => false,
                    'message' => 'Missing required cPanel account field: ' . $required,
                    'data' => [],
                ];
            }
        }

        return $this->request('createacct', $params, ['password']);
    }

    public function createLoginSession(array $payload): array
    {
        $params = array_filter([
            'user' => $payload['username'] ?? null,
            'service' => $payload['service'] ?? 'cpaneld',
            'locale' => $payload['locale'] ?? null,
        ], fn ($value) => $value !== null && $value !== '');

        if (empty($params['user'])) {
            return [
                'success' => false,
                'message' => 'cPanel username is required.',
                'data' => [],
            ];
        }

        $result = $this->request('create_user_session', $params);

        if ($result['success'] && data_get($result, 'data.response.data.url')) {
            $result['url'] = data_get($result, 'data.response.data.url');
        }

        return $result;
    }

    protected function request(string $function, array $params = [], array $sensitiveKeys = []): array
    {
        if (!$this->server->api_token || !$this->server->username) {
            return [
                'success' => false,
                'message' => 'WHM username and API token are required.',
                'data' => [],
            ];
        }

        try {
            $endpoint = $this->endpoint($function);
            $requestParams = array_merge([
                'api.version' => 1,
            ], $params);
            $response = $this->client()->get($endpoint, $requestParams);
            $requestLog = [
                'function' => $function,
                'endpoint' => $endpoint,
                'params' => $this->maskSensitive($requestParams, $sensitiveKeys),
            ];

            if (!$response->ok()) {
                return [
                    'success' => false,
                    'message' => 'WHM returned HTTP ' . $response->status() . '.',
                    'data' => [
                        'request' => $requestLog,
                        'response' => $this->sanitizeResponse($response->json() ?? []),
                    ],
                ];
            }

            $data = $this->sanitizeResponse($response->json() ?? []);
            $success = (int) data_get($data, 'metadata.result', 1) === 1;

            return [
                'success' => $success,
                'message' => data_get($data, 'metadata.reason') ?: ($success ? 'WHM request completed.' : 'WHM request failed.'),
                'data' => [
                    'request' => $requestLog,
                    'response' => $data,
                ],
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'data' => [],
            ];
        }
    }

    protected function client(): PendingRequest
    {
        return Http::connectTimeout(8)
            ->timeout(20)
            ->retry(2, 250)
            ->withHeaders([
                'Authorization' => 'whm ' . $this->server->username . ':' . $this->server->api_token,
                'Accept' => 'application/json',
            ]);
    }

    protected function endpoint(string $function): string
    {
        $scheme = $this->server->use_ssl ? 'https' : 'http';
        $host = $this->cleanHost($this->server->hostname);
        $port = $this->server->port ?: ($this->server->use_ssl ? 2087 : 2086);

        return "{$scheme}://{$host}:{$port}/json-api/{$function}";
    }

    protected function cleanHost(string $host): string
    {
        $host = strtolower(trim($host));
        $host = preg_replace('#^https?://#', '', $host);
        $host = preg_replace('#/.*$#', '', $host);

        if (!preg_match('/^[a-z0-9.-]+$/', $host)) {
            throw new InvalidArgumentException('Invalid WHM hostname configured.');
        }

        return $host;
    }

    protected function maskSensitive(array $params, array $sensitiveKeys): array
    {
        $sensitiveKeys = array_unique(array_merge($sensitiveKeys, [
            'password',
            'pass',
            'api_token',
            'token',
            'accesshash',
            'authorization',
        ]));

        foreach ($sensitiveKeys as $key) {
            if (array_key_exists($key, $params)) {
                $params[$key] = '[hidden]';
            }
        }

        return $params;
    }

    protected function sanitizeResponse(array $data): array
    {
        foreach ($data as $key => $value) {
            $normalized = strtolower((string) $key);

            if (in_array($normalized, ['password', 'pass', 'api_token', 'token', 'accesshash', 'authorization'], true)) {
                $data[$key] = '[hidden]';
                continue;
            }

            if (is_array($value)) {
                $data[$key] = $this->sanitizeResponse($value);
            }
        }

        return $data;
    }
}
