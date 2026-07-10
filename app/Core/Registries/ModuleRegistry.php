<?php

namespace App\Core\Registries;

class ModuleRegistry
{
    protected static array $modules = [];

    /**
     * Register a service module type.
     */
    public static function register(string $slug, array $config): void
    {
        self::$modules[$slug] = array_merge([
            'slug' => $slug,
            'name' => $slug,
            'icon' => '📦',
            'menu' => null,
            'config_injector' => null,
            'provisioner' => null,
            'settings' => [],
        ], $config);
    }

    /**
     * Get all registered modules.
     */
    public static function all(): array
    {
        return self::$modules;
    }

    /**
     * Get a specific module.
     */
    public static function get(string $slug): ?array
    {
        return self::$modules[$slug] ?? null;
    }

    /**
     * Get config injector callback for a module.
     */
    public static function getConfigInjector(string $slug): ?callable
    {
        return self::$modules[$slug]['config_injector'] ?? null;
    }

    /**
     * Get provisioner class for a module.
     */
    public static function getProvisioner(string $slug): ?string
    {
        return self::$modules[$slug]['provisioner'] ?? null;
    }

    /**
     * Get menu config for a module.
     */
    public static function getMenu(string $slug): ?array
    {
        return self::$modules[$slug]['menu'] ?? null;
    }

    /**
     * Remove a module.
     */
    public static function remove(string $slug): void
    {
        unset(self::$modules[$slug]);
    }
}