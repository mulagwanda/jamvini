@extends('themes.default::layouts.admin')

@section('title', 'Services Catalog')
@section('breadcrumbs')<span class="current">Services Catalog</span>@endsection

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Services Catalog</h1>
        <p class="page-subtitle">Manage your product catalog organized by groups</p>
    </div>
    <div class="btn-group" style="display: flex; gap: 8px;">
        <a href="{{ route('admin.services.servers') }}" class="btn btn-outline-primary">🖥️ Servers</a>
        <a href="{{ route('admin.services.groups') }}" class="btn btn-outline-primary">📁 Groups</a>
        <a href="{{ route('admin.services.create') }}" class="btn btn-primary">➕ Add Service</a>
    </div>
</div>

@foreach($groups as $group)
<div class="dash-card" style="margin-bottom: 1.5rem; padding: 0; overflow: hidden;">
    <div class="dash-card-head" style="padding: 1.25rem 1.25rem 0;">
        <h3>{{ $group->icon }} {{ $group->name }}</h3>
        <span class="pill pill-info">{{ $group->services_count }} service(s)</span>
    </div>
    <div style="padding: 0;">
        @if($group->services->count() > 0)
        <table class="table" style="margin: 0;">
            <thead>
                <tr>
                    <th>Service</th>
                    <th>Pricing</th>
                    <th>Billing</th>
                    <th>Features</th>
                    <th>Status</th>
                    <th class="text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($group->services as $service)
                <tr>
                    <td>
                        <strong>{{ $service->name }}</strong>
                        @if($service->description)
                            <div style="font-size: 0.8rem; color: var(--jv-gray-500);">{{ Str::limit($service->description, 60) }}</div>
                        @endif
                    </td>
                    <td>
                        @if($service->is_free)
                            <span class="pill pill-ok">Free</span>
                        @elseif($service->pricing)
                            <strong>{{ jv_format_money($service->pricing['monthly'] ?? $service->pricing['annually'] ?? $service->amount) }}</strong>
                            <small style="display: block; color: var(--jv-gray-500);">{{ count($service->pricing) }} cycles</small>
                        @else
                            <strong>{{ jv_format_money($service->amount) }}</strong>
                        @endif
                    </td>
                    <td><span class="badge badge-gray">{{ ucfirst($service->billing_cycle) }}</span></td>
                    <td>
                        @if(is_array($service->features) && count($service->features) > 0)
                            <span class="pill pill-mute">{{ count($service->features) }} features</span>
                        @else — @endif
                    </td>
                    <td>
                        <span class="pill pill-{{ $service->is_active ? 'ok' : 'mute' }}">
                            {{ $service->is_active ? 'Active' : 'Hidden' }}
                        </span>
                    </td>
                    <td class="text-center">
                        <div class="btn-group" style="justify-content: center; gap: 4px;">
                            <a href="{{ route('admin.services.show', $service) }}" class="btn btn-sm btn-outline-primary">👁️</a>
                            <a href="{{ route('admin.services.edit', $service) }}" class="btn btn-sm btn-outline-primary">✏️</a>
                            <form action="{{ route('admin.services.destroy', $service) }}" method="POST" style="display: inline;"
                                onsubmit="return confirm('Remove {{ $service->name }} from catalog? Existing orders are preserved.')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger">🗑️</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
        <div style="padding: 32px; text-align: center; color: var(--jv-gray-500);">
            No services in this group yet.
            <a href="{{ route('admin.services.create') }}">Add one</a>
        </div>
        @endif
    </div>
</div>
@endforeach

@if($groups->isEmpty())
<div class="empty-state" style="padding: 60px;">
    <div class="empty-state-icon">📦</div>
    <div class="empty-state-title">No service groups yet</div>
    <p class="empty-state-desc">Create service groups first, then add services to each group.</p>
    <a href="{{ route('admin.services.groups') }}" class="btn btn-primary">Manage Groups</a>
</div>
@endif
@endsection
