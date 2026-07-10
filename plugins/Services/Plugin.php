<?php

namespace Plugins\Services;

class Plugin
{
    public function install() {}

    public function activate()
    {
        \App\Core\Registries\MenuRegistry::registerAdmin('services', [
            'icon' => 'package',
            'label' => 'Services',
            'route' => 'admin.services.index',
            'position' => 20,
        ]);

        \App\Core\Registries\PermissionRegistry::register('services', ['manage_services']);
    }

    public function deactivate()
    {
        \App\Core\Registries\MenuRegistry::remove('services');
        \App\Core\Registries\PermissionRegistry::removePlugin('services');
    }

    public function uninstall() {}
}
