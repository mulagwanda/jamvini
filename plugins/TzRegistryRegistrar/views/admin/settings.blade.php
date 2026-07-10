@extends('themes.default::layouts.admin')

@section('title', 'tzNIC Registrar')
@section('breadcrumbs')<span class="current">tzNIC Registrar</span>@endsection

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">tzNIC FRED Registrar</h1>
        <p class="page-subtitle">Automate .tz availability, registration, transfer, renewal, nameservers, lock, and sync.</p>
    </div>
    <div style="display:flex;gap:8px;flex-wrap:wrap;">
        <button type="button" class="btn btn-outline-primary" onclick="testTznic()" id="testTznicBtn">{{ jv_icon('plug', '', 16) }} Test Connection</button>
        <form action="{{ route('admin.tznic.sync-domains') }}" method="POST">@csrf <button class="btn btn-outline-primary">{{ jv_icon('refresh-cw', '', 16) }} Sync Domains</button></form>
        <form action="{{ route('admin.tznic.sync-pricing') }}" method="POST">@csrf <button class="btn btn-outline-primary">{{ jv_icon('dollar-sign', '', 16) }} Sync Pricing</button></form>
    </div>
</div>

<form action="{{ route('admin.tznic.settings.save') }}" method="POST">
    @csrf

    <div class="grid-2">
        <div class="dash-card">
            <div class="dash-card-head"><h3>{{ jv_icon('lock', '', 20) }} EPP Connection</h3></div>
            <div class="form-group">
                <label class="toggle-switch">
                    <input type="checkbox" name="tznic_enabled" value="1" {{ $settings['enabled'] === '1' ? 'checked' : '' }}>
                    <span class="toggle-slider"></span><span>Enable tzNIC automation</span>
                </label>
            </div>
            <div style="display:grid;grid-template-columns:1fr 120px;gap:14px;">
                <div class="form-group">
                    <label class="form-label">EPP Host</label>
                    <input class="form-input" name="tznic_host" value="{{ old('tznic_host', $settings['host']) }}" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Port</label>
                    <input class="form-input" type="number" name="tznic_port" value="{{ old('tznic_port', $settings['port']) }}" required>
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Registrar Username</label>
                <input class="form-input" name="tznic_username" value="{{ old('tznic_username', $settings['username']) }}" required>
            </div>
            <div class="form-group">
                <label class="form-label">Registrar Password</label>
                <input class="form-input" type="password" name="tznic_password" placeholder="Leave blank to keep current password">
            </div>
            <div class="form-hint">Credentials are stored in JamVini settings. Restrict admin access to trusted staff only.</div>
        </div>

        <div class="dash-card">
            <div class="dash-card-head"><h3>{{ jv_icon('file', '', 20) }} Certificates</h3></div>
            <div class="form-group">
                <label class="form-label">Client Certificate Path</label>
                <input class="form-input" name="tznic_certificate_path" value="{{ old('tznic_certificate_path', $settings['certificate_path']) }}" placeholder="/secure/path/tznic-client.pem" required>
                <div class="form-hint">Use a server-side PEM path outside the public web root.</div>
            </div>
            <div class="form-group">
                <label class="form-label">Private Key Path</label>
                <input class="form-input" name="tznic_private_key_path" value="{{ old('tznic_private_key_path', $settings['private_key_path']) }}" placeholder="Optional if bundled with certificate">
            </div>
            <div class="form-group">
                <label class="form-label">Private Key Passphrase</label>
                <input class="form-input" type="password" name="tznic_private_key_passphrase" placeholder="Leave blank to keep current passphrase">
            </div>
            <label class="toggle-switch">
                <input type="checkbox" name="tznic_verify_peer" value="1" {{ $settings['verify_peer'] === '1' ? 'checked' : '' }}>
                <span class="toggle-slider"></span><span>Verify registry TLS certificate</span>
            </label>
        </div>
    </div>

    <div class="grid-2">
        <div class="dash-card">
            <div class="dash-card-head"><h3>{{ jv_icon('settings', '', 20) }} Automation</h3></div>
            <div style="display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:14px;">
                <div class="form-group">
                    <label class="form-label">Timeout</label>
                    <input class="form-input" type="number" name="tznic_timeout" value="{{ old('tznic_timeout', $settings['timeout']) }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Rate Limit</label>
                    <input class="form-input" type="number" step="0.1" name="tznic_rate_limit_seconds" value="{{ old('tznic_rate_limit_seconds', $settings['rate_limit_seconds']) }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Check Cache</label>
                    <input class="form-input" type="number" name="tznic_availability_cache_seconds" value="{{ old('tznic_availability_cache_seconds', $settings['availability_cache_seconds']) }}">
                </div>
            </div>
            <div style="display:grid;gap:10px;">
                <label class="toggle-switch"><input type="checkbox" name="tznic_domain_sync_enabled" value="1" {{ $settings['domain_sync_enabled'] === '1' ? 'checked' : '' }}><span class="toggle-slider"></span><span>Enable daily domain sync</span></label>
                <label class="toggle-switch"><input type="checkbox" name="tznic_pricing_sync_enabled" value="1" {{ $settings['pricing_sync_enabled'] === '1' ? 'checked' : '' }}><span class="toggle-slider"></span><span>Enable weekly TLD pricing sync</span></label>
                <label class="toggle-switch"><input type="checkbox" name="tznic_log_xml" value="1" {{ $settings['log_xml'] === '1' ? 'checked' : '' }}><span class="toggle-slider"></span><span>Log sanitized XML for debugging</span></label>
            </div>
        </div>

        <div class="dash-card">
            <div class="dash-card-head"><h3>{{ jv_icon('dollar-sign', '', 20) }} Pricing Sync</h3></div>
            <div class="form-group">
                <label class="form-label">Pricing JSON</label>
                <textarea class="form-textarea" name="tznic_pricing_json" rows="8" placeholder='[{"tld":".co.tz","years":1,"register":25000,"renewal":25000,"transfer":25000}]'>{{ old('tznic_pricing_json', $settings['pricing_json']) }}</textarea>
                <div class="form-hint">Use this until tzNIC provides a live pricing feed for your account.</div>
            </div>
        </div>
    </div>

    <div class="dash-card">
        <div class="dash-card-head">
            <h3>{{ jv_icon('globe', '', 20) }} Supported .tz TLDs</h3>
        </div>
        <div style="display:flex;flex-wrap:wrap;gap:8px;">
            @foreach(['.tz', '.co.tz', '.or.tz', '.go.tz', '.ac.tz', '.ne.tz', '.sc.tz', '.me.tz', '.hotel.tz', '.mobi.tz', '.info.tz', '.tv.tz'] as $tld)
                <span class="pill pill-info">{{ $tld }}</span>
            @endforeach
        </div>
    </div>

    <div class="dash-card" style="padding:0;overflow:hidden;">
        <div class="dash-card-head" style="padding:1.25rem 1.25rem 0;"><h3>{{ jv_icon('activity', '', 20) }} Recent Registrar Operations</h3></div>
        <table class="table" style="margin:0;">
            <thead><tr><th>Domain</th><th>Operation</th><th>Status</th><th>Message</th><th>Date</th></tr></thead>
            <tbody>
                @forelse($operations as $operation)
                    <tr>
                        <td>{{ $operation->domain_name }}</td>
                        <td>{{ ucwords(str_replace('_', ' ', $operation->operation)) }}</td>
                        <td><span class="pill pill-{{ $operation->status === 'success' ? 'ok' : ($operation->status === 'failed' ? 'bad' : 'warn') }}">{{ ucfirst($operation->status) }}</span></td>
                        <td style="max-width:420px;">{{ $operation->message }}</td>
                        <td>{{ \Carbon\Carbon::parse($operation->created_at)->format('M d, Y H:i') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" style="text-align:center;color:var(--jv-gray-500);padding:24px;">No registrar operations yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div style="display:flex;justify-content:flex-end;margin-top:16px;">
        <button class="btn btn-primary btn-lg">{{ jv_icon('check-circle', '', 16) }} Save tzNIC Settings</button>
    </div>
</form>
@endsection

@push('scripts')
<script>
async function testTznic() {
    const btn = document.getElementById('testTznicBtn');
    const original = btn.innerHTML;
    btn.textContent = 'Testing...';
    btn.disabled = true;

    try {
        const res = await fetch('{{ route('admin.tznic.test') }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        });
        const data = await res.json();
        Swal.fire({
            icon: data.success ? 'success' : 'error',
            title: data.success ? 'Connected' : 'Connection failed',
            text: data.message || 'No response message.'
        });
    } catch (e) {
        Swal.fire({ icon: 'error', title: 'Error', text: 'Could not call the test endpoint.' });
    }

    btn.innerHTML = original;
    btn.disabled = false;
}
</script>
@endpush
