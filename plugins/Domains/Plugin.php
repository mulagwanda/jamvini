<?php

namespace Plugins\Domains;

class Plugin
{
    public function install() {}
    public function activate()
    {
        \App\Core\Registries\MenuRegistry::registerAdmin('domains', [
            'icon' => 'globe',
            'label' => 'Domains',
            'route' => 'admin.domains.index',
            'position' => 30,
        ]);
        \App\Core\Registries\PermissionRegistry::register('domains', ['manage_domains']);
    }
    public function deactivate()
    {
        \App\Core\Registries\MenuRegistry::remove('domains');
        \App\Core\Registries\PermissionRegistry::removePlugin('domains');
    }
    public function uninstall() {}
}
