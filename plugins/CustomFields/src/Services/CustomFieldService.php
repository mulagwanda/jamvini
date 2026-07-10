<?php

namespace Plugins\CustomFields\src\Services;

use App\Core\Hooks\Action;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Plugins\CustomFields\src\Models\CustomField;
use Plugins\CustomFields\src\Models\CustomFieldValue;

class CustomFieldService
{
    public const TYPES = [
        'text' => 'Text',
        'textarea' => 'Textarea',
        'email' => 'Email',
        'url' => 'URL',
        'number' => 'Number',
        'date' => 'Date',
        'select' => 'Select',
        'radio' => 'Radio',
        'checkbox' => 'Checkbox',
        'boolean' => 'Yes / No',
    ];

    public function fields(string $entityType, array $filters = []): Collection
    {
        if (!Schema::hasTable('custom_fields')) {
            return collect();
        }

        return CustomField::query()
            ->forEntity($entityType)
            ->when($filters['active'] ?? true, fn ($query) => $query->active())
            ->when($filters['registration'] ?? false, fn ($query) => $query->where('show_on_registration', true))
            ->when($filters['admin_profile'] ?? false, fn ($query) => $query->where('show_on_admin_profile', true))
            ->orderBy('sort_order')
            ->orderBy('label')
            ->get();
    }

    public function valuesFor(string $entityType, int $entityId): Collection
    {
        if (!Schema::hasTable('custom_field_values')) {
            return collect();
        }

        return CustomFieldValue::query()
            ->with('field')
            ->where('entity_type', $entityType)
            ->where('entity_id', $entityId)
            ->get()
            ->keyBy(fn ($value) => $value->field?->name);
    }

    public function formattedValues(string $entityType, int $entityId, array $filters = []): Collection
    {
        $fields = $this->fields($entityType, $filters);
        $values = $this->valuesFor($entityType, $entityId);

        return $fields->map(function (CustomField $field) use ($values) {
            $raw = $values->get($field->name)?->value ?? $field->default_value;

            return [
                'field' => $field,
                'label' => $field->label,
                'value' => $this->formatValue($field, $raw),
                'raw' => $raw,
            ];
        })->filter(fn ($item) => filled($item['raw']) || $item['field']->type === 'boolean')->values();
    }

    public function validationRules(string $entityType, array $filters = []): array
    {
        $rules = [];

        foreach ($this->fields($entityType, $filters) as $field) {
            $fieldRules = [$field->is_required ? 'required' : 'nullable'];

            match ($field->type) {
                'email' => $fieldRules[] = 'email',
                'url' => $fieldRules[] = 'url',
                'number' => $fieldRules[] = 'numeric',
                'date' => $fieldRules[] = 'date',
                'select', 'radio' => $fieldRules[] = Rule::in($field->optionList()),
                'checkbox' => $fieldRules[] = 'array',
                'boolean' => $fieldRules[] = 'boolean',
                default => $fieldRules[] = 'string',
            };

            if (!in_array($field->type, ['checkbox', 'boolean', 'number', 'date'], true)) {
                $fieldRules[] = 'max:2000';
            }

            $rules['custom_fields.' . $field->name] = $fieldRules;
            if ($field->type === 'checkbox') {
                $rules['custom_fields.' . $field->name . '.*'] = ['string', Rule::in($field->optionList())];
            }
        }

        return $rules;
    }

    public function sync(Model $entity, string $entityType, array $input, array $filters = []): void
    {
        if (!Schema::hasTable('custom_field_values')) {
            return;
        }

        $fields = $this->fields($entityType, $filters);

        foreach ($fields as $field) {
            $value = $this->normalizeValue($field, $input[$field->name] ?? null);

            CustomFieldValue::updateOrCreate(
                [
                    'custom_field_id' => $field->id,
                    'entity_type' => $entityType,
                    'entity_id' => $entity->getKey(),
                ],
                ['value' => $value]
            );
        }

        Action::do('custom_fields.values_saved', $entityType, $entity, $fields);
    }

    public function normalizeName(string $label, ?string $name = null): string
    {
        return Str::slug($name ?: $label, '_');
    }

    protected function normalizeValue(CustomField $field, mixed $value): ?string
    {
        if ($field->type === 'checkbox') {
            return collect((array) $value)->filter()->values()->toJson();
        }

        if ($field->type === 'boolean') {
            return $value ? '1' : '0';
        }

        return filled($value) ? (string) $value : null;
    }

    protected function formatValue(CustomField $field, mixed $value): string
    {
        if ($field->type === 'checkbox') {
            $decoded = json_decode((string) $value, true);
            return collect(is_array($decoded) ? $decoded : [])->filter()->implode(', ');
        }

        if ($field->type === 'boolean') {
            return $value ? 'Yes' : 'No';
        }

        return (string) $value;
    }
}
