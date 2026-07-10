<?php

namespace Plugins\CMS\src\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $table = 'cms_categories';

    protected $fillable = ['name', 'slug', 'type', 'description', 'parent_id'];

    public function posts()
    {
        return $this->belongsToMany(Post::class, 'cms_post_category', 'category_id', 'post_id');
    }

    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }
}