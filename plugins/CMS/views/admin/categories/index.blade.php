@extends('themes.default::layouts.admin')

@section('title', 'Categories')
@section('breadcrumbs')<span class="current">Categories</span>@endsection

@section('content')
<div class="page-header">
    <div style="display: flex; justify-content: space-between;">
        <div><h1 class="page-title">Categories</h1><p class="page-subtitle">Organize posts and pages</p></div>
        <a href="{{ route('admin.cms.categories.create') }}" class="btn btn-primary">➕ New Category</a>
    </div>
</div>

<div class="card">
    <div class="card-body" style="padding: 0;">
        @if($categories->count() > 0)
        <table class="table">
            <thead><tr><th>Name</th><th>Slug</th><th>Type</th><th>Actions</th></tr></thead>
            <tbody>
                @foreach($categories as $cat)
                <tr>
                    <td><strong>{{ $cat->name }}</strong></td>
                    <td><code>{{ $cat->slug }}</code></td>
                    <td><span class="badge badge-info">{{ $cat->type }}</span></td>
                    <td>
                        <a href="{{ route('admin.cms.categories.edit', $cat) }}" class="btn btn-sm btn-outline-primary">✏️</a>
                        <form action="{{ route('admin.cms.categories.destroy', $cat) }}" method="POST" style="display: inline;"
                              data-confirm="Delete this category?" data-danger="true">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger">🗑️</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        {{ $categories->links() }}
        @else
        <div class="empty-state"><div class="empty-state-icon">🏷️</div><div class="empty-state-title">No categories</div></div>
        @endif
    </div>
</div>
@endsection