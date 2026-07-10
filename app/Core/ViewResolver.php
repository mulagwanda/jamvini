<?php

namespace App\Core;

use App\Models\Setting;
use App\Core\PluginManager;
use Illuminate\Support\Facades\View;

class ViewResolver
{
    protected string $activeTheme;

    public function __construct()
    {
        $this->activeTheme = active_theme('public');
    }

    /**
     * Resolve a view for a plugin, checking theme overrides first.
     */
    public function resolve(string $plugin, string $view): string
    {
        $themeView = "themes.{$this->activeTheme}.{$plugin}.{$view}";

        // Check if theme has an override
        if (View::exists($themeView)) {
            return $themeView;
        }

        // Fallback to plugin's default view
        return "plugins.{$plugin}.views.{$view}";
    }

    /**
     * Resolve a layout view.
     */
    public function layout(string $layout, string $area = 'public'): string
    {
        $theme = active_theme($area);
        $themeLayout = "themes.{$theme}.layouts.{$layout}";

        if (View::exists($themeLayout)) {
            return $themeLayout;
        }

        // Default fallback layouts
        return "themes.default.layouts.{$layout}";
    }

    /**
     * Get the active theme slug.
     */
    public function getActiveTheme(string $area = 'public'): string
    {
        return active_theme($area);
    }

    /**
     * Get the full path to a theme asset.
     */
    public function asset(string $path, string $area = 'public'): string
    {
        return asset("themes/" . active_theme($area) . "/assets/{$path}");
    }

    /**
     * Add view namespaces for all active plugins.
     */
    public function registerPluginViews(): void
    {
        $pluginsPath = base_path('plugins');
        
        if (!is_dir($pluginsPath)) return;

        foreach (scandir($pluginsPath) as $dir) {
            if ($dir === '.' || $dir === '..') continue;
            
            $viewsPath = "{$pluginsPath}/{$dir}/views";
            if (is_dir($viewsPath)) {
                View::addNamespace("plugins.{$dir}", $viewsPath);

                $manifest = app(PluginManager::class)->getManifest($dir);
                if (!empty($manifest['slug'])) {
                    View::addNamespace('plugins.' . PluginManager::normalizeSlug($manifest['slug']), $viewsPath);
                }
            }
        }

        // Register theme views
        $themesPath = base_path('themes');
        if (is_dir($themesPath)) {
            foreach (scandir($themesPath) as $dir) {
                if ($dir === '.' || $dir === '..') continue;
                View::addNamespace("themes.{$dir}", "{$themesPath}/{$dir}");
                View::addNamespace($dir, "{$themesPath}/{$dir}");
            }
        }
    }
}
