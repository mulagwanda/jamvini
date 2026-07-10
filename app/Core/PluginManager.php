<?php

namespace App\Core;

use App\Models\Plugin;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

class PluginManager
{
    protected string $pluginsPath;
    protected array $manifestCache = [];
    protected array $pathCache = [];

    public function __construct()
    {
        $this->pluginsPath = base_path('plugins');
    }

    /**
     * Initialize the plugin system.
     */
    public function initialize(): void
    {
        if (!File::exists($this->pluginsPath)) {
            File::makeDirectory($this->pluginsPath, 0755, true);
        }

        $this->ensureSystemPlugins();
        $this->bootActivePlugins();
    }

    /**
     * Ensure core system plugins exist in database.
     */
    protected function ensureSystemPlugins(): void
    {
        $systemPlugins = $this->scanForSystemPlugins();

        foreach ($systemPlugins as $slug => $manifest) {
            $plugin = Plugin::where('slug', $slug)->first();

            if (!$plugin) {
                $plugin = Plugin::where('name', $manifest['name'])->first();
            }

            if (!$plugin) {
                Plugin::create([
                    'name' => $manifest['name'],
                    'slug' => $slug,
                    'version' => $manifest['version'] ?? '1.0.0',
                    'description' => $manifest['description'] ?? '',
                    'author' => $manifest['author'] ?? 'JamVini',
                    'type' => $manifest['type'] ?? 'module',
                    'is_active' => $manifest['core'] ?? false,
                    'is_system' => $manifest['core'] ?? false,
                    'settings' => $manifest['settings'] ?? [],
                    'hooks' => $manifest['hooks'] ?? [],
                ]);
            } elseif ($plugin->slug !== $slug) {
                $plugin->update(['slug' => $slug]);
            }
        }
    }

    public function syncSystemPlugins(): void
    {
        $this->ensureSystemPlugins();
    }

    /**
     * Scan the plugins directory for available plugins.
     */
    public function scan(): array
    {
        $plugins = [];

        if (!File::exists($this->pluginsPath)) {
            File::makeDirectory($this->pluginsPath, 0755, true);
            return $plugins;
        }

        foreach (File::directories($this->pluginsPath) as $directory) {
            $manifest = $this->getManifest(basename($directory));
            
            if ($manifest) {
                $slug = $this->manifestSlug($manifest, basename($directory));
                $installed = $this->pluginsTableExists()
                    ? Plugin::where('slug', $slug)->first()
                    : null;

                $plugins[$slug] = [
                    'name' => $manifest['name'] ?? $slug,
                    'slug' => $slug,
                    'version' => $manifest['version'] ?? '1.0.0',
                    'description' => $manifest['description'] ?? '',
                    'author' => $manifest['author'] ?? 'Unknown',
                    'type' => $manifest['type'] ?? 'module',
                    'icon' => $manifest['icon'] ?? data_get($manifest, 'menu.admin.icon') ?? data_get($manifest, 'menu.client.icon'),
                    'menu' => $manifest['menu'] ?? [],
                    'premium' => $manifest['premium'] ?? false,
                    'price' => $manifest['price'] ?? null,
                    'core' => $manifest['core'] ?? false,
                    'dependencies' => $manifest['dependencies'] ?? [],
                    'path' => $directory,
                    'installed' => $installed !== null,
                    'installed_version' => $installed?->version,
                    'is_active' => $installed?->is_active ?? false,
                    'is_system' => $installed?->is_system ?? false,
                ];
            }
        }

        return $plugins;
    }

    /**
     * Scan for system plugins (bundled with core).
     */
    protected function scanForSystemPlugins(): array
    {
        $systemPlugins = [];

        if (!File::exists($this->pluginsPath)) return $systemPlugins;

        foreach (File::directories($this->pluginsPath) as $directory) {
            $manifest = $this->getManifest(basename($directory));
            
            if ($manifest && ($manifest['core'] ?? false)) {
                $slug = $this->manifestSlug($manifest, basename($directory));
                $systemPlugins[$slug] = $manifest;
            }
        }

        return $systemPlugins;
    }

    protected function pluginsTableExists(): bool
    {
        try {
            return Schema::hasTable('plugins');
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Get manifest for a plugin.
     */
    public function getManifest(string $slug): ?array
    {
        $slug = self::normalizeSlug($slug);

        if (isset($this->manifestCache[$slug])) {
            return $this->manifestCache[$slug];
        }

        $pluginPath = $this->pluginPath($slug);
        $manifestFile = $pluginPath . '/plugin.json';

        if (!File::exists($manifestFile)) {
            return null;
        }

        $manifest = json_decode(File::get($manifestFile), true);
        $this->manifestCache[$slug] = $manifest;
        $this->manifestCache[$this->manifestSlug($manifest, basename($pluginPath))] = $manifest;

        return $manifest;
    }

    /**
     * Install a plugin.
     */
    public function install(string $slug): bool
    {
        $slug = self::normalizeSlug($slug);
        $manifest = $this->getManifest($slug);
        
        if (!$manifest) {
            return false;
        }

        $slug = $this->manifestSlug($manifest, basename($this->pluginPath($slug)));

        // Check dependencies
        if (!$this->checkDependencies($manifest)) {
            return false;
        }

        // Check if already installed
        if (Plugin::where('slug', $slug)->exists()) {
            return false;
        }

        Plugin::create([
            'name' => $manifest['name'],
            'slug' => $slug,
            'version' => $manifest['version'] ?? '1.0.0',
            'description' => $manifest['description'] ?? '',
            'author' => $manifest['author'] ?? '',
            'type' => $manifest['type'] ?? 'module',
            'is_active' => false,
            'is_system' => $manifest['core'] ?? false,
            'settings' => $manifest['settings'] ?? [],
            'hooks' => $manifest['hooks'] ?? [],
        ]);

        $this->callPluginMethod($slug, 'install');

        return true;
    }

    /**
     * Activate a plugin.
     */
    public function activate(string $slug): bool
    {
        $slug = $this->canonicalSlug($slug);
        $plugin = Plugin::where('slug', $slug)->first();
        
        if (!$plugin) {
            return false;
        }

        $manifest = $this->getManifest($slug);

        // Check dependencies
        if ($manifest && !$this->checkDependencies($manifest)) {
            return false;
        }

        // Run migrations
        $this->runMigrations($slug);

        // Register routes & hooks
        $this->registerPluginRoutes($slug);
        $this->registerPluginHooks($slug);

        $plugin->update(['is_active' => true]);
        $this->callPluginMethod($slug, 'activate');

        return true;
    }

    /**
     * Deactivate a plugin.
     */
    public function deactivate(string $slug): bool
    {
        $slug = $this->canonicalSlug($slug);
        $plugin = Plugin::where('slug', $slug)->first();
        
        if (!$plugin || $plugin->is_system) {
            return false;
        }

        $this->callPluginMethod($slug, 'deactivate');
        $plugin->update(['is_active' => false]);

        // Clear registries for this plugin
        \App\Core\Registries\MenuRegistry::remove($slug);
        \App\Core\Registries\DashboardRegistry::removePluginWidgets($slug);
        \App\Core\Registries\PermissionRegistry::removePlugin($slug);
        \App\Core\Registries\ReportRegistry::removePluginReports($slug);

        return true;
    }

    /**
     * Uninstall a plugin.
     */
    public function uninstall(string $slug): bool
    {
        $slug = $this->canonicalSlug($slug);
        $plugin = Plugin::where('slug', $slug)->first();
        
        if (!$plugin || $plugin->is_system) {
            return false;
        }

        // Deactivate first
        if ($plugin->is_active) {
            $this->deactivate($slug);
        }

        $this->callPluginMethod($slug, 'uninstall');
        $plugin->delete();

        return true;
    }

    /**
     * Boot all active plugins.
     */
    public function bootActivePlugins(): void
    {
        $plugins = Plugin::active()->get();

        foreach ($plugins as $plugin) {
            $this->registerPluginRoutes($plugin->slug);
            $this->registerPluginHooks($plugin->slug);
        }

        \App\Core\Hooks\Action::do('plugins.booted');
    }

    public function activeMiddlewareFor(string $area): array
    {
        if (!$this->pluginsTableExists()) {
            return [];
        }

        try {
            $plugins = Plugin::active()->get();
        } catch (\Throwable $e) {
            return [];
        }

        $middleware = [];

        foreach ($plugins as $plugin) {
            $manifest = $this->getManifest($plugin->slug) ?: [];
            $declared = $manifest['middleware'] ?? [];

            foreach (['global', $area] as $group) {
                foreach ($this->normalizeMiddlewareDeclarations($declared[$group] ?? []) as $class) {
                    if (class_exists($class)) {
                        $middleware[] = $class;
                    }
                }
            }
        }

        return array_values(array_unique($middleware));
    }

    protected function normalizeMiddlewareDeclarations(array|string $declarations): array
    {
        if (is_string($declarations)) {
            return [$declarations];
        }

        $classes = [];
        foreach ($declarations as $declaration) {
            if (is_string($declaration)) {
                $classes[] = $declaration;
            } elseif (is_array($declaration) && !empty($declaration['class'])) {
                $classes[] = $declaration['class'];
            }
        }

        return $classes;
    }

    /**
     * Register routes for a plugin.
     */
    protected function registerPluginRoutes(string $slug): void
    {
        $routesFile = $this->pluginPath($slug) . '/routes.php';

        if (File::exists($routesFile)) {
            require_once $routesFile;
        }
    }

    /**
     * Register hooks for a plugin.
     */
    protected function registerPluginHooks(string $slug): void
    {
        $hooksFile = $this->pluginPath($slug) . '/hooks.php';

        if (File::exists($hooksFile)) {
            require_once $hooksFile;
        }
    }

    /**
     * Run migrations for a plugin.
     */
    public function runMigrations(string $slug): void
    {
        $pluginPath = $this->pluginPath($slug);
        $migrationsPath = $pluginPath . '/migrations';

        if (File::exists($migrationsPath)) {
            \Artisan::call('migrate', [
                '--path' => 'plugins/' . basename($pluginPath) . '/migrations',
                '--force' => true,
            ]);
        }
    }

    /**
     * Call a method on a plugin's main class.
     */
    protected function callPluginMethod(string $slug, string $method): void
    {
        $pluginPath = $this->pluginPath($slug);
        $pluginFile = $pluginPath . '/Plugin.php';

        if (File::exists($pluginFile)) {
            require_once $pluginFile;
            
            $className = $this->getPluginClassName(basename($pluginPath));
            if (class_exists($className) && method_exists($className, $method)) {
                $instance = app($className);
                $instance->{$method}();
            }
        }
    }

    /**
     * Get expected class name for a plugin.
     */
    protected function getPluginClassName(string $slug): string
    {
        return "Plugins\\{$slug}\\Plugin";
    }

    /**
     * Check if a plugin's dependencies are installed and active.
     */
    public function checkDependencies(array $manifest): bool
    {
        $dependencies = $manifest['dependencies'] ?? [];
        
        foreach ($dependencies as $dependency) {
            $dependency = self::normalizeSlug($dependency);
            $dep = Plugin::where('slug', $dependency)->first();
            
            if (!$dep || !$dep->is_active) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get a plugin's path.
     */
    public function pluginPath(string $slug): string
    {
        $slug = self::normalizeSlug($slug);

        if (isset($this->pathCache[$slug])) {
            return $this->pathCache[$slug];
        }

        $directPath = $this->pluginsPath . '/' . $slug;
        if (File::exists($directPath)) {
            return $this->pathCache[$slug] = $directPath;
        }

        if (File::exists($this->pluginsPath)) {
            foreach (File::directories($this->pluginsPath) as $directory) {
                $manifestFile = $directory . '/plugin.json';
                $directorySlug = self::normalizeSlug(basename($directory));

                if ($directorySlug === $slug) {
                    return $this->pathCache[$slug] = $directory;
                }

                if (File::exists($manifestFile)) {
                    $manifest = json_decode(File::get($manifestFile), true) ?: [];
                    $manifestSlug = $this->manifestSlug($manifest, basename($directory));

                    if ($manifestSlug === $slug) {
                        return $this->pathCache[$slug] = $directory;
                    }
                }
            }
        }

        return $this->pathCache[$slug] = $directPath;
    }

    public static function normalizeSlug(string $slug): string
    {
        $slug = preg_replace('/(?<!^)[A-Z]/', '-$0', $slug);
        $slug = strtolower((string) $slug);
        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);

        return trim((string) $slug, '-');
    }

    protected function manifestSlug(array $manifest, string $fallback): string
    {
        return self::normalizeSlug($manifest['slug'] ?? $fallback);
    }

    protected function canonicalSlug(string $slug): string
    {
        $normalized = self::normalizeSlug($slug);
        $manifest = $this->getManifest($normalized);

        if (!$manifest) {
            return $normalized;
        }

        return $this->manifestSlug($manifest, basename($this->pluginPath($normalized)));
    }

    /**
     * Check compatibility with core version.
     */
    public function isCompatible(array $manifest): bool
    {
        $coreVersion = require app_path('Core/version.php');
        $currentVersion = $coreVersion['version'];
        
        $minCore = $manifest['min_core_version'] ?? '1.0.0';
        $maxCore = $manifest['max_core_version'] ?? null;

        if (version_compare($currentVersion, $minCore, '<')) {
            return false;
        }

        if ($maxCore && version_compare($currentVersion, $maxCore, '>')) {
            return false;
        }

        return true;
    }
}
