<?php

namespace Plugins\AiAssistant\src\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AiSource extends Model
{
    use SoftDeletes;

    protected $table = 'ai_assistant_sources';

    protected $fillable = [
        'type', 'title', 'url', 'file_path', 'content', 'indexed_text', 'status', 'last_indexed_at', 'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
        'last_indexed_at' => 'datetime',
    ];

    public function scopeReady($query)
    {
        return $query->where('status', 'ready')->whereNotNull('indexed_text');
    }
}
