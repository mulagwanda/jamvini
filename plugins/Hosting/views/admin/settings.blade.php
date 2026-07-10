@extends('themes.default::layouts.admin')

@section('title', 'Hosting Automation')
@section('breadcrumbs')<span class="current">Hosting Automation</span>@endsection

@section('content')
<div class="page-header">
    <h1 class="page-title">Hosting Automation</h1>
    <p class="page-subtitle">Fallback provisioning rules for hosting services that do not have a specific server assignment.</p>
</div>

<form action="{{ route('admin.hosting.settings.save') }}" method="POST">
    @csrf
    <div class="dash-card" style="margin-bottom: 1.5rem;">
        <div class="dash-card-head"><h3>Fallback Provisioning Server</h3></div>
        <div class="form-group">
            <label class="form-label">Fallback Server</label>
            <select name="hosting_default_server" class="form-select" style="width: 350px;">
                <option value="">— None (manual provisioning) —</option>
                @foreach($servers as $srv)
                    <option value="{{ $srv->id }}" {{ $defaultServer == $srv->id ? 'selected' : '' }}>
                        {{ $srv->name }} ({{ ucfirst($srv->type) }}) — {{ $srv->current_accounts }}/{{ $srv->max_accounts ?: '∞' }}
                    </option>
                @endforeach
            </select>
            <div class="form-hint">JamVini first uses the server and package assigned on the service. This fallback is only used when no specific server is configured.</div>
        </div>
    </div>

    <div style="display: flex; gap: 12px; justify-content: flex-end;">
        <button type="submit" class="btn btn-primary btn-lg">💾 Save Settings</button>
    </div>
</form>
@endsection
