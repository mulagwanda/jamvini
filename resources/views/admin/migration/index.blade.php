@extends('themes.default::layouts.admin')

@section('title', 'JV Migration')
@section('breadcrumbs')<span class="current">JV Migration</span>@endsection

@section('content')
<style>
.migration-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(320px,1fr)); gap:18px; align-items:start; }
.migration-section-list { display:grid; gap:10px; margin-top:12px; }
.migration-section-list label { display:flex; align-items:center; gap:10px; padding:10px 12px; border:1px solid var(--jv-gray-200); border-radius:8px; background:#fff; font-weight:700; }
.migration-note { border:1px solid #fde68a; background:#fffbeb; color:#92400e; border-radius:8px; padding:12px 14px; font-size:.92rem; }
</style>

<div class="page-header">
    <div>
        <h1 class="page-title">JamVini Migration</h1>
        <p class="page-subtitle">Move theme content, CMS pages, menus, clients, and safe settings from one JamVini install to another.</p>
    </div>
</div>

<div class="migration-grid">
    <div class="card">
        <div class="card-header"><h3 class="card-title">{{ jv_icon('download-cloud', '', 18) }} Export From This Install</h3></div>
        <div class="card-body">
            <form action="{{ route('admin.migration.export') }}" method="GET">
                <div class="migration-note">Sensitive settings such as passwords, tokens, private keys, API keys, and certificates are skipped automatically.</div>
                <div class="migration-section-list">
                    @foreach($defaultSections as $section)
                        <label>
                            <input type="checkbox" name="sections[]" value="{{ $section }}" checked>
                            <span>{{ str($section)->headline() }}</span>
                        </label>
                    @endforeach
                </div>
                <div style="display:flex;justify-content:flex-end;margin-top:16px;">
                    <button class="btn btn-primary">{{ jv_icon('download', '', 16) }} Download JV Package</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header"><h3 class="card-title">{{ jv_icon('upload-cloud', '', 18) }} Import Into This Install</h3></div>
        <div class="card-body">
            <form action="{{ route('admin.migration.import') }}" method="POST" enctype="multipart/form-data" data-confirm="Import this JamVini migration package? Existing matching pages, menus, settings, and clients may be updated.">
                @csrf
                <div class="form-group">
                    <label class="form-label">JamVini package JSON</label>
                    <input type="file" name="package" class="form-input" accept="application/json,.json,.txt" required>
                    <div class="form-hint">Use a package exported from another JamVini installation or a trusted staging site.</div>
                </div>
                <div class="migration-note">Imports update matching pages by slug, menus by location, settings by key, and clients by email.</div>
                <div style="display:flex;justify-content:flex-end;margin-top:16px;">
                    <button class="btn btn-primary">{{ jv_icon('upload', '', 16) }} Import JV Package</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
