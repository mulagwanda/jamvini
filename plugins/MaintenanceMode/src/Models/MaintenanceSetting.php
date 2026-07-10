<?php

namespace Plugins\MaintenanceMode\src\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MaintenanceSetting extends Model
{
    protected $table = 'maintenance_settings';
    protected $fillable = ['key', 'value'];

    public static function get(string $key, mixed $default = null): mixed
    {
        try {
            if (!Schema::hasTable('maintenance_settings')) {
                return $default;
            }

            return DB::table('maintenance_settings')->where('key', $key)->value('value') ?? $default;
        } catch (\Throwable $e) {
            return $default;
        }
    }

    public static function set(string $key, mixed $value): void
    {
        DB::table('maintenance_settings')->updateOrInsert(
            ['key' => $key],
            ['value' => is_array($value) ? json_encode($value) : (string) $value, 'updated_at' => now(), 'created_at' => now()]
        );
    }
}
