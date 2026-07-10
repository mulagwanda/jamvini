<?php

namespace App\Core\Provisioning;

use App\Core\ActivityLogger;
use Plugins\Orders\src\Models\Order;
use Plugins\Orders\src\Models\OrderItem;
use Plugins\Services\src\Models\ClientService;

class ProvisioningManager
{
    public function provision(Order $order, OrderItem $item, ClientService $clientService, bool $manualIsSuccess = true): ProvisioningResult
    {
        $item->loadMissing('service.servers');
        $server = $item->service?->servers?->first(fn ($srv) => (bool) $srv->pivot?->is_default)
            ?: $item->service?->servers?->first();

        if (!$server) {
            ActivityLogger::log('provisioning.server.missing', 'Order', $order->id, 'No server assigned for ' . $item->description, [
                'order_item_id' => $item->id,
                'service_id' => $item->service_id,
            ]);

            return ProvisioningResult::manual('Manual provisioning: no server assigned.', $manualIsSuccess);
        }

        $module = ProvisioningModuleRegistry::forServerType($server->type);

        if (!$module) {
            ActivityLogger::log('provisioning.module.missing', 'Order', $order->id, ucfirst($server->type) . ' provisioning queued for manual handling.', [
                'order_item_id' => $item->id,
                'service_id' => $item->service_id,
                'server_id' => $server->id,
                'server_type' => $server->type,
            ]);

            return ProvisioningResult::manual(ucfirst($server->type) . ' provisioning is queued for manual handling.', $manualIsSuccess, [
                'server_id' => $server->id,
                'server_type' => $server->type,
            ]);
        }

        return $module->provision($order, $item, $clientService, $manualIsSuccess);
    }
}
