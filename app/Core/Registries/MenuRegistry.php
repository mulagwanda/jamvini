<?php

namespace App\Core\Registries;

class MenuRegistry
{
    protected static array $adminMenu = [];
    protected static array $clientMenu = [];
    protected static array $adminRoutes = [];
    protected static array $clientRoutes = [];

    /**
     * Register an admin menu item.
     */
    public static function registerAdmin(string $slug, array $menu): void
    {
        $menu['slug'] = $slug;
        $menu['section'] = $menu['section'] ?? 'main'; // main, operations, catalog, system
        self::$adminMenu[$slug] = $menu;
    }

    public static function getAdminMenuBySection(): array
    {
        $sections = ['main' => [], 'operations' => [], 'catalog' => [], 'system' => []];
        
        foreach (collect(self::$adminMenu)->sortBy('position') as $slug => $menu) {
            $section = $menu['section'] ?? 'main';
            $sections[$section][$slug] = $menu;
        }
        
        return $sections;
    }

    /**
     * Register a client menu item.
     */
    public static function registerClient(string $slug, array $menu): void
    {
        $menu['slug'] = $slug;
        self::$clientMenu[$slug] = $menu;
    }

    /**
     * Get all admin menu items sorted by position.
     */
    public static function getAdminMenu(): array
    {
        $items = collect(self::$adminMenu)->sortBy('position')->toArray();
        return $items;
    }

    /**
     * Get all client menu items sorted by position.
     */
    public static function getClientMenu(): array
    {
        $items = collect(self::$clientMenu)->sortBy('position')->toArray();
        return $items;
    }

    /**
     * Remove a menu item.
     */
    public static function remove(string $slug): void
    {
        unset(self::$adminMenu[$slug], self::$clientMenu[$slug]);
    }

    /**
     * Clear all menus (for plugin deactivation).
     */
    public static function clear(): void
    {
        self::$adminMenu = [];
        self::$clientMenu = [];
    }

    /**
     * Register admin routes for a plugin.
     */
    public static function registerAdminRoutes(string $plugin, callable $callback): void
    {
        self::$adminRoutes[$plugin] = $callback;
    }

    /**
     * Register client routes for a plugin.
     */
    public static function registerClientRoutes(string $plugin, callable $callback): void
    {
        self::$clientRoutes[$plugin] = $callback;
    }

    /**
     * Get all admin route callbacks.
     */
    public static function getAdminRoutes(): array
    {
        return self::$adminRoutes;
    }

    /**
     * Get all client route callbacks.
     */
    public static function getClientRoutes(): array
    {
        return self::$clientRoutes;
    }
}