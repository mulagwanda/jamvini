@extends('themes.default::layouts.admin')

@section('title', 'Plugins - JamVini Hosting')

@section('breadcrumbs')
    <span class="current">Plugins</span>
@endsection

@section('content')
@php
    $updates = app(\App\Core\UpdateManager::class)->getUpdatesSummary();
    $typeIcons = [
        'payment_gateway' => 'credit-card',
        'domain_registrar' => 'globe',
        'sms_provider' => 'message-circle',
        'module' => 'puzzle',
    ];
@endphp

<style>
.manager-header { display:grid; gap:14px; }
.manager-actions { display:flex; gap:8px; flex-wrap:wrap; justify-content:flex-start; }
.manager-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(280px,1fr)); gap:16px; }
.manager-card { background:#fff; border:1px solid var(--jv-gray-200); border-radius:8px; padding:16px; display:flex; flex-direction:column; gap:14px; min-height:100%; box-shadow:0 1px 3px rgba(15,23,42,.04); }
.manager-card-head { display:flex; align-items:flex-start; gap:12px; }
.manager-icon { width:44px; height:44px; border-radius:8px; display:grid; place-items:center; background:#eef2ff; color:var(--jv-primary); flex-shrink:0; }
.manager-title { margin:0; font-size:1rem; }
.manager-desc { margin:4px 0 0; color:var(--jv-gray-600); font-size:.86rem; line-height:1.45; }
.manager-meta { display:flex; gap:6px; flex-wrap:wrap; align-items:center; }
.manager-actions-row { margin-top:auto; display:flex; gap:8px; flex-wrap:wrap; justify-content:flex-start; }
.marketplace-band { display:flex; align-items:center; justify-content:space-between; gap:14px; flex-wrap:wrap; padding:18px; border:1px dashed var(--jv-gray-300); border-radius:8px; background:#f8fafc; }
.ajax-busy { opacity:.7; pointer-events:none; }
</style>

<div class="page-header manager-header">
    <div>
        <h1 class="page-title">Plugins</h1>
        <p class="page-subtitle">Extend JamVini with billing, registrar, automation, content, and support modules.</p>
    </div>
    <div class="manager-actions">
        <button class="btn btn-sm btn-outline-primary" type="button" onclick="checkUpdates(this)">{{ jv_icon('refresh-cw', '', 16) }} Check for Updates</button>
        <button class="btn btn-sm btn-primary" type="button" onclick="document.getElementById('uploadPlugin').click()">{{ jv_icon('upload', '', 16) }} Upload Plugin</button>
        <form id="uploadForm" class="jv-ajax-form" action="{{ route('admin.plugins.upload') }}" method="POST" enctype="multipart/form-data" style="display:none;">
            @csrf
            <input type="file" id="uploadPlugin" name="plugin_zip" accept=".zip" onchange="this.form.requestSubmit()">
        </form>
    </div>
</div>

@if($updates['total_updates'] > 0)
<div class="card" id="updatesPanel" style="border-left:4px solid var(--jv-primary);">
    <div class="card-header">
        <h3 class="card-title">{{ jv_icon('triangle-alert', '', 20) }} Available Updates</h3>
    </div>
    <div class="card-body" style="display:grid;gap:10px;">
        @if($updates['core'])
            <div class="marketplace-band">
                <div><strong>Core System</strong> <span class="badge badge-info">v{{ config('app.version', '1.0.0') }} to {{ $updates['core']['version'] }}</span></div>
                <button class="btn btn-sm btn-primary" disabled>Core update installer pending</button>
            </div>
        @endif
        @foreach($updates['plugins'] as $slug => $update)
            <div class="marketplace-band">
                <div><strong>{{ $update['name'] ?? $slug }}</strong> <span class="badge badge-info">v{{ $update['current'] ?? '?' }} to {{ $update['version'] }}</span></div>
                <form class="jv-ajax-form" action="{{ route('admin.plugins.update', ['slug' => $slug]) }}" method="POST">
                    @csrf
                    <button class="btn btn-sm btn-primary">{{ jv_icon('download', '', 16) }} Update</button>
                </form>
            </div>
        @endforeach
    </div>
</div>
@endif

<div class="card">
    <div class="card-header">
        <h3 class="card-title">{{ jv_icon('puzzle', '', 20) }} Installed Plugins</h3>
        <span style="font-size:.85rem;color:var(--jv-gray-500);">{{ $installedPlugins->count() }} installed</span>
    </div>
    <div class="card-body">
        @if($installedPlugins->count() > 0)
            <div class="manager-grid">
                @foreach($installedPlugins as $plugin)
                    @php
                        $manifest = $availablePlugins[$plugin->slug] ?? [];
                        $menu = data_get($manifest, 'menu.admin', []);
                        $icon = $manifest['icon'] ?? $menu['icon'] ?? $typeIcons[$plugin->type] ?? 'puzzle';
                    @endphp
                    <div class="manager-card" data-manager-card="{{ $plugin->slug }}">
                        <div class="manager-card-head">
                            <div class="manager-icon">{{ jv_icon($icon, '', 22) }}</div>
                            <div>
                                <h3 class="manager-title">{{ $plugin->name }}</h3>
                                @if($plugin->description)
                                    <p class="manager-desc">{{ $plugin->description }}</p>
                                @endif
                            </div>
                        </div>
                        <div class="manager-meta">
                            <span class="badge badge-gray">v{{ $plugin->version }}</span>
                            <span class="badge badge-info">{{ ucwords(str_replace('_', ' ', $plugin->type)) }}</span>
                            <span class="badge badge-{{ $plugin->is_active ? 'success' : 'gray' }} badge-with-dot">{{ $plugin->is_active ? 'Active' : 'Inactive' }}</span>
                            @if($plugin->is_system)<span class="badge badge-gray">{{ jv_icon('lock', '', 13) }} System</span>@endif
                        </div>
                        <div style="font-size:.78rem;color:var(--jv-gray-500);">by {{ $plugin->author ?: 'Unknown' }}</div>
                        <div class="manager-actions-row">
                            <a href="{{ route('admin.plugins.editor', $plugin->slug) }}" class="btn btn-sm btn-outline-primary">{{ jv_icon('code-2', '', 16) }} Edit</a>
                            @if($plugin->is_active)
                                @if(!$plugin->is_system)
                                    <form class="jv-ajax-form" action="{{ route('admin.plugins.deactivate') }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="slug" value="{{ $plugin->slug }}">
                                        <button class="btn btn-sm btn-warning">{{ jv_icon('pause-circle', '', 16) }} Deactivate</button>
                                    </form>
                                @else
                                    <button class="btn btn-sm btn-gray" disabled>{{ jv_icon('lock', '', 16) }} Protected</button>
                                @endif
                            @else
                                <form class="jv-ajax-form" action="{{ route('admin.plugins.activate') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="slug" value="{{ $plugin->slug }}">
                                    <button class="btn btn-sm btn-success">{{ jv_icon('play', '', 16) }} Activate</button>
                                </form>
                            @endif
                            @if(!$plugin->is_system && !$plugin->is_active)
                                <form class="jv-ajax-form" action="{{ route('admin.plugins.uninstall') }}" method="POST" data-confirm="Uninstall '{{ $plugin->name }}'? This cannot be undone.">
                                    @csrf
                                    <input type="hidden" name="slug" value="{{ $plugin->slug }}">
                                    <button class="btn btn-sm btn-outline-danger">{{ jv_icon('trash-2', '', 16) }}</button>
                                </form>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="empty-state">
                <div class="empty-state-icon">{{ jv_icon('puzzle', '', 42) }}</div>
                <div class="empty-state-title">No plugins installed</div>
                <div class="empty-state-desc">Install plugins from this server or upload a JamVini plugin ZIP.</div>
            </div>
        @endif
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">{{ jv_icon('package', '', 20) }} Local Plugin Directory</h3>
        <span style="font-size:.85rem;color:var(--jv-gray-500);">Found in <code>/plugins</code></span>
    </div>
    <div class="card-body">
        @php $installable = collect($availablePlugins)->reject(fn ($plugin) => $plugin['installed']); @endphp
        @if($installable->count())
            <div class="manager-grid">
                @foreach($installable as $slug => $plugin)
                    @php
                        $icon = $plugin['icon'] ?? data_get($plugin, 'menu.admin.icon') ?? $typeIcons[$plugin['type'] ?? 'module'] ?? 'package';
                        $depsMet = collect($plugin['dependencies'])->every(fn ($dep) => \App\Models\Plugin::where('slug', $dep)->where('is_active', true)->exists());
                    @endphp
                    <div class="manager-card">
                        <div class="manager-card-head">
                            <div class="manager-icon">{{ jv_icon($icon, '', 22) }}</div>
                            <div>
                                <h3 class="manager-title">{{ $plugin['name'] }}</h3>
                                @if($plugin['description'])<p class="manager-desc">{{ $plugin['description'] }}</p>@endif
                            </div>
                        </div>
                        <div class="manager-meta">
                            <span class="badge badge-gray">v{{ $plugin['version'] }}</span>
                            <span class="badge badge-info">{{ ucwords(str_replace('_', ' ', $plugin['type'])) }}</span>
                            @foreach($plugin['dependencies'] as $dep)
                                <span class="badge {{ \App\Models\Plugin::where('slug', $dep)->where('is_active', true)->exists() ? 'badge-success' : 'badge-danger' }}">{{ $dep }}</span>
                            @endforeach
                        </div>
                        <div style="font-size:.78rem;color:var(--jv-gray-500);">by {{ $plugin['author'] }}</div>
                        <div class="manager-actions-row">
                            @if($depsMet)
                                <form class="jv-ajax-form" action="{{ route('admin.plugins.install') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="slug" value="{{ $slug }}">
                                    <button class="btn btn-sm btn-primary">{{ jv_icon('package-plus', '', 16) }} Install</button>
                                </form>
                            @else
                                <button class="btn btn-sm btn-gray" disabled>{{ jv_icon('lock', '', 16) }} Dependencies Required</button>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div style="color:var(--jv-gray-500);font-size:.9rem;">All local plugins are already installed.</div>
        @endif
    </div>
</div>

<div class="marketplace-band">
    <div style="display:flex;gap:12px;align-items:center;">
        <div class="manager-icon">{{ jv_icon('store', '', 22) }}</div>
        <div>
            <strong>JamVini.org Marketplace</strong>
            <div style="color:var(--jv-gray-500);font-size:.88rem;">Browse official plugins such as WordPress automation, VPS modules, payment gateways, and registrar connectors when marketplace sync is enabled.</div>
        </div>
    </div>
    <button class="btn btn-sm btn-outline-primary" disabled>{{ jv_icon('external-link', '', 16) }} Coming Soon</button>
</div>
@endsection

@push('scripts')
<script>
function managerToast(title, message, type = 'success') {
    if (window.JamViniAdmin?.showToast) {
        window.JamViniAdmin.showToast(title, message, type);
        return;
    }
    alert(message || title);
}

async function submitManagerForm(form) {
    if (form.dataset.confirm && !confirm(form.dataset.confirm)) return;

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
        managerToast('Done', data.message, 'success');
        setTimeout(() => window.location.reload(), 650);
    } catch (error) {
        managerToast('Action failed', error.message, 'error');
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
        submitManagerForm(form);
    });
});

async function checkUpdates(button) {
    const original = button.innerHTML;
    button.disabled = true;
    button.innerHTML = 'Checking...';
    try {
        const response = await fetch(window.location.href, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
        if (!response.ok) throw new Error('Could not check for updates.');
        managerToast('Update check complete', 'Plugin update information has been refreshed.', 'info');
        setTimeout(() => window.location.reload(), 650);
    } catch (error) {
        managerToast('Update check failed', error.message, 'error');
        button.disabled = false;
        button.innerHTML = original;
    }
}
</script>
@endpush
