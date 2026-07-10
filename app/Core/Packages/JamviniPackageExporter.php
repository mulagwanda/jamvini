<?php

namespace App\Core\Packages;

use App\Models\Plugin;
use App\Models\Setting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class JamviniPackageExporter
{
    public function themeDemo(string $themeSlug): array
    {
        $themePrefix = 'theme_' . str_replace('-', '_', $themeSlug) . '_';
        $settings = $this->settings(fn (array $row) => str_starts_with($row['key'], $themePrefix));

        return $this->package('theme_demo', [
            'theme' => ['slug' => $themeSlug],
            'settings' => [],
            'theme_settings' => $settings,
            'cms' => $this->cms(),
            'menus' => $this->menus(),
            'media' => [],
            'homepage_slug' => $this->homepageSlug(),
        ]);
    }

    public function migration(array $sections = []): array
    {
        $sections = $sections ?: ['settings', 'cms', 'menus', 'clients', 'plugins'];

        $payload = [];

        if (in_array('settings', $sections, true)) {
            $payload['settings'] = $this->settings(fn (array $row) => !$this->isSensitiveSetting($row['key']));
        }

        if (in_array('cms', $sections, true)) {
            $payload['cms'] = $this->cms();
            $payload['homepage_slug'] = $this->homepageSlug();
        }

        if (in_array('menus', $sections, true)) {
            $payload['menus'] = $this->menus();
        }

        if (in_array('clients', $sections, true)) {
            $payload['clients'] = $this->clients();
        }

        if (in_array('plugins', $sections, true) && Schema::hasTable('plugins')) {
            $payload['plugins'] = Plugin::query()
                ->select(['name', 'slug', 'version', 'type', 'is_active', 'is_system'])
                ->orderBy('slug')
                ->get()
                ->map(fn ($plugin) => $plugin->toArray())
                ->all();
        }

        return $this->package('migration', $payload);
    }

    protected function package(string $type, array $sections): array
    {
        return [
            'format' => 'jamvini-package',
            'version' => '1.0',
            'type' => $type,
            'created_at' => now()->toIso8601String(),
            'source' => [
                'name' => config('app.name', 'JamVini'),
                'url' => config('app.url'),
                'jamvini_version' => config('app.version', 'dev'),
            ],
            'sections' => $sections,
        ];
    }

    protected function settings(callable $filter): array
    {
        if (!Schema::hasTable('settings')) {
            return [];
        }

        return Setting::query()
            ->select(['key', 'value'])
            ->orderBy('key')
            ->get()
            ->map(fn ($setting) => ['key' => $setting->key, 'value' => $setting->value])
            ->filter($filter)
            ->mapWithKeys(fn ($row) => [$row['key'] => $row['value']])
            ->all();
    }

    protected function cms(): array
    {
        return [
            'pages' => $this->tableRows('cms_pages', [
                'title', 'slug', 'excerpt', 'content', 'html', 'css', 'blocks', 'status',
                'template', 'featured_image', 'meta_title', 'meta_description', 'order',
            ]),
            'posts' => $this->tableRows('cms_posts', [
                'title', 'slug', 'excerpt', 'content', 'status', 'featured_image',
                'meta_title', 'meta_description', 'published_at',
            ]),
            'categories' => $this->tableRows('cms_categories', [
                'name', 'slug', 'type', 'description', 'parent_id',
            ]),
        ];
    }

    protected function menus(): array
    {
        if (!Schema::hasTable('menus') || !Schema::hasTable('menu_items')) {
            return [];
        }

        return DB::table('menus')
            ->orderBy('location')
            ->get()
            ->map(function ($menu) {
                $items = DB::table('menu_items')
                    ->where('menu_id', $menu->id)
                    ->orderBy('parent_id')
                    ->orderBy('position')
                    ->get()
                    ->map(fn ($item) => [
                        'label' => $item->label,
                        'type' => $item->type,
                        'url' => $item->url,
                        'page_id' => $item->page_id,
                        'route_name' => $item->route_name,
                        'target' => $item->target,
                        'visibility' => $item->visibility,
                        'position' => $item->position,
                        'is_active' => (bool) $item->is_active,
                    ])
                    ->all();

                return [
                    'name' => $menu->name,
                    'slug' => $menu->slug,
                    'location' => $menu->location,
                    'is_active' => (bool) $menu->is_active,
                    'items' => $items,
                ];
            })
            ->all();
    }

    protected function clients(): array
    {
        return $this->tableRows('clients', [
            'client_number', 'company_name', 'type', 'first_name', 'last_name', 'email',
            'phone', 'mobile', 'billing_email', 'technical_email', 'address', 'city',
            'state', 'postal_code', 'country', 'tin_number', 'vat_exempt', 'currency',
            'language', 'timezone', 'credit_balance', 'status', 'notes', 'source',
            'external_id', 'email_marketing_opt_in',
        ]);
    }

    protected function homepageSlug(): ?string
    {
        if (!Schema::hasTable('cms_pages')) {
            return null;
        }

        $pageId = Setting::get('homepage_page_id');

        return $pageId ? DB::table('cms_pages')->where('id', $pageId)->value('slug') : null;
    }

    protected function tableRows(string $table, array $columns): array
    {
        if (!Schema::hasTable($table)) {
            return [];
        }

        $existing = collect($columns)
            ->filter(fn ($column) => Schema::hasColumn($table, $column))
            ->values()
            ->all();

        if (!$existing) {
            return [];
        }

        return DB::table($table)
            ->select($existing)
            ->orderBy(Schema::hasColumn($table, 'slug') ? 'slug' : $existing[0])
            ->get()
            ->map(fn ($row) => collect((array) $row)->map(function ($value, $key) {
                if ($key === 'blocks' && is_string($value)) {
                    $decoded = json_decode($value, true);
                    return json_last_error() === JSON_ERROR_NONE ? $decoded : $value;
                }

                return $value;
            })->all())
            ->all();
    }

    protected function isSensitiveSetting(string $key): bool
    {
        return preg_match('/(password|passwd|secret|token|private|api[_-]?key|consumer[_-]?key|access[_-]?key|salt|cipher|certificate)/i', $key) === 1;
    }
}
