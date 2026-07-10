@extends('themes.default::layouts.admin')

@section('title', ($theme['name'] ?? 'Theme') . ' Options')
@section('breadcrumbs')<a href="{{ route('admin.themes.index') }}">Themes</a> <span class="separator">/</span> <span class="current">{{ $theme['name'] }} Options</span>@endsection

@section('content')
@php
    $groupLabels = ['General', 'Layout', 'Colors', 'Fonts', 'Branding', 'Navigation', 'Footer', 'Advanced'];
    $groupedSettings = collect($settings)->groupBy(fn ($config, $key) => $config['group'] ?? 'General');
    $orderedGroups = collect($groupLabels)
        ->filter(fn ($group) => $groupedSettings->has($group))
        ->concat($groupedSettings->keys()->diff($groupLabels))
        ->values();
@endphp

<style>
.theme-pro-shell { display:grid; grid-template-columns:260px minmax(0,1fr); gap:18px; align-items:start; }
.theme-pro-sidebar { position:sticky; top:84px; display:grid; gap:10px; }
.theme-pro-tab { display:flex; align-items:center; justify-content:space-between; gap:10px; width:100%; border:1px solid var(--jv-gray-200); background:#fff; border-radius:8px; padding:11px 12px; color:var(--jv-gray-700); font-weight:800; text-align:left; cursor:pointer; }
.theme-pro-tab.active { color:var(--jv-primary); border-color:var(--jv-primary); background:#f8f7ff; }
.theme-pro-panel { display:none; }
.theme-pro-panel.active { display:block; }
.theme-option-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(280px,1fr)); gap:18px; }
.theme-option-wide { grid-column:1/-1; }
.theme-code { font-family:ui-monospace,SFMono-Regular,Menlo,Consolas,monospace; min-height:220px; }
.theme-summary { display:flex; gap:8px; flex-wrap:wrap; align-items:center; margin-top:8px; }
.theme-demo-row { display:flex; align-items:center; justify-content:space-between; gap:12px; flex-wrap:wrap; padding:14px; border:1px solid var(--jv-gray-200); border-radius:8px; background:#fff; }
.theme-package-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(260px,1fr)); gap:14px; }
.theme-package-box { border:1px solid var(--jv-gray-200); border-radius:8px; background:#fff; padding:16px; display:grid; gap:12px; align-content:start; }
@media (max-width: 980px) { .theme-pro-shell { grid-template-columns:1fr; } .theme-pro-sidebar { position:static; grid-template-columns:repeat(auto-fit,minmax(160px,1fr)); } }
</style>

<div class="page-header">
    <div>
        <h1 class="page-title">{{ $theme['name'] }} Options</h1>
        <p class="page-subtitle">{{ $theme['description'] ?? 'Customize this theme and import starter content.' }}</p>
        <div class="theme-summary">
            <span class="badge badge-gray">v{{ $theme['version'] }}</span>
            <span class="badge badge-info">{{ $theme['folder'] }}</span>
            @if($manifest['premium'] ?? false)<span class="badge badge-warning">Premium</span>@endif
            @if(!empty($manifest['price']))<span class="badge badge-success">{{ $manifest['price'] }}</span>@endif
            @if(!empty($manifest['builder']['recommended']))<span class="badge badge-info">Builder: {{ $manifest['builder']['recommended'] }}</span>@endif
        </div>
    </div>
    <div style="display:flex;gap:8px;flex-wrap:wrap;">
        <a href="{{ route('admin.themes.index') }}" class="btn btn-outline-primary">{{ jv_icon('palette', '', 16) }} All Themes</a>
        <a href="{{ url('/') }}" target="_blank" class="btn btn-primary">{{ jv_icon('eye', '', 16) }} Preview</a>
    </div>
</div>

<div class="theme-pro-shell">
    <aside class="theme-pro-sidebar">
        <button type="button" class="theme-pro-tab active" data-theme-tab="demo">{{ jv_icon('download', '', 16) }} Demo Packs</button>
        @foreach($orderedGroups as $group)
            <button type="button" class="theme-pro-tab" data-theme-tab="{{ str($group)->slug() }}">
                <span>{{ $group }}</span>
                <small>{{ $groupedSettings[$group]->count() }}</small>
            </button>
        @endforeach
    </aside>

    <main style="display:grid;gap:18px;">
        <section class="theme-pro-panel active" data-theme-panel="demo">
            <div class="card">
                <div class="card-header"><h3 class="card-title">{{ jv_icon('download', '', 18) }} Demo Packs</h3></div>
                <div class="card-body" style="display:grid;gap:16px;">
                    <div class="theme-package-grid">
                        <div class="theme-package-box">
                            <div>
                                <strong>Export this theme demo</strong>
                                <div style="color:var(--jv-gray-500);font-size:.88rem;">Download pages, menus, and this theme's options as a reusable JamVini demo package.</div>
                            </div>
                            <a href="{{ route('admin.themes.demo.export', $theme['slug']) }}" class="btn btn-outline-primary">{{ jv_icon('download-cloud', '', 16) }} Export Demo Pack</a>
                        </div>
                        <form class="theme-package-box" action="{{ route('admin.themes.package.import', $theme['slug']) }}" method="POST" enctype="multipart/form-data" data-confirm="Import this JamVini theme package? Existing matching pages, menus, and settings may be updated.">
                            @csrf
                            <div>
                                <strong>Import a demo package</strong>
                                <div style="color:var(--jv-gray-500);font-size:.88rem;">Upload a JamVini JSON package from another install, staging site, or a commercial theme demo.</div>
                            </div>
                            <input type="file" name="package" class="form-input" accept="application/json,.json,.txt" required>
                            <button class="btn btn-primary">{{ jv_icon('upload-cloud', '', 16) }} Import Package</button>
                        </form>
                    </div>

                    @if(!empty($demos))
                        <div style="display:grid;gap:12px;">
                        @foreach($demos as $demo)
                            <div class="theme-demo-row">
                                <div>
                                    <strong>{{ $demo['name'] ?? str($demo['slug'] ?? 'default')->headline() }}</strong>
                                    <div style="color:var(--jv-gray-500);font-size:.88rem;">{{ $demo['description'] ?? 'Import pages, menus, and settings for this theme.' }}</div>
                                </div>
                                <form action="{{ route('admin.themes.demo.import', $theme['slug']) }}" method="POST" data-confirm="Import demo data for {{ $theme['name'] }}? Existing matching demo pages and menus may be updated.">
                                    @csrf
                                    <input type="hidden" name="demo" value="{{ $demo['slug'] ?? 'default' }}">
                                    <button class="btn btn-sm btn-primary">{{ jv_icon('download', '', 16) }} Import Demo</button>
                                </form>
                            </div>
                        @endforeach
                        </div>
                    @else
                        <div class="empty-state" style="padding:22px;">
                            <div class="empty-state-icon">{{ jv_icon('package-open', '', 34) }}</div>
                            <div class="empty-state-title">No bundled demo declared</div>
                            <div class="empty-state-desc">This theme can still import and export JamVini demo packages.</div>
                        </div>
                    @endif
                </div>
            </div>
        </section>

        @if(!empty($settings))
            <form action="{{ route('admin.themes.options.update', $theme['slug']) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @foreach($orderedGroups as $group)
                    <section class="theme-pro-panel" data-theme-panel="{{ str($group)->slug() }}">
                        <div class="card">
                            <div class="card-header"><h3 class="card-title">{{ $group }}</h3></div>
                            <div class="card-body theme-option-grid">
                                @foreach($groupedSettings[$group] as $key => $config)
                                    @php
                                        $type = $config['type'] ?? 'text';
                                        $label = $config['label'] ?? str($key)->headline();
                                        $value = $config['current_value'] ?? ($config['default'] ?? '');
                                        $wide = in_array($type, ['textarea', 'code'], true);
                                    @endphp
                                    <div class="form-group {{ $wide ? 'theme-option-wide' : '' }}">
                                        <label class="form-label">{{ $label }}</label>
                                        @if($type === 'color')
                                            <div style="display:flex;gap:10px;align-items:center;">
                                                <input type="color" value="{{ $value ?: '#000000' }}" onchange="this.nextElementSibling.value=this.value" style="width:48px;height:40px;border:0;background:transparent;">
                                                <input type="text" name="settings[{{ $key }}]" class="form-input" value="{{ $value }}">
                                            </div>
                                        @elseif($type === 'image')
                                            @if($value)
                                                <div style="margin-bottom:8px;"><img src="{{ str_starts_with($value, 'http') || str_starts_with($value, '/') ? $value : asset('storage/' . $value) }}" alt="" style="max-height:64px;border-radius:6px;border:1px solid var(--jv-gray-200);"></div>
                                            @endif
                                            <input type="file" name="files[{{ $key }}]" class="form-input" accept="image/*">
                                            <input type="hidden" name="settings[{{ $key }}]" value="{{ $value }}">
                                        @elseif(in_array($type, ['textarea', 'code'], true))
                                            <textarea name="settings[{{ $key }}]" class="form-textarea {{ $type === 'code' ? 'theme-code' : '' }}" rows="{{ $type === 'code' ? 12 : 5 }}">{{ $value }}</textarea>
                                        @elseif(in_array($type, ['boolean', 'toggle'], true))
                                            <input type="hidden" name="settings[{{ $key }}]" value="0">
                                            <label class="toggle-switch"><input type="checkbox" name="settings[{{ $key }}]" value="1" {{ (string) $value === '1' || $value === true ? 'checked' : '' }}><span class="toggle-slider"></span><span>{{ $label }}</span></label>
                                        @elseif($type === 'select')
                                            <select name="settings[{{ $key }}]" class="form-select">
                                                @foreach(($config['options'] ?? []) as $optionKey => $option)
                                                    @php
                                                        $optionValue = is_array($option) ? ($option['value'] ?? $optionKey) : $optionKey;
                                                        $optionLabel = is_array($option) ? ($option['label'] ?? $optionValue) : $option;
                                                    @endphp
                                                    <option value="{{ $optionValue }}" {{ (string) $value === (string) $optionValue ? 'selected' : '' }}>{{ $optionLabel }}</option>
                                                @endforeach
                                            </select>
                                        @else
                                            <input type="text" name="settings[{{ $key }}]" class="form-input" value="{{ $value }}">
                                        @endif
                                        @if(!empty($config['description']))<div class="form-hint">{{ $config['description'] }}</div>@endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </section>
                @endforeach

                <div style="display:flex;justify-content:flex-end;margin-top:16px;">
                    <button class="btn btn-primary btn-lg">{{ jv_icon('check-circle', '', 16) }} Save Theme Options</button>
                </div>
            </form>
        @else
            <div class="empty-state">
                <div class="empty-state-icon">{{ jv_icon('settings', '', 42) }}</div>
                <div class="empty-state-title">No options declared</div>
                <div class="empty-state-desc">This theme can add a settings schema in its theme.json file.</div>
            </div>
        @endif
    </main>
</div>
@endsection

@push('scripts')
<script>
document.querySelectorAll('[data-theme-tab]').forEach(tab => {
    tab.addEventListener('click', () => {
        const target = tab.dataset.themeTab;
        document.querySelectorAll('[data-theme-tab]').forEach(item => item.classList.toggle('active', item === tab));
        document.querySelectorAll('[data-theme-panel]').forEach(panel => panel.classList.toggle('active', panel.dataset.themePanel === target));
    });
});
</script>
@endpush
