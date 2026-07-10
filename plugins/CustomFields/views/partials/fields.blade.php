@php
    $fields = $fields ?? collect();
    $values = collect($values ?? []);
    $inputName = $inputName ?? 'custom_fields';
@endphp

@foreach($fields as $field)
    @php
        $oldKey = $inputName . '.' . $field->name;
        $stored = $values->get($field->name)?->value ?? $field->default_value;
        $current = old($oldKey, $field->type === 'checkbox' ? (json_decode((string) $stored, true) ?: []) : $stored);
        $fieldId = 'cf_' . $field->name;
    @endphp
    <div class="form-group">
        <label class="form-label" for="{{ $fieldId }}">
            {{ $field->label }} @if($field->is_required)<span class="required">*</span>@endif
        </label>

        @if($field->type === 'textarea')
            <textarea id="{{ $fieldId }}" name="{{ $inputName }}[{{ $field->name }}]" class="form-textarea" rows="3" placeholder="{{ $field->placeholder }}" {{ $field->is_required ? 'required' : '' }}>{{ $current }}</textarea>
        @elseif($field->type === 'select')
            <select id="{{ $fieldId }}" name="{{ $inputName }}[{{ $field->name }}]" class="form-select" {{ $field->is_required ? 'required' : '' }}>
                <option value="">Select...</option>
                @foreach($field->optionList() as $option)
                    <option value="{{ $option }}" {{ (string) $current === (string) $option ? 'selected' : '' }}>{{ $option }}</option>
                @endforeach
            </select>
        @elseif($field->type === 'radio')
            <div style="display:grid;gap:8px;">
                @foreach($field->optionList() as $option)
                    <label class="checkbox-group">
                        <input type="radio" name="{{ $inputName }}[{{ $field->name }}]" value="{{ $option }}" {{ (string) $current === (string) $option ? 'checked' : '' }} {{ $field->is_required ? 'required' : '' }}>
                        {{ $option }}
                    </label>
                @endforeach
            </div>
        @elseif($field->type === 'checkbox')
            <div style="display:grid;gap:8px;">
                @foreach($field->optionList() as $option)
                    <label class="checkbox-group">
                        <input type="checkbox" name="{{ $inputName }}[{{ $field->name }}][]" value="{{ $option }}" {{ in_array($option, (array) $current, true) ? 'checked' : '' }}>
                        {{ $option }}
                    </label>
                @endforeach
            </div>
        @elseif($field->type === 'boolean')
            <input type="hidden" name="{{ $inputName }}[{{ $field->name }}]" value="0">
            <label class="toggle-switch">
                <input type="checkbox" name="{{ $inputName }}[{{ $field->name }}]" value="1" {{ $current ? 'checked' : '' }}>
                <span class="toggle-slider"></span><span>Yes</span>
            </label>
        @else
            <input id="{{ $fieldId }}" type="{{ in_array($field->type, ['email', 'url', 'number', 'date'], true) ? $field->type : 'text' }}" name="{{ $inputName }}[{{ $field->name }}]" class="form-input" value="{{ $current }}" placeholder="{{ $field->placeholder }}" {{ $field->is_required ? 'required' : '' }}>
        @endif

        @if($field->help_text)
            <div class="form-hint">{{ $field->help_text }}</div>
        @endif
    </div>
@endforeach
