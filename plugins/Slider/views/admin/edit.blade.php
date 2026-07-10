@extends('themes.default::layouts.admin')

@section('title', 'Edit Slider')
@section('breadcrumbs')<a href="{{ route('admin.slider.index') }}">Sliders</a> <span class="separator">/</span> <span class="current">{{ $slider->title }}</span>@endsection

@section('content')
@php
    $settings = array_merge([
        'height' => 620,
        'speed' => 700,
        'delay' => 5500,
        'effect' => 'fade',
        'autoplay' => true,
        'pause_on_hover' => true,
        'navigation' => true,
        'pagination' => true,
        'keyboard' => true,
        'loop' => true,
    ], $slider->settings ?? []);
    $slidePayloads = $slider->slides->mapWithKeys(fn ($slide) => [
        $slide->id => $slide->only([
            'title', 'subtitle', 'description', 'image',
            'button_text', 'button_link', 'button2_text', 'button2_link',
            'alignment', 'overlay_color', 'text_color', 'background_position',
            'content_width', 'animation', 'order', 'is_active',
        ]),
    ])->all();
@endphp

<div class="page-header">
    <div style="display:flex;justify-content:space-between;align-items:center;gap:16px;flex-wrap:wrap;">
        <div>
            <h1 class="page-title">{{ $slider->title }}</h1>
            <p class="page-subtitle">Shortcode: <code>[slider slug="{{ $slider->slug }}"]</code></p>
        </div>
        <div style="display:flex;gap:8px;flex-wrap:wrap;">
            <a href="{{ route('admin.slider.studio', $slider) }}" class="btn btn-primary">{{ jv_icon('wand-sparkles', '', 16) }} Open Studio</a>
            <a href="{{ route('admin.slider.index') }}" class="btn btn-outline-primary">{{ jv_icon('arrow-left', '', 16) }} Back to Sliders</a>
        </div>
    </div>
</div>

<style>
.slider-studio { display:grid; gap:20px; align-items:start; }
.slider-workspace { display:grid; grid-template-columns:minmax(0,1fr) 360px; gap:20px; align-items:start; }
.slider-panel { background:#fff; border:1px solid var(--jv-gray-200); border-radius:8px; overflow:hidden; }
.slider-panel-head { padding:16px 18px; border-bottom:1px solid var(--jv-gray-200); display:flex; align-items:center; justify-content:space-between; gap:12px; }
.slider-panel-body { padding:18px; }
.slide-card { display:grid; grid-template-columns:120px minmax(0,1fr) auto; gap:14px; align-items:center; padding:14px; border-bottom:1px solid var(--jv-gray-200); }
.slide-card:last-child { border-bottom:0; }
.slide-thumb { width:120px; height:76px; border-radius:8px; background:linear-gradient(135deg,#0f172a,#2563eb); object-fit:cover; display:grid; place-items:center; color:#fff; font-weight:800; overflow:hidden; }
.slide-thumb img { width:100%; height:100%; object-fit:cover; display:block; }
.slide-actions { display:flex; gap:6px; flex-wrap:wrap; justify-content:flex-end; }
.slider-preview-frame { border-radius:10px; overflow:hidden; border:1px solid var(--jv-gray-200); background:#0f172a; box-shadow:0 24px 70px rgba(15,23,42,.12); }
.slider-preview-slide { min-height:520px; padding:56px; display:flex; align-items:center; color:#fff; background-size:cover; background-position:center; position:relative; }
.slider-preview-slide:before { content:""; position:absolute; inset:0; background:linear-gradient(90deg,rgba(15,23,42,.82),rgba(15,23,42,.2)); }
.slider-preview-content { position:relative; max-width:580px; }
.slider-preview-content h2 { color:#fff; font-size:clamp(2.2rem,5vw,4.8rem); line-height:.98; margin:0 0 14px; }
.slider-preview-content p { color:#dbeafe; line-height:1.75; font-size:1.1rem; }
.slider-media-preview { min-height:110px; border:1px dashed var(--jv-gray-300); border-radius:8px; display:grid; place-items:center; background:var(--jv-gray-50); color:var(--jv-gray-500); overflow:hidden; }
.slider-media-preview img { width:100%; height:150px; object-fit:cover; display:block; }
.slider-media-modal { display:none; position:fixed; inset:0; z-index:1300; background:rgba(15,23,42,.72); padding:28px; }
.slider-media-dialog { background:#fff; border-radius:10px; max-width:1040px; max-height:88vh; margin:0 auto; overflow:hidden; display:flex; flex-direction:column; }
.slider-media-head { padding:16px 18px; border-bottom:1px solid var(--jv-gray-200); display:flex; align-items:center; justify-content:space-between; gap:12px; }
.slider-media-tools { padding:14px 18px; display:flex; gap:10px; border-bottom:1px solid var(--jv-gray-200); }
.slider-media-grid { padding:18px; display:grid; grid-template-columns:repeat(auto-fill,minmax(150px,1fr)); gap:12px; overflow:auto; }
.slider-media-item { border:1px solid var(--jv-gray-200); border-radius:8px; background:#fff; overflow:hidden; cursor:pointer; text-align:left; }
.slider-media-item img { width:100%; height:120px; object-fit:cover; display:block; }
.slider-media-item span { display:block; padding:8px; font-size:.76rem; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.slider-media-empty { padding:46px; color:var(--jv-gray-500); text-align:center; }
@media (max-width:1050px) { .slider-workspace { grid-template-columns:1fr; } }
@media (max-width:680px) { .slide-card { grid-template-columns:1fr; } .slide-thumb { width:100%; height:160px; } }
</style>

<div class="slider-studio">
    <div class="slider-panel">
        <div class="slider-panel-head">
            <div>
                <h3 class="card-title" style="margin:0;">Stage Preview</h3>
                <div style="font-size:.82rem;color:var(--jv-gray-500);margin-top:3px;">Large preview of the first/current slide in a page-like canvas.</div>
            </div>
            <span class="badge badge-info">{{ $slider->slides->count() }} slides</span>
        </div>
        <div class="slider-panel-body">
            @php $previewSlide = $slider->slides->first(); @endphp
            <div class="slider-preview-frame">
                <div class="slider-preview-slide" id="studioPreviewSlide" style="@if($previewSlide?->image) background-image:url('{{ $previewSlide->image }}'); @endif">
                    <div class="slider-preview-content" id="studioPreviewContent">
                        <div style="font-size:.78rem;text-transform:uppercase;font-weight:800;color:#bfdbfe;margin-bottom:12px;" id="studioPreviewSubtitle">{{ $previewSlide?->subtitle ?: 'Slider Studio' }}</div>
                        <h2 id="studioPreviewTitle">{{ $previewSlide?->title ?: 'Design a slider that feels alive.' }}</h2>
                        <p id="studioPreviewDescription">{{ $previewSlide?->description ?: 'Choose an image from Media Library, tune the overlay, add buttons, and preview the result before placing it on a page.' }}</p>
                        <span class="btn btn-primary btn-sm" id="studioPreviewButton">{{ $previewSlide?->button_text ?: 'Primary Action' }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="slider-workspace">
    <div style="display:grid;gap:20px;">
        <div class="slider-panel">
            <div class="slider-panel-head">
                <h3 class="card-title" style="margin:0;">Slider Settings</h3>
                <span class="badge badge-{{ $slider->is_active ? 'success' : 'gray' }}">{{ $slider->is_active ? 'Active' : 'Inactive' }}</span>
            </div>
            <div class="slider-panel-body">
                <form action="{{ route('admin.slider.update', $slider) }}" method="POST">
                    @csrf @method('PUT')
                    <div style="display:grid;grid-template-columns:minmax(0,1fr) 220px;gap:16px;">
                        <div class="form-group"><label class="form-label">Title</label><input type="text" name="title" class="form-input" value="{{ $slider->title }}" required></div>
                        <div class="form-group">
                            <label class="form-label">Type</label>
                            <select name="type" class="form-select">
                                <option value="hero" {{ $slider->type === 'hero' ? 'selected' : '' }}>Hero</option>
                                <option value="carousel" {{ $slider->type === 'carousel' ? 'selected' : '' }}>Carousel</option>
                                <option value="testimonial" {{ $slider->type === 'testimonial' ? 'selected' : '' }}>Testimonial</option>
                            </select>
                        </div>
                    </div>
                    <div style="display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:14px;">
                        <div class="form-group"><label class="form-label">Height</label><input type="number" name="settings[height]" class="form-input" value="{{ $settings['height'] }}"></div>
                        <div class="form-group"><label class="form-label">Speed</label><input type="number" name="settings[speed]" class="form-input" value="{{ $settings['speed'] }}"></div>
                        <div class="form-group"><label class="form-label">Delay</label><input type="number" name="settings[delay]" class="form-input" value="{{ $settings['delay'] }}"></div>
                        <div class="form-group">
                            <label class="form-label">Effect</label>
                            <select name="settings[effect]" class="form-select">
                                <option value="fade" {{ $settings['effect'] === 'fade' ? 'selected' : '' }}>Fade</option>
                                <option value="slide" {{ $settings['effect'] === 'slide' ? 'selected' : '' }}>Slide</option>
                            </select>
                        </div>
                    </div>
                    <div style="display:flex;gap:18px;flex-wrap:wrap;">
                        @foreach(['autoplay'=>'Autoplay','pause_on_hover'=>'Pause on hover','navigation'=>'Navigation','pagination'=>'Pagination','keyboard'=>'Keyboard','loop'=>'Loop'] as $key => $label)
                            <label class="checkbox-group"><input type="checkbox" name="settings[{{ $key }}]" value="1" {{ !empty($settings[$key]) ? 'checked' : '' }}> {{ $label }}</label>
                        @endforeach
                        <label class="checkbox-group"><input type="checkbox" name="is_active" value="1" {{ $slider->is_active ? 'checked' : '' }}> Active</label>
                    </div>
                    <div style="display:flex;justify-content:flex-end;margin-top:16px;">
                        <button class="btn btn-primary">{{ jv_icon('save', '', 16) }} Save Settings</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="slider-panel">
            <div class="slider-panel-head">
                <h3 class="card-title" style="margin:0;">Slides</h3>
                <button class="btn btn-primary btn-sm" type="button" id="showSlideFormBtn">{{ jv_icon('plus', '', 16) }} Add Slide</button>
            </div>
            <div>
                @forelse($slider->slides as $slide)
                    <div class="slide-card">
                        <div class="slide-thumb">
                            @if($slide->image)<img src="{{ $slide->image }}" alt="">@else {{ strtoupper(substr($slide->title ?: 'Slide', 0, 2)) }} @endif
                        </div>
                        <div>
                            <strong>{{ $slide->title ?: 'Untitled Slide' }}</strong>
                            <div style="font-size:.82rem;color:var(--jv-gray-500);margin-top:3px;">{{ $slide->subtitle ?: 'No subtitle' }}</div>
                            <div style="display:flex;gap:6px;flex-wrap:wrap;margin-top:8px;">
                                <span class="badge badge-gray">Order {{ $slide->order }}</span>
                                <span class="badge badge-{{ $slide->is_active ? 'success' : 'gray' }}">{{ $slide->is_active ? 'Active' : 'Hidden' }}</span>
                                <span class="badge badge-info">{{ ucfirst($slide->alignment ?? 'center') }}</span>
                            </div>
                        </div>
                        <div class="slide-actions">
                            <button class="btn btn-sm btn-outline-primary" type="button" onclick="editSlide({{ $slide->id }})">{{ jv_icon('pencil', '', 14) }} Edit</button>
                            <form action="{{ route('admin.slider.slides.delete', [$slider, $slide]) }}" method="POST" data-confirm="Delete this slide?" data-danger="true">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger">{{ jv_icon('trash-2', '', 14) }}</button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="empty-state" style="padding:48px;">
                        <div class="empty-state-icon">SL</div>
                        <div class="empty-state-title">No slides yet</div>
                        <p>Add your first slide to bring this slider alive.</p>
                    </div>
                @endforelse
            </div>
        </div>

        <div id="addSlideForm" class="slider-panel" style="display:none;">
            <div class="slider-panel-head">
                <h3 class="card-title" id="slideFormTitle" style="margin:0;">Add Slide</h3>
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="closeSlideForm()">{{ jv_icon('x', '', 14) }}</button>
            </div>
            <div class="slider-panel-body">
                <form id="slideForm" action="{{ route('admin.slider.slides.store', $slider) }}" method="POST">
                    @csrf
                    <input type="hidden" name="_method" value="POST" id="slideMethod">
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                        <div class="form-group"><label class="form-label">Title</label><input type="text" name="title" class="form-input" id="slideTitle"></div>
                        <div class="form-group"><label class="form-label">Subtitle</label><input type="text" name="subtitle" class="form-input" id="slideSubtitle"></div>
                    </div>
                    <div class="form-group"><label class="form-label">Description</label><textarea name="description" class="form-textarea" rows="3" id="slideDescription"></textarea></div>
                    <div class="form-group">
                        <label class="form-label">Image</label>
                        <div class="slider-media-preview" id="slideImagePreview"><span>No image selected</span></div>
                        <div style="display:flex;gap:8px;margin-top:8px;">
                            <input type="text" name="image" class="form-input" id="slideImage" placeholder="https://... or /storage/..." style="flex:1;">
                            <button type="button" class="btn btn-outline-primary" onclick="openSliderMediaPicker()">{{ jv_icon('image', '', 16) }} Media</button>
                        </div>
                    </div>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                        <div class="form-group"><label class="form-label">Primary Button Text</label><input type="text" name="button_text" class="form-input" id="slideBtnText"></div>
                        <div class="form-group"><label class="form-label">Primary Button Link</label><input type="text" name="button_link" class="form-input" id="slideBtnLink"></div>
                        <div class="form-group"><label class="form-label">Secondary Button Text</label><input type="text" name="button2_text" class="form-input" id="slideBtn2Text"></div>
                        <div class="form-group"><label class="form-label">Secondary Button Link</label><input type="text" name="button2_link" class="form-input" id="slideBtn2Link"></div>
                    </div>
                    <div style="display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:16px;">
                        <div class="form-group"><label class="form-label">Alignment</label><select name="alignment" class="form-select" id="slideAlign"><option value="left">Left</option><option value="center">Center</option><option value="right">Right</option></select></div>
                        <div class="form-group"><label class="form-label">Animation</label><select name="animation" class="form-select" id="slideAnimation"><option value="fade-up">Fade Up</option><option value="fade">Fade</option><option value="slide-left">Slide Left</option><option value="zoom">Zoom</option></select></div>
                        <div class="form-group"><label class="form-label">Order</label><input type="number" name="order" class="form-input" id="slideOrder" value="0"></div>
                        <div class="form-group"><label class="form-label">Overlay</label><input type="text" name="overlay_color" class="form-input" id="slideOverlay" value="rgba(15,23,42,.58)"></div>
                        <div class="form-group"><label class="form-label">Text Color</label><input type="text" name="text_color" class="form-input" id="slideTextColor" value="#ffffff"></div>
                        <div class="form-group"><label class="form-label">Background Position</label><input type="text" name="background_position" class="form-input" id="slideBgPosition" value="center center"></div>
                        <div class="form-group"><label class="form-label">Content Width</label><input type="text" name="content_width" class="form-input" id="slideContentWidth" value="720px"></div>
                        <div class="form-group">
                            <label class="form-label">&nbsp;</label>
                            <label class="checkbox-group"><input type="checkbox" name="is_active" value="1" id="slideActive" checked> Active</label>
                        </div>
                    </div>
                    <div style="display:flex;gap:12px;justify-content:flex-end;margin-top:16px;">
                        <button type="button" class="btn btn-outline-danger" onclick="closeSlideForm()">{{ jv_icon('x', '', 16) }} Cancel</button>
                        <button type="submit" class="btn btn-primary" id="slideSubmitBtn">{{ jv_icon('save', '', 16) }} Save Slide</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <aside class="slider-panel">
        <div class="slider-panel-head"><h3 class="card-title" style="margin:0;">Builder Notes</h3></div>
        <div class="slider-panel-body">
            <div style="color:var(--jv-gray-500);font-size:.88rem;line-height:1.7;">
                Render with <code>[slider slug="{{ $slider->slug }}"]</code> in CMS content or builder shortcode blocks.
            </div>
            <hr style="border:0;border-top:1px solid var(--jv-gray-200);margin:16px 0;">
            <p style="margin:0;color:var(--jv-gray-600);line-height:1.7;">Next evolution: draggable layers for text, icons, images, shapes, and buttons. This studio preview is the foundation.</p>
        </div>
    </aside>
    </div>
</div>

<div class="slider-media-modal" id="sliderMediaModal">
    <div class="slider-media-dialog">
        <div class="slider-media-head">
            <h3 style="margin:0;">Choose from Media Library</h3>
            <button type="button" class="btn btn-sm btn-outline-danger" onclick="closeSliderMediaPicker()">{{ jv_icon('x', '', 14) }}</button>
        </div>
        <div class="slider-media-tools">
            <input type="search" class="form-input" id="sliderMediaSearch" placeholder="Search images...">
            <button type="button" class="btn btn-outline-primary" onclick="loadSliderMedia()">{{ jv_icon('search', '', 16) }} Search</button>
        </div>
        <div class="slider-media-grid" id="sliderMediaGrid">
            <div class="slider-media-empty">Loading media...</div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const slideForm = document.getElementById('slideForm');
const slideData = @json($slidePayloads);
document.getElementById('showSlideFormBtn')?.addEventListener('click', () => {
    resetSlideForm();
    document.getElementById('addSlideForm').style.display = 'block';
    document.getElementById('addSlideForm').scrollIntoView({ behavior: 'smooth', block: 'start' });
});

function editSlide(id) {
    const data = slideData[id] || {};
    document.getElementById('addSlideForm').style.display = 'block';
    document.getElementById('slideFormTitle').textContent = 'Edit Slide';
    slideForm.action = '{{ route('admin.slider.slides.update', [$slider, '__ID__']) }}'.replace('__ID__', id);
    document.getElementById('slideMethod').value = 'PUT';
    setValue('slideTitle', data.title);
    setValue('slideSubtitle', data.subtitle);
    setValue('slideDescription', data.description);
    setValue('slideImage', data.image);
    setValue('slideBtnText', data.button_text);
    setValue('slideBtnLink', data.button_link);
    setValue('slideBtn2Text', data.button2_text);
    setValue('slideBtn2Link', data.button2_link);
    setValue('slideAlign', data.alignment || 'center');
    setValue('slideOverlay', data.overlay_color || 'rgba(15,23,42,.58)');
    setValue('slideTextColor', data.text_color || '#ffffff');
    setValue('slideBgPosition', data.background_position || 'center center');
    setValue('slideContentWidth', data.content_width || '720px');
    setValue('slideAnimation', data.animation || 'fade-up');
    setValue('slideOrder', data.order || 0);
    document.getElementById('slideActive').checked = Boolean(data.is_active);
    document.getElementById('slideSubmitBtn').textContent = 'Update Slide';
    updateSlideImagePreview();
    updateStudioPreview();
    document.getElementById('addSlideForm').scrollIntoView({ behavior: 'smooth', block: 'start' });
}

function closeSlideForm() {
    document.getElementById('addSlideForm').style.display = 'none';
    resetSlideForm();
}

function resetSlideForm() {
    slideForm.reset();
    slideForm.action = '{{ route('admin.slider.slides.store', $slider) }}';
    document.getElementById('slideMethod').value = 'POST';
    document.getElementById('slideFormTitle').textContent = 'Add Slide';
    document.getElementById('slideSubmitBtn').textContent = 'Save Slide';
    document.getElementById('slideOverlay').value = 'rgba(15,23,42,.58)';
    document.getElementById('slideTextColor').value = '#ffffff';
    document.getElementById('slideBgPosition').value = 'center center';
    document.getElementById('slideContentWidth').value = '720px';
    document.getElementById('slideAnimation').value = 'fade-up';
    document.getElementById('slideActive').checked = true;
    updateSlideImagePreview();
    updateStudioPreview();
}

function setValue(id, value) {
    document.getElementById(id).value = value || '';
}

['slideTitle', 'slideSubtitle', 'slideDescription', 'slideImage', 'slideBtnText'].forEach(id => {
    document.getElementById(id)?.addEventListener('input', () => {
        if (id === 'slideImage') updateSlideImagePreview();
        updateStudioPreview();
    });
});

function updateSlideImagePreview() {
    const url = document.getElementById('slideImage')?.value.trim();
    const preview = document.getElementById('slideImagePreview');
    if (!preview) return;
    preview.innerHTML = url ? `<img src="${escapeAttr(url)}" alt="">` : '<span>No image selected</span>';
}

function updateStudioPreview() {
    const image = document.getElementById('slideImage')?.value.trim();
    const title = document.getElementById('slideTitle')?.value.trim() || 'Design a slider that feels alive.';
    const subtitle = document.getElementById('slideSubtitle')?.value.trim() || 'Slider Studio';
    const description = document.getElementById('slideDescription')?.value.trim() || 'Choose an image from Media Library, tune the overlay, add buttons, and preview the result before placing it on a page.';
    const button = document.getElementById('slideBtnText')?.value.trim() || 'Primary Action';
    const slide = document.getElementById('studioPreviewSlide');
    if (slide) slide.style.backgroundImage = image ? `url("${image.replace(/"/g, '\\"')}")` : '';
    document.getElementById('studioPreviewTitle').textContent = title;
    document.getElementById('studioPreviewSubtitle').textContent = subtitle;
    document.getElementById('studioPreviewDescription').textContent = description;
    document.getElementById('studioPreviewButton').textContent = button;
}

function openSliderMediaPicker() {
    document.getElementById('sliderMediaModal').style.display = 'block';
    loadSliderMedia();
}

function closeSliderMediaPicker() {
    document.getElementById('sliderMediaModal').style.display = 'none';
}

async function loadSliderMedia() {
    const grid = document.getElementById('sliderMediaGrid');
    const search = document.getElementById('sliderMediaSearch')?.value || '';
    grid.innerHTML = '<div class="slider-media-empty">Loading media...</div>';
    try {
        const url = new URL(@json(route('admin.media.picker')), window.location.origin);
        url.searchParams.set('type', 'image');
        if (search) url.searchParams.set('search', search);
        const response = await fetch(url, { headers: { Accept: 'application/json' } });
        const payload = await response.json();
        if (!payload.items?.length) {
            grid.innerHTML = '<div class="slider-media-empty">No images found.</div>';
            return;
        }
        grid.innerHTML = payload.items.map(item => `
            <button type="button" class="slider-media-item" onclick="selectSliderMedia('${escapeJs(item.url)}', '${escapeJs(item.name)}')">
                <img src="${escapeAttr(item.thumbnail_url)}" alt="">
                <span>${escapeHtml(item.name)}</span>
            </button>
        `).join('');
    } catch (error) {
        grid.innerHTML = '<div class="slider-media-empty">Could not load media.</div>';
    }
}

function selectSliderMedia(url) {
    document.getElementById('slideImage').value = url;
    updateSlideImagePreview();
    updateStudioPreview();
    closeSliderMediaPicker();
}

function escapeHtml(value) {
    return String(value || '').replace(/[&<>"']/g, char => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[char]));
}

function escapeAttr(value) {
    return escapeHtml(value);
}

function escapeJs(value) {
    return String(value || '').replace(/\\/g, '\\\\').replace(/'/g, "\\'");
}

document.getElementById('sliderMediaSearch')?.addEventListener('keydown', event => {
    if (event.key === 'Enter') {
        event.preventDefault();
        loadSliderMedia();
    }
});
</script>
@endpush
