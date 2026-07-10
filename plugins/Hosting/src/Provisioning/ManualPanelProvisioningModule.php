<?php

namespace Plugins\Hosting\src\Provisioning;

use App\Core\Contracts\ProvisioningModuleInterface;
use App\Core\Provisioning\ProvisioningResult;
use Plugins\Orders\src\Models\Order;
use Plugins\Orders\src\Models\OrderItem;
use Plugins\Services\src\Models\ClientService;

class ManualPanelProvisioningModule implements ProvisioningModuleInterface
{
    public function __construct(protected string $slug, protected string $name, protected array $serverTypes)
    {
    }

    public function slug(): string
    {
        return $this->slug;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function supports(string $serverType): bool
    {
        return in_array($serverType, $this->serverTypes, true);
    }

    public function provision(Order $order, OrderItem $item, ClientService $clientService, bool $manualIsSuccess = true): ProvisioningResult
    {
        $clientService->setProperty('panel', $this->name, 'Control Panel', false, true);

        return ProvisioningResult::manual($this->name . ' provisioning is queued for manual handling until its API connector is enabled.', $manualIsSuccess);
    }

    public function suspend(ClientService $clientService, string $reason = ''): ProvisioningResult
    {
        return ProvisioningResult::manual($this->name . ' suspension is queued for manual handling.', false);
    }

    public function unsuspend(ClientService $clientService): ProvisioningResult
    {
        return ProvisioningResult::manual($this->name . ' unsuspension is queued for manual handling.', false);
    }

    public function terminate(ClientService $clientService): ProvisioningResult
    {
        return ProvisioningResult::manual($this->name . ' termination is queued for manual handling.', false);
    }
}
