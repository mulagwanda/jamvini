<?php

namespace App\Core\Provisioning;

use App\Core\Contracts\ProvisioningModuleInterface;

class ProvisioningModuleRegistry
{
    protected static array $modules = [];

    public static function register(ProvisioningModuleInterface $module): void
    {
        self::$modules[$module->slug()] = $module;
    }

    public static function all(): array
    {
        return self::$modules;
    }

    public static function get(string $slug): ?ProvisioningModuleInterface
    {
        return self::$modules[$slug] ?? null;
    }

    public static function forServerType(?string $serverType): ?ProvisioningModuleInterface
    {
        foreach (self::$modules as $module) {
            if ($module->supports((string) $serverType)) {
                return $module;
            }
        }

        return null;
    }
}
