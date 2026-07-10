@extends('themes.default::layouts.admin')

@section('title', $post->exists ? 'Edit Social Post' : 'New Social Post')
@section('breadcrumbs')<a href="{{ route('admin.social.posts.index') }}">Posts</a> <span class="separator">/</span> <span class="current">{{ $post->exists ? 'Edit' : 'New' }}</span>@endsection

@push('styles')
<style>
.social-composer { display:grid; grid-template-columns:minmax(0,1fr) minmax(360px,440px); gap:18px; align-items:start; }
.composer-section { border-bottom:1px solid var(--jv-gray-100); padding-bottom:18px; margin-bottom:18px; }
.composer-section:last-child { border-bottom:0; padding-bottom:0; margin-bottom:0; }
.composer-heading { display:flex; align-items:center; justify-content:space-between; gap:12px; margin-bottom:12px; }
.composer-heading h3 { margin:0; font-size:1rem; color:var(--jv-gray-900); }
.composer-muted { color:var(--jv-gray-500); font-size:.82rem; }
.social-platforms { display:grid; grid-template-columns:repeat(auto-fit,minmax(138px,1fr)); gap:8px; }
.social-check { border:1px solid var(--jv-gray-200); border-radius:10px; padding:10px; display:flex; gap:8px; align-items:center; background:#fff; cursor:pointer; font-weight:700; color:var(--jv-gray-700); }
.social-check:has(input:checked) { border-color:var(--jv-primary); color:var(--jv-primary); background:#f8fbff; box-shadow:0 0 0 3px rgba(37,99,235,.08); }
.social-media-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(94px,1fr)); gap:10px; max-height:360px; overflow:auto; padding-right:2px; }
.social-media-card { border:2px solid var(--jv-gray-200); border-radius:10px; overflow:hidden; background:#fff; cursor:pointer; text-align:left; transition:.16s ease; }
.social-media-card.selected { border-color:var(--jv-primary); box-shadow:0 0 0 3px rgba(37,99,235,.12); }
.social-media-card img { width:100%; height:78px; object-fit:cover; display:block; background:#f1f5f9; }
.social-media-card span { display:block; padding:6px; font-size:.7rem; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.selected-media-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(112px,1fr)); gap:10px; }
.selected-media-card { position:relative; border:1px solid var(--jv-gray-200); border-radius:10px; overflow:hidden; background:#fff; }
.selected-media-card img, .selected-media-card video { width:100%; height:88px; object-fit:cover; display:block; background:#f1f5f9; }
.selected-media-card span { display:block; padding:7px; font-size:.72rem; color:var(--jv-gray-600); white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.selected-media-card button { position:absolute; top:6px; right:6px; width:26px; height:26px; border-radius:50%; border:0; background:rgba(15,23,42,.78); color:#fff; cursor:pointer; }
.selected-media-empty { border:1px dashed var(--jv-gray-300); border-radius:10px; padding:18px; color:var(--jv-gray-500); text-align:center; background:#f8fafc; }
.selected-media-empty.is-hidden { display:none; }
.media-picker-backdrop { display:none; position:fixed; inset:0; z-index:1400; background:rgba(15,23,42,.72); padding:24px; }
.media-picker-backdrop.active { display:grid; place-items:center; }
.media-picker-dialog { width:min(1040px,100%); max-height:88vh; overflow:hidden; background:#fff; border-radius:14px; box-shadow:0 30px 90px rgba(15,23,42,.28); display:grid; grid-template-rows:auto auto 1fr auto; }
.media-picker-head { display:flex; align-items:center; justify-content:space-between; gap:14px; padding:16px 18px; border-bottom:1px solid var(--jv-gray-200); }
.media-picker-head h3 { margin:0; font-size:1.05rem; }
.media-picker-close { border:0; background:#f1f5f9; color:var(--jv-gray-700); width:34px; height:34px; border-radius:50%; cursor:pointer; font-size:1.1rem; }
.media-picker-tools { display:grid; grid-template-columns:minmax(0,1fr) 150px 120px; gap:10px; padding:14px 18px; border-bottom:1px solid var(--jv-gray-100); }
.media-picker-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(132px,1fr)); gap:12px; padding:18px; overflow:auto; min-height:320px; }
.media-picker-card { border:2px solid var(--jv-gray-200); border-radius:10px; background:#fff; overflow:hidden; cursor:pointer; text-align:left; transition:.16s ease; }
.media-picker-card:hover { transform:translateY(-1px); box-shadow:0 10px 24px rgba(15,23,42,.08); }
.media-picker-card.selected { border-color:var(--jv-primary); box-shadow:0 0 0 3px rgba(37,99,235,.12); }
.media-picker-card img, .media-picker-card video { width:100%; height:108px; object-fit:cover; display:block; background:#f1f5f9; }
.media-picker-card strong { display:block; padding:8px 8px 2px; font-size:.76rem; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.media-picker-card small { display:block; padding:0 8px 8px; color:var(--jv-gray-500); font-size:.68rem; }
.media-picker-foot { display:flex; justify-content:space-between; gap:10px; align-items:center; padding:14px 18px; border-top:1px solid var(--jv-gray-200); }
.media-type-badge { position:absolute; top:6px; left:6px; border-radius:999px; padding:3px 7px; background:rgba(15,23,42,.76); color:#fff; font-size:.65rem; font-weight:900; }
.media-picker-thumb { position:relative; }
.composer-counter { text-align:right; margin-top:6px; font-size:.78rem; color:var(--jv-gray-500); }
.composer-counter.warn { color:#b45309; font-weight:800; }
.ai-helper { border:1px solid var(--jv-gray-200); border-radius:12px; background:#f8fafc; padding:12px; margin-top:12px; }
.ai-helper-head { display:flex; justify-content:space-between; gap:10px; align-items:center; margin-bottom:10px; }
.ai-helper-head h4 { margin:0; font-size:.95rem; color:var(--jv-gray-900); }
.ai-actions { display:grid; grid-template-columns:repeat(auto-fit,minmax(140px,1fr)); gap:8px; margin-top:10px; }
.ai-result { display:none; border:1px solid var(--jv-gray-200); border-radius:10px; background:#fff; padding:11px; margin-top:12px; }
.ai-result.active { display:block; }
.ai-result-text { white-space:pre-wrap; color:var(--jv-gray-800); line-height:1.5; max-height:240px; overflow:auto; }
.ai-result-actions { display:flex; gap:8px; flex-wrap:wrap; margin-top:10px; }
.ai-status { color:var(--jv-gray-500); font-size:.78rem; }
.preview-shell { position:sticky; top:84px; }
.preview-tabs { display:flex; gap:6px; flex-wrap:wrap; margin-bottom:12px; }
.preview-tab { border:1px solid var(--jv-gray-200); background:#fff; color:var(--jv-gray-600); border-radius:999px; padding:7px 10px; font-size:.78rem; font-weight:800; cursor:pointer; }
.preview-tab.active { background:var(--jv-primary); border-color:var(--jv-primary); color:#fff; }
.preview-frame { display:none; }
.preview-frame.active { display:block; }
.social-preview { border:1px solid var(--jv-gray-200); background:#fff; overflow:hidden; box-shadow:0 16px 40px rgba(15,23,42,.08); }
.social-preview.facebook, .social-preview.linkedin { border-radius:8px; }
.social-preview.instagram { border-radius:14px; }
.social-preview.x, .social-preview.telegram, .social-preview.whatsapp { border-radius:16px; }
.preview-head { display:flex; align-items:center; gap:10px; padding:12px 14px; }
.preview-avatar { width:42px; height:42px; border-radius:50%; display:grid; place-items:center; background:linear-gradient(135deg,var(--jv-primary),#22c55e); color:#fff; font-weight:900; flex:0 0 auto; }
.preview-account { font-weight:900; color:var(--jv-gray-900); line-height:1.1; }
.preview-meta { color:var(--jv-gray-500); font-size:.76rem; margin-top:3px; }
.preview-body { padding:0 14px 12px; }
.preview-menu { margin-left:auto; color:var(--jv-gray-400); font-size:1.2rem; line-height:1; }
.preview-caption { white-space:pre-wrap; line-height:1.45; color:var(--jv-gray-900); word-break:break-word; }
.preview-tags { margin-top:8px; color:var(--jv-primary); font-weight:800; line-height:1.5; word-break:break-word; }
.preview-link { margin-top:10px; border:1px solid var(--jv-gray-200); border-radius:8px; padding:9px 10px; color:var(--jv-gray-600); background:#f8fafc; font-size:.8rem; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
.preview-media { background:#f1f5f9; display:grid; gap:2px; }
.preview-media.empty { min-height:220px; place-items:center; color:var(--jv-gray-400); font-weight:800; }
.preview-media img, .preview-media video { width:100%; height:100%; object-fit:cover; display:block; min-height:0; }
.preview-video-wrap { position:relative; min-height:0; }
.preview-video-wrap:after { content:'PLAY'; position:absolute; inset:0; display:grid; place-items:center; color:#fff; font-size:.8rem; font-weight:900; letter-spacing:.08em; text-shadow:0 2px 10px rgba(0,0,0,.45); pointer-events:none; }
.preview-actions { display:flex; justify-content:space-around; gap:8px; padding:9px 12px; border-top:1px solid var(--jv-gray-100); color:var(--jv-gray-600); font-size:.82rem; font-weight:800; }
.preview-actions span { display:inline-flex; align-items:center; gap:6px; }
.preview-reactions { display:flex; align-items:center; justify-content:space-between; gap:10px; padding:9px 14px; color:var(--jv-gray-500); font-size:.78rem; }
.preview-media.count-1 { aspect-ratio:1.2; grid-template-columns:1fr; }
.preview-media.count-2 { aspect-ratio:1.5; grid-template-columns:1fr 1fr; }
.preview-media.count-3 { aspect-ratio:1.5; grid-template-columns:1.15fr .85fr; grid-template-rows:1fr 1fr; }
.preview-media.count-3 img:first-child { grid-row:1 / span 2; }
.preview-media.count-4 { aspect-ratio:1.4; grid-template-columns:1fr 1fr; grid-template-rows:1fr 1fr; }
.instagram .preview-media.count-1 { aspect-ratio:1; }
.x .preview-body { padding-top:0; }
.x .preview-media, .telegram .preview-media, .whatsapp .preview-media { margin:0 14px 12px; border-radius:14px; overflow:hidden; border:1px solid var(--jv-gray-200); }
.telegram { background:#eff6ff; border-color:#bfdbfe; }
.telegram .preview-message, .whatsapp .preview-message { margin:12px; border-radius:14px; padding:12px; background:#fff; box-shadow:0 8px 24px rgba(15,23,42,.06); }
.whatsapp { background:#e9f8ef; border-color:#bbf7d0; }
.whatsapp .preview-message { background:#dcf8c6; margin-left:46px; }
.preview-note { margin-top:12px; color:var(--jv-gray-500); font-size:.8rem; line-height:1.45; }
@media (max-width:1120px) { .social-composer { grid-template-columns:1fr; } .preview-shell { position:static; } }
@media (max-width:760px) { .media-picker-tools { grid-template-columns:1fr; } .media-picker-backdrop { padding:10px; } }
@media (max-width:680px) { .social-platforms { grid-template-columns:1fr; } .preview-head { padding:10px 12px; } .preview-body { padding:0 12px 12px; } }
</style>
@endpush

@section('content')
@php
    $selectedMedia = old('media_ids', $post->media?->pluck('id')->map(fn ($id) => (string) $id)->all() ?? []);
@endphp

<div class="page-header">
    <div>
        <h1 class="page-title">{{ $post->exists ? 'Edit Social Post' : 'New Social Post' }}</h1>
        <p class="page-subtitle">
            Create reusable posts for campaigns, offers, announcements, and hosting updates.
            @if(($selectedTemplate ?? null)) Using template: {{ $selectedTemplate->name }}. @endif
        </p>
    </div>
    <a href="{{ route('admin.social.templates.index') }}" class="btn btn-outline-primary">Templates</a>
</div>

<form action="{{ $post->exists ? route('admin.social.posts.update', $post) : route('admin.social.posts.store') }}" method="POST">
    @csrf
    @if($post->exists) @method('PUT') @endif

    <div class="social-composer">
        <div class="dash-card">
            <div class="composer-section">
                <div class="composer-heading"><h3>Post Details</h3><span class="composer-muted">Internal title and campaign</span></div>
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Title</label>
                        <input name="title" id="titleInput" class="form-input" value="{{ old('title', $post->title) }}" placeholder="July hosting offer" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Campaign</label>
                        <select name="campaign_id" class="form-select">
                            <option value="">None</option>
                            @foreach($campaigns as $campaign)
                                <option value="{{ $campaign->id }}" {{ old('campaign_id', $post->campaign_id) == $campaign->id ? 'selected' : '' }}>{{ $campaign->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div class="composer-section">
                <div class="composer-heading"><h3>Content</h3><span class="composer-muted">This appears in the preview</span></div>
                <div class="form-group">
                    <label class="form-label">Caption</label>
                    <textarea name="caption" id="captionInput" class="form-textarea" rows="9" placeholder="Write your social media caption..." required>{{ old('caption', $post->caption) }}</textarea>
                    <div id="captionCounter" class="composer-counter">0 characters</div>
                </div>
                <div class="ai-helper">
                    <div class="ai-helper-head">
                        <h4>AI Caption Helper</h4>
                        <span id="aiStatus" class="ai-status">Optional assistant</span>
                    </div>
                    <div class="form-group" style="margin:0;">
                        <label class="form-label">Tone</label>
                        <input id="aiToneInput" class="form-input" value="{{ $socialSettings['ai_tone'] ?? 'professional, friendly, clear' }}" placeholder="professional, friendly, urgent, playful">
                    </div>
                    <div class="form-group" style="margin:10px 0 0;">
                        <label class="form-label">AI Brief</label>
                        <textarea id="aiBriefInput" class="form-textarea" rows="3" placeholder="Example: Promote Starter Hosting for small businesses in Tanzania. Mention TZS 25,000/year, free SSL, and friendly support.">{{ $socialSettings['ai_default_brief'] ?? '' }}</textarea>
                    </div>
                    <div class="ai-actions">
                        <button type="button" class="btn btn-sm btn-outline-primary" data-ai-action="generate_caption">Generate Caption</button>
                        <button type="button" class="btn btn-sm btn-outline-primary" data-ai-action="improve_caption">Improve Caption</button>
                        <button type="button" class="btn btn-sm btn-outline-primary" data-ai-action="shorten_for_x">Shorten for X</button>
                        <button type="button" class="btn btn-sm btn-outline-primary" data-ai-action="generate_hashtags">Generate Hashtags</button>
                        <button type="button" class="btn btn-sm btn-outline-primary" data-ai-action="image_prompt">Image Prompt</button>
                    </div>
                    <div id="aiResult" class="ai-result">
                        <div id="aiResultText" class="ai-result-text"></div>
                        <div class="ai-result-actions">
                            <button type="button" id="applyAiCaption" class="btn btn-sm btn-primary">Apply to Caption</button>
                            <button type="button" id="appendAiCaption" class="btn btn-sm btn-outline-primary">Append to Caption</button>
                            <button type="button" id="applyAiHashtags" class="btn btn-sm btn-outline-primary">Apply to Hashtags</button>
                            <button type="button" id="copyAiResult" class="btn btn-sm btn-outline-primary">Copy</button>
                        </div>
                    </div>
                </div>
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Link URL</label>
                        <input type="url" name="link_url" id="linkInput" class="form-input" value="{{ old('link_url', $post->link_url) }}" placeholder="https://example.com/offer">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Hashtags</label>
                        <input name="hashtags" id="hashtagsInput" class="form-input" value="{{ old('hashtags', implode(' ', $post->hashtags ?? [])) }}" placeholder="#hosting #domains #webdesign">
                    </div>
                </div>
            </div>

            <div class="composer-section">
                <div class="composer-heading"><h3>Platforms</h3><span class="composer-muted">Choose where this post is prepared for</span></div>
                <div class="social-platforms">
                    @foreach($platforms as $value => $label)
                        <label class="social-check">
                            <input type="checkbox" name="platforms[]" value="{{ $value }}" {{ in_array($value, old('platforms', $post->platforms ?? []), true) ? 'checked' : '' }}>
                            <span>{{ $label }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            <div class="composer-section">
                <div class="composer-heading"><h3>Schedule</h3><span class="composer-muted">Manual publishing workflow for V1</span></div>
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Status</label>
                        <select name="status" id="statusSelect" class="form-select">
                            @foreach(['draft','ready','scheduled','published','failed'] as $status)
                                <option value="{{ $status }}" {{ old('status', $post->status ?? 'draft') === $status ? 'selected' : '' }}>{{ ucfirst($status) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Scheduled At</label>
                        <input type="datetime-local" name="scheduled_at" id="scheduledInput" class="form-input" value="{{ old('scheduled_at', $post->scheduled_at?->format('Y-m-d\\TH:i')) }}">
                    </div>
                </div>
            </div>

            <div class="composer-section">
                <div class="composer-heading"><h3>Media</h3><span id="mediaCounter" class="composer-muted">0 selected</span></div>
                <div id="selectedMediaGrid" class="selected-media-grid">
                    @foreach($media as $file)
                        @if(in_array((string) $file->id, $selectedMedia, true))
                            <div class="selected-media-card" data-selected-media="{{ $file->id }}">
                                <input type="hidden" name="media_ids[]" value="{{ $file->id }}" data-url="{{ $file->url }}" data-thumb="{{ $file->thumbnail_url }}" data-type="{{ str_starts_with($file->mime_type, 'video/') ? 'video' : 'image' }}" data-name="{{ $file->original_name }}">
                                @if(str_starts_with($file->mime_type, 'video/'))
                                    <video src="{{ $file->url }}" muted playsinline></video>
                                @else
                                    <img src="{{ $file->thumbnail_url }}" alt="{{ $file->original_name }}">
                                @endif
                                <button type="button" data-remove-media="{{ $file->id }}">x</button>
                                <span>{{ $file->original_name }}</span>
                            </div>
                        @endif
                    @endforeach
                </div>
                <div id="selectedMediaEmpty" class="selected-media-empty">No media selected yet.</div>
                <div style="margin-top:10px;display:flex;justify-content:space-between;gap:10px;align-items:center;flex-wrap:wrap;">
                    <span class="composer-muted">Select multiple images or videos from Media Library.</span>
                    <button type="button" id="openMediaPicker" class="btn btn-sm btn-outline-primary">Open Media Library</button>
                </div>
            </div>

            <div class="composer-section">
                <div class="composer-heading"><h3>Internal Notes</h3><span class="composer-muted">Not shown publicly</span></div>
                <textarea name="notes" class="form-textarea" rows="3">{{ old('notes', $post->notes) }}</textarea>
            </div>
        </div>

        <aside class="preview-shell">
            <div class="dash-card">
                <div class="dash-card-head">
                    <h3>Live Preview</h3>
                    <span class="composer-muted" id="previewStatus">Draft view</span>
                </div>
                <div id="previewTabs" class="preview-tabs"></div>
                <div id="previewList"></div>
                <p class="preview-note">Preview follows each platform's common layout closely, but final spacing can still vary after publishing inside the real social app.</p>
            </div>
        </aside>
    </div>

    <div style="display:flex;justify-content:flex-end;gap:10px;margin-top:1rem;">
        <a href="{{ route('admin.social.posts.index') }}" class="btn btn-outline-danger">Cancel</a>
        <button class="btn btn-primary btn-lg">{{ $post->exists ? 'Save Changes' : 'Create Post' }}</button>
    </div>
</form>

<div id="socialMediaPicker" class="media-picker-backdrop" aria-hidden="true">
    <div class="media-picker-dialog" role="dialog" aria-modal="true" aria-labelledby="mediaPickerTitle">
        <div class="media-picker-head">
            <div>
                <h3 id="mediaPickerTitle">Choose Media</h3>
                <div class="composer-muted">Images and videos can be selected together.</div>
            </div>
            <button type="button" id="closeMediaPicker" class="media-picker-close">x</button>
        </div>
        <form id="mediaPickerSearch" class="media-picker-tools">
            <input type="search" name="search" class="form-input" placeholder="Search media by name...">
            <select name="folder" class="form-select">
                <option value="">All folders</option>
                @foreach(\Plugins\Media\src\Models\Media::getFolders() as $folderName)
                    <option value="{{ $folderName }}">{{ ucfirst($folderName) }}</option>
                @endforeach
            </select>
            <select name="type" class="form-select">
                <option value="visual">Images + videos</option>
                <option value="image">Images</option>
                <option value="video">Videos</option>
            </select>
        </form>
        <div id="mediaPickerGrid" class="media-picker-grid"></div>
        <div class="media-picker-foot">
            <span id="mediaPickerStatus" class="composer-muted">Loading media...</span>
            <div style="display:flex;gap:8px;">
                <button type="button" id="mediaPickerLoadMore" class="btn btn-outline-primary">Load More</button>
                <button type="button" id="mediaPickerDone" class="btn btn-primary">Done</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const platformLabels = @json($platforms);
const accountNames = @json($accountNames ?? []);
const platformStyles = {
    facebook: { label: 'Facebook', meta: 'Sponsored - Public', limit: 63206 },
    instagram: { label: 'Instagram', meta: 'Feed post', limit: 2200 },
    linkedin: { label: 'LinkedIn', meta: 'Company update', limit: 3000 },
    x: { label: 'X', meta: 'Post', limit: 280 },
    telegram: { label: 'Telegram', meta: 'Channel message', limit: 4096 },
    whatsapp: { label: 'WhatsApp', meta: 'Channel update', limit: 1024 },
};

const captionInput = document.getElementById('captionInput');
const hashtagsInput = document.getElementById('hashtagsInput');
const linkInput = document.getElementById('linkInput');
const titleInput = document.getElementById('titleInput');
const statusSelect = document.getElementById('statusSelect');
const scheduledInput = document.getElementById('scheduledInput');
const previewList = document.getElementById('previewList');
const previewTabs = document.getElementById('previewTabs');
const captionCounter = document.getElementById('captionCounter');
const mediaCounter = document.getElementById('mediaCounter');
const previewStatus = document.getElementById('previewStatus');
const aiToneInput = document.getElementById('aiToneInput');
const aiBriefInput = document.getElementById('aiBriefInput');
const aiStatus = document.getElementById('aiStatus');
const aiResult = document.getElementById('aiResult');
const aiResultText = document.getElementById('aiResultText');
const selectedMediaGrid = document.getElementById('selectedMediaGrid');
const selectedMediaEmpty = document.getElementById('selectedMediaEmpty');
const pickerModal = document.getElementById('socialMediaPicker');
const pickerGrid = document.getElementById('mediaPickerGrid');
const pickerSearch = document.getElementById('mediaPickerSearch');
const pickerStatus = document.getElementById('mediaPickerStatus');
const pickerLoadMore = document.getElementById('mediaPickerLoadMore');
let activePreview = '';
let pickerPage = 1;
let pickerHasMore = false;
let latestAiAction = '';

function selectedPlatforms() {
    return [...document.querySelectorAll('input[name="platforms[]"]:checked')].map(input => input.value);
}

function selectedMedia() {
    return [...document.querySelectorAll('input[name="media_ids[]"]')].map(input => ({
        id: input.value,
        url: input.dataset.url || input.dataset.thumb,
        thumb: input.dataset.thumb || input.dataset.url,
        type: input.dataset.type || 'image',
        name: input.dataset.name || '',
    }));
}

function renderPreviews() {
    const platforms = selectedPlatforms();
    const visiblePlatforms = platforms.length ? platforms : ['facebook'];
    if (!visiblePlatforms.includes(activePreview)) activePreview = visiblePlatforms[0];

    renderPreviewTabs(visiblePlatforms);
    previewList.innerHTML = '';
    visiblePlatforms.forEach(platform => previewList.appendChild(buildPreview(platform, platform === activePreview)));
    updateCounters();
}

function renderPreviewTabs(platforms) {
    previewTabs.innerHTML = '';
    platforms.forEach(platform => {
        const button = document.createElement('button');
        button.type = 'button';
        button.className = 'preview-tab' + (platform === activePreview ? ' active' : '');
        button.textContent = platformLabels[platform] || platform;
        button.addEventListener('click', () => {
            activePreview = platform;
            renderPreviews();
        });
        previewTabs.appendChild(button);
    });
}

function buildPreview(platform, active) {
    const style = platformStyles[platform] || platformStyles.facebook;
    const caption = captionInput.value.trim() || 'Your caption preview will appear here as you type.';
    const tags = normalizeTags(hashtagsInput.value);
    const link = linkInput.value.trim();
    const media = selectedMedia();
    const account = accountNames[platform] || 'JamVini Hosting';
    const wrapper = document.createElement('div');
    wrapper.className = 'preview-frame' + (active ? ' active' : '');

    if (platform === 'telegram' || platform === 'whatsapp') {
        wrapper.innerHTML = messagePreview(platform, style, account, caption, tags, link, media);
        return wrapper;
    }

    wrapper.innerHTML = `
        <article class="social-preview ${platform}">
            <div class="preview-head">
                <div class="preview-avatar">${initials(account)}</div>
                <div>
                    <div class="preview-account">${escapeHtml(account)}</div>
                    <div class="preview-meta">${escapeHtml(style.meta)} - Just now${scheduledText()}</div>
                </div>
                <div class="preview-menu">...</div>
            </div>
            ${mediaHtml(media)}
            <div class="preview-body">
                <div class="preview-caption">${escapeHtml(caption)}</div>
                ${tags ? `<div class="preview-tags">${escapeHtml(tags)}</div>` : ''}
                ${link ? `<div class="preview-link">${escapeHtml(link)}</div>` : ''}
            </div>
            ${reactionsHtml(platform)}
            ${actionsHtml(platform)}
        </article>`;
    return wrapper;
}

function messagePreview(platform, style, account, caption, tags, link, media) {
    const body = `
        ${mediaHtml(media)}
        <div class="preview-caption">${escapeHtml(caption)}</div>
        ${tags ? `<div class="preview-tags">${escapeHtml(tags)}</div>` : ''}
        ${link ? `<div class="preview-link">${escapeHtml(link)}</div>` : ''}`;

    return `
        <article class="social-preview ${platform}">
            <div class="preview-head">
                <div class="preview-avatar">${initials(account)}</div>
                <div>
                    <div class="preview-account">${escapeHtml(account)}</div>
                    <div class="preview-meta">${escapeHtml(style.meta)} - Just now${scheduledText()}</div>
                </div>
                <div class="preview-menu">...</div>
            </div>
            <div class="preview-message">${body}</div>
            ${actionsHtml(platform)}
        </article>`;
}

function mediaHtml(media) {
    if (!media.length) return '<div class="preview-media empty">Media preview</div>';
    const shown = media.slice(0, 4);
    return `<div class="preview-media count-${shown.length}">${shown.map(item => {
        if (item.type === 'video') {
            return `<div class="preview-video-wrap"><video src="${escapeHtml(item.url)}" poster="${escapeHtml(item.thumb)}" muted playsinline></video></div>`;
        }
        return `<img src="${escapeHtml(item.thumb || item.url)}" alt="${escapeHtml(item.name)}">`;
    }).join('')}</div>`;
}

function reactionsHtml(platform) {
    if (platform === 'instagram') return '<div class="preview-reactions"><span>0 likes</span><span>View comments</span></div>';
    if (platform === 'x') return '<div class="preview-reactions"><span>0 replies</span><span>0 reposts</span><span>0 likes</span></div>';
    if (platform === 'linkedin') return '<div class="preview-reactions"><span>0 reactions</span><span>0 comments</span></div>';
    return '<div class="preview-reactions"><span>0 likes</span><span>0 comments - 0 shares</span></div>';
}

function actionsHtml(platform) {
    const actions = {
        facebook: ['Like', 'Comment', 'Share'],
        instagram: ['Like', 'Comment', 'Send'],
        linkedin: ['Like', 'Comment', 'Repost', 'Send'],
        x: ['Reply', 'Repost', 'Like', 'Share'],
        telegram: ['View', 'Reply', 'Share'],
        whatsapp: ['React', 'Reply', 'Forward'],
    }[platform] || ['Like', 'Comment', 'Share'];

    return `<div class="preview-actions">${actions.map(action => `<span>${actionIcon(action)} ${action}</span>`).join('')}</div>`;
}

function actionIcon(action) {
    return {
        Like: 'o',
        Comment: '[]',
        Share: '->',
        Send: '>',
        Repost: 'rt',
        Reply: '<-',
        View: 'ok',
        React: 'o',
        Forward: '->',
    }[action] || '•';
}

function normalizeTags(value) {
    return value.split(/[\s,]+/)
        .map(tag => tag.trim())
        .filter(Boolean)
        .map(tag => tag.startsWith('#') ? tag : '#' + tag)
        .join(' ');
}

function scheduledText() {
    const value = scheduledInput.value;
    if (!value || statusSelect.value !== 'scheduled') return '';
    return ' - Scheduled';
}

function updateCounters() {
    const activeStyle = platformStyles[activePreview] || platformStyles.facebook;
    const length = captionInput.value.length + (hashtagsInput.value ? normalizeTags(hashtagsInput.value).length + 1 : 0);
    captionCounter.textContent = `${length} / ${activeStyle.limit.toLocaleString()} characters for ${activeStyle.label}`;
    captionCounter.classList.toggle('warn', length > activeStyle.limit);
    const count = selectedMedia().length;
    mediaCounter.textContent = `${count} selected`;
    selectedMediaEmpty?.classList.toggle('is-hidden', count > 0);
    previewStatus.textContent = statusSelect.options[statusSelect.selectedIndex]?.text || 'Draft view';
}

function initials(value) {
    const parts = value.trim().split(/\s+/).filter(Boolean).slice(0, 2);
    return (parts.map(part => part[0]).join('') || 'JV').toUpperCase();
}

function escapeHtml(value) {
    return String(value).replace(/[&<>"']/g, char => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[char]));
}

document.querySelectorAll('input, textarea, select').forEach(el => {
    el.addEventListener('input', renderPreviews);
    el.addEventListener('change', renderPreviews);
});

document.getElementById('openMediaPicker')?.addEventListener('click', () => openMediaPicker());
document.getElementById('closeMediaPicker')?.addEventListener('click', closeMediaPicker);
document.getElementById('mediaPickerDone')?.addEventListener('click', closeMediaPicker);
pickerModal?.addEventListener('click', event => {
    if (event.target === pickerModal) closeMediaPicker();
});

pickerSearch?.addEventListener('input', debounce(() => fetchPickerMedia(1, false), 300));
pickerSearch?.addEventListener('change', () => fetchPickerMedia(1, false));
pickerSearch?.addEventListener('submit', event => {
    event.preventDefault();
    fetchPickerMedia(1, false);
});
pickerLoadMore?.addEventListener('click', () => {
    if (pickerHasMore) fetchPickerMedia(pickerPage + 1, true);
});

document.querySelectorAll('[data-ai-action]').forEach(button => {
    button.addEventListener('click', () => requestAiSuggestion(button.dataset.aiAction, button));
});

document.getElementById('applyAiCaption')?.addEventListener('click', () => {
    captionInput.value = aiResultText.textContent.trim();
    renderPreviews();
});

document.getElementById('appendAiCaption')?.addEventListener('click', () => {
    captionInput.value = [captionInput.value.trim(), aiResultText.textContent.trim()].filter(Boolean).join("\n\n");
    renderPreviews();
});

document.getElementById('applyAiHashtags')?.addEventListener('click', () => {
    hashtagsInput.value = aiResultText.textContent.trim();
    renderPreviews();
});

document.getElementById('copyAiResult')?.addEventListener('click', async () => {
    await navigator.clipboard.writeText(aiResultText.textContent.trim());
    aiStatus.textContent = 'Copied.';
});

async function requestAiSuggestion(action, button) {
    latestAiAction = action;
    aiStatus.textContent = 'Thinking...';
    button.disabled = true;
    aiResult.classList.remove('active');

    try {
        const response = await fetch(@json(route('admin.social.ai.suggest')), {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': @json(csrf_token()),
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify({
                action,
                title: titleInput.value,
                caption: captionInput.value,
                hashtags: hashtagsInput.value,
                brief: aiBriefInput.value,
                tone: aiToneInput.value,
                platforms: selectedPlatforms(),
            }),
        });
        const data = await response.json().catch(() => ({}));
        if (!response.ok) throw new Error(data.message || 'AI request failed.');
        aiResultText.textContent = data.content || '';
        aiResult.classList.add('active');
        aiStatus.textContent = data.message || 'Suggestion ready.';
        document.getElementById('applyAiHashtags').style.display = action === 'generate_hashtags' ? 'inline-flex' : 'none';
        document.getElementById('applyAiCaption').style.display = action === 'generate_hashtags' ? 'none' : 'inline-flex';
        document.getElementById('appendAiCaption').style.display = action === 'generate_hashtags' ? 'none' : 'inline-flex';
    } catch (error) {
        aiResultText.textContent = error.message;
        aiResult.classList.add('active');
        aiStatus.textContent = 'Needs attention.';
    } finally {
        button.disabled = false;
    }
}

document.addEventListener('click', event => {
    const remove = event.target.closest('[data-remove-media]');
    if (remove) {
        remove.closest('[data-selected-media]')?.remove();
        syncPickerSelection();
        renderPreviews();
        return;
    }

    const pickerCard = event.target.closest('[data-picker-media]');
    if (pickerCard) {
        toggleSelectedMedia({
            id: pickerCard.dataset.id,
            name: pickerCard.dataset.name,
            url: pickerCard.dataset.url,
            thumb: pickerCard.dataset.thumb,
            type: pickerCard.dataset.type,
        });
        pickerCard.classList.toggle('selected', isMediaSelected(pickerCard.dataset.id));
    }
});

function openMediaPicker() {
    pickerModal.classList.add('active');
    pickerModal.setAttribute('aria-hidden', 'false');
    fetchPickerMedia(1, false);
}

function closeMediaPicker() {
    pickerModal.classList.remove('active');
    pickerModal.setAttribute('aria-hidden', 'true');
}

async function fetchPickerMedia(page, append) {
    pickerPage = page;
    pickerStatus.textContent = append ? 'Loading more media...' : 'Loading media...';
    pickerLoadMore.disabled = true;
    if (!append) pickerGrid.innerHTML = '';

    const params = new URLSearchParams(new FormData(pickerSearch));
    params.set('page', page);

    try {
        const response = await fetch(@json(route('admin.media.picker')) + '?' + params.toString(), {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        });
        const data = await response.json();
        if (!response.ok) throw new Error(data.message || 'Could not load media.');
        const html = (data.items || []).map(pickerCardHtml).join('');
        if (append) {
            pickerGrid.insertAdjacentHTML('beforeend', html);
        } else {
            pickerGrid.innerHTML = html || '<div class="selected-media-empty" style="grid-column:1/-1;">No media found.</div>';
        }
        pickerHasMore = Boolean(data.pagination?.has_more);
        pickerLoadMore.style.display = pickerHasMore ? 'inline-flex' : 'none';
        pickerStatus.textContent = data.items?.length ? 'Select one or more assets.' : 'No media found.';
    } catch (error) {
        pickerGrid.innerHTML = `<div class="alert alert-danger" style="grid-column:1/-1;">${escapeHtml(error.message)}</div>`;
        pickerStatus.textContent = 'Media could not be loaded.';
    } finally {
        pickerLoadMore.disabled = false;
    }
}

function pickerCardHtml(item) {
    const type = item.is_video ? 'video' : 'image';
    const media = item.is_video
        ? `<video src="${escapeHtml(item.url)}" muted playsinline></video>`
        : `<img src="${escapeHtml(item.thumbnail_url)}" alt="${escapeHtml(item.name)}">`;
    return `<button type="button" class="media-picker-card ${isMediaSelected(item.id) ? 'selected' : ''}" data-picker-media data-id="${escapeHtml(item.id)}" data-name="${escapeHtml(item.name)}" data-url="${escapeHtml(item.url)}" data-thumb="${escapeHtml(item.thumbnail_url)}" data-type="${type}">
        <div class="media-picker-thumb"><span class="media-type-badge">${type}</span>${media}</div>
        <strong>${escapeHtml(item.name)}</strong>
        <small>${escapeHtml(item.folder)} - ${escapeHtml(item.size)}</small>
    </button>`;
}

function toggleSelectedMedia(item) {
    if (isMediaSelected(item.id)) {
        selectedMediaGrid.querySelector(`[data-selected-media="${cssEscape(item.id)}"]`)?.remove();
    } else {
        selectedMediaGrid.insertAdjacentHTML('beforeend', selectedMediaCardHtml(item));
    }
    renderPreviews();
}

function selectedMediaCardHtml(item) {
    const media = item.type === 'video'
        ? `<video src="${escapeHtml(item.url)}" muted playsinline></video>`
        : `<img src="${escapeHtml(item.thumb || item.url)}" alt="${escapeHtml(item.name)}">`;
    return `<div class="selected-media-card" data-selected-media="${escapeHtml(item.id)}">
        <input type="hidden" name="media_ids[]" value="${escapeHtml(item.id)}" data-url="${escapeHtml(item.url)}" data-thumb="${escapeHtml(item.thumb || item.url)}" data-type="${escapeHtml(item.type)}" data-name="${escapeHtml(item.name)}">
        ${media}
        <button type="button" data-remove-media="${escapeHtml(item.id)}">x</button>
        <span>${escapeHtml(item.name)}</span>
    </div>`;
}

function isMediaSelected(id) {
    return Boolean(selectedMediaGrid.querySelector(`[data-selected-media="${cssEscape(id)}"]`));
}

function syncPickerSelection() {
    document.querySelectorAll('[data-picker-media]').forEach(card => {
        card.classList.toggle('selected', isMediaSelected(card.dataset.id));
    });
}

function debounce(fn, wait) {
    let timeout;
    return (...args) => {
        clearTimeout(timeout);
        timeout = setTimeout(() => fn(...args), wait);
    };
}

function cssEscape(value) {
    return String(value).replace(/["\\]/g, '\\$&');
}

renderPreviews();
</script>
@endpush
