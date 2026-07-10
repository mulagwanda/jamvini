<?php

namespace App\Core\Registries;

class RegistrarRegistry
{
    protected static array $registrars = [];

    /**
     * Register a domain registrar.
     */
    public static function register(string $slug, array $config): void
    {
        self::$registrars[$slug] = array_merge([
            'slug' => $slug,
            'name' => $slug,
            'icon' => 'plug',
            'tlds' => [],
            'class' => null,
            'settings_route' => null,
            'is_configured' => false,
        ], $config);
    }

    /**
     * Get all registered registrars.
     */
    public static function all(): array
    {
        return self::$registrars;
    }

    /**
     * Get active (configured) registrars.
     */
    public static function active(): array
    {
        return array_filter(self::$registrars, fn($r) => $r['is_configured'] ?? false);
    }

    /**
     * Get a specific registrar by slug.
     */
    public static function get(string $slug): ?array
    {
        return self::$registrars[$slug] ?? null;
    }

    /**
     * Find a registrar that supports a given TLD.
     */
    public static function findForTld(string $tld): ?array
    {
        foreach (self::$registrars as $slug => $config) {
            if (in_array($tld, $config['tlds'] ?? [])) {
                return $config;
            }
        }
        return null;
    }

    /**
     * Get all TLDs supported by all registrars.
     */
    public static function getAllTlds(): array
    {
        $tlds = [];
        foreach (self::$registrars as $config) {
            $tlds = array_merge($tlds, $config['tlds'] ?? []);
        }
        return array_unique($tlds);
    }

    /**
     * Remove a registrar.
     */
    public static function remove(string $slug): void
    {
        unset(self::$registrars[$slug]);
    }
}
