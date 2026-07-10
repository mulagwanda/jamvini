<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminDepartment extends Model
{
    protected $fillable = [
        'name', 'slug', 'description', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function admins()
    {
        return $this->hasMany(Admin::class);
    }
}
