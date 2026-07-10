@extends('themes.default::layouts.admin')

@section('title', 'Forms')
@section('breadcrumbs')<span class="current">Forms</span>@endsection

@section('content')
<div class="page-header">
    <div style="display: flex; justify-content: space-between;">
        <div><h1 class="page-title">Contact Forms</h1><p class="page-subtitle">Build and manage forms. Use shortcode <code>[form id="1"]</code> in pages.</p></div>
        <a href="{{ route('admin.forms.create') }}" class="btn btn-primary">➕ New Form</a>
    </div>
</div>

<div class="card">
    <div class="card-body" style="padding: 0;">
        @if($forms->count() > 0)
        <table class="table">
            <thead><tr><th>Form</th><th>Shortcode</th><th>Entries</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
                @foreach($forms as $form)
                <tr>
                    <td><strong>{{ $form->title }}</strong></td>
                    <td><code>[form id="{{ $form->id }}"]</code></td>
                    <td><span class="badge badge-info">{{ $form->submissions_count }}</span></td>
                    <td><span class="badge badge-{{ $form->is_active ? 'success' : 'gray' }}">{{ $form->is_active ? 'Active' : 'Inactive' }}</span></td>
                    <td>
                        <a href="{{ route('admin.forms.submissions', $form) }}" class="btn btn-sm btn-outline-info">📩</a>
                        <a href="{{ route('admin.forms.edit', $form) }}" class="btn btn-sm btn-outline-primary">✏️</a>
                        <form action="{{ route('admin.forms.destroy', $form) }}" method="POST" style="display: inline;" data-confirm="Delete this form?" data-danger="true">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger">🗑️</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
        <div class="empty-state"><div class="empty-state-icon">📋</div><div class="empty-state-title">No forms yet</div><a href="{{ route('admin.forms.create') }}" class="btn btn-primary">Create First Form</a></div>
        @endif
    </div>
</div>
@endsection