@extends('themes.default::layouts.admin')

@section('title', 'Edit - ' . $client->full_name)
@section('breadcrumbs')<a href="{{ route('admin.clients.index') }}">Clients</a> <span class="separator">/</span> <a href="{{ route('admin.clients.show', $client) }}">{{ $client->full_name }}</a> <span class="separator">/</span> <span class="current">Edit</span>@endsection

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Edit: {{ $client->full_name }}</h1>
        <p class="page-subtitle">Update account, billing, contact, and migration details</p>
    </div>
    <div style="display: flex; gap: 8px; flex-wrap: wrap;">
        <a href="{{ route('admin.clients.show', $client) }}" class="btn btn-outline-primary">View Profile</a>
        <form action="{{ route('admin.clients.destroy', $client) }}" method="POST" style="display: inline;" data-confirm="Delete {{ $client->full_name }}?" data-danger="true">
            @csrf @method('DELETE')
            <button class="btn btn-outline-danger">Delete</button>
        </form>
    </div>
</div>

<form action="{{ route('admin.clients.update', $client) }}" method="POST">
    @csrf @method('PUT')

    @include('plugins.Clients::admin.partials.form', ['client' => $client])

    <div style="display: flex; gap: 12px; justify-content: flex-end; margin-top: 1.5rem;">
        <a href="{{ route('admin.clients.show', $client) }}" class="btn btn-outline-danger">Cancel</a>
        <button type="submit" class="btn btn-primary btn-lg">Update Client</button>
    </div>
</form>
@endsection
