@extends('themes.default::layouts.admin')

@section('title', 'Clients')
@section('breadcrumbs')<span class="current">Clients</span>@endsection

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Clients</h1>
        <p class="page-subtitle">Manage hosting customers, billing exposure, services, and migration references</p>
    </div>
    <div style="display: flex; gap: 8px; flex-wrap: wrap;">
        <a href="{{ route('admin.clients.export') }}" class="btn btn-outline-primary">{{ jv_icon('file', '', 16) }} Export</a>
        <a href="{{ route('admin.clients.create') }}" class="btn btn-primary">{{ jv_icon('user-plus', '', 16) }} Add New Client</a>
    </div>
</div>

<div class="kpi-grid">
    <div class="kpi"><div><div class="label">Total Clients</div><div class="value">{{ $stats['total'] ?? 0 }}</div><div class="delta up">{{ $stats['active'] ?? 0 }} active</div></div><div class="ico blue">{{ jv_icon('users') }}</div></div>
    <div class="kpi"><div><div class="label">Companies</div><div class="value">{{ $stats['companies'] ?? 0 }}</div><div class="delta">{{ $stats['inactive'] ?? 0 }} inactive</div></div><div class="ico purple">{{ jv_icon('building-2') }}</div></div>
    <div class="kpi"><div><div class="label">Outstanding</div><div class="value">{{ jv_format_money($stats['outstanding'] ?? 0) }}</div><div class="delta {{ ($stats['outstanding'] ?? 0) > 0 ? 'down' : 'up' }}">{{ $stats['suspended'] ?? 0 }} suspended</div></div><div class="ico amber">{{ jv_icon('file-text') }}</div></div>
    <div class="kpi"><div><div class="label">Client Credits</div><div class="value">{{ jv_format_money($stats['credits'] ?? 0) }}</div><div class="delta up">Prepaid balance</div></div><div class="ico green">{{ jv_icon('dollar-sign') }}</div></div>
</div>

<div class="dash-card" style="margin-bottom: 1.5rem;">
    <form method="GET" id="filterForm" style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap;">
        <input type="text" name="search" class="form-input" placeholder="Search name, email, company, client #, WHMCS ID..." value="{{ request('search') }}" style="width: min(100%, 360px);">
        <select name="status" class="form-select" style="width: 160px;" onchange="this.form.submit()">
            <option value="">All Statuses</option>
            @foreach(['active' => 'Active', 'inactive' => 'Inactive', 'suspended' => 'Suspended', 'closed' => 'Closed'] as $value => $label)
                <option value="{{ $value }}" {{ request('status') === $value ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
        <select name="type" class="form-select" style="width: 170px;" onchange="this.form.submit()">
            <option value="">All Account Types</option>
            @foreach(['individual' => 'Individual', 'company' => 'Company', 'government' => 'Government', 'nonprofit' => 'Non-profit'] as $value => $label)
                <option value="{{ $value }}" {{ request('type') === $value ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
        <button class="btn btn-outline-primary">Filter</button>
        @if(request()->anyFilled(['search', 'status', 'type']))
            <a href="{{ route('admin.clients.index') }}" class="btn btn-sm btn-outline-danger">Clear</a>
        @endif
    </form>
</div>

<div id="bulkBar" style="display: none; margin-bottom: 1rem; padding: 12px 16px; background: #f8fafc; border: 1px solid var(--jv-gray-200); border-radius: 8px; align-items: center; gap: 12px;">
    <span id="selectedCount" style="font-weight: 600;">0 selected</span>
    <form id="bulkForm" action="{{ route('admin.clients.bulk') }}" method="POST">
        @csrf
        <input type="hidden" name="ids" id="bulkIds">
        <select name="action" class="form-select" style="width: 160px; display: inline;">
            <option value="">Bulk Action</option>
            <option value="activate">Activate</option>
            <option value="suspend">Suspend</option>
            <option value="delete">Delete</option>
        </select>
        <button type="submit" class="btn btn-sm btn-primary" onclick="return confirm('Apply this action to selected clients?')">Apply</button>
    </form>
</div>

<div class="dash-card" style="padding: 0; overflow: hidden;">
    @if($clients->count() > 0)
        <table class="table" style="margin: 0;">
            <thead>
                <tr>
                    <th style="width: 40px;"><input type="checkbox" id="selectAll" onchange="toggleSelectAll()"></th>
                    <th>Client</th>
                    <th>Account</th>
                    <th>Contact</th>
                    <th>Products</th>
                    <th>Billing</th>
                    <th>Status</th>
                    <th class="text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($clients as $client)
                <tr>
                    <td><input type="checkbox" class="client-check" value="{{ $client->id }}" onchange="updateBulk()"></td>
                    <td>
                        <div class="mini-user">
                            <div class="avatar">{{ strtoupper(substr($client->first_name, 0, 1) . substr($client->last_name, 0, 1)) }}</div>
                            <div>
                                <a href="{{ route('admin.clients.show', $client) }}" style="font-weight: 700; color: var(--jv-gray-900);">{{ $client->full_name }}</a>
                                <small>{{ $client->client_number ?: 'Client #' . $client->id }}</small>
                            </div>
                        </div>
                    </td>
                    <td>
                        <strong>{{ ucfirst($client->type ?? 'individual') }}</strong>
                        @if($client->company_name)<small style="display:block;color:var(--jv-gray-500);">{{ $client->company_name }}</small>@endif
                        @if($client->external_id)<small style="display:block;color:var(--jv-gray-500);">External: {{ $client->external_id }}</small>@endif
                    </td>
                    <td>
                        <div>{{ $client->email }}</div>
                        @if($client->phone || $client->mobile)<small>{{ $client->phone ?: $client->mobile }}</small>@endif
                    </td>
                    <td>
                        <span class="pill pill-info">{{ $client->services_count ?? 0 }} services</span>
                        <span class="pill pill-info">{{ $client->domains_count ?? 0 }} domains</span>
                    </td>
                    <td>
                        <strong>{{ jv_format_money($client->outstanding_balance ?? 0) }}</strong>
                        <small style="display:block;color:var(--jv-gray-500);">{{ $client->open_invoices_count ?? 0 }} open invoices</small>
                        @if(($client->credit_balance ?? 0) > 0)<small style="display:block;color:var(--jv-success);">Credit {{ jv_format_money($client->credit_balance) }}</small>@endif
                    </td>
                    <td>
                        <span class="pill pill-{{ $client->status === 'active' ? 'ok' : ($client->status === 'suspended' ? 'bad' : 'warn') }}">{{ ucfirst($client->status) }}</span>
                        @if($client->source)<small style="display:block;color:var(--jv-gray-500);margin-top:4px;">{{ $client->source }}</small>@endif
                    </td>
                    <td class="text-center">
                        <div class="btn-group" style="justify-content: center; gap: 4px;">
                            <a href="{{ route('admin.clients.show', $client) }}" class="btn btn-sm btn-outline-primary" title="View">View</a>
                            <a href="{{ route('admin.clients.edit', $client) }}" class="btn btn-sm btn-outline-primary" title="Edit">Edit</a>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <div style="padding: 16px 24px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
            <small style="color: var(--jv-gray-500);">Showing {{ $clients->firstItem() ?? 0 }}-{{ $clients->lastItem() ?? 0 }} of {{ $clients->total() }}</small>
            {{ $clients->appends(request()->query())->links() }}
        </div>
    @else
        <div class="empty-state" style="padding: 60px;">
            <div class="empty-state-title">No clients found</div>
            <p class="empty-state-desc">{{ request()->anyFilled(['search', 'status', 'type']) ? 'Try different filters.' : 'Start building your hosting client base.' }}</p>
            @if(!request()->anyFilled(['search', 'status', 'type']))
                <a href="{{ route('admin.clients.create') }}" class="btn btn-primary">Add Your First Client</a>
            @endif
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
function toggleSelectAll() {
    const checked = document.getElementById('selectAll').checked;
    document.querySelectorAll('.client-check').forEach(cb => cb.checked = checked);
    updateBulk();
}

function updateBulk() {
    const checked = document.querySelectorAll('.client-check:checked');
    const ids = Array.from(checked).map(cb => cb.value);
    document.getElementById('bulkIds').value = ids.join(',');
    document.getElementById('selectedCount').textContent = ids.length + ' selected';
    document.getElementById('bulkBar').style.display = ids.length > 0 ? 'flex' : 'none';
}
</script>
@endpush
