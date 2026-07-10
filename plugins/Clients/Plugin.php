<?php

namespace Plugins\Clients;

class Plugin
{
    public function install()
    {
        // Run migrations handled by PluginManager
    }

    public function activate()
    {
        // Register menu, permissions
        \App\Core\Registries\MenuRegistry::registerAdmin('clients', [
            'icon' => 'users',
            'label' => 'Clients',
            'route' => 'admin.clients.index',
            'position' => 10,
            'children' => [
                ['label' => 'All Clients', 'route' => 'admin.clients.index'],
                ['label' => 'Add New', 'route' => 'admin.clients.create'],
            ],
        ]);

        \App\Core\Registries\PermissionRegistry::register('clients', [
            'manage_clients',
            'view_clients',
            'create_clients',
            'edit_clients',
            'delete_clients',
        ]);
    }

    public function deactivate()
    {
        \App\Core\Registries\MenuRegistry::remove('clients');
        \App\Core\Registries\PermissionRegistry::removePlugin('clients');
    }

    public function uninstall()
    {
        // Optionally drop tables
    }
}
