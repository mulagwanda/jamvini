<?php

namespace Plugins\Security\src\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SecuritySetting extends Model
{
    protected $table = 'security_settings';
    protected $fillable = ['key', 'value'];

    public static function get(string $key, mixed $default = null): mixed
    {
        try {
            if (!Schema::hasTable('security_settings')) {
                return $default;
            }

            return DB::table('security_settings')->where('key', $key)->value('value') ?? $default;
        } catch (\Throwable $e) {
            return $default;
        }
    }

    public static function set(string $key, mixed $value): void
    {
        DB::table('security_settings')->updateOrInsert(
            ['key' => $key],
            ['value' => is_array($value) ? json_encode($value) : (string) $value, 'updated_at' => now(), 'created_at' => now()]
        );
    }
}
