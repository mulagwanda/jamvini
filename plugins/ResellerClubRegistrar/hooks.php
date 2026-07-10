<?php

use App\Core\Registries\RegistrarRegistry;
use App\Models\Setting;

// Register this registrar
RegistrarRegistry::register('resellerclub', [
    'name' => 'ResellerClub',
    'icon' => 'globe',
    'tlds' => [
        '.com', '.net', '.org', '.io', '.co', '.africa',
        '.biz', '.info', '.xyz', '.online', '.store',
        '.site', '.tech', '.dev', '.app', '.me', '.club',
        '.design', '.agency', '.marketing', '.digital',
    ],
    'class' => \Plugins\ResellerClubRegistrar\src\ResellerClubRegistrar::class,
    'settings_route' => 'admin.resellerclub.settings',
    'is_configured' => !empty(Setting::get('resellerclub_reseller_id')) && !empty(Setting::get('resellerclub_api_key')),
]);
