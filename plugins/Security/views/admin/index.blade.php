@extends('themes.default::layouts.admin')

@section('title', 'Security Shield')
@section('breadcrumbs')<span class="current">Security Shield</span>@endsection

@section('content')
<style>
.security-kpis { display:grid; grid-template-columns:repeat(auto-fit,minmax(170px,1fr)); gap:14px; margin-bottom:18px; }
.security-kpi { background:#fff; border:1px solid var(--jv-gray-200); border-radius:8px; padding:16px; }
.security-kpi strong { display:block; font-size:1.7rem; }
.security-grid { display:grid; grid-template-columns:minmax(0,1.2fr) minmax(340px,.8fr); gap:18px; align-items:start; }
.security-row { display:flex; justify-content:space-between; gap:12px; padding:10px 0; border-bottom:1px solid var(--jv-gray-100); }
@media(max-width:980px){ .security-grid{grid-template-columns:1fr;} }
</style>

<div class="page-header">
    <div>
        <h1 class="page-title">Security Shield</h1>
        <p class="page-subtitle">Firewall protection, IP rules, request audit history, and file integrity checks.</p>
    </div>
    <form action="{{ route('admin.security.scan') }}" method="POST">
        @csrf
        <button class="btn btn-primary">{{ jv_icon('scan-line', '', 16) }} Run File Scan</button>
    </form>
</div>

<div class="security-kpis">
    <div class="security-kpi"><span>High Risk Events</span><strong>{{ number_format($stats['high']) }}</strong></div>
    <div class="security-kpi"><span>Blocked Requests</span><strong>{{ number_format($stats['blocked']) }}</strong></div>
    <div class="security-kpi"><span>IP Rules</span><strong>{{ number_format($stats['rules']) }}</strong></div>
    <div class="security-kpi"><span>Critical Events</span><strong>{{ number_format($stats['critical']) }}</strong></div>
</div>

<div class="security-grid">
    <main style="display:grid;gap:18px;">
        <div class="card">
            <div class="card-header"><h3 class="card-title">{{ jv_icon('activity', '', 18) }} Recent Security Events</h3></div>
            <div class="card-body">
                @forelse($events as $event)
                    <div class="security-row">
                        <div>
                            <strong>{{ str($event->type)->headline() }}</strong>
                            <div style="color:var(--jv-gray-500);font-size:.86rem;">{{ $event->message }} · {{ $event->ip_address }} · {{ \Carbon\Carbon::parse($event->occurred_at)->diffForHumans() }}</div>
                        </div>
                        <span class="badge {{ in_array($event->severity, ['critical','high'], true) ? 'badge-danger' : 'badge-info' }}">{{ $event->severity }}</span>
                    </div>
                @empty
                    <div class="empty-state" style="padding:22px;">No security events logged yet.</div>
                @endforelse
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h3 class="card-title">{{ jv_icon('file-search', '', 18) }} File Scan Results</h3></div>
            <div class="card-body">
                @forelse($scanResults as $result)
                    <div class="security-row">
                        <div>
                            <strong>{{ $result->path }}</strong>
                            <div style="color:var(--jv-gray-500);font-size:.86rem;">{{ $result->message ?: 'No issue detected' }}</div>
                        </div>
                        <span class="badge {{ $result->status === 'ok' ? 'badge-success' : 'badge-warning' }}">{{ $result->status }}</span>
                    </div>
                @empty
                    <div class="empty-state" style="padding:22px;">No scans yet. Run a file scan when ready.</div>
                @endforelse
            </div>
        </div>
    </main>

    <aside style="display:grid;gap:18px;">
        <form action="{{ route('admin.security.settings') }}" method="POST" class="card">
            @csrf
            <div class="card-header"><h3 class="card-title">{{ jv_icon('settings', '', 18) }} Protection Settings</h3></div>
            <div class="card-body" style="display:grid;gap:14px;">
                <label class="toggle-switch"><input type="checkbox" name="firewall_enabled" value="1" {{ $settings['firewall_enabled'] === '1' ? 'checked' : '' }}><span class="toggle-slider"></span><span>Enable firewall</span></label>
                <label class="toggle-switch"><input type="checkbox" name="rate_limit_enabled" value="1" {{ $settings['rate_limit_enabled'] === '1' ? 'checked' : '' }}><span class="toggle-slider"></span><span>Enable rate limit</span></label>
                <div class="form-group"><label class="form-label">Requests per minute</label><input type="number" class="form-input" name="rate_limit_per_minute" value="{{ $settings['rate_limit_per_minute'] }}" min="30" max="5000"></div>
                <div class="form-group"><label class="form-label">Scan paths</label><textarea class="form-textarea" rows="3" name="scan_paths">{{ $settings['scan_paths'] }}</textarea></div>
            </div>
            <div class="card-footer" style="display:flex;justify-content:flex-end;"><button class="btn btn-primary">{{ jv_icon('save', '', 16) }} Save Security</button></div>
        </form>

        <div class="card">
            <div class="card-header"><h3 class="card-title">{{ jv_icon('ban', '', 18) }} IP Rules</h3></div>
            <div class="card-body" style="display:grid;gap:12px;">
                <form action="{{ route('admin.security.rules.store') }}" method="POST" style="display:grid;gap:12px;">
                    @csrf
                    <input class="form-input" name="ip_address" placeholder="IP address" required>
                    <select class="form-select" name="action"><option value="block">Block</option><option value="allow">Allow</option></select>
                    <input class="form-input" name="reason" placeholder="Reason">
                    <input type="datetime-local" class="form-input" name="expires_at">
                    <button class="btn btn-outline-primary">{{ jv_icon('plus', '', 16) }} Add Rule</button>
                </form>
                <div style="display:grid;gap:8px;margin-top:8px;">
                    @foreach($rules as $rule)
                        <div class="security-row">
                            <div><strong>{{ $rule->ip_address }}</strong><div style="font-size:.84rem;color:var(--jv-gray-500);">{{ $rule->reason }}</div></div>
                            <form action="{{ route('admin.security.rules.delete', $rule->id) }}" method="POST">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger">{{ jv_icon('trash-2', '', 14) }}</button>
                            </form>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </aside>
</div>
@endsection
