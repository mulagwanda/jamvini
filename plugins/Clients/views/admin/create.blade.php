@extends('themes.default::layouts.admin')

@section('title', 'Add New Client')
@section('breadcrumbs')<a href="{{ route('admin.clients.index') }}">Clients</a> <span class="separator">/</span> <span class="current">Add New</span>@endsection

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Add New Client</h1>
        <p class="page-subtitle">Create a complete hosting customer profile</p>
    </div>
    <a href="{{ route('admin.clients.index') }}" class="btn btn-outline-primary">Back to Clients</a>
</div>

<form action="{{ route('admin.clients.store') }}" method="POST">
    @csrf

    @include('plugins.Clients::admin.partials.form')

    <div style="display: flex; gap: 12px; justify-content: flex-end; margin-top: 1.5rem;">
        <a href="{{ route('admin.clients.index') }}" class="btn btn-outline-danger">Cancel</a>
        <button type="submit" class="btn btn-primary btn-lg">Create Client</button>
    </div>
</form>
@endsection
