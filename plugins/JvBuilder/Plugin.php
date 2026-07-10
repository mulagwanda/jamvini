<?php

namespace Plugins\JvBuilder;

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

    protected function publishAssets(): void
    {
        $source = base_path('plugins/JvBuilder/assets');
        $target = public_path('plugins/jv-builder');

        if (!File::isDirectory($source)) {
            return;
        }

        $this->removePublishedAssets();
        File::ensureDirectoryExists(dirname($target));

        if (function_exists('symlink') && @symlink($source, $target)) {
            return;
        }

        File::copyDirectory($source, $target);
    }

    protected function removePublishedAssets(): void
    {
        $target = public_path('plugins/jv-builder');

        if (is_link($target)) {
            @unlink($target);
            return;
        }

        if (File::isDirectory($target)) {
            File::deleteDirectory($target);
        }
    }
}
