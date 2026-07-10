<?php

namespace Plugins\SmsNotifications;

class Plugin
{
    public function install() {}
    
    public function activate()
    {
        \App\Core\Registries\MenuRegistry::registerAdmin('sms-notifications', [
            'icon' => 'message-circle',
            'label' => 'SMS Notifications',
            'route' => 'admin.sms.index',
            'position' => 61,
        ]);
    }
    
    public function deactivate()
    {
        \App\Core\Registries\MenuRegistry::remove('sms-notifications');
    }
    
    public function uninstall() {}
}
