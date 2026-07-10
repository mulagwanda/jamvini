<?php

namespace App\Core;

use App\Core\Packages\JamviniPackageImporter;
use Illuminate\Support\Facades\File;

class ThemeDemoImporter
{
    public function __construct(protected JamviniPackageImporter $packages)
    {
    }

    public function import(string $theme, string $demo = 'default'): array
    {
        $theme = preg_replace('/[^a-z0-9-]+/', '-', strtolower($theme));
        $demo = preg_replace('/[^a-z0-9-]+/', '-', strtolower($demo ?: 'default'));
        $file = base_path("themes/{$theme}/demo/{$demo}.json");

        if (!File::exists($file)) {
            return ['success' => false, 'message' => 'Demo data file was not found.'];
        }

        $data = json_decode(File::get($file), true);
        if (!is_array($data)) {
            return ['success' => false, 'message' => 'Demo data file is not valid JSON.'];
        }

        $result = $this->packages->importArray($data, [
            'theme' => $theme,
            'base_path' => dirname($file),
        ]);

        $result['message'] = $result['success'] ? 'Demo data imported.' : ($result['message'] ?? 'Demo import failed.');

        return $result;
    }
}
