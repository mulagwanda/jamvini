<?php

namespace App\Core\Registries;

class SettingRegistry
{
    protected static array $settings = [];
    protected static array $themeSettings = [];

    /**
     * Register settings for a plugin or theme.
     */
    public static function register(string $group, array $settings): void
    {
        foreach ($settings as $key => $config) {
            $config['group'] = $group;
            $config['key'] = $key;
            self::$settings[$group . '.' . $key] = $config;
        }
    }

    /**
     * Get all registered settings.
     */
    public static function all(): array
    {
        return self::$settings;
    }

    /**
     * Get settings by group.
     */
    public static function group(string $group): array
    {
        return array_filter(self::$settings, fn($s) => ($s['group'] ?? '') === $group);
    }

    /**
     * Get a specific setting.
     */
    public static function get(string $key): ?array
    {
        return self::$settings[$key] ?? null;
    }

    /**
     * Remove settings for a group.
     */
    public static function removeGroup(string $group): void
    {
        foreach (self::$settings as $key => $setting) {
            if (($setting['group'] ?? '') === $group) {
                unset(self::$settings[$key]);
            }
        }
    }

    /**
     * Get default values for all registered settings.
     */
    public static function defaults(): array
    {
        $defaults = [];
        foreach (self::$settings as $key => $config) {
            $defaults[$key] = $config['default'] ?? null;
        }
        return $defaults;
    }

        /**
     * Register theme-specific settings from theme.json
     */
    public static function registerThemeSettings(string $theme, array $settings): void
    {
        foreach ($settings as $key => $config) {
            $config['theme'] = $theme;
            $config['key'] = "theme_{$theme}_{$key}";
            self::$themeSettings[$config['key']] = $config;
        }
    }

    /**
     * Get all theme settings for active theme.
     */
    public static function getThemeSettings(string $theme = null): array
    {
        $theme = $theme ?? active_theme('public');
        return array_filter(self::$themeSettings, fn($s) => ($s['theme'] ?? '') === $theme);
    }

    /**
     * Get a theme setting value.
     */
    public static function getThemeValue(string $key, $default = null, string $area = 'public')
    {
        $theme = active_theme($area);
        $fullKey = "theme_{$theme}_{$key}";
        return \App\Models\Setting::get($fullKey, $default);
    }
}
