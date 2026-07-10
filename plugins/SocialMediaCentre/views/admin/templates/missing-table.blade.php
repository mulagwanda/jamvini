@extends('themes.default::layouts.admin')

@section('title', 'Post Templates')
@section('breadcrumbs')<a href="{{ route('admin.social.index') }}">Social Centre</a> <span class="separator">/</span> <span class="current">Templates</span>@endsection

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Post Templates</h1>
        <p class="page-subtitle">Templates are installed, but the database table has not been created yet.</p>
    </div>
</div>

<div class="dash-card" style="max-width:760px;">
    <h3 style="margin-top:0;">Database update needed</h3>
    <p style="color:var(--jv-gray-600);line-height:1.6;">
        Run database migrations from <strong>Admin System</strong>. JamVini will create the Social Media Centre templates table and add the starter hosting templates.
    </p>
    <a href="{{ route('admin.system.index') }}" class="btn btn-primary">Open System Tools</a>
</div>
@endsection
