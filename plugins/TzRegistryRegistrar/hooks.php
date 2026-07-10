<?php

use App\Core\CronManager;
use App\Core\Registries\RegistrarRegistry;
use App\Models\Setting;
use Plugins\TzRegistryRegistrar\src\Services\TzRegistryRegistrar;

RegistrarRegistry::register('tznic', [
    'name' => 'tzNIC FRED',
    'icon' => 'globe',
    'tlds' => ['.tz', '.co.tz', '.or.tz', '.go.tz', '.ac.tz', '.ne.tz', '.sc.tz', '.me.tz', '.hotel.tz', '.mobi.tz', '.info.tz', '.tv.tz'],
    'class' => TzRegistryRegistrar::class,
    'settings_route' => 'admin.tznic.settings',
    'is_configured' => Setting::get('tznic_enabled', '0') === '1'
        && filled(Setting::get('tznic_username'))
        && filled(Setting::get('tznic_password'))
        && filled(Setting::get('tznic_certificate_path')),
]);

CronManager::register('tznic.sync_domains', 'daily', function () {
    if (Setting::get('tznic_domain_sync_enabled', '1') !== '1') {
        return 'tzNIC domain sync disabled';
    }

    $result = app(TzRegistryRegistrar::class)->syncAllDomains(100);

    return "{$result['synced']} domain(s) synced, {$result['failed']} failed";
}, ['description' => 'Sync .tz expiry dates, nameservers, statuses, and registrar lock']);

CronManager::register('tznic.sync_pricing', 'weekly', function () {
    if (Setting::get('tznic_pricing_sync_enabled', '0') !== '1') {
        return 'tzNIC pricing sync disabled';
    }

    $result = app(TzRegistryRegistrar::class)->syncPricing();

    return $result['message'];
}, ['description' => 'Sync .tz TLD prices from configured pricing data']);
