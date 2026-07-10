<?php

use App\Core\Hooks\Action;
use App\Core\Hooks\Filter;

// Fire hooks when client events happen
// These will be called from the controller
// Example: Action::do('client.created', $client);

// Dashboard widget for client stats
\App\Core\Registries\DashboardRegistry::registerWidget('clients-stats', function () {
    $total = \Plugins\Clients\src\Models\Client::count();
    $active = \Plugins\Clients\src\Models\Client::where('status', 'active')->count();
    
    return view('plugins.Clients::dashboard-widget', [
        'total' => $total,
        'active' => $active,
    ]);
}, [
    'title' => 'Client Overview',
    'position' => 1,
    'size' => 'small',
    'plugin' => 'clients',
]);
