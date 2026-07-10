<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class Setting extends Model
{
    protected $fillable = ['key', 'value', 'type', 'group', 'label'];

    /**
     * Get a setting value by key.
     */
    public static function get(string $key, $default = null)
    {
        try {
            if (!Schema::hasTable('settings')) {
                return $default;
            }

            $setting = static::where('key', $key)->first();
            return $setting ? $setting->value : $default;
        } catch (\Throwable $e) {
            return $default;
        }
    }

    /**
     * Set a setting value by key.
     */
    public static function set(string $key, $value, ?string $group = null, ?string $label = null): void
    {
        if (!Schema::hasTable('settings')) {
            return;
        }

        $attributes = ['value' => $value];

        if ($group !== null) {
            $attributes['group'] = $group;
        }

        if ($label !== null) {
            $attributes['label'] = $label;
        }

        static::updateOrCreate(['key' => $key], $attributes);
    }

    /**
     * Get all settings as a key-value array.
     */
    public static function allAsArray(): array
    {
        if (!Schema::hasTable('settings')) {
            return [];
        }

        return static::pluck('value', 'key')->toArray();
    }
}
