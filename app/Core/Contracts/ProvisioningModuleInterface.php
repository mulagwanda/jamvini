<?php

namespace App\Core\Contracts;

use App\Core\Provisioning\ProvisioningResult;
use Plugins\Orders\src\Models\Order;
use Plugins\Orders\src\Models\OrderItem;
use Plugins\Services\src\Models\ClientService;

interface ProvisioningModuleInterface
{
    public function slug(): string;

    public function name(): string;

    public function supports(string $serverType): bool;

    public function provision(Order $order, OrderItem $item, ClientService $clientService, bool $manualIsSuccess = true): ProvisioningResult;

    public function suspend(ClientService $clientService, string $reason = ''): ProvisioningResult;

    public function unsuspend(ClientService $clientService): ProvisioningResult;

    public function terminate(ClientService $clientService): ProvisioningResult;
}
