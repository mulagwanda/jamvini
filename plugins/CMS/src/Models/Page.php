<?php

namespace Plugins\CMS\src\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Page extends Model
{
    use SoftDeletes;

    protected $table = 'cms_pages';

    protected $fillable = [
        'title', 'slug', 'excerpt', 'content', 'html', 'css', 'blocks', 'status',
        'author_id', 'template', 'featured_image', 'meta_title', 'meta_description', 'order'
    ];
    protected $casts = [
        'blocks' => 'json',
        'order' => 'integer',
    ];

    public function author()
    {
        return $this->belongsTo(\App\Models\Admin::class, 'author_id');
    }

    public function scopePublished($q) { return $q->where('status', 'published'); }
}
