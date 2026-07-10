<?php

namespace App\Core;

use App\Models\Setting;
use App\Models\Plugin;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use ZipArchive;

class ThemeManager
{
    public function scan(): array
    {
        $themes = [];
        $themesPath = base_path('themes');
        $activeThemes = $this->activeThemes();

        if (!File::isDirectory($themesPath)) {
            return $themes;
        }

        foreach (File::directories($themesPath) as $directory) {
            $manifest = $this->manifestFromDirectory($directory);

            if (!$manifest) {
                continue;
            }

            $folder = basename($directory);
            $slug = $this->manifestSlug($manifest, $folder);
            $requirements = $this->pluginRequirements($manifest);
            $requirementsMet = collect($requirements)->every(fn ($plugin) => $plugin['active']);

            $themes[$slug] = [
                'name' => $manifest['name'] ?? Str::headline($slug),
                'slug' => $slug,
                'folder' => $folder,
                'version' => $manifest['version'] ?? '1.0.0',
                'author' => $manifest['author'] ?? 'Unknown',
                'description' => $manifest['description'] ?? '',
                'screenshot' => $this->screenshotUrl($folder, $manifest['screenshot'] ?? null),
                'supports' => $this->normalizeSupports($manifest['supports'] ?? []),
                'requires' => $manifest['requires'] ?? [],
                'required_plugins' => $requirements,
                'requirements_met' => $requirementsMet,
                'path' => $directory,
                'active_areas' => $this->activeAreas($slug, $folder, $activeThemes),
                'is_active' => !empty($this->activeAreas($slug, $folder, $activeThemes)),
                'is_system' => $slug === 'default' || $folder === 'default',
                'supports_public' => $this->canActivate($directory, $manifest, 'public'),
                'supports_client' => $this->canActivate($directory, $manifest, 'client'),
                'supports_admin' => $this->canActivate($directory, $manifest, 'admin'),
                'can_activate' => $this->canActivate($directory, $manifest, 'public') && $requirementsMet,
                'can_activate_public' => $this->canActivate($directory, $manifest, 'public') && $requirementsMet,
                'can_activate_client' => $this->canActivate($directory, $manifest, 'client') && $requirementsMet,
                'can_activate_admin' => $this->canActivate($directory, $manifest, 'admin') && $requirementsMet,
            ];
        }

        return collect($themes)->sortBy([
            ['is_active', 'desc'],
            ['name', 'asc'],
        ])->toArray();
    }

    public function activeTheme(string $area = 'public'): string
    {
        return active_theme($area);
    }

    public function activeThemes(): array
    {
        return [
            'public' => $this->activeTheme('public'),
            'client' => $this->activeTheme('client'),
            'admin' => $this->activeTheme('admin'),
        ];
    }

    public function activate(string $slug, string $area = 'public'): bool
    {
        $area = $this->normalizeArea($area);
        $theme = $this->find($slug);

        if (!$theme || !$theme["can_activate_{$area}"] || !$theme['requirements_met']) {
            return false;
        }

        $this->publishAssets($theme['folder']);
        Setting::set("active_{$area}_theme", $theme['folder'], 'site', 'Active ' . Str::headline($area) . ' Theme');

        if ($area === 'public') {
            Setting::set('active_theme', $theme['folder'], 'site', 'Active Theme');
        }

        return true;
    }

    public function upload(UploadedFile $file): array
    {
        $zip = new ZipArchive();
        $tempRoot = storage_path('app/temp/theme-' . Str::uuid());
        $zipPath = $file->storeAs('temp', 'theme-' . Str::uuid() . '.zip');
        $absoluteZipPath = storage_path('app/' . $zipPath);

        File::ensureDirectoryExists($tempRoot);

        if ($zip->open($absoluteZipPath) !== true) {
            $this->cleanup($tempRoot, $absoluteZipPath);
            return ['success' => false, 'message' => 'Could not open theme ZIP.'];
        }

        $zip->extractTo($tempRoot);
        $zip->close();

        $manifestFile = $this->findManifest($tempRoot);

        if (!$manifestFile) {
            $this->cleanup($tempRoot, $absoluteZipPath);
            return ['success' => false, 'message' => 'Invalid theme ZIP. No theme.json file was found.'];
        }

        $manifest = json_decode(File::get($manifestFile), true) ?: [];
        $sourceDirectory = dirname($manifestFile);
        $slug = $this->manifestSlug($manifest, basename($sourceDirectory));
        $folder = $this->safeFolderName($slug);
        $targetDirectory = base_path('themes/' . $folder);

        if (!$this->canActivate($sourceDirectory, $manifest, 'public')) {
            $this->cleanup($tempRoot, $absoluteZipPath);
            return ['success' => false, 'message' => 'Theme is missing required files for JamVini.'];
        }

        if (File::exists($targetDirectory)) {
            $this->cleanup($tempRoot, $absoluteZipPath);
            return ['success' => false, 'message' => "Theme '{$slug}' already exists."];
        }

        File::ensureDirectoryExists(base_path('themes'));
        File::moveDirectory($sourceDirectory, $targetDirectory);
        $this->publishAssets($folder);
        $this->cleanup($tempRoot, $absoluteZipPath);

        return ['success' => true, 'message' => "Theme '" . ($manifest['name'] ?? $slug) . "' uploaded.", 'slug' => $slug];
    }

    public function find(string $slug): ?array
    {
        $slug = $this->normalizeSlug($slug);

        foreach ($this->scan() as $theme) {
            if ($theme['slug'] === $slug || $this->normalizeSlug($theme['folder']) === $slug) {
                return $theme;
            }
        }

        return null;
    }

    protected function manifestFromDirectory(string $directory): ?array
    {
        $manifestFile = $directory . '/theme.json';

        if (!File::exists($manifestFile)) {
            return null;
        }

        return json_decode(File::get($manifestFile), true) ?: null;
    }

    protected function manifestSlug(array $manifest, string $fallback): string
    {
        return $this->normalizeSlug($manifest['slug'] ?? $fallback);
    }

    protected function normalizeSlug(string $slug): string
    {
        return trim(preg_replace('/[^a-z0-9]+/', '-', strtolower($slug)), '-');
    }

    protected function safeFolderName(string $slug): string
    {
        return $this->normalizeSlug($slug) ?: 'theme';
    }

    protected function canActivate(string $directory, array $manifest, string $area = 'public'): bool
    {
        if (!File::exists($directory . '/theme.json')) {
            return false;
        }

        return match ($this->normalizeArea($area)) {
            'admin' => File::exists($directory . '/layouts/admin.blade.php')
                && File::exists($directory . '/assets/css/admin.css')
                && in_array('admin-panel', $this->normalizeSupports($manifest['supports'] ?? []), true),
            'client' => File::exists($directory . '/layouts/client.blade.php')
                && File::exists($directory . '/assets/css/client.css')
                && in_array('client-portal', $this->normalizeSupports($manifest['supports'] ?? []), true),
            default => File::exists($directory . '/layouts/frontend.blade.php')
                && File::exists($directory . '/assets/css/frontend.css'),
        };
    }

    protected function normalizeSupports(array $supports): array
    {
        if (array_is_list($supports)) {
            return $supports;
        }

        return collect($supports)
            ->filter()
            ->keys()
            ->map(fn ($key) => str_replace('_', '-', (string) $key))
            ->values()
            ->all();
    }

    protected function activeAreas(string $slug, string $folder, array $activeThemes): array
    {
        $areas = [];

        foreach ($activeThemes as $area => $theme) {
            if ($slug === $theme || $folder === $theme) {
                $areas[] = $area;
            }
        }

        return $areas;
    }

    protected function pluginRequirements(array $manifest): array
    {
        $plugins = $manifest['requires']['plugins'] ?? [];

        return collect($plugins)->map(function ($slug) {
            $slug = \App\Core\PluginManager::normalizeSlug((string) $slug);
            $plugin = Plugin::where('slug', $slug)->first();

            return [
                'slug' => $slug,
                'installed' => $plugin !== null,
                'active' => (bool) ($plugin?->is_active),
                'name' => $plugin?->name ?? $slug,
            ];
        })->values()->all();
    }

    protected function normalizeArea(string $area): string
    {
        return in_array($area, ['public', 'client', 'admin'], true) ? $area : 'public';
    }

    protected function screenshotUrl(string $folder, ?string $screenshot): ?string
    {
        if (!$screenshot || str_contains($screenshot, '..')) {
            return null;
        }

        $path = "themes/{$folder}/{$screenshot}";

        return File::exists(public_path($path)) ? asset($path) : null;
    }

    protected function publishAssets(string $folder): void
    {
        $source = base_path("themes/{$folder}/assets");
        $target = public_path("themes/{$folder}/assets");

        if (!File::isDirectory($source)) {
            return;
        }

        if (File::isDirectory($target)) {
            File::deleteDirectory($target);
        }

        File::ensureDirectoryExists(dirname($target));
        File::copyDirectory($source, $target);
    }

    protected function findManifest(string $directory): ?string
    {
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($directory, \RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if ($file->getFilename() === 'theme.json') {
                return $file->getPathname();
            }
        }

        return null;
    }

    protected function cleanup(string $directory, ?string $file = null): void
    {
        if ($file && File::exists($file)) {
            File::delete($file);
        }

        if (File::isDirectory($directory)) {
            File::deleteDirectory($directory);
        }
    }
}
