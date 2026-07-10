@extends('themes.default::layouts.admin')

@section('title', 'Admin Users')
@section('breadcrumbs')<span class="current">Admin Users</span>@endsection

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Admin Users</h1>
        <p class="page-subtitle">Manage staff accounts, roles, departments, and access levels</p>
    </div>
    <div style="display:flex;gap:8px;flex-wrap:wrap;">
        <a href="{{ route('admin.departments.index') }}" class="btn btn-outline-primary">{{ jv_icon('building-2', '', 16) }} Departments</a>
        <a href="{{ route('admin.admin-users.create') }}" class="btn btn-primary">{{ jv_icon('user-plus', '', 16) }} Add Admin User</a>
    </div>
</div>

<div class="kpi-grid">
    <div class="kpi"><div><div class="label">Total Admins</div><div class="value">{{ $stats['total'] }}</div></div><div class="ico blue">{{ jv_icon('users') }}</div></div>
    <div class="kpi"><div><div class="label">Active</div><div class="value">{{ $stats['active'] }}</div></div><div class="ico green">{{ jv_icon('check-circle') }}</div></div>
    <div class="kpi"><div><div class="label">Inactive</div><div class="value">{{ $stats['inactive'] }}</div></div><div class="ico amber">{{ jv_icon('pause-circle') }}</div></div>
    <div class="kpi"><div><div class="label">Departments</div><div class="value">{{ $stats['departments'] }}</div></div><div class="ico purple">{{ jv_icon('building-2') }}</div></div>
</div>

<div class="dash-card" style="margin-bottom:1.5rem;">
    <form method="GET" style="display:flex;gap:12px;align-items:center;flex-wrap:wrap;">
        <input type="text" name="search" class="form-input" value="{{ request('search') }}" placeholder="Search name, email, or title..." style="width:min(100%,320px);">
        <select name="role" class="form-select" style="width:180px;" onchange="this.form.submit()">
            <option value="">All Roles</option>
            @foreach($roles as $value => $label)
                <option value="{{ $value }}" {{ request('role') === $value ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
        <select name="status" class="form-select" style="width:160px;" onchange="this.form.submit()">
            <option value="">All Statuses</option>
            @foreach(['active' => 'Active', 'inactive' => 'Inactive', 'suspended' => 'Suspended'] as $value => $label)
                <option value="{{ $value }}" {{ request('status') === $value ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
        <button class="btn btn-outline-primary">Filter</button>
        @if(request()->anyFilled(['search', 'role', 'status']))
            <a href="{{ route('admin.admin-users.index') }}" class="btn btn-sm btn-outline-danger">Clear</a>
        @endif
    </form>
</div>

<div class="dash-card" style="padding:0;overflow:hidden;">
    @if($admins->count() > 0)
        <table class="table" style="margin:0;">
            <thead><tr><th>Admin</th><th>Department</th><th>Role</th><th>Key Access</th><th>Status</th><th>Last Login</th><th class="text-center">Actions</th></tr></thead>
            <tbody>
                @foreach($admins as $admin)
                    <tr>
                        <td>
                            <div class="mini-user">
                                <div class="avatar">{{ strtoupper(substr($admin->name, 0, 1)) }}</div>
                                <div><strong>{{ $admin->name }}</strong><small>{{ $admin->email }}@if($admin->job_title) · {{ $admin->job_title }}@endif</small></div>
                            </div>
                        </td>
                        <td>{{ $admin->department->name ?? '-' }}</td>
                        <td><span class="pill pill-info">{{ $roles[$admin->role] ?? ucfirst($admin->role) }}</span></td>
                        <td>
                            @if($admin->isSuperAdmin() || $admin->role === 'admin')
                                <span class="pill pill-ok">All areas</span>
                            @else
                                @foreach($permissions as $module => $label)
                                    @php $level = $admin->permissionLevel($module); @endphp
                                    @if($level !== 'none')
                                        <small style="display:inline-block;margin:2px 4px 2px 0;color:var(--jv-gray-600);">{{ $label }}: {{ ucfirst($level) }}</small>
                                    @endif
                                @endforeach
                            @endif
                        </td>
                        <td><span class="pill pill-{{ $admin->status === 'active' ? 'ok' : ($admin->status === 'suspended' ? 'bad' : 'warn') }}">{{ ucfirst($admin->status ?? 'active') }}</span></td>
                        <td>{{ $admin->last_login_at ? $admin->last_login_at->format('M d, Y H:i') : '-' }}</td>
                        <td class="text-center">
                            <div class="btn-group" style="justify-content:center;gap:4px;">
                                <a href="{{ route('admin.admin-users.edit', $admin) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                                @if($admin->id !== auth('admin')->id())
                                    <form action="{{ route('admin.admin-users.destroy', $admin) }}" method="POST" data-confirm="Delete {{ $admin->name }}?" data-danger="true">
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
            <small style="color:var(--jv-gray-500);">Showing {{ $admins->firstItem() ?? 0 }}-{{ $admins->lastItem() ?? 0 }} of {{ $admins->total() }}</small>
            {{ $admins->appends(request()->query())->links() }}
        </div>
    @else
        <div class="empty-state" style="padding:60px;"><div class="empty-state-title">No admin users found</div><a href="{{ route('admin.admin-users.create') }}" class="btn btn-primary">Add Admin User</a></div>
    @endif
</div>
@endsection
