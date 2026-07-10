<?php

namespace Plugins\OfflinePayments;

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
        MenuRegistry::registerAdmin('offline-payments', [
            'icon' => 'banknote',
            'label' => 'Offline Payments',
            'route' => 'admin.offline-payments.settings',
            'position' => 64,
            'section' => 'main',
        ]);

        PermissionRegistry::register('offline-payments', ['manage_offline_payments']);
    }

    public function deactivate(): void
    {
        MenuRegistry::remove('offline-payments');
        PermissionRegistry::removePlugin('offline-payments');
    }
}
