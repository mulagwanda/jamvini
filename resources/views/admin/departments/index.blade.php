@extends('themes.default::layouts.admin')

@section('title', 'Departments')
@section('breadcrumbs')<a href="{{ route('admin.admin-users.index') }}">Admin Users</a> <span class="separator">/</span> <span class="current">Departments</span>@endsection

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Departments</h1>
        <p class="page-subtitle">Group staff by business function such as Billing, Support, and Technical</p>
    </div>
    <div style="display:flex;gap:8px;flex-wrap:wrap;">
        <a href="{{ route('admin.admin-users.index') }}" class="btn btn-outline-primary">{{ jv_icon('users', '', 16) }} Admin Users</a>
        <a href="{{ route('admin.departments.create') }}" class="btn btn-primary">{{ jv_icon('building-2', '', 16) }} Add Department</a>
    </div>
</div>

<div class="kpi-grid">
    <div class="kpi"><div><div class="label">Total</div><div class="value">{{ $stats['total'] }}</div></div><div class="ico blue">{{ jv_icon('building-2') }}</div></div>
    <div class="kpi"><div><div class="label">Active</div><div class="value">{{ $stats['active'] }}</div></div><div class="ico green">{{ jv_icon('check-circle') }}</div></div>
    <div class="kpi"><div><div class="label">Inactive</div><div class="value">{{ $stats['inactive'] }}</div></div><div class="ico amber">{{ jv_icon('pause-circle') }}</div></div>
    <div class="kpi"><div><div class="label">Assigned Staff</div><div class="value">{{ $stats['assigned_admins'] }}</div></div><div class="ico purple">{{ jv_icon('users') }}</div></div>
</div>

<div class="dash-card" style="margin-bottom:1.5rem;">
    <form method="GET" style="display:flex;gap:12px;align-items:center;flex-wrap:wrap;">
        <input type="text" name="search" class="form-input" value="{{ request('search') }}" placeholder="Search department..." style="width:min(100%,320px);">
        <select name="status" class="form-select" style="width:160px;" onchange="this.form.submit()">
            <option value="">All Statuses</option>
            <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
            <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
        </select>
        <button class="btn btn-outline-primary">Filter</button>
        @if(request()->anyFilled(['search', 'status']))
            <a href="{{ route('admin.departments.index') }}" class="btn btn-sm btn-outline-danger">Clear</a>
        @endif
    </form>
</div>

<div class="dash-card" style="padding:0;overflow:hidden;">
    @if($departments->count() > 0)
        <table class="table" style="margin:0;">
            <thead><tr><th>Department</th><th>Slug</th><th>Staff</th><th>Status</th><th class="text-center">Actions</th></tr></thead>
            <tbody>
                @foreach($departments as $department)
                    <tr>
                        <td><strong>{{ $department->name }}</strong>@if($department->description)<small style="display:block;color:var(--jv-gray-500);">{{ $department->description }}</small>@endif</td>
                        <td><code>{{ $department->slug }}</code></td>
                        <td><span class="pill pill-info">{{ $department->admins_count }} admin(s)</span></td>
                        <td><span class="pill pill-{{ $department->is_active ? 'ok' : 'warn' }}">{{ $department->is_active ? 'Active' : 'Inactive' }}</span></td>
                        <td class="text-center">
                            <div class="btn-group" style="justify-content:center;gap:4px;">
                                <a href="{{ route('admin.departments.edit', $department) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                                @if($department->admins_count === 0)
                                    <form action="{{ route('admin.departments.destroy', $department) }}" method="POST" data-confirm="Delete {{ $department->name }}?" data-danger="true">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger">Delete</button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div style="padding:16px 24px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px;">
            <small style="color:var(--jv-gray-500);">Showing {{ $departments->firstItem() ?? 0 }}-{{ $departments->lastItem() ?? 0 }} of {{ $departments->total() }}</small>
            {{ $departments->appends(request()->query())->links() }}
        </div>
    @else
        <div class="empty-state" style="padding:60px;"><div class="empty-state-title">No departments found</div><a href="{{ route('admin.departments.create') }}" class="btn btn-primary">Add Department</a></div>
    @endif
</div>
@endsection
