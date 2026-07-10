<?php

namespace Plugins\Hosting\src\Provisioning;

use App\Core\ActivityLogger;
use App\Core\Contracts\ProvisioningModuleInterface;
use App\Core\Provisioning\ProvisioningResult;
use Illuminate\Support\Str;
use Plugins\Orders\src\Models\Order;
use Plugins\Orders\src\Models\OrderItem;
use Plugins\Services\src\Connectors\ServerConnectorFactory;
use Plugins\Services\src\Models\ClientService;

class CpanelProvisioningModule implements ProvisioningModuleInterface
{
    public function slug(): string
    {
        return 'cpanel';
    }

    public function name(): string
    {
        return 'cPanel / WHM';
    }

    public function supports(string $serverType): bool
    {
        return $serverType === 'cpanel';
    }

    public function provision(Order $order, OrderItem $item, ClientService $clientService, bool $manualIsSuccess = true): ProvisioningResult
    {
        if ($item->type !== 'hosting') {
            return ProvisioningResult::success('Remote provisioning not required.');
        }

        $service = $item->service;
        $server = $service?->servers?->first(fn ($srv) => (bool) $srv->pivot?->is_default) ?: $service?->servers?->first();

        if (!$server) {
            return ProvisioningResult::manual('Manual provisioning: no server assigned.', $manualIsSuccess);
        }

        $domain = $this->cleanDomain($item->domain ?: $clientService->domain);

        if (!$domain) {
            ActivityLogger::log('provisioning.validation.failed', 'Order', $order->id, 'cPanel account not created because domain is missing.', [
                'order_item_id' => $item->id,
                'service_id' => $item->service_id,
                'server_id' => $server->id,
            ]);

            return ProvisioningResult::failed('A domain is required to create a cPanel account.');
        }

        $package = $server->pivot?->package_name;

        if (!$package) {
            ActivityLogger::log('provisioning.validation.failed', 'Order', $order->id, 'cPanel account not created because no WHM package is assigned.', [
                'order_item_id' => $item->id,
                'service_id' => $item->service_id,
                'server_id' => $server->id,
            ]);

            return ProvisioningResult::failed('No WHM package is assigned to this service.');
        }

        $remoteUsername = $clientService->remote_username ?: $this->generateUsername($domain, $clientService->id);
        $remotePassword = Str::password(20, true, true, false, false);
        $payload = [
            'domain' => $domain,
            'username' => $remoteUsername,
            'password' => $remotePassword,
            'email' => $order->client?->technical_email ?: ($order->client?->billing_email ?: $order->client?->email),
            'package' => $package,
        ];

        ActivityLogger::log('provisioning.request.sent', 'Order', $order->id, 'WHM create account request sent for ' . $domain, [
            'order_item_id' => $item->id,
            'service_id' => $item->service_id,
            'client_service_id' => $clientService->id,
            'server_id' => $server->id,
            'server_name' => $server->name,
            'package' => $package,
            'request' => collect($payload)->except('password')->all() + ['password' => '[hidden]'],
        ]);

        $result = app(ServerConnectorFactory::class)->for($server)->createAccount($payload);

        ActivityLogger::log($result['success'] ? 'provisioning.response.received' : 'provisioning.response.failed', 'Order', $order->id, $result['message'], [
            'order_item_id' => $item->id,
            'service_id' => $item->service_id,
            'client_service_id' => $clientService->id,
            'server_id' => $server->id,
            'server_name' => $server->name,
            'package' => $package,
            'details' => $result['data'] ?? [],
        ]);

        if (!$result['success']) {
            return ProvisioningResult::failed('cPanel account failed on ' . $server->name . ': ' . $result['message'], $result['data'] ?? []);
        }

        $server->increment('current_accounts');
        $clientService->update([
            'server_id' => $server->id,
            'control_panel' => $server->type,
            'remote_username' => $remoteUsername,
            'remote_domain' => $domain,
            'notes' => trim(($clientService->notes ? $clientService->notes . "\n" : '') . 'cPanel account created on ' . $server->name . ' from order #' . $order->order_number . '.'),
        ]);

        $clientService->setProperty('panel', 'cPanel / WHM', 'Control Panel', false, true);
        $clientService->setProperty('server_name', $server->name, 'Server', false, true);
        $clientService->setProperty('server_id', (string) $server->id, 'Server ID');
        $clientService->setProperty('package', $package, 'Hosting Package', false, true);
        $clientService->setProperty('remote_username', $remoteUsername, 'Username', false, true);
        $clientService->setProperty('remote_domain', $domain, 'Domain', false, true);
        $clientService->setProperty('remote_password', $remotePassword, 'Initial Password', true);

        return ProvisioningResult::success('cPanel account created on ' . $server->name . ': ' . $result['message'], $result['data'] ?? []);
    }

    public function suspend(ClientService $clientService, string $reason = ''): ProvisioningResult
    {
        return ProvisioningResult::manual('cPanel suspension API support is prepared but not enabled yet.', false);
    }

    public function unsuspend(ClientService $clientService): ProvisioningResult
    {
        return ProvisioningResult::manual('cPanel unsuspension API support is prepared but not enabled yet.', false);
    }

    public function terminate(ClientService $clientService): ProvisioningResult
    {
        return ProvisioningResult::manual('cPanel termination API support is prepared but not enabled yet.', false);
    }

    protected function cleanDomain(?string $domain): ?string
    {
        $domain = strtolower(trim((string) $domain));
        $domain = preg_replace('#^https?://#', '', $domain);
        $domain = trim($domain, "/ \t\n\r\0\x0B");

        return preg_match('/^(?!-)[a-z0-9-]+(\.[a-z0-9-]+)+$/', $domain) ? $domain : null;
    }

    protected function generateUsername(string $domain, int $clientServiceId): string
    {
        $label = Str::before($domain, '.');
        $username = preg_replace('/[^a-z0-9]/', '', strtolower($label)) ?: 'acct';
        $username = preg_match('/^[a-z]/', $username) ? $username : 'u' . $username;
        $username = substr($username, 0, 8);

        return substr($username . $clientServiceId, 0, 16);
    }
}
