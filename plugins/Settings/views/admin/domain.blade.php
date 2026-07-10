@extends('themes.default::layouts.admin')

@section('title', 'Domain Settings')
@section('breadcrumbs')<span class="current">Domain Settings</span>@endsection

@section('content')
<div class="page-header"><h1 class="page-title">Domain Settings</h1><p class="page-subtitle">Default nameservers, registrar, and domain configuration</p></div>

<form action="{{ route('admin.settings.domain.update') }}" method="POST">
    @csrf
    <div class="card">
        <div class="card-header"><h3 class="card-title">{{ jv_icon('globe', '', 20) }} Default Nameservers</h3></div>
        <div class="card-body">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                <div class="form-group"><label class="form-label">Nameserver 1 *</label><input type="text" name="settings[domain_default_ns1]" class="form-input" value="{{ old('settings.domain_default_ns1', \App\Models\Setting::get('domain_default_ns1', 'ns1.jamvini.co.tz')) }}" required></div>
                <div class="form-group"><label class="form-label">Nameserver 2 *</label><input type="text" name="settings[domain_default_ns2]" class="form-input" value="{{ old('settings.domain_default_ns2', \App\Models\Setting::get('domain_default_ns2', 'ns2.jamvini.co.tz')) }}" required></div>
                <div class="form-group"><label class="form-label">Nameserver 3</label><input type="text" name="settings[domain_default_ns3]" class="form-input" value="{{ old('settings.domain_default_ns3', \App\Models\Setting::get('domain_default_ns3')) }}"></div>
                <div class="form-group"><label class="form-label">Nameserver 4</label><input type="text" name="settings[domain_default_ns4]" class="form-input" value="{{ old('settings.domain_default_ns4', \App\Models\Setting::get('domain_default_ns4')) }}"></div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header"><h3 class="card-title">{{ jv_icon('clipboard-list', '', 20) }} Registration Settings</h3></div>
        <div class="card-body">
        <div class="form-group">
            <label class="form-label">Default Registrar</label>
            <select name="settings[domain_default_registrar]" class="form-select" style="width: 250px;">
                <option value="">— None (manual handling) —</option>
                @php $registrars = \App\Core\Registries\RegistrarRegistry::all(); @endphp
                @foreach($registrars as $slug => $config)
                    <option value="{{ $slug }}" {{ \App\Models\Setting::get('domain_default_registrar') === $slug ? 'selected' : '' }}>
                        {{ $config['name'] }}
                    </option>
                @endforeach
            </select>
            <div class="form-hint">Used as fallback when a TLD doesn't have a specific registrar assigned.</div>
        </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                <div class="form-group"><label class="form-label">Grace Period (Days)</label><input type="number" name="settings[domain_grace_period_days]" class="form-input" value="{{ \App\Models\Setting::get('domain_grace_period_days', '30') }}" style="width: 150px;"></div>
                <div class="form-group"><label class="form-label">Redemption Period (Days)</label><input type="number" name="settings[domain_redemption_period_days]" class="form-input" value="{{ \App\Models\Setting::get('domain_redemption_period_days', '30') }}" style="width: 150px;"></div>
            </div>
            <div style="display: flex; gap: 24px; margin-top: 16px;">
                <label class="toggle-switch">
                    <input type="hidden" name="settings[domain_auto_renew]" value="0">
                    <input type="checkbox" name="settings[domain_auto_renew]" value="1" {{ \App\Models\Setting::get('domain_auto_renew') === '1' ? 'checked' : '' }}>
                    <span class="toggle-slider"></span><span>Auto Renew by Default</span>
                </label>
                <label class="toggle-switch">
                    <input type="hidden" name="settings[domain_id_protection_default]" value="0">
                    <input type="checkbox" name="settings[domain_id_protection_default]" value="1" {{ \App\Models\Setting::get('domain_id_protection_default') === '1' ? 'checked' : '' }}>
                    <span class="toggle-slider"></span><span>ID Protection by Default</span>
                </label>
            </div>
        </div>
    </div>

    <div class="dash-card" style="margin-bottom: 1.5rem;">
    <div class="dash-card-head"><h3>{{ jv_icon('plug', '', 20) }} Domain Registrars</h3></div>
    <div class="card-body" style="padding: 0;">
        @php $registrars = \App\Core\Registries\RegistrarRegistry::all(); @endphp
        @if(count($registrars) > 0)
        <table class="table" style="margin: 0;">
            <thead><tr><th>Registrar</th><th>TLDs</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
                @foreach($registrars as $slug => $config)
                <tr>
                    <td><strong>{{ jv_icon($config['icon'] ?? 'plug', '', 16) }} {{ $config['name'] }}</strong></td>
                    <td>{{ implode(', ', $config['tlds'] ?? []) ?: 'All' }}</td>
                    <td>
                        @if($config['is_configured'] ?? false)
                            <span class="pill pill-ok">Configured</span>
                        @else
                            <span class="pill pill-warn">Not Configured</span>
                        @endif
                    </td>
                    <td>
                        @if(!empty($config['settings_route']))
                            <a href="{{ route($config['settings_route']) }}" class="btn btn-sm btn-outline-primary">{{ jv_icon('settings', '', 16) }} Configure</a>
                        @else
                            <span style="color: var(--jv-gray-400);">—</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
        <div style="padding: 24px; text-align: center; color: var(--jv-gray-500);">
            <p>No registrar plugins installed.</p>
            <p style="font-size: 0.85rem;">Install registrar plugins from the <a href="{{ route('admin.plugins.index') }}">Plugins page</a>.</p>
        </div>
        @endif
    </div>
</div>

    <div style="display: flex; gap: 12px; justify-content: flex-end; margin-top: 16px;">
        <button type="submit" class="btn btn-primary btn-lg">{{ jv_icon('check-circle', '', 16) }} Save Domain Settings</button>
    </div>
</form>
@endsection
