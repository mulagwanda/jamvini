@extends('themes.default::layouts.admin')

@section('title', 'Posts')
@section('breadcrumbs')<span class="current">Posts</span>@endsection

@section('content')
<div class="page-header">
    <div style="display: flex; justify-content: space-between;">
        <div><h1 class="page-title">Posts</h1><p class="page-subtitle">Manage blog posts and articles</p></div>
        <a href="{{ route('admin.cms.posts.create') }}" class="btn btn-primary">➕ New Post</a>
    </div>
</div>

<div class="card">
    <div class="card-body" style="padding: 0;">
        @if($posts->count() > 0)
        <table class="table">
            <thead><tr><th>Title</th><th>Categories</th><th>Status</th><th>Published</th><th>Actions</th></tr></thead>
            <tbody>
                @foreach($posts as $post)
                <tr>
                    <td><strong>{{ $post->title }}</strong></td>
                    <td>
                        @foreach($post->categories as $cat)
                            <span class="badge badge-info">{{ $cat->name }}</span>
                        @endforeach
                    </td>
                    <td><span class="badge badge-{{ $post->status === 'published' ? 'success' : 'gray' }}">{{ ucfirst($post->status) }}</span></td>
                    <td>{{ $post->published_at ? $post->published_at->format('M d, Y') : '—' }}</td>
                    <td>
                        <a href="{{ route('admin.cms.posts.preview', $post) }}" target="_blank" class="btn btn-sm btn-outline-primary">👁️</a>
                        <a href="{{ route('admin.cms.posts.edit', $post) }}" class="btn btn-sm btn-outline-primary">✏️</a>
                        <form action="{{ route('admin.cms.posts.destroy', $post) }}" method="POST" style="display: inline;"
                              data-confirm="Delete this post?" data-danger="true">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger">🗑️</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        {{ $posts->links() }}
        @else
        <div class="empty-state">
            <div class="empty-state-icon">📝</div>
            <div class="empty-state-title">No posts yet</div>
            <a href="{{ route('admin.cms.posts.create') }}" class="btn btn-primary">Write First Post</a>
        </div>
        @endif
    </div>
</div>
@endsection
