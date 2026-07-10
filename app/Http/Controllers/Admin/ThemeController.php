<?php

namespace App\Http\Controllers\Admin;

use App\Core\ThemeManager;
use App\Core\ThemeDemoImporter;
use App\Core\Packages\JamviniPackageExporter;
use App\Core\Packages\JamviniPackageImporter;
use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class ThemeController extends Controller
{
    public function __construct(protected ThemeManager $themes)
    {
    }

    public function index()
    {
        $themes = $this->themes->scan();
        $activeThemes = $this->themes->activeThemes();

        return view('admin.themes.index', compact('themes', 'activeThemes'));
    }

    public function activate(Request $request)
    {
        $validated = $request->validate([
            'slug' => ['required', 'string', 'max:100'],
            'area' => ['required', 'in:public,client,admin'],
        ]);

        if ($this->themes->activate($validated['slug'], $validated['area'])) {
            return $this->respond($request, true, 'Theme activated for ' . str_replace('_', ' ', $validated['area']) . '.');
        }

        return $this->respond($request, false, 'Theme could not be activated.');
    }

    public function upload(Request $request)
    {
        $validated = $request->validate([
            'theme_zip' => ['required', 'file', 'mimes:zip', 'max:20480'],
        ]);

        $result = $this->themes->upload($validated['theme_zip']);

        return $this->respond($request, (bool) $result['success'], $result['message']);
    }

    public function options(string $slug)
    {
        $theme = $this->themes->find($slug);
        abort_unless($theme, 404);

        $manifest = json_decode(file_get_contents($theme['path'] . '/theme.json'), true) ?: [];
        $settings = $manifest['settings'] ?? [];

        foreach ($settings as $key => &$config) {
            if (!is_array($config)) {
                $config = ['type' => 'text', 'label' => str($key)->headline()->toString(), 'default' => $config];
            }

            $config['current_value'] = Setting::get(jv_theme_setting_key($theme['folder'], $key), $config['default'] ?? '');
        }

        $demos = $manifest['demo_data'] ?? [];

        return view('admin.themes.options', compact('theme', 'manifest', 'settings', 'demos'));
    }

    public function updateOptions(Request $request, string $slug)
    {
        $theme = $this->themes->find($slug);
        abort_unless($theme, 404);

        $manifest = json_decode(file_get_contents($theme['path'] . '/theme.json'), true) ?: [];
        $settings = $manifest['settings'] ?? [];

        $validated = $request->validate([
            'settings' => ['nullable', 'array'],
            'settings.*' => ['nullable', 'string', 'max:50000'],
            'files' => ['nullable', 'array'],
            'files.*' => ['nullable', 'image', 'max:4096'],
        ]);

        foreach (($validated['settings'] ?? []) as $key => $value) {
            if (array_key_exists($key, $settings)) {
                Setting::set(jv_theme_setting_key($theme['folder'], $key), (string) $value, 'themes', $theme['name'] . ' ' . str_replace('_', ' ', $key));
            }
        }

        foreach ($request->file('files', []) as $key => $file) {
            if (array_key_exists($key, $settings)) {
                Setting::set(jv_theme_setting_key($theme['folder'], $key), $file->store('theme/' . $theme['folder'], 'public'), 'themes', $theme['name'] . ' ' . str_replace('_', ' ', $key));
            }
        }

        return redirect()->route('admin.themes.options', $theme['slug'])->with('success', 'Theme options saved.');
    }

    public function importDemo(Request $request, string $slug, ThemeDemoImporter $importer)
    {
        $theme = $this->themes->find($slug);
        abort_unless($theme, 404);

        $validated = $request->validate([
            'demo' => ['required', 'string', 'max:80'],
        ]);

        $result = $importer->import($theme['folder'], $validated['demo']);

        return redirect()->route('admin.themes.options', $theme['slug'])
            ->with($result['success'] ? 'success' : 'error', $result['message']);
    }

    public function exportDemo(string $slug, JamviniPackageExporter $exporter)
    {
        $theme = $this->themes->find($slug);
        abort_unless($theme, 404);

        $package = $exporter->themeDemo($theme['folder']);
        $filename = $theme['folder'] . '-demo-pack-' . now()->format('Ymd-His') . '.json';

        return response()->streamDownload(function () use ($package) {
            echo json_encode($package, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        }, $filename, ['Content-Type' => 'application/json']);
    }

    public function importPackage(Request $request, string $slug, JamviniPackageImporter $importer)
    {
        $theme = $this->themes->find($slug);
        abort_unless($theme, 404);

        $validated = $request->validate([
            'package' => ['required', 'file', 'mimes:json,txt', 'max:51200'],
        ]);

        $package = json_decode(file_get_contents($validated['package']->getRealPath()), true);
        if (!is_array($package)) {
            return redirect()->route('admin.themes.options', $theme['slug'])
                ->with('error', 'The uploaded theme package is not valid JSON.');
        }

        $result = $importer->importArray($package, ['theme' => $theme['folder']]);
        $counts = collect($result['counts'] ?? [])
            ->map(fn ($count, $name) => str($name)->headline() . ': ' . $count)
            ->implode(', ');

        return redirect()->route('admin.themes.options', $theme['slug'])
            ->with($result['success'] ? 'success' : 'error', trim(($result['message'] ?? 'Theme package imported.') . ' ' . $counts));
    }

    protected function respond(Request $request, bool $success, string $message)
    {
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => $success,
                'message' => $message,
                'redirect' => route('admin.themes.index'),
            ], $success ? 200 : 422);
        }

        return redirect()->route('admin.themes.index')
            ->with($success ? 'success' : 'error', $message);
    }
}
