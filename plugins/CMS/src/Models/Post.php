<?php

namespace Plugins\CMS\src\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Post extends Model
{
    use SoftDeletes;

    protected $table = 'cms_posts';

    protected $fillable = [
        'title', 'slug', 'excerpt', 'content', 'status', 'author_id',
        'featured_image', 'meta_title', 'meta_description', 'published_at'
    ];

    protected $casts = ['published_at' => 'datetime'];

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'cms_post_category', 'post_id', 'category_id');
    }

    public function author()
    {
        return $this->belongsTo(\App\Models\Admin::class, 'author_id');
    }

    public function scopePublished($q)
    {
        return $q->where('status', 'published')
            ->where(fn ($query) => $query->whereNull('published_at')->orWhere('published_at', '<=', now()));
    }
}
