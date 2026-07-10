@extends('themes.default::layouts.admin')

@section('title', 'Service Groups')
@section('breadcrumbs')<a href="{{ route('admin.services.index') }}">Services</a> <span class="separator">/</span> <span class="current">Groups</span>@endsection

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Service Groups</h1>
        <p class="page-subtitle">Organize services into categories</p>
    </div>
    <button class="btn btn-primary" onclick="document.getElementById('addGroupForm').style.display='block'">➕ Add Group</button>
</div>

{{-- Add Group Form --}}
<div id="addGroupForm" style="display: none; margin-bottom: 24px;">
    <div class="dash-card">
        <div class="dash-card-head"><h3>New Service Group</h3></div>
        <form action="{{ route('admin.services.groups.store') }}" method="POST">
            @csrf
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                <div class="form-group"><label class="form-label">Name *</label><input type="text" name="name" class="form-input" required></div>
                <div class="form-group"><label class="form-label">Icon (emoji)</label><input type="text" name="icon" class="form-input" placeholder="🖥️" maxlength="5"></div>
            </div>
            <div class="form-group"><label class="form-label">Description</label><textarea name="description" class="form-textarea" rows="2"></textarea></div>
            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px;">
                <div class="form-group">
                    <label class="form-label">Module</label>
                    <select name="module" class="form-select">
                        <option value="">Generic</option>
                        <option value="hosting">Hosting</option>
                        <option value="domains">Domain Registration</option>
                        <option value="ssl">SSL Certificates</option>
                        <option value="email">Email Services</option>
                        <option value="custom">Custom</option>
                    </select>
                </div>
                <div class="form-group"><label class="form-label">Order</label><input type="number" name="order" class="form-input" value="0"></div>
                <div class="form-group">
                    <input type="hidden" name="requires_domain" value="0">
                    <label class="toggle-switch"><input type="checkbox" name="requires_domain" value="1"><span class="toggle-slider"></span><span>Requires domain</span></label>
                    <input type="hidden" name="is_active" value="0">
                    <label class="toggle-switch" style="margin-top: 10px;"><input type="checkbox" name="is_active" value="1" checked><span class="toggle-slider"></span><span>Active</span></label>
                </div>
            </div>
            <div style="display: flex; gap: 12px;">
                <button type="submit" class="btn btn-primary">Create Group</button>
                <button type="button" class="btn btn-outline-danger" onclick="document.getElementById('addGroupForm').style.display='none'">Cancel</button>
            </div>
        </form>
    </div>
</div>

{{-- Groups Table --}}
<div class="dash-card" style="padding: 0; overflow: hidden;">
    @if($groups->count() > 0)
    <table class="table" style="margin: 0;">
        <thead><tr><th>Group</th><th>Module</th><th>Services</th><th>Domain</th><th>Order</th><th class="text-center">Actions</th></tr></thead>
        <tbody>
            @foreach($groups as $group)
            <tr>
                <td>
                    <strong>{{ $group->icon }} {{ $group->name }}</strong>
                    @if($group->description)<div style="font-size: 0.8rem; color: var(--jv-gray-500);">{{ Str::limit($group->description, 50) }}</div>@endif
                </td>
                <td>
                    @if($group->module)<span class="pill pill-info">{{ ucfirst($group->module) }}</span>
                    @else <span class="pill pill-mute">Generic</span> @endif
                </td>
                <td><span class="pill pill-info">{{ $group->services_count ?? $group->services()->count() }}</span></td>
                <td>{{ $group->requires_domain ? '✅' : '—' }}</td>
                <td>{{ $group->order }}</td>
                <td class="text-center">
                    <button class="btn btn-sm btn-outline-primary" onclick='editGroup(@json($group))'>✏️</button>
                    <form action="{{ route('admin.services.groups.destroy', $group) }}" method="POST" style="display: inline;" data-confirm="Delete '{{ $group->name }}'?" data-danger="true">
                        @csrf @method('DELETE')
                        <button class="btn btn-sm btn-outline-danger">🗑️</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
    <div class="empty-state" style="padding: 60px;"><div class="empty-state-icon">📁</div><div class="empty-state-title">No groups</div></div>
    @endif
</div>

{{-- Edit Modal --}}
<div id="editGroupModal" style="display: none; margin-top: 24px;">
    <div class="dash-card">
        <div class="dash-card-head"><h3>Edit Group</h3></div>
        <form id="editGroupForm" method="POST">
            @csrf @method('PUT')
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                <div class="form-group"><label class="form-label">Name</label><input type="text" name="name" id="editName" class="form-input" required></div>
                <div class="form-group"><label class="form-label">Icon</label><input type="text" name="icon" id="editIcon" class="form-input"></div>
            </div>
            <div class="form-group"><label class="form-label">Description</label><textarea name="description" id="editDesc" class="form-textarea" rows="2"></textarea></div>
            <div class="form-group"><label class="form-label">Module</label><select name="module" id="editModule" class="form-select"><option value="">Generic</option><option value="hosting">Hosting</option><option value="domains">Domain Registration</option><option value="ssl">SSL</option><option value="email">Email Services</option><option value="custom">Custom</option></select></div>
            <div style="display: grid; grid-template-columns: 160px 1fr; gap: 16px; align-items: end;">
                <div class="form-group"><label class="form-label">Order</label><input type="number" name="order" id="editOrder" class="form-input"></div>
                <div class="form-group">
                    <input type="hidden" name="requires_domain" value="0">
                    <label class="toggle-switch"><input type="checkbox" name="requires_domain" id="editRequiresDomain" value="1"><span class="toggle-slider"></span><span>Requires domain</span></label>
                    <input type="hidden" name="is_active" value="0">
                    <label class="toggle-switch" style="margin-top:10px;"><input type="checkbox" name="is_active" id="editIsActive" value="1"><span class="toggle-slider"></span><span>Active</span></label>
                </div>
            </div>
            <div style="display: flex; gap: 12px;">
                <button type="submit" class="btn btn-primary">Update Group</button>
                <button type="button" class="btn btn-outline-danger" onclick="document.getElementById('editGroupModal').style.display='none'">Cancel</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
function editGroup(group) {
    var modal = document.getElementById('editGroupModal');
    if (!modal) return;
    modal.style.display = 'block';
    document.getElementById('editGroupForm').action = '{{ route('admin.services.groups.update', '__ID__') }}'.replace('__ID__', group.id);
    document.getElementById('editName').value = group.name || '';
    document.getElementById('editIcon').value = group.icon || '';
    document.getElementById('editDesc').value = group.description || '';
    document.getElementById('editOrder').value = group.order || 0;
    document.getElementById('editModule').value = group.module || '';
    document.getElementById('editRequiresDomain').checked = Boolean(group.requires_domain);
    document.getElementById('editIsActive').checked = group.is_active !== false;
    modal.scrollIntoView({ behavior: 'smooth' });
}
</script>
@endpush
