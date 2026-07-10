<?php

namespace Themes\Pulse\src;

use App\Core\ThemeManager;
use App\Core\Registries\MenuRegistry;
use App\Core\Registries\DashboardRegistry;
use App\Core\Hooks\Filter;
use Illuminate\Support\ServiceProvider;

class PulseThemeProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Register theme assets
        $this->publishes([
            __DIR__ . '/../assets' => public_path('themes/pulse/assets'),
        ], 'pulse-assets');

        // Register theme view namespace
        $this->loadViewsFrom(__DIR__ . '/..', 'pulse');

        // Register theme widgets
        $this->registerWidgets();

        // Register theme settings
        $this->registerSettings();

        // Register theme routes
        $this->loadRoutesFrom(__DIR__ . '/../routes.php');
    }

    public function register(): void
    {
        $this->app->singleton('pulse.theme', function ($app) {
            return (object) ['slug' => 'pulse', 'name' => 'Pulse'];
        });
    }

    protected function registerWidgets(): void
    {
        // Admin Dashboard Widgets
        DashboardRegistry::registerWidget('pulse_stats', fn () => view('themes.pulse::admin.widgets.stats'), [
            'position' => 1,
            'size' => 'full',
            'title' => 'Quick Stats',
            'plugin' => 'pulse',
            'color' => 'blue',
        ]);

        DashboardRegistry::registerWidget('pulse_recent_orders', fn () => view('themes.pulse::admin.widgets.recent-orders'), [
            'position' => 2,
            'size' => 'medium',
            'title' => 'Recent Orders',
            'plugin' => 'pulse',
            'color' => 'green',
        ]);

        DashboardRegistry::registerWidget('pulse_income', fn () => view('themes.pulse::admin.widgets.income-chart'), [
            'position' => 2,
            'size' => 'medium',
            'title' => 'Income Overview',
            'plugin' => 'pulse',
            'color' => 'purple',
        ]);
    }

    protected function registerSettings(): void
    {
        // Theme settings that appear in admin
        Filter::add('theme_settings', function ($settings) {
            $settings['pulse'] = [
                'group' => 'Pulse Theme',
                'fields' => [
                    'primary_color' => [
                        'type' => 'color',
                        'label' => 'Primary Color',
                        'default' => '#1a5276'
                    ],
                    'secondary_color' => [
                        'type' => 'color',
                        'label' => 'Secondary Color',
                        'default' => '#2e86c1'
                    ],
                    'accent_color' => [
                        'type' => 'color',
                        'label' => 'Accent Color',
                        'default' => '#f39c12'
                    ],
                    'dark_mode' => [
                        'type' => 'toggle',
                        'label' => 'Enable Dark Mode',
                        'default' => false
                    ],
                    'layout' => [
                        'type' => 'select',
                        'label' => 'Layout Style',
                        'options' => [
                            'boxed' => 'Boxed',
                            'fullwidth' => 'Full Width'
                        ],
                        'default' => 'boxed'
                    ]
                ]
            ];
            return $settings;
        }, 10);
    }
}
