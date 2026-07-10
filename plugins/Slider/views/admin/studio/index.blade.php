<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Slider Studio - {{ $slider->title }}</title>
    <link rel="stylesheet" href="{{ $studioCssUrl }}">
</head>
<body>
<div class="jvs-app" data-slider-id="{{ $slider->id }}">
    <header class="jvs-topbar">
        <div class="jvs-brand">
            <a href="{{ route('admin.slider.edit', $slider) }}" class="jvs-icon-btn" title="Back">{{ jv_icon('arrow-left', '', 16) }}</a>
            <div>
                <strong>{{ $slider->title }}</strong>
                <span>Slider Studio</span>
            </div>
        </div>
        <nav class="jvs-slide-tabs" id="jvsSlideTabs">
            @foreach($slides as $slide)
                <button type="button" class="{{ $loop->first ? 'is-active' : '' }}" data-slide-id="{{ $slide->id }}">Slide {{ $loop->iteration }}</button>
            @endforeach
            <button type="button" class="jvs-add-slide" id="jvsAddSlideBtn">{{ jv_icon('plus', '', 16) }} Add Slide</button>
        </nav>
        <div class="jvs-actions">
            <button type="button" class="jvs-icon-btn" id="jvsSettingsBtn" title="Slider settings">{{ jv_icon('settings', '', 16) }}</button>
            <button type="button" class="jvs-icon-btn" id="jvsPreviewBtn" title="Preview">{{ jv_icon('eye', '', 16) }}</button>
            <button type="button" class="jvs-primary-btn" id="jvsSaveBtn">{{ jv_icon('save', '', 16) }} Save</button>
        </div>
    </header>

    <aside class="jvs-tools">
        <button data-tool="select" class="is-active" title="Select">{{ jv_icon('mouse-pointer-2', '', 18) }}<span>Select</span></button>
        <button data-add-layer="heading" title="Heading">{{ jv_icon('heading-2', '', 18) }}<span>Heading</span></button>
        <button data-add-layer="text" title="Text">{{ jv_icon('type', '', 18) }}<span>Text</span></button>
        <button data-add-layer="button" title="Button">{{ jv_icon('square-mouse-pointer', '', 18) }}<span>Button</span></button>
        <button data-add-layer="image" title="Image">{{ jv_icon('image', '', 18) }}<span>Image</span></button>
        <button data-add-layer="shape" title="Shape">{{ jv_icon('shapes', '', 18) }}<span>Shape</span></button>
    </aside>

    <main class="jvs-stage-wrap">
        <div class="jvs-stage-toolbar">
            <span id="jvsSlideName">Slide 1</span>
            <span>Drag layers. Click a layer to edit attributes.</span>
        </div>
        <section class="jvs-stage" id="jvsStage"></section>
    </main>

    <aside class="jvs-rightbar">
        <section class="jvs-panel">
            <div class="jvs-panel-head">
                <strong>Layers</strong>
                <span id="jvsLayerCount">0</span>
            </div>
            <div class="jvs-layer-list" id="jvsLayerList"></div>
        </section>

        <section class="jvs-panel">
            <div class="jvs-panel-head"><strong>Inspector</strong></div>
            <div class="jvs-inspector" id="jvsInspector">
                <p>Select a layer to edit its attributes.</p>
            </div>
        </section>
    </aside>
</div>

<div class="jvs-settings-panel" id="jvsSettingsPanel">
    <div class="jvs-settings-card">
        <div class="jvs-panel-head">
            <strong>Slider Settings</strong>
            <button type="button" class="jvs-icon-btn" id="jvsCloseSettings">{{ jv_icon('x', '', 16) }}</button>
        </div>
        @php $settings = array_merge(['height' => 620, 'delay' => 5500, 'speed' => 700, 'autoplay' => true, 'navigation' => true, 'pagination' => true], $slider->settings ?? []); @endphp
        <div class="jvs-field-grid">
            <label>Height <input type="number" id="jvsSettingHeight" value="{{ $settings['height'] }}"></label>
            <label>Delay <input type="number" id="jvsSettingDelay" value="{{ $settings['delay'] }}"></label>
            <label>Speed <input type="number" id="jvsSettingSpeed" value="{{ $settings['speed'] }}"></label>
            <label><input type="checkbox" id="jvsSettingAutoplay" {{ !empty($settings['autoplay']) ? 'checked' : '' }}> Autoplay</label>
            <label><input type="checkbox" id="jvsSettingNavigation" {{ !empty($settings['navigation']) ? 'checked' : '' }}> Navigation</label>
            <label><input type="checkbox" id="jvsSettingPagination" {{ !empty($settings['pagination']) ? 'checked' : '' }}> Pagination</label>
        </div>
    </div>
</div>

<script>
@php
    $studioSlides = $slides->map(fn ($slide) => [
        'id' => $slide->id,
        'name' => $slide->title ?: 'Slide',
        'saveUrl' => route('admin.slider.slides.layers', [$slider, $slide]),
        'background' => [
            'image' => $slide->image,
            'overlay' => $slide->overlay_color ?: 'rgba(15,23,42,.58)',
            'position' => $slide->background_position ?: 'center center',
        ],
        'layers' => $slide->layers ?: [],
    ])->values();
@endphp
window.JamViniSliderStudio = {
    csrfToken: @json(csrf_token()),
    settingsUrl: @json(route('admin.slider.studio.settings', $slider)),
    createSlideUrl: @json(route('admin.slider.studio.slides', $slider)),
    slides: @json($studioSlides),
};
</script>
<script src="{{ $studioJsUrl }}"></script>
</body>
</html>
