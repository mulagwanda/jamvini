<?php

namespace Plugins\Services\src\Connectors;

use Plugins\Services\src\Models\Server;

class ServerConnectorFactory
{
    public function for(Server $server): ServerConnector
    {
        return match ($server->type) {
            'cpanel' => new CpanelConnector($server),
            default => new ManualConnector($server),
        };
    }
}
