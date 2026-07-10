<?php

namespace App\Core;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ActivityLogger
{
    public static function log(string $action, string $entityType, $entityId, string $description, array $metadata = []): void
    {
        DB::table('activity_logs')->insert([
            'action' => $action,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'description' => $description,
            'metadata' => json_encode($metadata),
            'admin_id' => Auth::guard('admin')->id(),
            'ip_address' => request()->ip(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public static function recent(int $limit = 10): array
    {
        return DB::table('activity_logs')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->toArray();
    }
}