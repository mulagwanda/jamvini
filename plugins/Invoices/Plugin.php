<?php

namespace Plugins\Invoices;

class Plugin
{
    public function install() {}
    public function activate()
    {
        \App\Core\Registries\MenuRegistry::registerAdmin('invoices', [
            'icon' => 'file-text',
            'label' => 'Invoices',
            'route' => 'admin.invoices.index',
            'position' => 40,
        ]);
        \App\Core\Registries\PermissionRegistry::register('invoices', ['manage_invoices']);
    }
    public function deactivate()
    {
        \App\Core\Registries\MenuRegistry::remove('invoices');
        \App\Core\Registries\PermissionRegistry::removePlugin('invoices');
    }
    public function uninstall() {}
}
