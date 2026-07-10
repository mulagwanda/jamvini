@extends('themes.default::layouts.admin')

@section('title', 'Themes')
@section('breadcrumbs')<span class="current">Themes</span>@endsection

@section('content')
<style>
.manager-header { display:grid; gap:14px; }
.manager-actions { display:flex; gap:8px; flex-wrap:wrap; justify-content:flex-start; }
.theme-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(290px,1fr)); gap:18px; }
.theme-card { background:#fff; border:1px solid var(--jv-gray-200); border-radius:8px; overflow:hidden; display:flex; flex-direction:column; min-height:100%; box-shadow:0 1px 3px rgba(15,23,42,.04); }
.theme-preview { aspect-ratio:16/9; background:linear-gradient(135deg,#0f172a,#0ea5e9 55%,#f59e0b); display:grid; place-items:center; color:#fff; font-weight:800; font-size:2rem; }
.theme-preview img { width:100%; height:100%; object-fit:cover; display:block; }
.theme-body { padding:16px; display:grid; gap:10px; flex:1; }
.theme-meta, .theme-supports { display:flex; gap:6px; flex-wrap:wrap; align-items:center; }
.theme-actions { padding:14px 16px; border-top:1px solid var(--jv-gray-200); display:flex; gap:8px; justify-content:flex-start; flex-wrap:wrap; }
.theme-area-list { display:grid; gap:6px; font-size:.82rem; color:var(--jv-gray-600); }
.theme-area-item { display:flex; align-items:center; justify-content:space-between; gap:8px; padding:7px 9px; border:1px solid var(--jv-gray-200); border-radius:6px; background:var(--jv-gray-50); }
.theme-requirements { display:grid; gap:6px; }
.theme-requirement { display:flex; align-items:center; justify-content:space-between; gap:8px; font-size:.78rem; padding:6px 8px; border-radius:6px; background:#fff; border:1px solid var(--jv-gray-200); }
.marketplace-band { display:flex; align-items:center; justify-content:space-between; gap:14px; flex-wrap:wrap; padding:18px; border:1px dashed var(--jv-gray-300); border-radius:8px; background:#f8fafc; margin-top:18px; }
.manager-icon { width:44px; height:44px; border-radius:8px; display:grid; place-items:center; background:#eef2ff; color:var(--jv-primary); flex-shrink:0; }
.ajax-busy { opacity:.7; pointer-events:none; }
</style>

<div class="page-header manager-header">
    <div>
        <h1 class="page-title">Themes</h1>
        <p class="page-subtitle">Install, preview, and assign themes for the website, client portal, and admin panel.</p>
    </div>
    <div class="manager-actions">
        <button type="button" class="btn btn-sm btn-outline-primary" onclick="checkThemeUpdates(this)">{{ jv_icon('refresh-cw', '', 16) }} Check for Updates</button>
        <button type="button" class="btn btn-sm btn-primary" onclick="document.getElementById('uploadTheme').click()">{{ jv_icon('upload', '', 16) }} Upload Theme</button>
        <form class="jv-ajax-form" action="{{ route('admin.themes.upload') }}" method="POST" enctype="multipart/form-data" style="display:none;">
            @csrf
            <input type="file" id="uploadTheme" name="theme_zip" accept=".zip" onchange="this.form.requestSubmit()">
        </form>
    </div>
</div>

<div class="card" style="border-left:4px solid var(--jv-primary);">
    <div class="card-body" style="display:flex;gap:12px;flex-wrap:wrap;align-items:center;justify-content:space-between;">
        <div>
            <strong>Active Theme Slots</strong>
            <div style="color:var(--jv-gray-500);font-size:.88rem;">Each JamVini surface can have its own theme.</div>
        </div>
        <div style="display:flex;gap:8px;flex-wrap:wrap;">
            <span class="badge badge-info">Website: {{ $activeThemes['public'] }}</span>
            <span class="badge badge-info">Client: {{ $activeThemes['client'] }}</span>
            <span class="badge badge-info">Admin: {{ $activeThemes['admin'] }}</span>
        </div>
    </div>
</div>

@if(count($themes))
    <div class="theme-grid">
        @foreach($themes as $theme)
            <div class="theme-card">
                <div class="theme-preview">
                    @if($theme['screenshot'])
                        <img src="{{ $theme['screenshot'] }}" alt="{{ $theme['name'] }} screenshot">
                    @else
                        {{ strtoupper(substr($theme['name'], 0, 2)) }}
                    @endif
                </div>
                <div class="theme-body">
                    <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:10px;">
                        <div>
                            <h3 style="margin:0;font-size:1.05rem;">{{ $theme['name'] }}</h3>
                            <div style="font-size:.8rem;color:var(--jv-gray-500);">by {{ $theme['author'] }}</div>
                        </div>
                        @if($theme['is_active'])
                            <span class="badge badge-success badge-with-dot">Active</span>
                        @elseif($theme['is_system'])
                            <span class="badge badge-gray">Default</span>
                        @endif
                    </div>

                    @if($theme['description'])
                        <p style="margin:0;color:var(--jv-gray-600);font-size:.9rem;line-height:1.45;">{{ $theme['description'] }}</p>
                    @endif

                    <div class="theme-meta">
                        <span class="badge badge-gray">v{{ $theme['version'] }}</span>
                        <code style="font-size:.75rem;">{{ $theme['folder'] }}</code>
                    </div>

                    <div class="theme-area-list">
                        @foreach(['public' => 'Website', 'client' => 'Client Portal', 'admin' => 'Admin Panel'] as $area => $label)
                            <div class="theme-area-item">
                                <span>{{ $label }}</span>
                                @if(in_array($area, $theme['active_areas'], true))
                                    <span class="badge badge-success">Active</span>
                                @elseif($theme["can_activate_{$area}"])
                                    <span class="badge badge-info">Available</span>
                                @elseif($theme["supports_{$area}"] && !$theme['requirements_met'])
                                    <span class="badge badge-warning">Needs plugins</span>
                                @elseif($theme["supports_{$area}"])
                                    <span class="badge badge-gray">Supported</span>
                                @else
                                    <span class="badge badge-gray">Unsupported</span>
                                @endif
                            </div>
                        @endforeach
                    </div>

                    @if(!empty($theme['supports']))
                        <div class="theme-supports">
                            @foreach($theme['supports'] as $support)
                                <span class="badge badge-info">{{ str_replace('-', ' ', $support) }}</span>
                            @endforeach
                        </div>
                    @endif

                    @if(!empty($theme['required_plugins']))
                        <div class="theme-requirements">
                            <strong style="font-size:.82rem;">Required Plugins</strong>
                            @foreach($theme['required_plugins'] as $plugin)
                                <div class="theme-requirement">
                                    <span>{{ $plugin['name'] }}</span>
                                    @if($plugin['active'])
                                        <span class="badge badge-success">Active</span>
                                    @elseif($plugin['installed'])
                                        <span class="badge badge-warning">Installed, inactive</span>
                                    @else
                                        <span class="badge badge-danger">Missing</span>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif

                    @if(!$theme['requirements_met'])
                        <div class="alert alert-warning" style="margin:0;padding:10px 12px;">Required plugins must be installed and active before this theme can be used.</div>
                    @endif
                </div>
                <div class="theme-actions">
                    <a href="{{ url('/') }}" target="_blank" class="btn btn-sm btn-outline-primary">{{ jv_icon('eye', '', 16) }} Preview</a>
                    <a href="{{ route('admin.themes.options', $theme['slug']) }}" class="btn btn-sm btn-outline-primary">{{ jv_icon('settings', '', 16) }} Options</a>
                    @foreach(['public' => 'Website', 'client' => 'Client', 'admin' => 'Admin'] as $area => $label)
                        <form class="jv-ajax-form" action="{{ route('admin.themes.activate') }}" method="POST">
                            @csrf
                            <input type="hidden" name="slug" value="{{ $theme['slug'] }}">
                            <input type="hidden" name="area" value="{{ $area }}">
                            <button class="btn btn-sm {{ $area === 'public' ? 'btn-primary' : 'btn-outline-primary' }}"
                                {{ $theme["can_activate_{$area}"] && !in_array($area, $theme['active_areas'], true) ? '' : 'disabled' }}>
                                Use for {{ $label }}
                            </button>
                        </form>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>
@else
    <div class="empty-state">
        <div class="empty-state-icon">{{ jv_icon('palette', '', 42) }}</div>
        <div class="empty-state-title">No themes found</div>
        <div class="empty-state-desc">Upload a JamVini theme ZIP or add one to the themes directory.</div>
    </div>
@endif

<div class="marketplace-band">
    <div style="display:flex;gap:12px;align-items:center;">
        <div class="manager-icon">{{ jv_icon('store', '', 22) }}</div>
        <div>
            <strong>JamVini.org Theme Gallery</strong>
            <div style="color:var(--jv-gray-500);font-size:.88rem;">Official public, client, and admin themes will appear here when remote marketplace sync is switched on.</div>
        </div>
    </div>
    <button class="btn btn-sm btn-outline-primary" disabled>{{ jv_icon('external-link', '', 16) }} Coming Soon</button>
</div>
@endsection

@push('scripts')
<script>
function themeToast(title, message, type = 'success') {
    if (window.JamViniAdmin?.showToast) {
        window.JamViniAdmin.showToast(title, message, type);
        return;
    }
    alert(message || title);
}

async function submitThemeForm(form) {
    const button = form.querySelector('button');
    const original = button ? button.innerHTML : '';
    form.classList.add('ajax-busy');
    if (button) {
        button.disabled = true;
        button.innerHTML = 'Working...';
    }

    try {
        const response = await fetch(form.action, {
            method: form.method || 'POST',
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            body: new FormData(form),
        });
        const data = await response.json();
        if (!response.ok || !data.success) throw new Error(data.message || 'Action failed.');
        themeToast('Done', data.message, 'success');
        setTimeout(() => window.location.reload(), 650);
    } catch (error) {
        themeToast('Action failed', error.message, 'error');
        form.classList.remove('ajax-busy');
        if (button) {
            button.disabled = false;
            button.innerHTML = original;
        }
    }
}

document.querySelectorAll('.jv-ajax-form').forEach(form => {
    form.addEventListener('submit', event => {
        event.preventDefault();
        submitThemeForm(form);
    });
});

async function checkThemeUpdates(button) {
    const original = button.innerHTML;
    button.disabled = true;
    button.innerHTML = 'Checking...';
    try {
        const response = await fetch(window.location.href, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
        if (!response.ok) throw new Error('Could not check for theme updates.');
        themeToast('Update check complete', 'Theme information has been refreshed.', 'info');
        setTimeout(() => window.location.reload(), 650);
    } catch (error) {
        themeToast('Update check failed', error.message, 'error');
        button.disabled = false;
        button.innerHTML = original;
    }
}
</script>
@endpush
