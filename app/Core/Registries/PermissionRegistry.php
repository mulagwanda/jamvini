<?php

namespace App\Core\Registries;

class PermissionRegistry
{
    protected static array $permissions = [];
    protected static array $modules = [];

    /**
     * Register permissions for a plugin.
     */
    public static function register(string $plugin, array $permissions): void
    {
        self::$modules[$plugin] = self::labelFromSlug($plugin);

        foreach ($permissions as $permission) {
            self::$permissions[$permission] = [
                'slug' => $permission,
                'plugin' => $plugin,
            ];
        }
    }

    /**
     * Get all registered permissions.
     */
    public static function all(): array
    {
        return self::$permissions;
    }

    /**
     * Get permission modules exposed by plugins.
     */
    public static function modules(): array
    {
        return self::$modules;
    }

    /**
     * Check if a permission is registered.
     */
    public static function exists(string $permission): bool
    {
        return isset(self::$permissions[$permission]);
    }

    /**
     * Remove permissions for a plugin.
     */
    public static function removePlugin(string $plugin): void
    {
        foreach (self::$permissions as $slug => $data) {
            if ($data['plugin'] === $plugin) {
                unset(self::$permissions[$slug]);
            }
        }

        unset(self::$modules[$plugin]);
    }

    protected static function labelFromSlug(string $slug): string
    {
        return ucwords(str_replace(['-', '_'], ' ', $slug));
    }
}
