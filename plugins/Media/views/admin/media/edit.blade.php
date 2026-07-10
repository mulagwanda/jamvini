@extends('themes.default::layouts.admin')

@section('title', 'Edit File')
@section('breadcrumbs')<a href="{{ route('admin.media.index') }}">Media</a> <span class="separator">/</span> <span class="current">Edit</span>@endsection

@section('content')
<div class="page-header"><h1 class="page-title">Edit File</h1></div>

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px;">
    <div class="card">
        <div class="card-header"><h3 class="card-title">🖼️ Preview</h3></div>
        <div class="card-body" style="text-align: center;">
            @if($medium->is_image)
                <img src="{{ $medium->url }}" style="max-width: 100%; max-height: 400px; border-radius: 8px;">
            @else
                <div style="font-size: 80px;">📄</div>
            @endif
        </div>
    </div>
    
    <div class="card">
        <div class="card-header"><h3 class="card-title">📝 Details</h3></div>
        <div class="card-body">
            <form action="{{ route('admin.media.update', $medium) }}" method="POST">
                @csrf @method('PUT')
                <table class="table">
                    <tr><td style="font-weight: 600; width: 120px;">Filename</td><td>{{ $medium->original_name }}</td></tr>
                    <tr><td style="font-weight: 600;">Type</td><td>{{ $medium->mime_type }}</td></tr>
                    <tr><td style="font-weight: 600;">Size</td><td>{{ $medium->size_for_humans }}</td></tr>
                    <tr><td style="font-weight: 600;">Uploaded</td><td>{{ $medium->created_at->format('M d, Y H:i') }}</td></tr>
                </table>
                
                <div class="form-group">
                    <label class="form-label" for="alt_text">Alt Text</label>
                    <input type="text" id="alt_text" name="alt_text" class="form-input" value="{{ old('alt_text', $medium->alt_text) }}">
                </div>
                <div class="form-group">
                    <label class="form-label" for="folder">Folder</label>
                    <input type="text" id="folder" name="folder" class="form-input" value="{{ old('folder', $medium->folder) }}">
                </div>
                
                <div style="display: flex; gap: 12px; justify-content: flex-end;">
                    <a href="{{ route('admin.media.index') }}" class="btn btn-outline-danger">Back</a>
                    <button type="submit" class="btn btn-primary">💾 Save</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
