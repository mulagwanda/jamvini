<?php

namespace Plugins\TzRegistryRegistrar;

use App\Core\Registries\MenuRegistry;
use App\Core\Registries\PermissionRegistry;

class Plugin
{
    public function install(): void
    {
        //
    }

    public function activate(): void
    {
        MenuRegistry::registerAdmin('tznic-registrar', [
            'icon' => 'globe',
            'label' => 'tzNIC Registrar',
            'route' => 'admin.tznic.settings',
            'position' => 63,
            'section' => 'operations',
        ]);

        PermissionRegistry::register('tznic-registrar', [
            'manage_tznic_registrar',
        ]);
    }

    public function deactivate(): void
    {
        MenuRegistry::remove('tznic-registrar');
        PermissionRegistry::removePlugin('tznic-registrar');
    }

    public function uninstall(): void
    {
        //
    }
}
