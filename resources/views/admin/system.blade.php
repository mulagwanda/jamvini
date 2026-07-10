@extends('themes.default::layouts.admin')

@section('title', 'System — JamVini Hosting')
@section('breadcrumbs')<span class="current">System</span>@endsection

@section('content')
<div class="page-header">
    <h1 class="page-title">System Status</h1>
    <p class="page-subtitle">Server information and maintenance tools</p>
</div>

<div class="stats-grid">
    <div class="stat-card primary">
        <div class="stat-card-icon">{{ jv_icon('package') }}</div>
        <div class="stat-card-value">{{ $info['core_version']['version'] }}</div>
        <div class="stat-card-label">Core Version</div>
    </div>
    <div class="stat-card info">
        <div class="stat-card-icon">{{ jv_icon('terminal') }}</div>
        <div class="stat-card-value">{{ $info['php_version'] }}</div>
        <div class="stat-card-label">PHP Version</div>
    </div>
    <div class="stat-card success">
        <div class="stat-card-icon">{{ jv_icon('puzzle') }}</div>
        <div class="stat-card-value">{{ $info['active_plugins'] }}</div>
        <div class="stat-card-label">Active Plugins</div>
    </div>
    <div class="stat-card warning">
        <div class="stat-card-icon">{{ jv_icon('hard-drive') }}</div>
        <div class="stat-card-value">{{ $info['disk_used_percent'] }}%</div>
        <div class="stat-card-label">Disk Used</div>
    </div>
</div>

<div class="card">
    <div class="card-header"><h3 class="card-title">Production Health Checks</h3></div>
    <div class="card-body" style="padding: 0;">
        <table class="table" style="margin: 0;">
            <thead><tr><th>Check</th><th>Status</th><th>Details</th></tr></thead>
            <tbody>
                @foreach($checks as $check)
                    <tr>
                        <td style="font-weight: 600;">{{ $check['label'] }}</td>
                        <td>
                            @if($check['status'])
                                <span class="pill pill-ok">Passed</span>
                            @else
                                <span class="pill pill-bad">Needs Attention</span>
                            @endif
                        </td>
                        <td style="color: var(--jv-gray-600);">{{ $check['detail'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<div class="stats-grid">
    <div class="stat-card info">
        <div class="stat-card-icon">{{ jv_icon('server') }}</div>
        <div class="stat-card-value">{{ $info['environment'] }}</div>
        <div class="stat-card-label">Environment</div>
    </div>
    <div class="stat-card {{ $info['debug'] ? 'warning' : 'success' }}">
        <div class="stat-card-icon">{{ jv_icon($info['debug'] ? 'triangle-alert' : 'check-circle') }}</div>
        <div class="stat-card-value">{{ $info['debug'] ? 'On' : 'Off' }}</div>
        <div class="stat-card-label">Debug Mode</div>
    </div>
    <div class="stat-card info">
        <div class="stat-card-icon">{{ jv_icon('database') }}</div>
        <div class="stat-card-value">{{ $info['cache_driver'] }}</div>
        <div class="stat-card-label">Cache Driver</div>
    </div>
    <div class="stat-card {{ $info['storage_linked'] ? 'success' : 'warning' }}">
        <div class="stat-card-icon">{{ jv_icon($info['storage_linked'] ? 'link' : 'triangle-alert') }}</div>
        <div class="stat-card-value">{{ $info['storage_linked'] ? 'Linked' : 'Missing' }}</div>
        <div class="stat-card-label">Public Storage</div>
    </div>
</div>

<div class="card">
    <div class="card-header"><h3 class="card-title">Maintenance Tools</h3></div>
    <div class="card-body">
        <div class="btn-group">
            <form action="{{ route('admin.system.clear-cache') }}" method="POST" data-confirm="Clear application and configuration cache?" data-title="Clear Cache">
                @csrf
                <button class="btn btn-warning">{{ jv_icon('refresh-cw', '', 16) }} Clear Cache</button>
            </form>
            <form action="{{ route('admin.system.clear-views') }}" method="POST" data-confirm="Clear compiled view files?" data-title="Clear Views">
                @csrf
                <button class="btn btn-warning">{{ jv_icon('file', '', 16) }} Clear Views</button>
            </form>
            <form action="{{ route('admin.system.clear-routes') }}" method="POST" data-confirm="Clear route cache?" data-title="Clear Routes">
                @csrf
                <button class="btn btn-warning">{{ jv_icon('list-check', '', 16) }} Clear Routes</button>
            </form>
            <form action="{{ route('admin.system.storage-link') }}" method="POST" data-confirm="Create public storage link for uploads?" data-title="Create Storage Link">
                @csrf
                <button class="btn btn-outline-primary">{{ jv_icon('link', '', 16) }} Create Storage Link</button>
            </form>
            <form action="{{ route('admin.system.migrate') }}" method="POST"
                  data-confirm="Run database migrations? Make a backup first on production." data-title="Run Migrations">
                @csrf
                <button class="btn btn-primary">{{ jv_icon('database', '', 16) }} Run Migrations</button>
            </form>
        </div>
        <p style="margin-top: 12px; color: var(--jv-gray-500); font-size: .88rem;">
            On production, run migrations after a database backup and during a quiet maintenance window.
        </p>
    </div>
</div>

<div class="card">
    <div class="card-header"><h3 class="card-title">System Info</h3></div>
    <div class="card-body">
        <table class="table">
            <tr><td style="font-weight: 600;">PHP Version</td><td>{{ $info['php_version'] }}</td></tr>
            <tr><td style="font-weight: 600;">Laravel Version</td><td>{{ $info['laravel_version'] }}</td></tr>
            <tr><td style="font-weight: 600;">Core Version</td><td>{{ $info['core_version']['version'] }} (Build {{ $info['core_version']['build'] }})</td></tr>
            <tr><td style="font-weight: 600;">Database</td><td>{{ strtoupper($info['database_driver']) }} {{ $info['database'] }}</td></tr>
            <tr><td style="font-weight: 600;">App URL</td><td>{{ $info['app_url'] }}</td></tr>
            <tr><td style="font-weight: 600;">Timezone</td><td>{{ $info['timezone'] }}</td></tr>
            <tr><td style="font-weight: 600;">Disk Space</td><td>{{ $info['disk_free'] }} free of {{ $info['disk_total'] }} ({{ $info['disk_used_percent'] }}% used)</td></tr>
            <tr><td style="font-weight: 600;">Cache / Session / Queue</td><td>{{ $info['cache_driver'] }} / {{ $info['session_driver'] }} / {{ $info['queue_driver'] }}</td></tr>
            <tr><td style="font-weight: 600;">Mail Mailer</td><td>{{ $info['mail_mailer'] }}</td></tr>
            <tr><td style="font-weight: 600;">Plugins</td><td>{{ $info['active_plugins'] }} active / {{ $info['plugins_count'] }} installed</td></tr>
        </table>
    </div>
</div>
@endsection
