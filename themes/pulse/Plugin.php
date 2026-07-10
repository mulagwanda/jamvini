<?php

namespace Themes\Pulse;

use App\Core\ThemeManager;
use Illuminate\Support\Facades\File;

class Plugin
{
    public function install(): void
    {
        $this->publishAssets();
        $this->createDefaultPages();
    }

    public function activate(): void
    {
        $this->publishAssets();
        $this->registerTheme();
    }

    public function deactivate(): void
    {
        // Remove published assets
        $target = public_path('themes/pulse');
        if (File::exists($target)) {
            File::deleteDirectory($target);
        }
    }

    public function uninstall(): void
    {
        $this->deactivate();
        // Remove theme data from database
    }

    public function publishAssets(): void
    {
        $source = base_path('themes/pulse/assets');
        $target = public_path('themes/pulse/assets');

        if (!File::exists($source)) {
            return;
        }

        if (File::exists($target)) {
            File::deleteDirectory($target);
        }

        File::ensureDirectoryExists(dirname($target));
        File::copyDirectory($source, $target);
    }

    protected function createDefaultPages(): void
    {
        // Create default pages for the theme
        // Home, About, Services, Pricing, Contact
    }

    protected function registerTheme(): void
    {
        // Register theme with ThemeManager
        $manager = app(ThemeManager::class);
        $manager->activate('pulse', 'public');
        $manager->activate('pulse', 'client');
        $manager->activate('pulse', 'admin');
    }
}
