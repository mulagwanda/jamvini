<div class="jamvini-form" id="form-{{ $form->id }}">
    @if(session('form_success'))
        <div style="background: #dcfce7; color: #16a34a; padding: 16px; border-radius: 8px; margin-bottom: 16px;">{{ session('form_success') }}</div>
    @endif
    
    <form action="{{ route('form.submit', $form->slug) }}" method="POST">
        @csrf
        @foreach($form->fields as $field)
        <div style="margin-bottom: 16px;">
            <label style="display: block; font-weight: 600; margin-bottom: 6px; font-size: 0.9rem;">
                {{ $field['label'] ?? $field['name'] }}
                @if(!empty($field['required'])) <span style="color: #dc2626;">*</span> @endif
            </label>
            @if(($field['type'] ?? 'text') === 'textarea')
                <textarea name="{{ $field['name'] }}" style="width: 100%; padding: 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 0.95rem; font-family: inherit;" rows="4" {{ !empty($field['required']) ? 'required' : '' }}></textarea>
            @else
                <input type="{{ $field['type'] ?? 'text' }}" name="{{ $field['name'] }}" style="width: 100%; padding: 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 0.95rem;" {{ !empty($field['required']) ? 'required' : '' }}>
            @endif
        </div>
        @endforeach
        <button type="submit" style="background: #6C5CE7; color: white; border: none; padding: 12px 32px; border-radius: 8px; font-weight: 600; cursor: pointer; font-size: 0.95rem;">📤 Submit</button>
    </form>
</div>