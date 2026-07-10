<?php

namespace Plugins\SEO\src\Models;

use Illuminate\Database\Eloquent\Model;

class SeoMeta extends Model
{
    protected $table = 'seo_meta';

    protected $fillable = [
        'entity_type', 'entity_id', 'meta_title', 'meta_description',
        'meta_keywords', 'og_title', 'og_description', 'og_image',
        'canonical_url', 'no_index', 'no_follow'
    ];

    protected $casts = [
        'no_index' => 'boolean',
        'no_follow' => 'boolean',
    ];

    public static function getFor(string $type, int $id): ?self
    {
        return static::where('entity_type', $type)->where('entity_id', $id)->first();
    }

    public static function setting(string $key, $default = null): ?string
    {
        try {
            if (!\Schema::hasTable('seo_settings')) {
                return $default;
            }

            return \DB::table('seo_settings')->where('key', $key)->value('value') ?? $default;
        } catch (\Throwable $e) {
            return $default;
        }
    }

    public static function setSetting(string $key, mixed $value): void
    {
        \DB::table('seo_settings')->updateOrInsert(
            ['key' => $key],
            ['value' => is_array($value) ? json_encode($value) : (string) $value, 'updated_at' => now(), 'created_at' => now()]
        );
    }
}
