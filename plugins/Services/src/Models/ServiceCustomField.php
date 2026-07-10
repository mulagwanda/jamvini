<?php

namespace Plugins\Services\src\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceCustomField extends Model
{
    protected $table = 'service_custom_fields';

    protected $fillable = [
        'service_id',
        'name',
        'label',
        'type',
        'options',
        'placeholder',
        'help_text',
        'is_required',
        'is_public',
        'sort_order',
    ];

    protected $casts = [
        'is_required' => 'boolean',
        'is_public' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function optionList(): array
    {
        return collect(preg_split('/\r\n|\r|\n/', (string) $this->options))
            ->map(fn ($option) => trim($option))
            ->filter()
            ->values()
            ->all();
    }
}
