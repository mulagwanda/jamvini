@extends('themes.default::layouts.admin')

@section('title', 'Domains — JamVini Hosting')

@section('breadcrumbs')
    <span class="current">Domains</span>
@endsection

@section('content')
<div class="page-header">
    <div style="display: flex; align-items: center; justify-content: space-between;">
        <div>
            <h1 class="page-title">Domains</h1>
            <p class="page-subtitle">Track and manage client domain registrations</p>
        </div>
        <a href="{{ route('admin.domains.create') }}" class="btn btn-primary">
            {{ jv_icon('globe', '', 16) }} Add Domain
        </a>
    </div>
</div>

{{-- Stats --}}
<div class="stats-grid">
    <div class="stat-card primary">
        <div class="stat-card-header"><div class="stat-card-icon">{{ jv_icon('globe') }}</div></div>
        <div class="stat-card-value">{{ $stats['total'] }}</div>
        <div class="stat-card-label">Total Domains</div>
    </div>
    <div class="stat-card success">
        <div class="stat-card-header"><div class="stat-card-icon">{{ jv_icon('check-circle') }}</div></div>
        <div class="stat-card-value">{{ $stats['active'] }}</div>
        <div class="stat-card-label">Active</div>
    </div>
    <div class="stat-card warning">
        <div class="stat-card-header"><div class="stat-card-icon">{{ jv_icon('clock') }}</div></div>
        <div class="stat-card-value">{{ $stats['expiring'] }}</div>
        <div class="stat-card-label">Expiring Soon</div>
    </div>
    <div class="stat-card danger">
        <div class="stat-card-header"><div class="stat-card-icon">{{ jv_icon('x-circle') }}</div></div>
        <div class="stat-card-value">{{ $stats['expired'] }}</div>
        <div class="stat-card-label">Expired</div>
    </div>
</div>

{{-- Filters --}}
<div class="card">
    <div class="card-header">
        <h3 class="card-title">{{ jv_icon('clipboard-list', '', 20) }} All Domains</h3>
        <div class="card-actions">
            <form method="GET" style="display: flex; gap: 8px;">
                <input type="text" name="search" class="form-input" placeholder="Search domain..." 
                       value="{{ request('search') }}" style="width: 220px;">
                <select name="status" class="form-select" style="width: 150px;" onchange="this.form.submit()">
                    <option value="">All Status</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="expired" {{ request('status') === 'expired' ? 'selected' : '' }}>Expired</option>
                    <option value="transferred" {{ request('status') === 'transferred' ? 'selected' : '' }}>Transferred</option>
                    <option value="suspended" {{ request('status') === 'suspended' ? 'selected' : '' }}>Suspended</option>
                </select>
                <button type="submit" class="btn btn-sm btn-outline-primary">Filter</button>
                @if(request()->anyFilled(['search', 'status']))
                    <a href="{{ route('admin.domains.index') }}" class="btn btn-sm btn-outline-danger">Clear</a>
                @endif
            </form>
        </div>
    </div>
    <div class="card-body" style="padding: 0;">
        @if($domains->count() > 0)
            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Domain</th>
                            <th>Client</th>
                            <th>Registrar</th>
                            <th>Registration</th>
                            <th>Expiry</th>
                            <th>Status</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($domains as $domain)
                        <tr>
                            <td>
                                <a href="{{ route('admin.domains.show', $domain) }}" style="font-weight: 600;">
                                    {{ $domain->domain_name }}
                                </a>
                                <div style="font-size: 0.8rem; color: var(--jv-gray-500);">{{ $domain->tld }}</div>
                            </td>
                            <td>
                                <a href="{{ route('admin.clients.show', $domain->client) }}">
                                    {{ $domain->client->full_name }}
                                </a>
                            </td>
                            <td>{{ $domain->registrar ?? '—' }}</td>
                            <td>{{ $domain->registration_date ? $domain->registration_date->format('M d, Y') : '—' }}</td>
                            <td>
                                <span class="@if($domain->days_until_expiry !== null && $domain->days_until_expiry <= 30) text-danger font-bold @endif">
                                    {{ $domain->expiry_date ? $domain->expiry_date->format('M d, Y') : '—' }}
                                </span>
                                @if($domain->days_until_expiry !== null && $domain->days_until_expiry > 0 && $domain->days_until_expiry <= 30)
                                    <br><small class="text-danger">{{ $domain->days_until_expiry }} days left</small>
                                @endif
                            </td>
                            <td>
                                <span class="badge badge-{{ $domain->status === 'active' ? 'success' : ($domain->status === 'expired' ? 'danger' : 'warning') }} badge-with-dot">
                                    {{ ucfirst($domain->status) }}
                                </span>
                            </td>
                            <td class="text-center">
                                <div class="btn-group" style="justify-content: center;">
                                    <a href="{{ route('admin.domains.show', $domain) }}" class="btn btn-sm btn-outline-primary">{{ jv_icon('search', '', 16) }}</a>
                                    <a href="{{ route('admin.domains.edit', $domain) }}" class="btn btn-sm btn-outline-primary">{{ jv_icon('notebook-pen', '', 16) }}</a>
                                    <form action="{{ route('admin.domains.destroy', $domain) }}" method="POST" style="display: inline;"
                                          data-confirm="Delete domain '{{ $domain->domain_name }}'?"
                                          data-title="Delete Domain"
                                          data-confirm-text="Yes, Delete"
                                          data-danger="true">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">{{ jv_icon('x', '', 16) }}</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div style="padding: 16px 24px;">{{ $domains->appends(request()->query())->links() }}</div>
        @else
            <div class="empty-state">
                <div class="empty-state-icon">{{ jv_icon('globe', '', 42) }}</div>
                <div class="empty-state-title">No domains found</div>
                <div class="empty-state-desc">Start tracking your client domains.</div>
                <a href="{{ route('admin.domains.create') }}" class="btn btn-primary">{{ jv_icon('globe', '', 16) }} Add First Domain</a>
            </div>
        @endif
    </div>
</div>
@endsection
