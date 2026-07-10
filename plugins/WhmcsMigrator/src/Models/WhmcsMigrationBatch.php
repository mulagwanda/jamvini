<?php

namespace Plugins\WhmcsMigrator\src\Models;

use Illuminate\Database\Eloquent\Model;

class WhmcsMigrationBatch extends Model
{
    protected $table = 'whmcs_migration_batches';

    protected $fillable = ['name', 'status', 'source_type', 'file_path', 'summary', 'mapping', 'notes'];

    protected $casts = [
        'summary' => 'array',
        'mapping' => 'array',
    ];
}
