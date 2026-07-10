<?php

namespace App\Core\Packages;

use App\Models\Setting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class JamviniPackageImporter
{
    public function importArray(array $package, array $context = []): array
    {
        $data = $this->normalize($package);
        $mediaMap = $this->importMedia($data['media'] ?? [], $context);
        $counts = [
            'settings' => 0,
            'pages' => 0,
            'posts' => 0,
            'categories' => 0,
            'menus' => 0,
            'clients' => 0,
            'media' => count($mediaMap),
        ];

        foreach (($data['settings'] ?? []) as $key => $value) {
            Setting::set((string) $key, $this->stringValue($this->replaceMediaPlaceholders($value, $mediaMap)), 'migration', 'Imported ' . str_replace('_', ' ', (string) $key));
            $counts['settings']++;
        }

        if (Schema::hasTable('cms_categories')) {
            foreach (($data['categories'] ?? []) as $category) {
                if (empty($category['slug'])) {
                    continue;
                }

                DB::table('cms_categories')->updateOrInsert(
                    ['slug' => $category['slug']],
                    $this->onlyExistingColumns('cms_categories', [
                        'name' => $category['name'] ?? Str::headline($category['slug']),
                        'slug' => $category['slug'],
                        'type' => $category['type'] ?? 'post',
                        'description' => $category['description'] ?? null,
                        'parent_id' => null,
                        'updated_at' => now(),
                        'created_at' => now(),
                    ])
                );
                $counts['categories']++;
            }
        }

        if (Schema::hasTable('cms_pages')) {
            foreach (($data['pages'] ?? []) as $page) {
                if (empty($page['slug'])) {
                    continue;
                }

                $page = $this->replaceMediaPlaceholders($page, $mediaMap);

                DB::table('cms_pages')->updateOrInsert(
                    ['slug' => $page['slug']],
                    $this->onlyExistingColumns('cms_pages', [
                        'title' => $page['title'] ?? Str::headline($page['slug']),
                        'excerpt' => $page['excerpt'] ?? null,
                        'content' => $page['content'] ?? '',
                        'html' => $page['html'] ?? null,
                        'css' => $page['css'] ?? null,
                        'blocks' => isset($page['blocks']) ? json_encode($page['blocks']) : null,
                        'status' => $page['status'] ?? 'published',
                        'template' => $page['template'] ?? 'default',
                        'featured_image' => $page['featured_image'] ?? null,
                        'meta_title' => $page['meta_title'] ?? null,
                        'meta_description' => $page['meta_description'] ?? null,
                        'order' => $page['order'] ?? 0,
                        'updated_at' => now(),
                        'created_at' => now(),
                    ])
                );
                $counts['pages']++;
            }

            if (!empty($data['homepage_slug'])) {
                $homepageId = DB::table('cms_pages')->where('slug', $data['homepage_slug'])->value('id');
                if ($homepageId) {
                    Setting::set('homepage_type', 'page', 'site', 'Homepage Type');
                    Setting::set('homepage_page_id', (string) $homepageId, 'site', 'Homepage Page');
                }
            }
        }

        if (Schema::hasTable('cms_posts')) {
            foreach (($data['posts'] ?? []) as $post) {
                if (empty($post['slug'])) {
                    continue;
                }

                $post = $this->replaceMediaPlaceholders($post, $mediaMap);

                DB::table('cms_posts')->updateOrInsert(
                    ['slug' => $post['slug']],
                    $this->onlyExistingColumns('cms_posts', [
                        'title' => $post['title'] ?? Str::headline($post['slug']),
                        'excerpt' => $post['excerpt'] ?? null,
                        'content' => $post['content'] ?? '',
                        'status' => $post['status'] ?? 'published',
                        'featured_image' => $post['featured_image'] ?? null,
                        'meta_title' => $post['meta_title'] ?? null,
                        'meta_description' => $post['meta_description'] ?? null,
                        'published_at' => $post['published_at'] ?? now(),
                        'updated_at' => now(),
                        'created_at' => now(),
                    ])
                );
                $counts['posts']++;
            }
        }

        if (Schema::hasTable('menus') && Schema::hasTable('menu_items')) {
            foreach (($data['menus'] ?? []) as $menu) {
                if (empty($menu['location']) && empty($menu['slug'])) {
                    continue;
                }

                $location = $menu['location'] ?? $menu['slug'];
                DB::table('menus')->updateOrInsert(
                    ['location' => $location],
                    $this->onlyExistingColumns('menus', [
                        'name' => $menu['name'] ?? Str::headline($location),
                        'slug' => $menu['slug'] ?? Str::slug($menu['name'] ?? $location),
                        'location' => $location,
                        'is_active' => $menu['is_active'] ?? true,
                        'updated_at' => now(),
                        'created_at' => now(),
                    ])
                );

                $menuId = DB::table('menus')->where('location', $location)->value('id');
                DB::table('menu_items')->where('menu_id', $menuId)->delete();

                foreach (($menu['items'] ?? []) as $index => $item) {
                    $item = $this->replaceMediaPlaceholders($item, $mediaMap);
                    DB::table('menu_items')->insert($this->onlyExistingColumns('menu_items', [
                        'menu_id' => $menuId,
                        'parent_id' => null,
                        'label' => $item['label'] ?? 'Menu Item',
                        'type' => $item['type'] ?? 'custom',
                        'url' => $item['url'] ?? '#',
                        'route_name' => $item['route'] ?? $item['route_name'] ?? null,
                        'page_id' => $item['page_id'] ?? null,
                        'target' => $item['target'] ?? '_self',
                        'position' => $item['sort_order'] ?? $item['position'] ?? $index,
                        'is_active' => $item['is_active'] ?? true,
                        'visibility' => $item['visibility'] ?? 'all',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]));
                }
                $counts['menus']++;
            }
        }

        if (Schema::hasTable('clients')) {
            foreach (($data['clients'] ?? []) as $client) {
                if (empty($client['email'])) {
                    continue;
                }

                DB::table('clients')->updateOrInsert(
                    ['email' => $client['email']],
                    $this->onlyExistingColumns('clients', [
                        'client_number' => $client['client_number'] ?? null,
                        'company_name' => $client['company_name'] ?? null,
                        'type' => $client['type'] ?? 'individual',
                        'first_name' => $client['first_name'] ?? 'Client',
                        'last_name' => $client['last_name'] ?? 'Imported',
                        'email' => $client['email'],
                        'phone' => $client['phone'] ?? null,
                        'mobile' => $client['mobile'] ?? null,
                        'billing_email' => $client['billing_email'] ?? null,
                        'technical_email' => $client['technical_email'] ?? null,
                        'address' => $client['address'] ?? null,
                        'city' => $client['city'] ?? null,
                        'state' => $client['state'] ?? null,
                        'postal_code' => $client['postal_code'] ?? null,
                        'country' => $client['country'] ?? 'Tanzania',
                        'tin_number' => $client['tin_number'] ?? null,
                        'vat_exempt' => $client['vat_exempt'] ?? false,
                        'currency' => $client['currency'] ?? null,
                        'language' => $client['language'] ?? 'en',
                        'timezone' => $client['timezone'] ?? null,
                        'credit_balance' => $client['credit_balance'] ?? 0,
                        'status' => $client['status'] ?? 'active',
                        'notes' => $client['notes'] ?? null,
                        'source' => $client['source'] ?? 'jamvini_import',
                        'external_id' => $client['external_id'] ?? null,
                        'email_marketing_opt_in' => $client['email_marketing_opt_in'] ?? false,
                        'updated_at' => now(),
                        'created_at' => now(),
                    ])
                );
                $counts['clients']++;
            }
        }

        return ['success' => true, 'message' => 'JamVini package imported.', 'counts' => $counts];
    }

    protected function normalize(array $package): array
    {
        if (($package['format'] ?? null) === 'jamvini-package') {
            $sections = $package['sections'] ?? [];

            return [
                'settings' => array_merge($sections['settings'] ?? [], $sections['theme_settings'] ?? []),
                'pages' => $sections['cms']['pages'] ?? $sections['pages'] ?? [],
                'posts' => $sections['cms']['posts'] ?? $sections['posts'] ?? [],
                'categories' => $sections['cms']['categories'] ?? $sections['categories'] ?? [],
                'menus' => $sections['menus'] ?? [],
                'clients' => $sections['clients'] ?? [],
                'media' => $sections['media'] ?? [],
                'homepage_slug' => $sections['homepage_slug'] ?? $package['homepage_slug'] ?? null,
            ];
        }

        return $package;
    }

    protected function importMedia(array $items, array $context): array
    {
        $map = [];

        foreach ($items as $item) {
            $key = $item['key'] ?? $item['id'] ?? null;
            if (!$key) {
                continue;
            }

            $source = $item['source'] ?? $item['path'] ?? null;
            $localPath = $this->resolveLocalMediaPath($source, $context);
            if (!$localPath || !File::exists($localPath)) {
                continue;
            }

            $filename = Str::slug(pathinfo($localPath, PATHINFO_FILENAME)) . '-' . Str::random(8) . '.' . pathinfo($localPath, PATHINFO_EXTENSION);
            $relative = 'theme-demo/' . ($context['theme'] ?? 'package') . '/' . $filename;
            $destination = storage_path('app/public/' . $relative);
            File::ensureDirectoryExists(dirname($destination));
            File::copy($localPath, $destination);

            if (Schema::hasTable('cms_media')) {
                $lookup = Schema::hasColumn('cms_media', 'external_id')
                    ? ['external_id' => 'jamvini-package:' . $key]
                    : ['path' => $relative];

                DB::table('cms_media')->updateOrInsert(
                    $lookup,
                    $this->onlyExistingColumns('cms_media', [
                        'filename' => $filename,
                        'original_name' => $item['original_name'] ?? basename($localPath),
                        'mime_type' => File::mimeType($localPath) ?: 'application/octet-stream',
                        'size' => File::size($localPath),
                        'path' => $relative,
                        'thumbnail_path' => null,
                        'folder' => $item['folder'] ?? 'theme-demo',
                        'source' => 'jamvini_package',
                        'external_id' => 'jamvini-package:' . $key,
                        'attribution' => isset($item['attribution']) ? json_encode($item['attribution']) : null,
                        'metadata' => isset($item['metadata']) ? json_encode($item['metadata']) : null,
                        'alt_text' => $item['alt_text'] ?? null,
                        'updated_at' => now(),
                        'created_at' => now(),
                    ])
                );
            }

            $map[$key] = 'storage/' . $relative;
        }

        return $map;
    }

    protected function resolveLocalMediaPath(?string $source, array $context): ?string
    {
        if (!$source || Str::startsWith($source, ['http://', 'https://'])) {
            return null;
        }

        if (Str::startsWith($source, ['/'])) {
            return $source;
        }

        if (!empty($context['base_path'])) {
            return rtrim($context['base_path'], '/') . '/' . ltrim($source, '/');
        }

        return base_path(ltrim($source, '/'));
    }

    protected function replaceMediaPlaceholders(mixed $value, array $mediaMap): mixed
    {
        if (is_array($value)) {
            foreach ($value as $key => $nested) {
                $value[$key] = $this->replaceMediaPlaceholders($nested, $mediaMap);
            }

            return $value;
        }

        if (!is_string($value)) {
            return $value;
        }

        foreach ($mediaMap as $key => $path) {
            $value = str_replace('{{media.' . $key . '}}', $path, $value);
        }

        return $value;
    }

    protected function onlyExistingColumns(string $table, array $values): array
    {
        return collect($values)
            ->filter(fn ($value, $column) => Schema::hasColumn($table, $column))
            ->all();
    }

    protected function stringValue(mixed $value): string
    {
        return is_array($value) ? json_encode($value) : (string) $value;
    }
}
