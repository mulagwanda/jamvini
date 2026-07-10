<?php

use App\Core\Registries\ModuleRegistry;
use App\Core\Hooks\Action;
use App\Core\Provisioning\ProvisioningModuleRegistry;
use Plugins\Hosting\src\Provisioning\CpanelProvisioningModule;
use Plugins\Hosting\src\Provisioning\ManualPanelProvisioningModule;

ProvisioningModuleRegistry::register(new CpanelProvisioningModule());
ProvisioningModuleRegistry::register(new ManualPanelProvisioningModule('directadmin', 'DirectAdmin', ['directadmin']));
ProvisioningModuleRegistry::register(new ManualPanelProvisioningModule('plesk', 'Plesk', ['plesk']));
ProvisioningModuleRegistry::register(new ManualPanelProvisioningModule('webuzo', 'Webuzo', ['webuzo']));
ProvisioningModuleRegistry::register(new ManualPanelProvisioningModule('cyberpanel', 'CyberPanel', ['cyberpanel']));
ProvisioningModuleRegistry::register(new ManualPanelProvisioningModule('ispconfig', 'ISPConfig', ['ispconfig']));
ProvisioningModuleRegistry::register(new ManualPanelProvisioningModule('proxmox', 'Proxmox VPS', ['proxmox']));
ProvisioningModuleRegistry::register(new ManualPanelProvisioningModule('vmware', 'VMware vCenter', ['vmware']));
ProvisioningModuleRegistry::register(new ManualPanelProvisioningModule('irc', 'IRC Provisioning', ['irc']));
ProvisioningModuleRegistry::register(new ManualPanelProvisioningModule('manual-panels', 'Manual / Custom Panel', ['custom']));

// Register as a service module
ModuleRegistry::register('hosting', [
    'name' => 'Hosting',
    'icon' => 'server',
    'menu' => [
        'admin' => [
            'icon' => 'server',
            'label' => 'Hosting Automation',
            'route' => 'admin.hosting.settings',
            'position' => 22,
            'section' => 'operations',
        ]
    ],
    'config_injector' => function($service = null) {
        $servers = \Plugins\Services\src\Models\Server::active()->with('packages')->get();
        $selectedServer = null;
        if ($service) {
            $selectedServer = \DB::table('server_service')
                ->where('service_id', $service->id)
                ->where('is_default', true)
                ->first();
        }
        $selectedServerId = $selectedServer?->server_id ?? old('server_id');
        $selectedPackageName = old('package_name', $selectedServer?->package_name);
        
        return view('plugins.Hosting::admin.service-config', compact('servers', 'service', 'selectedServerId', 'selectedPackageName'));
    },
]);

// Keep a lightweight post-completion hook for reporting; actual provisioning is handled by OrderController + ProvisioningManager.
Action::add('order.completed', function($order) {
    foreach ($order->items as $item) {
        if (!in_array($item->type, ['hosting'])) continue;
        if (empty($item->service_id)) continue;
        
        // Find server for this service
        $server = get_server_for_service($item->service_id);
        
        if (!$server) {
            \App\Core\ActivityLogger::log('warning', 'Hosting', null,
                "No server configured for service #{$item->service_id} — manual provisioning required");
            continue;
        }
        
        try {
            \App\Core\ActivityLogger::log('info', 'Hosting', null,
                "Hosting provisioning workflow completed for {$item->description} on {$server->name} ({$server->type})");
            
        } catch (\Exception $e) {
            \App\Core\ActivityLogger::log('error', 'Hosting', null,
                "Failed to provision {$item->description}: " . $e->getMessage());
        }
    }
});
