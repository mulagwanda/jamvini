@extends('themes.default::layouts.admin')

@section('title', 'New Form')
@section('breadcrumbs')<a href="{{ route('admin.forms.index') }}">Forms</a> <span class="separator">/</span> <span class="current">New</span>@endsection

@section('content')
<div class="page-header"><h1 class="page-title">New Form</h1></div>
<form action="{{ route('admin.forms.store') }}" method="POST">
    @csrf
    <div class="card">
        <div class="card-body">
            <div class="form-group"><label class="form-label">Form Title</label><input type="text" name="title" class="form-input" required></div>
            <div class="form-group"><label class="form-label">Send To (Email)</label><input type="email" name="recipient_email" class="form-input" placeholder="admin@example.com"></div>
            <div class="form-group"><label class="form-label">Success Message</label><input type="text" name="success_message" class="form-input" value="Thank you! Your message has been sent."></div>
            <div class="form-group">
                <label class="form-label">Fields (JSON)</label>
                <textarea name="fields" class="form-textarea" rows="6" placeholder='[{"name":"name","label":"Your Name","type":"text","required":true},{"name":"email","label":"Email","type":"email","required":true},{"name":"message","label":"Message","type":"textarea"}]'>[{"name":"name","label":"Your Name","type":"text","required":true},{"name":"email","label":"Email","type":"email","required":true},{"name":"message","label":"Message","type":"textarea"}]</textarea>
                <div class="form-hint">Types: text, email, textarea, number, select</div>
            </div>
        </div>
    </div>
    <div style="display: flex; gap: 12px; justify-content: flex-end; margin-top: 16px;">
        <a href="{{ route('admin.forms.index') }}" class="btn btn-outline-danger">Cancel</a>
        <button type="submit" class="btn btn-primary btn-lg">✅ Create Form</button>
    </div>
</form>
@endsection