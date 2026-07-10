@extends('themes.default::layouts.admin')

@section('title', 'Servers')
@section('breadcrumbs')<a href="{{ route('admin.services.index') }}">Services</a> <span class="separator">/</span> <span class="current">Servers</span>@endsection

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Servers</h1>
        <p class="page-subtitle">Manage provisioning servers for hosting services</p>
    </div>
    <button class="btn btn-primary" onclick="document.getElementById('addServerForm').style.display='block'">➕ Add Server</button>
</div>

{{-- Add Server Form --}}
<div id="addServerForm" style="display: none; margin-bottom: 24px;">
    <div class="dash-card">
        <div class="dash-card-head"><h3>New Server</h3></div>
        <form action="{{ route('admin.services.servers.store') }}" method="POST">
            @csrf
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                <div class="form-group"><label class="form-label">Server Name *</label><input type="text" name="name" class="form-input" placeholder="US-East Production Server" required></div>
                <div class="form-group">
                    <label class="form-label">Control Panel Type *</label>
                    <select name="type" class="form-select" onchange="showServerFields(this.value)" required>
                        <option value="">— Select Type —</option>
                        <option value="cpanel">cPanel / WHM</option>
                        <option value="plesk">Plesk</option>
                        <option value="directadmin">DirectAdmin</option>
                        <option value="webuzo">Webuzo</option>
                        <option value="cyberpanel">CyberPanel</option>
                        <option value="ispconfig">ISPConfig</option>
                        <option value="proxmox">Proxmox VPS</option>
                        <option value="vmware">VMware vCenter</option>
                        <option value="irc">IRC Provisioning</option>
                        <option value="custom">Custom / Manual</option>
                    </select>
                </div>
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                <div class="form-group"><label class="form-label">Hostname *</label><input type="text" name="hostname" class="form-input" placeholder="server.yourcompany.com" required><div class="form-hint">Used to connect to your server's API</div></div>
                <div class="form-group"><label class="form-label">IP Address *</label><input type="text" name="ip_address" class="form-input" placeholder="192.168.1.100" required></div>
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px;">
                <div class="form-group"><label class="form-label">Username</label><input type="text" name="username" class="form-input" placeholder="root or admin"></div>
                <div class="form-group"><label class="form-label">Password</label><input type="password" name="password" class="form-input" placeholder="Server password"></div>
                <div class="form-group"><label class="form-label">Port</label><input type="number" name="port" class="form-input" value="2087"></div>
            </div>

            <div id="cpanelFields" style="display: none;">
                <div class="form-group"><label class="form-label">WHM API Token *</label><input type="password" name="api_token" class="form-input" placeholder="WHM API token"><div class="form-hint">WHM → Development → Manage API Tokens. Use port 2087 for SSL WHM.</div></div>
            </div>
            <div id="pleskFields" style="display: none;">
                <div class="form-group"><label class="form-label">Access Hash / Secret Key *</label><input type="password" name="api_token" class="form-input" placeholder="Plesk API secret key"><div class="form-hint">Plesk → Tools & Settings → API Access Keys</div></div>
            </div>
            <div id="directadminFields" style="display: none;">
                <div class="form-group"><label class="form-label">Access Hash *</label><input type="password" name="api_token" class="form-input" placeholder="DirectAdmin login key"><div class="form-hint">Login as admin → Create Login Key</div></div>
            </div>
            <div id="otherFields" style="display: none;">
                <div class="form-group"><label class="form-label">Password / API Key *</label><input type="password" name="api_token" class="form-input"></div>
            </div>

            <div class="form-group"><label class="form-label">Nameservers (one per line)</label><textarea name="nameserver_list" class="form-textarea" rows="3" placeholder="ns1.yourcompany.com&#10;ns2.yourcompany.com"></textarea></div>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                <div class="form-group"><label class="form-label">Max Accounts</label><input type="number" name="max_accounts" class="form-input" value="0"><div class="form-hint">0 = Unlimited</div></div>
                <div class="form-group">
                    <label class="toggle-switch"><input type="checkbox" name="use_ssl" value="1" checked><span class="toggle-slider"></span><span>Use SSL Connection</span></label>
                </div>
            </div>
            <div class="form-group"><label class="form-label">Notes</label><textarea name="notes" class="form-textarea" rows="2" placeholder="Internal notes..."></textarea></div>

            <div style="display: flex; gap: 12px;">
                <button type="submit" class="btn btn-primary">➕ Add Server</button>
                <button type="button" class="btn btn-outline-danger" onclick="document.getElementById('addServerForm').style.display='none'">Cancel</button>
            </div>
        </form>
    </div>
</div>

{{-- Servers List --}}
@if(isset($servers) && $servers->count() > 0)
<div class="dash-card" style="padding: 0; overflow: hidden;">
    <table class="table" style="margin: 0;">
        <thead><tr><th>Server</th><th>Type</th><th>Hostname</th><th>Accounts</th><th>Packages</th><th>Status</th><th>Actions</th></tr></thead>
        <tbody>
            @foreach($servers as $server)
            <tr>
                <td><strong>{{ $server->name }}</strong></td>
                <td><span class="pill pill-info">{{ ucfirst($server->type) }}</span></td>
                <td><code>{{ $server->hostname }}</code></td>
                <td>{{ $server->current_accounts }}/{{ $server->max_accounts ?: '∞' }}</td>
                <td>
                    <strong>{{ $server->packages_count ?? 0 }}</strong>
                    @if($server->packages->count())
                        <details style="margin-top:4px;">
                            <summary style="cursor:pointer;color:var(--jv-primary);font-size:.78rem;">View packages</summary>
                            <div style="display:flex;gap:4px;flex-wrap:wrap;margin-top:6px;max-width:260px;">
                                @foreach($server->packages as $package)
                                    <span class="pill pill-mute">{{ $package->display_name ?: $package->name }}</span>
                                @endforeach
                            </div>
                        </details>
                    @endif
                </td>
                <td><span class="pill pill-{{ $server->status === 'active' ? 'ok' : 'warn' }}">{{ ucfirst($server->status) }}</span></td>
	                <td>
	                    <div class="btn-group" style="gap: 4px;">
	                        <form action="{{ route('admin.services.servers.test', $server) }}" method="POST" style="display: inline;">@csrf <button class="btn btn-sm btn-outline-primary">🔌 Test</button></form>
                            @if($server->type === 'cpanel')
                                <form action="{{ route('admin.services.servers.sync-packages', $server) }}" method="POST" style="display: inline;">@csrf <button class="btn btn-sm btn-outline-primary">📦 Sync Packages</button></form>
                            @endif
	                        <button class="btn btn-sm btn-outline-primary" onclick='editServer(@json($server))'>✏️</button>
                        <form action="{{ route('admin.services.servers.destroy', $server) }}" method="POST" style="display: inline;" data-confirm="Delete this server?" data-danger="true">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger">🗑️</button>
                        </form>
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@else
<div class="dash-card"><div class="empty-state"><div class="empty-state-icon">🖥️</div><div class="empty-state-title">No servers configured</div><p>Add servers to provision hosting accounts automatically</p></div></div>
@endif

<div id="editServerForm" style="display: none; margin-top: 24px;">
    <div class="dash-card">
        <div class="dash-card-head"><h3>Edit Server</h3></div>
        <form id="serverEditForm" method="POST">
            @csrf @method('PUT')
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                <div class="form-group"><label class="form-label">Server Name *</label><input type="text" name="name" id="editServerName" class="form-input" required></div>
                <div class="form-group">
                    <label class="form-label">Control Panel Type *</label>
                    <select name="type" id="editServerType" class="form-select" required>
                        <option value="cpanel">cPanel / WHM</option>
                        <option value="plesk">Plesk</option>
                        <option value="directadmin">DirectAdmin</option>
                        <option value="webuzo">Webuzo</option>
                        <option value="cyberpanel">CyberPanel</option>
                        <option value="ispconfig">ISPConfig</option>
                        <option value="proxmox">Proxmox VPS</option>
                        <option value="vmware">VMware vCenter</option>
                        <option value="irc">IRC Provisioning</option>
                        <option value="custom">Custom / Manual</option>
                    </select>
                </div>
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                <div class="form-group"><label class="form-label">Hostname *</label><input type="text" name="hostname" id="editServerHostname" class="form-input" required></div>
                <div class="form-group"><label class="form-label">IP Address *</label><input type="text" name="ip_address" id="editServerIp" class="form-input" required></div>
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px;">
                <div class="form-group"><label class="form-label">Username</label><input type="text" name="username" id="editServerUsername" class="form-input"></div>
                <div class="form-group"><label class="form-label">New Password</label><input type="password" name="password" class="form-input" placeholder="Leave blank to keep current"></div>
                <div class="form-group"><label class="form-label">Port</label><input type="number" name="port" id="editServerPort" class="form-input"></div>
            </div>
            <div class="form-group"><label class="form-label">New API Token</label><input type="password" name="api_token" class="form-input" placeholder="Leave blank to keep current"></div>
            <div class="form-group"><label class="form-label">Nameservers (one per line)</label><textarea name="nameserver_list" id="editServerNameservers" class="form-textarea" rows="3"></textarea></div>
            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px;">
                <div class="form-group"><label class="form-label">Max Accounts</label><input type="number" name="max_accounts" id="editServerMax" class="form-input" min="0"></div>
                <div class="form-group"><label class="form-label">Current Accounts</label><input type="number" name="current_accounts" id="editServerCurrent" class="form-input" min="0"></div>
                <div class="form-group"><label class="form-label">Status</label><select name="status" id="editServerStatus" class="form-select"><option value="active">Active</option><option value="inactive">Inactive</option><option value="maintenance">Maintenance</option><option value="unreachable">Unreachable</option></select></div>
            </div>
            <div class="form-group">
                <input type="hidden" name="use_ssl" value="0">
                <label class="toggle-switch"><input type="checkbox" name="use_ssl" id="editServerSsl" value="1"><span class="toggle-slider"></span><span>Use SSL Connection</span></label>
            </div>
            <div class="form-group"><label class="form-label">Notes</label><textarea name="notes" id="editServerNotes" class="form-textarea" rows="2"></textarea></div>
            <div style="display:flex;gap:12px;">
                <button type="submit" class="btn btn-primary">Update Server</button>
                <button type="button" class="btn btn-outline-danger" onclick="document.getElementById('editServerForm').style.display='none'">Cancel</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
function showServerFields(type) {
    ['cpanelFields','pleskFields','directadminFields','otherFields'].forEach(id => {
        const group = document.getElementById(id);
        group.style.display = 'none';
        group.querySelectorAll('input, select, textarea').forEach(input => input.disabled = true);
    });
    if (!type) return;

    const map = { cpanel: 'cpanelFields', plesk: 'pleskFields', directadmin: 'directadminFields' };
    const target = map[type] || 'otherFields';
    const group = document.getElementById(target);
    group.style.display = '';
    group.querySelectorAll('input, select, textarea').forEach(input => input.disabled = false);
}

function editServer(server) {
    const wrapper = document.getElementById('editServerForm');
    wrapper.style.display = 'block';
    document.getElementById('serverEditForm').action = '{{ route('admin.services.servers.update', '__ID__') }}'.replace('__ID__', server.id);
    document.getElementById('editServerName').value = server.name || '';
    document.getElementById('editServerType').value = server.type || 'custom';
    document.getElementById('editServerHostname').value = server.hostname || '';
    document.getElementById('editServerIp').value = server.ip_address || '';
    document.getElementById('editServerUsername').value = server.username || '';
    document.getElementById('editServerPort').value = server.port || '';
    document.getElementById('editServerMax').value = server.max_accounts || 0;
    document.getElementById('editServerCurrent').value = server.current_accounts || 0;
    document.getElementById('editServerStatus').value = server.status || 'active';
    document.getElementById('editServerSsl').checked = Boolean(server.use_ssl);
    document.getElementById('editServerNotes').value = server.notes || '';
    document.getElementById('editServerNameservers').value = Array.isArray(server.nameservers) ? server.nameservers.join('\n') : '';
    wrapper.scrollIntoView({ behavior: 'smooth' });
}

document.addEventListener('DOMContentLoaded', function() {
    const typeSelect = document.querySelector('#addServerForm select[name="type"]');
    showServerFields(typeSelect?.value || '');
});
</script>
@endpush
