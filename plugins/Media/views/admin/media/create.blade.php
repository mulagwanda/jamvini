@extends('themes.default::layouts.admin')

@section('title', 'Upload Media')
@section('breadcrumbs')<a href="{{ route('admin.media.index') }}">Media</a> <span class="separator">/</span> <span class="current">Upload</span>@endsection

@section('content')
<div class="page-header"><h1 class="page-title">Upload Files</h1><p class="page-subtitle">Upload multiple files at once</p></div>

<form action="{{ route('admin.media.store') }}" method="POST" enctype="multipart/form-data">
    @csrf
    <div class="card">
        <div class="card-body">
            <div class="form-group">
                <label class="form-label" for="folder">Folder</label>
                <select id="folder" name="folder" class="form-select">
                    <option value="general">General</option>
                    @foreach($folders as $f)
                        <option value="{{ $f }}">{{ $f }}</option>
                    @endforeach
                    <option value="__new__">+ Create New Folder</option>
                </select>
            </div>
            <div class="form-group" id="newFolderGroup" style="display: none;">
                <label class="form-label" for="new_folder">New Folder Name</label>
                <input type="text" id="new_folder" name="new_folder" class="form-input" placeholder="my-folder">
                <div class="form-hint">Use a short name such as blog, products, invoices, or social-media.</div>
            </div>
            <div class="form-group">
                <label class="form-label">Files</label>
                <div class="file-upload">
                    <div class="file-upload-icon">📁</div>
                    <div class="file-upload-text"><strong>Click to browse</strong> or drag and drop</div>
                    <input type="file" name="files[]" multiple style="display: none;">
                </div>
            </div>
        </div>
    </div>
    <div style="display: flex; gap: 12px; justify-content: flex-end; margin-top: 16px;">
        <a href="{{ route('admin.media.index') }}" class="btn btn-outline-danger">Cancel</a>
        <button type="submit" class="btn btn-primary btn-lg">📤 Upload</button>
    </div>
</form>
@endsection

@push('scripts')
<script>
const folderSelect = document.getElementById('folder');
const newFolderGroup = document.getElementById('newFolderGroup');
const newFolderInput = document.getElementById('new_folder');

function toggleNewFolder() {
    const isNew = folderSelect?.value === '__new__';
    if (newFolderGroup) newFolderGroup.style.display = isNew ? 'block' : 'none';
    if (newFolderInput) newFolderInput.required = isNew;
}

folderSelect?.addEventListener('change', toggleNewFolder);
toggleNewFolder();
</script>
@endpush
