<?php

namespace App\Core\Api;

use App\Core\Hooks\Action;
use Plugins\Clients\src\Models\Client;
use Plugins\Domains\src\Models\Domain;
use Plugins\Invoices\src\Models\Invoice;
use Plugins\Services\src\Models\ClientService;

class InternalApi
{
    public function call(string $action, array $params = []): array
    {
        $extensionResult = apply_filters('api.local_action', null, $action, $params);
        if (is_array($extensionResult)) {
            return $extensionResult;
        }

        return match ($action) {
            'CreateClient' => $this->createClient($params),
            'UpdateClient' => $this->updateClient($params),
            'GetClients' => ['success' => true, 'data' => Client::query()->latest()->limit($params['limit'] ?? 50)->get()],
            'GetInvoices' => ['success' => true, 'data' => Invoice::query()->latest()->limit($params['limit'] ?? 50)->get()],
            'GetServices' => ['success' => true, 'data' => ClientService::query()->latest()->limit($params['limit'] ?? 50)->get()],
            'GetDomains' => ['success' => true, 'data' => Domain::query()->latest()->limit($params['limit'] ?? 50)->get()],
            default => ['success' => false, 'message' => 'Unknown API action.'],
        };
    }

    protected function createClient(array $params): array
    {
        $client = Client::create([
            'first_name' => $params['first_name'] ?? 'New',
            'last_name' => $params['last_name'] ?? 'Client',
            'email' => $params['email'] ?? throw new \InvalidArgumentException('email is required'),
            'phone' => $params['phone'] ?? null,
            'company_name' => $params['company_name'] ?? null,
            'status' => $params['status'] ?? 'active',
            'password' => $params['password'] ?? \Illuminate\Support\Str::random(20),
            'source' => $params['source'] ?? 'api',
            'external_id' => $params['external_id'] ?? null,
        ]);

        Action::do('client.created', $client);

        return ['success' => true, 'data' => $client];
    }

    protected function updateClient(array $params): array
    {
        $client = Client::findOrFail($params['id'] ?? null);
        $client->update(collect($params)->except('id')->all());
        Action::do('client.updated', $client);

        return ['success' => true, 'data' => $client];
    }
}
