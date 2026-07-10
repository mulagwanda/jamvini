@extends('themes.default::layouts.admin')

@section('title', 'Plugin Editor')
@section('breadcrumbs')<a href="{{ route('admin.plugins.index') }}">Plugins</a> <span class="separator">/</span> <span class="current">Editor</span>@endsection

@section('content')
<style>
.plugin-editor { display:grid; grid-template-columns:320px minmax(0,1fr); gap:18px; align-items:start; }
.plugin-file-list { max-height:70vh; overflow:auto; display:grid; gap:4px; }
.plugin-file-link { display:flex; justify-content:space-between; gap:10px; padding:9px 10px; border-radius:6px; color:var(--jv-gray-700); text-decoration:none; font-size:.88rem; }
.plugin-file-link.active { background:#eef2ff; color:var(--jv-primary); font-weight:800; }
.plugin-code-editor { min-height:64vh; font-family:ui-monospace,SFMono-Regular,Menlo,Consolas,monospace; font-size:.9rem; line-height:1.55; tab-size:4; }
@media (max-width: 980px) { .plugin-editor { grid-template-columns:1fr; } }
</style>

<div class="page-header">
    <div>
        <h1 class="page-title">Plugin Editor</h1>
        <p class="page-subtitle">{{ $manifest['name'] ?? $slug }} · edit safe plugin files without leaving JamVini.</p>
    </div>
    <a href="{{ route('admin.plugins.index') }}" class="btn btn-outline-primary">{{ jv_icon('arrow-left', '', 16) }} Plugins</a>
</div>

<div class="plugin-editor">
    <aside class="card">
        <div class="card-header"><h3 class="card-title">{{ jv_icon('folder-code', '', 18) }} Files</h3></div>
        <div class="card-body plugin-file-list">
            @foreach($files as $file)
                <a class="plugin-file-link {{ $selected === $file['path'] ? 'active' : '' }}" href="{{ route('admin.plugins.editor', ['slug' => $slug, 'file' => $file['path']]) }}">
                    <span>{{ $file['path'] }}</span>
                    <small>{{ number_format($file['size'] / 1024, 1) }} KB</small>
                </a>
            @endforeach
        </div>
    </aside>

    <form action="{{ route('admin.plugins.editor.update', $slug) }}" method="POST" class="card">
        @csrf
        <input type="hidden" name="file" value="{{ $selected }}">
        <div class="card-header">
            <h3 class="card-title">{{ jv_icon('file-code-2', '', 18) }} {{ $selected }}</h3>
            <span style="color:var(--jv-gray-500);font-size:.85rem;">Text files only · max 600 KB</span>
        </div>
        <div class="card-body">
            <textarea name="content" class="form-textarea plugin-code-editor" spellcheck="false">{{ $content }}</textarea>
        </div>
        <div class="card-footer" style="display:flex;justify-content:flex-end;gap:8px;">
            <a href="{{ route('admin.plugins.editor', ['slug' => $slug, 'file' => $selected]) }}" class="btn btn-outline-primary">{{ jv_icon('rotate-ccw', '', 16) }} Reload</a>
            <button class="btn btn-primary">{{ jv_icon('save', '', 16) }} Save File</button>
        </div>
    </form>
</div>
@endsection
