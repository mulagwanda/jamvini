@extends('themes.default::layouts.admin')

@php $isEdit = $adminUser->exists; @endphp

@section('title', $isEdit ? 'Edit Admin User' : 'Add Admin User')
@section('breadcrumbs')<a href="{{ route('admin.admin-users.index') }}">Admin Users</a> <span class="separator">/</span> <span class="current">{{ $isEdit ? 'Edit' : 'Add' }}</span>@endsection

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">{{ $isEdit ? 'Edit Admin User' : 'Add Admin User' }}</h1>
        <p class="page-subtitle">Set staff identity, department, role, and permission scope</p>
    </div>
    <a href="{{ route('admin.admin-users.index') }}" class="btn btn-outline-primary">Back to Admin Users</a>
</div>

<form action="{{ $isEdit ? route('admin.admin-users.update', $adminUser) : route('admin.admin-users.store') }}" method="POST">
    @csrf
    @if($isEdit) @method('PUT') @endif

    <div style="display:grid;grid-template-columns:minmax(0,1fr) 360px;gap:1.5rem;align-items:start;">
        <div style="display:grid;gap:1.5rem;">
            <div class="dash-card">
                <div class="dash-card-head"><h3>Identity</h3></div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                    <div class="form-group"><label class="form-label">Name *</label><input type="text" name="name" class="form-input" value="{{ old('name', $adminUser->name) }}" required></div>
                    <div class="form-group"><label class="form-label">Email *</label><input type="email" name="email" class="form-input" value="{{ old('email', $adminUser->email) }}" required></div>
                    <div class="form-group"><label class="form-label">Phone</label><input type="text" name="phone" class="form-input" value="{{ old('phone', $adminUser->phone) }}"></div>
                    <div class="form-group"><label class="form-label">Job Title</label><input type="text" name="job_title" class="form-input" value="{{ old('job_title', $adminUser->job_title) }}" placeholder="Billing Officer, Support Lead..."></div>
                </div>
            </div>

            <div class="dash-card">
                <div class="dash-card-head"><h3>Permissions</h3></div>
                <div style="display:grid;gap:10px;">
                    @foreach($permissions as $module => $label)
                        @php $currentLevel = old('access.' . $module, $adminUser->exists ? $adminUser->permissionLevel($module) : 'none'); @endphp
                        <div style="display:grid;grid-template-columns:minmax(0,1fr) 180px;gap:12px;align-items:center;padding:10px;border:1px solid var(--jv-gray-200);border-radius:8px;">
                            <div>
                                <strong>{{ $label }}</strong>
                                <small style="display:block;color:var(--jv-gray-500);">{{ $module }} module access</small>
                            </div>
                            <select name="access[{{ $module }}]" class="form-select">
                                @foreach($accessLevels as $level => $levelLabel)
                                    <option value="{{ $level }}" {{ $currentLevel === $level ? 'selected' : '' }}>{{ $levelLabel }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endforeach
                </div>
                <small style="display:block;color:var(--jv-gray-500);margin-top:10px;">Super Admin and Administrator roles bypass permission limits. Other roles combine their preset permissions with these explicit access levels.</small>
            </div>
        </div>

        <aside style="display:grid;gap:1.5rem;">
            <div class="dash-card">
                <div class="dash-card-head"><h3>Access Level</h3></div>
                <div class="form-group">
                    <label class="form-label">Role *</label>
                    <select name="role" class="form-select" required>
                        @foreach($roles as $value => $label)
                            <option value="{{ $value }}" {{ old('role', $adminUser->role ?? 'support') === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Department</label>
                    <select name="admin_department_id" class="form-select">
                        <option value="">No department</option>
                        @foreach($departments as $department)
                            <option value="{{ $department->id }}" {{ (string) old('admin_department_id', $adminUser->admin_department_id) === (string) $department->id ? 'selected' : '' }}>{{ $department->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        @foreach(['active' => 'Active', 'inactive' => 'Inactive', 'suspended' => 'Suspended'] as $value => $label)
                            <option value="{{ $value }}" {{ old('status', $adminUser->status ?? 'active') === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="dash-card">
                <div class="dash-card-head"><h3>Password</h3></div>
                <div class="form-group">
                    <label class="form-label">{{ $isEdit ? 'New Password' : 'Password *' }}</label>
                    <input type="password" name="password" class="form-input" {{ $isEdit ? '' : 'required' }} placeholder="{{ $isEdit ? 'Leave blank to keep current' : 'Minimum 8 characters' }}">
                </div>
            </div>
        </aside>
    </div>

    <div style="display:flex;gap:12px;justify-content:flex-end;margin-top:1.5rem;">
        <a href="{{ route('admin.admin-users.index') }}" class="btn btn-outline-danger">Cancel</a>
        <button class="btn btn-primary btn-lg">{{ $isEdit ? 'Update Admin User' : 'Create Admin User' }}</button>
    </div>
</form>
@endsection
