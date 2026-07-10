<?php

namespace Plugins\SelcomPayment;

class Plugin
{
    public function install() {}
    
    public function activate()
    {
        \App\Core\Registries\MenuRegistry::registerAdmin('selcom-payment', [
            'icon' => 'credit-card',
            'label' => 'Selcom Payments',
            'route' => 'admin.selcom.index',
            'position' => 60,
        ]);
    }
    
    public function deactivate()
    {
        \App\Core\Registries\MenuRegistry::remove('selcom-payment');
    }
    
    public function uninstall() {}
}
