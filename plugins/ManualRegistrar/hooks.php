<?php

use App\Core\Registries\RegistrarRegistry;

RegistrarRegistry::register('manual', [
    'name' => 'Manual Registration',
    'icon' => 'clipboard-list',
    'tlds' => [], // All TLDs
    'class' => \Plugins\ManualRegistrar\src\ManualRegistrar::class,
    'is_configured' => true, // Always available
]);
