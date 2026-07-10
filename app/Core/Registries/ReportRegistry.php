<?php

namespace App\Core\Registries;

class ReportRegistry
{
    protected static array $reports = [];

    public static function register(string $key, callable|string $handler, array $options = []): void
    {
        self::$reports[$key] = array_merge([
            'key' => $key,
            'label' => str($key)->headline()->toString(),
            'description' => '',
            'category' => 'General',
            'icon' => 'bar-chart-3',
            'plugin' => '',
            'permission' => null,
            'handler' => $handler,
            'exportable' => true,
            'filters' => ['date_range'],
        ], $options);
    }

    public static function registerManifest(string $plugin, array $reports): void
    {
        foreach ($reports as $report) {
            if (empty($report['key']) || empty($report['handler'])) {
                continue;
            }

            self::register($report['key'], $report['handler'], array_merge($report, ['plugin' => $plugin]));
        }
    }

    public static function all(): array
    {
        return collect(self::$reports)
            ->sortBy(['category', 'label'])
            ->toArray();
    }

    public static function grouped(): array
    {
        return collect(self::all())->groupBy('category')->toArray();
    }

    public static function get(string $key): ?array
    {
        return self::$reports[$key] ?? null;
    }

    public static function run(string $key, array $filters = []): array
    {
        $report = self::get($key);
        if (!$report) {
            abort(404);
        }

        $handler = $report['handler'];
        $result = is_string($handler)
            ? app($handler)->handle($filters, $report)
            : call_user_func($handler, $filters, $report);

        return array_merge([
            'summary' => [],
            'columns' => [],
            'rows' => [],
            'chart' => null,
        ], is_array($result) ? $result : []);
    }

    public static function removePluginReports(string $plugin): void
    {
        foreach (self::$reports as $key => $report) {
            if (($report['plugin'] ?? '') === $plugin) {
                unset(self::$reports[$key]);
            }
        }
    }
}
