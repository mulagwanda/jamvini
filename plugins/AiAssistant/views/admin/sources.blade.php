@extends('themes.default::layouts.admin')

@section('title', 'AI Sources')
@section('breadcrumbs')<a href="{{ route('admin.ai-assistant.index') }}">AI Assistant</a> <span class="separator">/</span> <span class="current">Sources</span>@endsection

@section('content')
<div class="page-header"><h1 class="page-title">Knowledge Sources</h1></div>

<div class="dash-card" style="margin-bottom:1.5rem;">
    <div class="dash-card-head"><h3>Add Source</h3></div>
    <form action="{{ route('admin.ai-assistant.sources.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="form-grid">
            <div class="form-group"><label class="form-label">Type</label><select name="type" class="form-select" id="sourceType"><option value="manual">Manual Text</option><option value="url">Web Link</option><option value="file">File</option></select></div>
            <div class="form-group"><label class="form-label">Title</label><input name="title" class="form-input" required></div>
        </div>
        <div class="form-group source-field" data-source="url" style="display:none;"><label class="form-label">URL</label><input type="url" name="url" class="form-input" placeholder="https://example.com/help"></div>
        <div class="form-group source-field" data-source="file" style="display:none;"><label class="form-label">File</label><input type="file" name="file" class="form-input" accept=".txt,.md,.html,.htm,.csv,.json"><div class="form-hint">This version indexes text files. PDF/DOC parsing can be added next.</div></div>
        <div class="form-group source-field" data-source="manual"><label class="form-label">Content</label><textarea name="content" class="form-textarea" rows="5"></textarea></div>
        <div style="display:flex;justify-content:flex-end;"><button class="btn btn-primary">Add and Index</button></div>
    </form>
</div>

<div class="dash-card">
    <div class="dash-card-head"><h3>Sources</h3></div>
    <table class="data-table">
        <thead><tr><th>Title</th><th>Type</th><th>Status</th><th>Indexed</th><th></th></tr></thead>
        <tbody>
            @forelse($sources as $source)
                <tr>
                    <td><strong>{{ $source->title }}</strong><br><small>{{ $source->url ?: ($source->metadata['original_name'] ?? '') }}</small></td>
                    <td>{{ ucfirst($source->type) }}</td>
                    <td><span class="pill pill-{{ $source->status === 'ready' ? 'ok' : 'warn' }}">{{ ucfirst($source->status) }}</span></td>
                    <td>{{ $source->last_indexed_at?->format('M d, Y H:i') ?? '-' }}</td>
                    <td style="text-align:right;">
                        <form action="{{ route('admin.ai-assistant.sources.reindex', $source) }}" method="POST" style="display:inline;">@csrf<button class="btn btn-sm btn-outline-primary">Re-index</button></form>
                        <form action="{{ route('admin.ai-assistant.sources.destroy', $source) }}" method="POST" style="display:inline;" data-confirm="Remove this source?" data-danger="true">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger">Delete</button></form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" style="padding:24px;text-align:center;color:var(--jv-gray-500);">No sources yet. Published Knowledge Base articles are also searched automatically.</td></tr>
            @endforelse
        </tbody>
    </table>
    {{ $sources->links() }}
</div>
@endsection

@push('scripts')
<script>
document.getElementById('sourceType')?.addEventListener('change', function () {
    document.querySelectorAll('.source-field').forEach((field) => {
        field.style.display = field.dataset.source === this.value ? '' : 'none';
    });
});
</script>
@endpush
