<?php

namespace Plugins\CustomFields\src\Models;

use Illuminate\Database\Eloquent\Model;

class CustomField extends Model
{
    protected $fillable = [
        'entity_type',
        'name',
        'label',
        'type',
        'options',
        'placeholder',
        'help_text',
        'default_value',
        'is_required',
        'is_public',
        'show_on_registration',
        'show_on_admin_profile',
        'is_active',
        'sort_order',
        'settings',
    ];

    protected $casts = [
        'is_required' => 'boolean',
        'is_public' => 'boolean',
        'show_on_registration' => 'boolean',
        'show_on_admin_profile' => 'boolean',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
        'settings' => 'array',
    ];

    public function values()
    {
        return $this->hasMany(CustomFieldValue::class);
    }

    public function optionList(): array
    {
        return collect(preg_split('/\r\n|\r|\n/', (string) $this->options))
            ->map(fn ($option) => trim($option))
            ->filter()
            ->values()
            ->all();
    }

    public function scopeForEntity($query, string $entityType)
    {
        return $query->where('entity_type', $entityType);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
