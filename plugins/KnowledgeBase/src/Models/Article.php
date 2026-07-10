<?php

namespace Plugins\KnowledgeBase\src\Models;

use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    protected $table = 'kb_articles';

    protected $fillable = [
        'title', 'slug', 'content', 'category', 'is_published', 'views'
    ];

    protected $casts = [
        'is_published' => 'boolean',
    ];

    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }
}