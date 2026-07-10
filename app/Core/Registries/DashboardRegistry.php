<?php

namespace App\Core\Registries;

use App\Models\Setting;

class DashboardRegistry
{
    protected static array $widgets = [];

    /**
     * Register a dashboard widget.
     */
    public static function registerWidget(string $slug, callable $callback, array $options = []): void
    {
        self::$widgets[$slug] = array_merge([
            'slug' => $slug,
            'callback' => $callback,
            'position' => 10,
            'size' => 'small', // small, medium, large, full
            'title' => '',
            'plugin' => '',
            'color' => 'blue',
            'column' => 'main',
            'description' => '',
        ], $options);
    }

    /**
     * Get all widgets sorted by position.
     */
    public static function getWidgets(): array
    {
        $settings = self::settings();

        return collect(self::$widgets)
            ->map(function (array $widget, string $slug) use ($settings) {
                $override = $settings[$slug] ?? [];

                return array_merge($widget, [
                    'enabled' => $override['enabled'] ?? true,
                    'position' => $override['position'] ?? $widget['position'],
                    'size' => $override['size'] ?? $widget['size'],
                    'color' => $override['color'] ?? $widget['color'],
                    'column' => $override['column'] ?? $widget['column'],
                ]);
            })
            ->filter(fn ($widget) => $widget['enabled'])
            ->sortBy(['column', 'position'])
            ->toArray();
    }

    public static function allRegistered(): array
    {
        $settings = self::settings();

        return collect(self::$widgets)
            ->map(fn (array $widget, string $slug) => array_merge($widget, $settings[$slug] ?? [], ['slug' => $slug]))
            ->sortBy('position')
            ->toArray();
    }

    public static function saveSettings(array $settings): void
    {
        Setting::set('dashboard_widget_settings', json_encode($settings), 'dashboard', 'Dashboard Widget Settings');
    }

    public static function settings(): array
    {
        $value = Setting::get('dashboard_widget_settings', '{}');
        $decoded = json_decode((string) $value, true);

        return is_array($decoded) ? $decoded : [];
    }

    /**
     * Remove a widget.
     */
    public static function removeWidget(string $slug): void
    {
        unset(self::$widgets[$slug]);
    }

    /**
     * Remove all widgets for a plugin.
     */
    public static function removePluginWidgets(string $plugin): void
    {
        foreach (self::$widgets as $slug => $widget) {
            if (($widget['plugin'] ?? '') === $plugin) {
                unset(self::$widgets[$slug]);
            }
        }
    }

    /**
     * Render all widgets.
     */
    public static function render(): string
    {
        $output = '';
        $widgets = self::getWidgets();

        foreach ($widgets as $slug => $widget) {
            try {
                $callback = $widget['callback'];
                $output .= call_user_func($callback);
            } catch (\Exception $e) {
                $output .= "<!-- Widget {$slug} error: " . $e->getMessage() . " -->";
            }
        }

        return $output;
    }
}
