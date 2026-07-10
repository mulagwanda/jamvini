<?php

namespace App\Providers;

use App\Core\PluginManager;
use App\Core\ViewResolver;
use App\Models\Plugin;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PluginServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(PluginManager::class);
        $this->app->singleton(ViewResolver::class);
    }

    public function boot(): void
    {
        if (!$this->databaseIsReady()) {
            return;
        }

        // Register plugin view namespaces
        $pluginsPath = base_path('plugins');
        if (is_dir($pluginsPath)) {
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
        }

        // Load active admin theme routes and register menu
        $activeTheme = active_theme('admin');
        $themeConfigFile = base_path("themes/{$activeTheme}/theme.json");

        if (file_exists($themeConfigFile)) {
            $themeConfig = json_decode(file_get_contents($themeConfigFile), true);
            
            // Register theme menu if defined in theme.json
            if (!empty($themeConfig['menu']['admin'])) {
                \App\Core\Registries\MenuRegistry::registerAdmin(
                    'theme-' . $activeTheme,
                    $themeConfig['menu']['admin']
                );
            }
        }

        // Load routes for themes assigned to any area.
        foreach (array_unique([active_theme('public'), active_theme('client'), active_theme('admin')]) as $theme) {
            $themeRoutes = base_path("themes/{$theme}/routes.php");
            if (file_exists($themeRoutes)) {
                require_once $themeRoutes;
            }
        }

        // Register theme view namespaces
        $themesPath = base_path('themes');
        if (is_dir($themesPath)) {
            foreach (scandir($themesPath) as $dir) {
                if ($dir === '.' || $dir === '..') continue;
                View::addNamespace("themes.{$dir}", "{$themesPath}/{$dir}");
                View::addNamespace($dir, "{$themesPath}/{$dir}");
            }
        }

        // Load routes and register menus for active plugins
        $this->bootActivePlugins();
    }

    protected function databaseIsReady(): bool
    {
        try {
            DB::connection()->getPdo();

            return Schema::hasTable('plugins') && Schema::hasTable('settings');
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Convert plugin slug to namespace prefix.
     * "client-portal" → "ClientPortal"
     */
    protected function slugToNamespace(string $slug): string
    {
        return str_replace(' ', '', ucwords(str_replace('-', ' ', $slug)));
    }

    /**
     * Boot all active plugins: load routes, register menus, permissions.
     */
    protected function bootActivePlugins(): void
    {
        $manager = app(PluginManager::class);
        $manager->syncSystemPlugins();
        $activePlugins = Plugin::where('is_active', true)->get();

        foreach ($activePlugins as $plugin) {
            $manifest = $manager->getManifest($plugin->slug);
            if (!$manifest) continue;

            // Register menus and permissions
            if (!empty($manifest['menu']['admin'])) {
                \App\Core\Registries\MenuRegistry::registerAdmin($plugin->slug, $manifest['menu']['admin']);
            }
            if (!empty($manifest['menu']['client'])) {
                \App\Core\Registries\MenuRegistry::registerClient($plugin->slug, $manifest['menu']['client']);
            }
            if (!empty($manifest['permissions'])) {
                \App\Core\Registries\PermissionRegistry::register($plugin->slug, $manifest['permissions']);
            }
            if (!empty($manifest['reports'])) {
                \App\Core\Registries\ReportRegistry::registerManifest($plugin->slug, $manifest['reports']);
            }

            $hooksFile = $manager->pluginPath($plugin->slug) . '/hooks.php';
            if (file_exists($hooksFile)) {
                require_once $hooksFile;
            }
        }
        
        // Note: Routes are loaded from routes/web.php using Route::group with proper middleware
    }

    /**
     * Load routes from a plugin's routes.php file.
     * The file uses Route:: methods directly within the existing middleware group.
     */
    protected function loadPluginRoutes(string $slug): void
    {
        $routesFile = base_path("plugins/{$slug}/routes.php");

        if (file_exists($routesFile)) {
            require_once $routesFile;
        }
    }
}
