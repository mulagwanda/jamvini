<?php

namespace Plugins\CustomFields\src\Controllers;

use App\Core\Hooks\Action;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Plugins\CustomFields\src\Models\CustomField;
use Plugins\CustomFields\src\Services\CustomFieldService;

class CustomFieldController extends Controller
{
    public function index(Request $request)
    {
        $entity = $request->get('entity_type', 'client');
        $fields = CustomField::query()
            ->when($entity, fn ($query) => $query->where('entity_type', $entity))
            ->orderBy('entity_type')
            ->orderBy('sort_order')
            ->orderBy('label')
            ->paginate(25)
            ->withQueryString();

        $types = CustomFieldService::TYPES;
        $entities = $this->entities();

        return view('plugins.CustomFields::admin.index', compact('fields', 'types', 'entities', 'entity'));
    }

    public function create()
    {
        $field = new CustomField([
            'entity_type' => request('entity_type', 'client'),
            'type' => 'text',
            'is_active' => true,
            'show_on_admin_profile' => true,
        ]);

        return view('plugins.CustomFields::admin.form', [
            'field' => $field,
            'types' => CustomFieldService::TYPES,
            'entities' => $this->entities(),
        ]);
    }

    public function store(Request $request, CustomFieldService $service)
    {
        $validated = $this->validated($request);
        $validated['name'] = $service->normalizeName($validated['label'], $validated['name'] ?? null);
        $this->ensureUniqueName($validated['entity_type'], $validated['name']);
        $validated = $this->booleans($validated, $request);

        $field = CustomField::create($validated);
        Action::do('custom_fields.created', $field);

        return redirect()->route('admin.custom-fields.index', ['entity_type' => $field->entity_type])
            ->with('success', 'Custom field created.');
    }

    public function edit(CustomField $field)
    {
        return view('plugins.CustomFields::admin.form', [
            'field' => $field,
            'types' => CustomFieldService::TYPES,
            'entities' => $this->entities(),
        ]);
    }

    public function update(Request $request, CustomField $field, CustomFieldService $service)
    {
        $validated = $this->validated($request, $field);
        $validated['name'] = $service->normalizeName($validated['label'], $validated['name'] ?? null);
        $this->ensureUniqueName($validated['entity_type'], $validated['name'], $field);
        $validated = $this->booleans($validated, $request);

        $field->update($validated);
        Action::do('custom_fields.updated', $field);

        return redirect()->route('admin.custom-fields.index', ['entity_type' => $field->entity_type])
            ->with('success', 'Custom field updated.');
    }

    public function destroy(CustomField $field)
    {
        $entity = $field->entity_type;
        Action::do('custom_fields.deleted', $field);
        $field->delete();

        return redirect()->route('admin.custom-fields.index', ['entity_type' => $entity])
            ->with('success', 'Custom field deleted.');
    }

    protected function validated(Request $request, ?CustomField $field = null): array
    {
        $entityTypes = array_keys($this->entities());
        $types = array_keys(CustomFieldService::TYPES);

        return $request->validate([
            'entity_type' => ['required', Rule::in($entityTypes)],
            'name' => [
                'nullable',
                'string',
                'max:100',
                Rule::unique('custom_fields', 'name')
                    ->where(fn ($query) => $query->where('entity_type', $request->input('entity_type')))
                    ->ignore($field?->id),
            ],
            'label' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::in($types)],
            'options' => ['nullable', 'string', 'max:5000'],
            'placeholder' => ['nullable', 'string', 'max:255'],
            'help_text' => ['nullable', 'string', 'max:1000'],
            'default_value' => ['nullable', 'string', 'max:1000'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
        ]);
    }

    protected function booleans(array $validated, Request $request): array
    {
        foreach (['is_required', 'is_public', 'show_on_registration', 'show_on_admin_profile', 'is_active'] as $key) {
            $validated[$key] = $request->boolean($key);
        }

        return $validated;
    }

    protected function ensureUniqueName(string $entityType, string $name, ?CustomField $field = null): void
    {
        $exists = CustomField::query()
            ->where('entity_type', $entityType)
            ->where('name', $name)
            ->when($field, fn ($query) => $query->where('id', '!=', $field->id))
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'name' => 'A custom field with this machine name already exists for this entity.',
            ]);
        }
    }

    protected function entities(): array
    {
        return [
            'client' => 'Client',
            'order' => 'Order',
            'service' => 'Client Service',
            'domain' => 'Domain',
            'ticket' => 'Support Ticket',
        ];
    }
}
