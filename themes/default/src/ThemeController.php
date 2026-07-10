<?php

namespace Themes\DefaultTheme;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class ThemeController extends Controller
{
    protected string $theme = 'default';
    protected array $themeConfig;

    public function __construct()
    {
        $this->theme = active_theme('public');
        $configFile = base_path("themes/{$this->theme}/theme.json");
        $this->themeConfig = file_exists($configFile) ? json_decode(file_get_contents($configFile), true) : [];
    }

    public function index()
    {
        if (\Illuminate\Support\Facades\Route::has('admin.themes.options')) {
            return redirect()->route('admin.themes.options', active_theme('public'));
        }

        $settings = $this->themeConfig['settings'] ?? [];
        
        foreach ($settings as $key => &$config) {
            if (!is_array($config)) {
                $config = ['type' => 'text', 'label' => str($key)->headline()->toString(), 'default' => $config];
            }
            $config['current_value'] = Setting::get("theme_{$this->theme}_{$key}", $config['default'] ?? '');
        }

        return view('themes.default::admin.options', [
            'theme' => $this->themeConfig,
            'settings' => $settings,
        ]);
    }

    public function update(Request $request)
    {
        if (\Illuminate\Support\Facades\Route::has('admin.themes.options.update')) {
            return redirect()->route('admin.themes.options', active_theme('public'));
        }

        $settings = $this->themeConfig['settings'] ?? [];

        $validated = $request->validate([
            'settings' => 'array',
            'settings.*' => 'nullable|string|max:1000',
            'logo_file' => 'nullable|image|max:2048',
            'favicon_file' => 'nullable|image|max:512',
        ]);

        if ($request->hasFile('logo_file')) {
            $validated['settings']['logo_url'] = $request->file('logo_file')->store('theme', 'public');
        }
        if ($request->hasFile('favicon_file')) {
            $validated['settings']['favicon_url'] = $request->file('favicon_file')->store('theme', 'public');
        }

        foreach ($validated['settings'] as $key => $value) {
            if ($value !== null && isset($settings[$key])) {
                Setting::set("theme_{$this->theme}_{$key}", $value);
            }
        }

        return redirect()->route('admin.theme.index')
            ->with('success', 'Theme options saved!');
    }
}
