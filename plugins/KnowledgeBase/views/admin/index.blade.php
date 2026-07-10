@extends('themes.default::layouts.admin')

@section('title', 'Knowledge Base')
@section('breadcrumbs')<span class="current">Knowledge Base</span>@endsection

@section('content')
<div class="page-header">
    <div style="display: flex; justify-content: space-between;">
        <div>
            <h1 class="page-title">Knowledge Base</h1>
            <p class="page-subtitle">Manage help articles and FAQs</p>
        </div>
        <a href="{{ route('admin.kb.create') }}" class="btn btn-primary">➕ New Article</a>
    </div>
</div>

<div class="card">
    <div class="card-body" style="padding: 0;">
        @if($articles->count() > 0)
        <table class="table">
            <thead><tr><th>Title</th><th>Category</th><th>Views</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
                @foreach($articles as $article)
                <tr>
                    <td><strong>{{ $article->title }}</strong></td>
                    <td><span class="badge badge-info">{{ $article->category }}</span></td>
                    <td>{{ $article->views }}</td>
                    <td><span class="badge badge-{{ $article->is_published ? 'success' : 'gray' }}">{{ $article->is_published ? 'Published' : 'Draft' }}</span></td>
                    <td>
                        <a href="{{ route('admin.kb.edit', $article) }}" class="btn btn-sm btn-outline-primary">✏️</a>
                        <form action="{{ route('admin.kb.destroy', $article) }}" method="POST" style="display: inline;"
                              data-confirm="Delete this article?" data-title="Delete" data-danger="true">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger">🗑️</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
        <div class="empty-state">
            <div class="empty-state-icon">📚</div>
            <div class="empty-state-title">No articles yet</div>
            <a href="{{ route('admin.kb.create') }}" class="btn btn-primary">Create First Article</a>
        </div>
        @endif
    </div>
</div>
@endsection