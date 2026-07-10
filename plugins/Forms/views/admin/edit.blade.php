@extends('themes.default::layouts.admin')

@section('title', 'Edit Form')
@section('breadcrumbs')<a href="{{ route('admin.forms.index') }}">Forms</a> <span class="separator">/</span> <span class="current">Edit</span>@endsection

@section('content')
<div class="page-header"><h1 class="page-title">Edit: {{ $form->title }}</h1></div>
<form action="{{ route('admin.forms.update', $form) }}" method="POST">
    @csrf @method('PUT')
    <div class="card">
        <div class="card-body">
            <div class="form-group"><label class="form-label">Form Title</label><input type="text" name="title" class="form-input" value="{{ $form->title }}" required></div>
            <div class="form-group"><label class="form-label">Send To (Email)</label><input type="email" name="recipient_email" class="form-input" value="{{ $form->recipient_email }}"></div>
            <div class="form-group"><label class="form-label">Success Message</label><input type="text" name="success_message" class="form-input" value="{{ $form->success_message }}"></div>
            <div class="form-group"><label class="toggle-switch"><input type="checkbox" name="is_active" value="1" {{ $form->is_active ? 'checked' : '' }}><span class="toggle-slider"></span><span>Active</span></label></div>
            <div class="form-group"><label class="form-label">Fields (JSON)</label><textarea name="fields" class="form-textarea" rows="6">{{ json_encode($form->fields, JSON_PRETTY_PRINT) }}</textarea></div>
        </div>
    </div>
    <div style="display: flex; gap: 12px; justify-content: flex-end; margin-top: 16px;">
        <a href="{{ route('admin.forms.index') }}" class="btn btn-outline-danger">Cancel</a>
        <button type="submit" class="btn btn-primary btn-lg">💾 Update Form</button>
    </div>
</form>
@endsection