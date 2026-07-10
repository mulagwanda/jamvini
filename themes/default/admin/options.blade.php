@extends('themes.default::layouts.admin')

@section('title', ($theme['name'] ?? 'Tanzanite') . ' Options')
@section('breadcrumbs')<span class="current">{{ $theme['name'] ?? 'Tanzanite' }} Options</span>@endsection

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">{{ $theme['name'] ?? 'Theme' }} Options</h1>
        <p class="page-subtitle">{{ $theme['description'] ?? '' }}</p>
    </div>
</div>

@if(!empty($settings))
<form action="{{ route('admin.theme.update') }}" method="POST" enctype="multipart/form-data">
    @csrf
    
    {{-- Colors --}}
    @php $colors = array_filter($settings, fn($c) => ($c['type'] ?? '') === 'color'); @endphp
    @if(count($colors))
    <div class="card">
        <div class="card-header"><h3 class="card-title">🎨 Colors</h3></div>
        <div class="card-body">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                @foreach($colors as $key => $config)
                <div class="form-group">
                    <label class="form-label">{{ $config['label'] }}</label>
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <input type="color" value="{{ $config['current_value'] }}" style="width: 50px; height: 40px; border: none; cursor: pointer;"
                               onchange="this.nextElementSibling.value=this.value">
                        <input type="text" name="settings[{{ $key }}]" class="form-input" value="{{ $config['current_value'] }}" style="width: 120px;">
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif
    
    {{-- Images --}}
    @php $images = array_filter($settings, fn($c) => ($c['type'] ?? '') === 'image'); @endphp
    @if(count($images))
    <div class="card">
        <div class="card-header"><h3 class="card-title">🖼️ Branding</h3></div>
        <div class="card-body">
            @foreach($images as $key => $config)
            <div class="form-group">
                <label class="form-label">{{ $config['label'] }}</label>
                @if($config['current_value'])
                <div style="margin-bottom: 8px;">
                    <img src="{{ asset('storage/' . $config['current_value']) }}" style="max-height: 60px; border-radius: 4px;">
                </div>
                @endif
                <input type="file" name="{{ $key }}_file" class="form-input" accept="image/*">
                @if(!empty($config['description']))<div class="form-hint">{{ $config['description'] }}</div>@endif
            </div>
            @endforeach
        </div>
    </div>
    @endif
    
    {{-- Typography --}}
    @php $fonts = array_filter($settings, fn($c, $k) => ($c['type'] ?? '') === 'select' && str_starts_with($k, 'font'), ARRAY_FILTER_USE_BOTH); @endphp
    @if(count($fonts))
    <div class="card">
        <div class="card-header"><h3 class="card-title">✍️ Typography</h3></div>
        <div class="card-body">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                @foreach($settings as $key => $config)
                    @if(($config['type'] ?? '') === 'select' && str_starts_with($key, 'font'))
                    <div class="form-group">
                        <label class="form-label">{{ $config['label'] }}</label>
                        <select name="settings[{{ $key }}]" class="form-select">
                            @foreach($config['options'] as $option)
                            <option value="{{ $option['value'] }}" {{ $config['current_value'] === $option['value'] ? 'selected' : '' }}>
                                {{ $option['label'] }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    @endif
                @endforeach
            </div>
        </div>
    </div>
    @endif
    
    {{-- Toggles --}}
    @php $booleans = array_filter($settings, fn($c) => ($c['type'] ?? '') === 'boolean'); @endphp
    @if(count($booleans))
    <div class="card">
        <div class="card-header"><h3 class="card-title">📐 Display Options</h3></div>
        <div class="card-body">
            @foreach($booleans as $key => $config)
            <div class="form-group">
                <label class="toggle-switch">
                    <input type="hidden" name="settings[{{ $key }}]" value="0">
                    <input type="checkbox" name="settings[{{ $key }}]" value="1" {{ $config['current_value'] ? 'checked' : '' }}>
                    <span class="toggle-slider"></span>
                    <span>{{ $config['label'] }}</span>
                </label>
                @if(!empty($config['description']))<div class="form-hint">{{ $config['description'] }}</div>@endif
            </div>
            @endforeach
        </div>
    </div>
    @endif
    
    {{-- Text/Textarea fields --}}
    @php $texts = array_filter($settings, fn($c) => in_array($c['type'] ?? '', ['text', 'textarea'])); @endphp
    @if(count($texts))
    <div class="card">
        <div class="card-header"><h3 class="card-title">📝 Content</h3></div>
        <div class="card-body">
            @foreach($texts as $key => $config)
            <div class="form-group">
                <label class="form-label">{{ $config['label'] }}</label>
                @if(($config['type'] ?? 'text') === 'textarea')
                <textarea name="settings[{{ $key }}]" class="form-textarea" rows="3">{{ $config['current_value'] }}</textarea>
                @else
                <input type="text" name="settings[{{ $key }}]" class="form-input" value="{{ $config['current_value'] }}">
                @endif
                @if(!empty($config['description']))<div class="form-hint">{{ $config['description'] }}</div>@endif
            </div>
            @endforeach
        </div>
    </div>
    @endif
    
    {{-- Selects (non-font) --}}
    @php $selects = array_filter($settings, fn($c, $k) => ($c['type'] ?? '') === 'select' && !str_starts_with($k, 'font'), ARRAY_FILTER_USE_BOTH); @endphp
    @if(count($selects))
    <div class="card">
        <div class="card-header"><h3 class="card-title">⚙️ Layout</h3></div>
        <div class="card-body">
            @foreach($selects as $key => $config)
            <div class="form-group">
                <label class="form-label">{{ $config['label'] }}</label>
                <select name="settings[{{ $key }}]" class="form-select" style="width: 250px;">
                    @foreach($config['options'] as $option)
                    <option value="{{ $option['value'] }}" {{ $config['current_value'] === $option['value'] ? 'selected' : '' }}>
                        {{ $option['label'] }}
                    </option>
                    @endforeach
                </select>
            </div>
            @endforeach
        </div>
    </div>
    @endif
    
    <div style="display: flex; gap: 12px; justify-content: flex-end; margin-top: 16px;">
        <button type="submit" class="btn btn-primary btn-lg">💾 Save Options</button>
    </div>
</form>
@else
<div class="card"><div class="card-body" style="text-align: center; padding: 40px;">
    <p>This theme has no customizable options. Edit <code>theme.json</code> to add settings.</p>
</div></div>
@endif
@endsection
