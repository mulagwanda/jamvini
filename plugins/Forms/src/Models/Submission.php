<?php

namespace Plugins\Forms\src\Models;

use Illuminate\Database\Eloquent\Model;

class Submission extends Model
{
    protected $table = 'form_submissions';

    protected $fillable = ['form_id', 'data', 'ip_address', 'status'];

    protected $casts = ['data' => 'array'];

    public function form()
    {
        return $this->belongsTo(Form::class);
    }
}