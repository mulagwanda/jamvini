<?php

namespace App\Core;

use App\Models\Plugin;
use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;

class UpdateManager
{
    protected string $apiBase;
    protected array $coreVersion;
    protected string $licenseKey;

    public function __construct()
    {
        $this->apiBase = config('app.update_api', 'https://api.jamvini.co.tz/api/v1');
        $this->coreVersion = require app_path('Core/version.php');
        $this->licenseKey = Setting::get('license_key', '');
    }

    /**
     * Check for core update.
     */
    public function checkCoreUpdate(): ?array
    {
        $cached = Cache::get('update_core');
        if ($cached !== null) return $cached;

        try {
            $response = Http::timeout(10)->get("{$this->apiBase}/core/version", [
                'current' => $this->coreVersion['version'],
                'build' => $this->coreVersion['build'],
                'php' => PHP_VERSION,
                'license' => $this->licenseKey,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                if (version_compare($data['version'] ?? '0', $this->coreVersion['version'], '>')) {
                    Cache::put('update_core', $data, 3600);
                    return $data;
                }
            }
        } catch (\Exception $e) {
            // Silently fail if offline
        }

        Cache::put('update_core', null, 3600);
        return null;
    }

    /**
     * Check for plugin updates.
     */
    public function checkPluginUpdates(): array
    {
        $updates = [];
        $plugins = Plugin::all();

        try {
            $slugs = $plugins->pluck('slug')->toArray();
            
            $response = Http::timeout(10)->post("{$this->apiBase}/plugins/check-bulk", [
                'plugins' => $plugins->map(fn($p) => [
                    'slug' => $p->slug,
                    'version' => $p->version,
                ])->toArray(),
                'core_version' => $this->coreVersion['version'],
                'license' => $this->licenseKey,
            ]);

            if ($response->successful()) {
                foreach ($response->json('updates', []) as $update) {
                    $updates[$update['slug']] = $update;
                }
            }
        } catch (\Exception $e) {
            // Offline
        }

        return $updates;
    }

    /**
     * Check for theme updates.
     */
    public function checkThemeUpdates(): array
    {
        $updates = [];
        $themesPath = base_path('themes');

        if (!File::exists($themesPath)) return $updates;

        foreach (File::directories($themesPath) as $dir) {
            $themeFile = $dir . '/theme.json';
            if (!File::exists($themeFile)) continue;

            $theme = json_decode(File::get($themeFile), true);
            $slug = $theme['slug'] ?? basename($dir);

            try {
                $response = Http::timeout(10)->get("{$this->apiBase}/themes/check", [
                    'slug' => $slug,
                    'version' => $theme['version'] ?? '1.0.0',
                    'core_version' => $this->coreVersion['version'],
                    'license' => $this->licenseKey,
                ]);

                if ($response->successful() && $response->json('update_available')) {
                    $updates[$slug] = $response->json();
                }
            } catch (\Exception $e) {
                // Skip
            }
        }

        return $updates;
    }

    /**
     * Get complete updates summary.
     */
    public function getUpdatesSummary(): array
    {
        $coreUpdate = $this->checkCoreUpdate();
        $pluginUpdates = $this->checkPluginUpdates();
        $themeUpdates = $this->checkThemeUpdates();

        $total = ($coreUpdate ? 1 : 0) + count($pluginUpdates) + count($themeUpdates);

        return [
            'core' => $coreUpdate,
            'plugins' => $pluginUpdates,
            'themes' => $themeUpdates,
            'total_updates' => $total,
            'last_checked' => now()->toDateTimeString(),
        ];
    }

    /**
     * Download and install a plugin update.
     */
    public function installPluginUpdate(string $slug): array
    {
        try {
            $plugin = Plugin::where('slug', $slug)->first();
            if (!$plugin) return ['success' => false, 'message' => 'Plugin not found'];

            $update = $this->checkPluginUpdateSingle($slug);
            if (!$update) return ['success' => false, 'message' => 'No update available'];

            // Backup current version
            $backupPath = storage_path("app/backups/{$slug}_" . date('YmdHis'));
            $sourcePath = plugin_path($slug);
            if (File::exists($sourcePath)) {
                File::copyDirectory($sourcePath, $backupPath);
            }

            // Download and extract
            $zipPath = storage_path("app/temp/{$slug}.zip");
            $response = Http::timeout(300)->get($update['download_url']);
            File::put($zipPath, $response->body());

            $extractPath = storage_path("app/temp/{$slug}_extracted");
            $this->extractZip($zipPath, $extractPath);

            // Replace files
            if (File::exists($sourcePath)) {
                File::deleteDirectory($sourcePath);
            }
            File::copyDirectory($extractPath, $sourcePath);

            // Run migrations
            app(PluginManager::class)->runMigrations($slug);

            // Update version
            $plugin->update(['version' => $update['version']]);

            // Cleanup
            File::delete($zipPath);
            File::deleteDirectory($extractPath);
            Cache::flush();

            return ['success' => true, 'version' => $update['version']];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    protected function checkPluginUpdateSingle(string $slug): ?array
    {
        $plugin = Plugin::where('slug', $slug)->first();
        if (!$plugin) return null;

        try {
            $response = Http::timeout(10)->get("{$this->apiBase}/plugins/check", [
                'slug' => $slug,
                'version' => $plugin->version,
                'core_version' => $this->coreVersion['version'],
                'license' => $this->licenseKey,
            ]);

            if ($response->successful() && $response->json('update_available')) {
                return $response->json();
            }
        } catch (\Exception $e) {
            // Offline
        }

        return null;
    }

    protected function extractZip(string $zipPath, string $destination): void
    {
        $zip = new \ZipArchive();
        if ($zip->open($zipPath) === true) {
            $zip->extractTo($destination);
            $zip->close();
        }
    }
}
