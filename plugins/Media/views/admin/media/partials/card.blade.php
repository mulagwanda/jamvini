<div class="media-card" data-media-id="{{ $file->id }}" data-folder="{{ $file->folder }}">
    <div class="media-thumb">
        <span class="media-type">{{ $file->is_image ? 'Image' : strtoupper(pathinfo($file->filename, PATHINFO_EXTENSION) ?: 'File') }}</span>
        @if($file->is_image)
            <img src="{{ $file->thumbnail_url }}" alt="{{ $file->alt_text ?? $file->original_name }}" onclick="previewImage('{{ $file->url }}')">
        @else
            <div style="font-size:2.4rem;color:var(--jv-gray-400);">FILE</div>
        @endif
    </div>
    <div class="media-card-body">
        <div class="media-name" title="{{ $file->original_name }}">{{ $file->original_name }}</div>
        <div class="media-meta">{{ $file->size_for_humans }} · {{ ucfirst($file->source ?? 'upload') }} · {{ $file->created_at->format('M d, Y') }}</div>
        @if(($file->attribution['photographer_name'] ?? null))
            <div class="media-attribution">Photo by {{ $file->attribution['photographer_name'] }}</div>
        @endif
        <div class="media-actions">
            <button class="btn btn-sm btn-outline-primary" type="button" onclick="copyToClipboard('{{ $file->url }}')">Copy</button>
            <a href="{{ route('admin.media.edit', $file) }}" class="btn btn-sm btn-outline-primary">Edit</a>
            <form action="{{ route('admin.media.destroy', $file) }}" method="POST" data-media-delete>
                @csrf @method('DELETE')
                <button class="btn btn-sm btn-outline-danger" style="width:100%;">Delete</button>
            </form>
        </div>
    </div>
</div>
