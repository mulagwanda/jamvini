@extends('themes.default::layouts.admin')

@section('title', 'Media Library')
@section('breadcrumbs')<span class="current">Media Library</span>@endsection

@push('styles')
<style>
.media-toolbar { display:flex; justify-content:space-between; align-items:center; gap:16px; flex-wrap:wrap; margin-bottom:18px; }
.media-drop { border:2px dashed var(--jv-gray-300); border-radius:18px; padding:44px; background:linear-gradient(180deg,#fff,#f8fafc); cursor:pointer; transition:.18s ease; display:grid; grid-template-columns:auto 1fr auto; align-items:center; gap:22px; margin-bottom:18px; min-height:180px; }
.media-drop:hover, .media-drop.is-dragging { border-color:var(--jv-primary); background:#f8fbff; box-shadow:0 12px 30px rgba(15,23,42,.06); }
.media-drop-icon { width:78px; height:78px; border-radius:18px; display:grid; place-items:center; color:#fff; background:linear-gradient(135deg,var(--jv-primary),#22c55e); font-size:1.9rem; }
.media-stat-grid { display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:12px; margin-bottom:18px; }
.media-stat { border:1px solid var(--jv-gray-200); background:#fff; border-radius:12px; padding:14px 16px; }
.media-stat strong { display:block; font-size:1.25rem; color:var(--jv-gray-900); }
.media-folders { display:flex; gap:8px; flex-wrap:wrap; margin-bottom:18px; }
.media-folder { display:inline-flex; align-items:center; gap:7px; border:1px solid var(--jv-gray-200); background:#fff; border-radius:999px; padding:8px 12px; color:var(--jv-gray-700); font-weight:700; font-size:.84rem; text-decoration:none; }
.media-folder.active { background:var(--jv-primary); color:#fff; border-color:var(--jv-primary); }
.media-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(180px,1fr)); gap:16px; }
.media-card { border:1px solid var(--jv-gray-200); border-radius:14px; overflow:hidden; background:#fff; transition:.18s ease; }
.media-card:hover { transform:translateY(-2px); box-shadow:0 14px 34px rgba(15,23,42,.09); }
.media-thumb { height:150px; background:#f1f5f9; display:grid; place-items:center; position:relative; overflow:hidden; }
.media-thumb img { width:100%; height:100%; object-fit:cover; display:block; }
.media-type { position:absolute; top:10px; left:10px; border-radius:999px; padding:4px 8px; background:rgba(15,23,42,.72); color:#fff; font-size:.68rem; font-weight:800; }
.media-card-body { padding:12px; }
.media-name { font-weight:800; color:var(--jv-gray-900); white-space:nowrap; overflow:hidden; text-overflow:ellipsis; margin-bottom:5px; }
.media-meta { color:var(--jv-gray-500); font-size:.76rem; margin-bottom:10px; }
.media-actions { display:grid; grid-template-columns:repeat(3,1fr); gap:6px; }
.media-actions .btn { padding:6px 8px; font-size:.78rem; justify-content:center; }
.media-progress { display:none; margin-bottom:18px; border:1px solid var(--jv-gray-200); border-radius:12px; padding:12px; background:#fff; }
.media-tool-grid { display:grid; grid-template-columns:1fr 1fr; gap:14px; margin-bottom:18px; }
.media-tool { border:1px solid var(--jv-gray-200); border-radius:14px; background:#fff; padding:16px; }
.media-tool h3 { margin:0 0 6px; font-size:1rem; }
.media-tool p { margin:0 0 12px; color:var(--jv-gray-500); font-size:.86rem; }
.media-unsplash-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(170px,1fr)); gap:12px; margin-bottom:18px; }
.media-unsplash-card { border:1px solid var(--jv-gray-200); border-radius:14px; overflow:hidden; background:#fff; }
.media-unsplash-card img { width:100%; height:130px; object-fit:cover; display:block; }
.media-unsplash-card-body { padding:10px; }
.media-attribution { font-size:.72rem; color:var(--jv-gray-500); margin:6px 0 10px; }
.media-loader { display:none; align-items:center; gap:8px; color:var(--jv-gray-600); font-size:.86rem; margin-top:10px; }
.media-loader.active { display:flex; }
.media-spinner { width:16px; height:16px; border-radius:50%; border:2px solid var(--jv-gray-200); border-top-color:var(--jv-primary); animation:mediaSpin .8s linear infinite; }
.media-status { display:none; margin-bottom:18px; border-radius:12px; padding:12px 14px; font-weight:700; }
.media-status.ok { display:block; background:#ecfdf5; color:#047857; border:1px solid #bbf7d0; }
.media-status.error { display:block; background:#fef2f2; color:#b91c1c; border:1px solid #fecaca; }
.media-search { display:flex; gap:8px; align-items:center; min-width:min(100%,420px); }
.media-search .form-input { min-width:240px; }
.media-load-more { display:none; width:100%; margin-top:2px; }
.media-load-more.is-visible { display:flex; justify-content:center; }
.empty-state.is-hidden { display:none; }
@keyframes mediaSpin { to { transform:rotate(360deg); } }
@media (max-width:760px) { .media-drop { grid-template-columns:1fr; text-align:center; padding:30px 20px; } .media-drop-icon { margin:0 auto; } .media-stat-grid { grid-template-columns:1fr; } .media-search, .media-search .form-input { width:100%; min-width:0; } }
@media (max-width:980px) { .media-tool-grid { grid-template-columns:1fr; } }
</style>
@endpush

@section('content')
@php
    $imageCount = $media->getCollection()->where('is_image', true)->count();
    $folderCount = count($folders);
@endphp

<div class="page-header">
    <div>
        <h1 class="page-title">Media Library</h1>
        <p class="page-subtitle">A central home for files used across CMS, themes, AI, marketing, and future plugins.</p>
    </div>
    <div style="display:flex;gap:10px;flex-wrap:wrap;">
        <a href="{{ route('admin.media.create') }}" class="btn btn-outline-primary">Upload Page</a>
        <button class="btn btn-primary" type="button" onclick="document.getElementById('fileInput').click()">Upload Files</button>
    </div>
</div>

<form action="{{ route('admin.media.store') }}" method="POST" enctype="multipart/form-data" style="display:none;" id="uploadForm">
    @csrf
    <input type="hidden" name="folder" value="{{ $folder }}">
    <input type="file" id="fileInput" name="files[]" multiple accept="image/*,video/*,application/pdf,.doc,.docx,.xls,.xlsx,.zip,.txt,.csv,.json" onchange="handleFiles(this)" style="display:none;">
</form>

<div class="media-stat-grid">
    <div class="media-stat"><span class="stat-label">Current Folder</span><strong>{{ ucfirst($folder) }}</strong></div>
    <div class="media-stat"><span class="stat-label">Files Shown</span><strong>{{ $media->count() }}</strong></div>
    <div class="media-stat"><span class="stat-label">Image Assets</span><strong>{{ $imageCount }}</strong></div>
</div>

<div id="mediaStatus" class="media-status"></div>

<div id="dropZone" class="media-drop"
     ondragover="event.preventDefault(); this.classList.add('is-dragging');"
     ondragleave="this.classList.remove('is-dragging');"
     ondrop="handleDrop(event); this.classList.remove('is-dragging');"
     onclick="document.getElementById('fileInput').click()">
    <div class="media-drop-icon">↑</div>
    <div>
        <h3 style="margin:0 0 6px;font-size:1.35rem;">Drop files here or browse</h3>
        <p style="margin:0;color:var(--jv-gray-500);font-size:.95rem;">This is the main Media Library workflow. Upload images, documents, archives and text files up to 10MB each.</p>
    </div>
    <button type="button" class="btn btn-primary btn-lg" onclick="event.stopPropagation();document.getElementById('fileInput').click()">Choose Files</button>
</div>

<div id="uploadProgress" class="media-progress">
    <div class="progress"><div id="progressBar" class="progress-bar primary" style="width:0%;"></div></div>
    <p id="progressText" style="text-align:center;margin:8px 0 0;font-size:.85rem;color:var(--jv-gray-500);"></p>
</div>

<div class="media-tool-grid">
    <div class="media-tool">
        <h3>Search Unsplash</h3>
        <p>Find stock images and import selected assets into Media Library with attribution metadata.</p>
        <form id="unsplashSearchForm" action="{{ route('admin.media.unsplash.search') }}" style="display:grid;grid-template-columns:1fr auto;gap:10px;">
            <input type="search" name="query" class="form-input" value="{{ request('unsplash_query') }}" placeholder="hosting, datacenter, business, cloud servers" required>
            <button class="btn btn-outline-primary">Search</button>
        </form>
        <div id="unsplashLoader" class="media-loader"><span class="media-spinner"></span><span>Searching Unsplash...</span></div>
        <div id="unsplashResults" class="media-unsplash-grid" style="margin-top:12px;">
            @foreach($unsplashResults as $photo)
                @include('plugins.Media::admin.media.partials.unsplash-card', ['photo' => $photo])
            @endforeach
        </div>
        <div id="unsplashLoadMoreWrap" class="media-load-more {{ ($unsplashMeta['has_more'] ?? false) ? 'is-visible' : '' }}">
            <button type="button" id="unsplashLoadMore" class="btn btn-outline-primary">Load More</button>
        </div>
    </div>

    <div class="media-tool">
        <h3>Generate With AI</h3>
        <p>Create promotional images and save them directly into the library.</p>
        <form id="aiGenerateForm" action="{{ route('admin.media.generate') }}" method="POST">
            @csrf
            <input type="hidden" name="folder" value="ai-generated">
            <div class="form-group"><textarea name="prompt" class="form-textarea" rows="4" placeholder="Modern web hosting promo banner, Tanzanite blue accents, clean datacenter background..." required></textarea></div>
            <button class="btn btn-primary" style="width:100%;">Generate Image</button>
        </form>
        <div id="aiLoader" class="media-loader"><span class="media-spinner"></span><span>Generating and saving image...</span></div>
    </div>
</div>

<div class="media-toolbar">
    <div class="media-folders">
        @forelse($folders as $f)
            <a href="{{ route('admin.media.index', ['folder' => $f]) }}" class="media-folder {{ $f === $folder ? 'active' : '' }}">Folder {{ $f }}</a>
        @empty
            <span class="media-folder active">Folder general</span>
        @endforelse
    </div>
    <form id="mediaSearchForm" class="media-search" action="{{ route('admin.media.index') }}">
        <input type="hidden" name="folder" value="{{ $folder }}">
        <input type="search" name="search" class="form-input" value="{{ $search ?? '' }}" placeholder="Search this folder...">
        <button class="btn btn-outline-primary" type="submit">Search</button>
    </form>
</div>

<div class="dash-card">
    <div class="media-grid" id="mediaGrid">
        @foreach($media as $file)
            @include('plugins.Media::admin.media.partials.card', ['file' => $file])
        @endforeach
    </div>
    <div class="empty-state {{ $media->count() > 0 ? 'is-hidden' : '' }}" id="mediaEmptyState">
        <div class="empty-state-icon">IMG</div>
        <div class="empty-state-title">No files in this folder</div>
        <p class="empty-state-desc">Upload assets here and reuse them across JamVini.</p>
    </div>
    <div id="mediaPagination">{{ $media->appends(['folder' => $folder, 'search' => $search ?? null])->links() }}</div>
</div>

<div id="previewModal" style="display:none;position:fixed;inset:0;background:rgba(15,23,42,.88);z-index:1200;align-items:center;justify-content:center;padding:24px;" onclick="this.style.display='none'">
    <img id="previewImage" src="" style="max-width:92vw;max-height:88vh;border-radius:12px;box-shadow:0 24px 80px rgba(0,0,0,.35);" onclick="event.stopPropagation()">
</div>
@endsection

@push('scripts')
<script>
const unsplashState = {
    query: @json(request('unsplash_query', '')),
    page: @json($unsplashMeta['page'] ?? 1),
    hasMore: @json($unsplashMeta['has_more'] ?? false),
};

function handleFiles(input) {
    if (input.files.length > 0) uploadFiles(input.files);
    input.value = '';
}

function handleDrop(event) {
    event.preventDefault();
    if (event.dataTransfer.files.length > 0) uploadFiles(event.dataTransfer.files);
}

async function uploadFiles(files) {
    const formData = new FormData();
    formData.append('_token', '{{ csrf_token() }}');
    formData.append('folder', '{{ $folder }}');

    for (const file of files) formData.append('files[]', file);

    document.getElementById('uploadProgress').style.display = 'block';
    document.getElementById('progressBar').style.width = '35%';
    document.getElementById('progressText').textContent = 'Uploading ' + files.length + ' file(s)...';

    try {
        const response = await fetch('{{ route('admin.media.store') }}', {
            method: 'POST',
            body: formData,
            headers: {'X-Requested-With': 'XMLHttpRequest'}
        });

        const data = await response.json();
        if (!response.ok) throw new Error(data.message || 'Upload failed.');
        document.getElementById('progressBar').style.width = '100%';
        document.getElementById('progressText').textContent = 'Upload complete.';
        appendMediaCards((data.files || []).map(file => file.html), data.folder);
        showMediaStatus(data.message || 'Upload complete.', 'ok');
        setTimeout(() => document.getElementById('uploadProgress').style.display = 'none', 900);
    } catch (error) {
        document.getElementById('progressBar').style.width = '0%';
        document.getElementById('progressText').textContent = 'Upload failed. Please try again.';
        showMediaStatus(error.message || 'Upload failed. Please try again.', 'error');
    }
}

document.getElementById('mediaSearchForm')?.addEventListener('submit', async function(event) {
    event.preventDefault();
    const grid = document.getElementById('mediaGrid');
    const empty = document.getElementById('mediaEmptyState');
    const pagination = document.getElementById('mediaPagination');
    grid.style.opacity = '.55';

    try {
        const params = new URLSearchParams(new FormData(this));
        const response = await fetch(this.action + '?' + params.toString(), {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        });
        const data = await response.json();
        if (!response.ok) throw new Error(data.message || 'Search failed.');
        grid.innerHTML = data.html || '';
        empty?.classList.toggle('is-hidden', Boolean(data.html));
        if (pagination) pagination.innerHTML = '';
    } catch (error) {
        showMediaStatus(error.message || 'Search failed.', 'error');
    } finally {
        grid.style.opacity = '1';
    }
});

document.getElementById('unsplashSearchForm')?.addEventListener('submit', async function(event) {
    event.preventDefault();
    const formData = new FormData(this);
    unsplashState.query = formData.get('query') || '';
    unsplashState.page = 1;
    await fetchUnsplashResults(1, false);
});

document.getElementById('unsplashLoadMore')?.addEventListener('click', async function() {
    if (!unsplashState.query || !unsplashState.hasMore) return;
    await fetchUnsplashResults(unsplashState.page + 1, true);
});

async function fetchUnsplashResults(page, append) {
    const loader = document.getElementById('unsplashLoader');
    const results = document.getElementById('unsplashResults');
    const loadMoreWrap = document.getElementById('unsplashLoadMoreWrap');
    const loadMoreButton = document.getElementById('unsplashLoadMore');

    loader.classList.add('active');
    if (loadMoreButton) {
        loadMoreButton.disabled = true;
        loadMoreButton.textContent = append ? 'Loading...' : 'Load More';
    }
    if (!append) results.innerHTML = '';

    try {
        const params = new URLSearchParams({ query: unsplashState.query, page });
        const response = await fetch(document.getElementById('unsplashSearchForm').action + '?' + params.toString(), { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
        const data = await response.json();
        if (!response.ok) throw new Error(data.message || 'Unsplash search failed.');
        if (append) {
            results.insertAdjacentHTML('beforeend', data.html || '');
        } else {
            results.innerHTML = data.html || '<div class="alert alert-warning" style="grid-column:1/-1;">No results found.</div>';
        }
        unsplashState.page = data.meta?.page || page;
        unsplashState.hasMore = Boolean(data.meta?.has_more);
        loadMoreWrap?.classList.toggle('is-visible', unsplashState.hasMore);
    } catch (error) {
        const errorHtml = '<div class="alert alert-danger" style="grid-column:1/-1;">' + escapeHtml(error.message) + '</div>';
        if (append) {
            results.insertAdjacentHTML('beforeend', errorHtml);
        } else {
            results.innerHTML = errorHtml;
        }
    } finally {
        loader.classList.remove('active');
        if (loadMoreButton) {
            loadMoreButton.disabled = false;
            loadMoreButton.textContent = 'Load More';
        }
    }
}

document.addEventListener('submit', async function(event) {
    const form = event.target.closest('[data-unsplash-import]');
    if (!form) return;
    event.preventDefault();
    const button = form.querySelector('button');
    button.disabled = true;
    button.textContent = 'Importing...';
    try {
        const data = await postForm(form);
        if (appendMediaCards([data.file.html], data.folder)) {
            showMediaStatus(data.message || 'Imported.', 'ok');
        }
        form.closest('.media-unsplash-card')?.remove();
    } catch (error) {
        showMediaStatus(error.message, 'error');
    } finally {
        button.disabled = false;
        button.textContent = 'Import';
    }
});

document.addEventListener('submit', async function(event) {
    const form = event.target.closest('[data-media-delete]');
    if (!form) return;
    event.preventDefault();
    if (!confirm('Delete this file?')) return;

    const card = form.closest('.media-card');
    const button = form.querySelector('button');
    button.disabled = true;
    button.textContent = 'Deleting...';

    try {
        const data = await postForm(form);
        card?.remove();
        showMediaStatus(data.message || 'File deleted.', 'ok');
        toggleEmptyState();
    } catch (error) {
        showMediaStatus(error.message || 'Delete failed.', 'error');
        button.disabled = false;
        button.textContent = 'Delete';
    }
});

document.getElementById('aiGenerateForm')?.addEventListener('submit', async function(event) {
    event.preventDefault();
    const loader = document.getElementById('aiLoader');
    const button = this.querySelector('button');
    loader.classList.add('active');
    button.disabled = true;
    try {
        const data = await postForm(this);
        if (appendMediaCards([data.file.html], data.folder)) {
            showMediaStatus(data.message || 'Generated.', 'ok');
        }
        this.reset();
    } catch (error) {
        showMediaStatus(error.message, 'error');
    } finally {
        loader.classList.remove('active');
        button.disabled = false;
    }
});

async function postForm(form) {
    const response = await fetch(form.action, {
        method: form.method || 'POST',
        body: new FormData(form),
        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
    });
    const data = await response.json().catch(() => ({}));
    if (!response.ok) throw new Error(data.message || 'Request failed.');
    return data;
}

function appendMediaCards(cards, folder) {
    if (folder && folder !== @json($folder)) {
        showMediaStatus('Saved in folder "' + folder + '". Use the folder filter to view it there.', 'ok');
        return false;
    }

    const grid = document.getElementById('mediaGrid');
    if (!grid) return false;
    cards.filter(Boolean).reverse().forEach(html => grid.insertAdjacentHTML('afterbegin', html));
    toggleEmptyState();
    return true;
}

function showMediaStatus(message, type) {
    const status = document.getElementById('mediaStatus');
    status.textContent = message;
    status.className = 'media-status ' + (type || 'ok');
}

function toggleEmptyState() {
    const hasCards = document.querySelectorAll('#mediaGrid .media-card').length > 0;
    document.getElementById('mediaEmptyState')?.classList.toggle('is-hidden', hasCards);
}

function previewImage(url) {
    document.getElementById('previewImage').src = url;
    document.getElementById('previewModal').style.display = 'flex';
}

function copyToClipboard(text) {
    navigator.clipboard.writeText(text);
    if (window.JamViniAdmin) {
        JamViniAdmin.showToast('Copied', 'Media URL copied to clipboard', 'success', 2000);
    }
}

function escapeHtml(value) {
    return String(value).replace(/[&<>"']/g, char => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[char]));
}
</script>
@endpush
