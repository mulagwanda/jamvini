<?php

namespace Plugins\Slider;

use Illuminate\Support\Facades\File;

class Plugin
{
    public function install(): void
    {
        $this->publishAssets();
    }

    public function activate(): void
    {
        $this->publishAssets();
    }

    public function deactivate(): void
    {
        $this->removePublishedAssets();
    }

    public function uninstall(): void
    {
        $this->removePublishedAssets();
    }

    public static function publishAssets(): void
    {
        $source = base_path('plugins/Slider/assets');
        $target = public_path('plugins/slider');

        if (!File::isDirectory($source)) {
            return;
        }

        self::removePublishedAssets();
        File::ensureDirectoryExists(dirname($target));

        if (function_exists('symlink') && @symlink($source, $target)) {
            return;
        }

        File::copyDirectory($source, $target);
    }

    public static function removePublishedAssets(): void
    {
        $target = public_path('plugins/slider');

        if (is_link($target)) {
            @unlink($target);
            return;
        }

        if (File::isDirectory($target)) {
            File::deleteDirectory($target);
        }
    }
}
