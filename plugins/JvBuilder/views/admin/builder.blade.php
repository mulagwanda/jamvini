<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @php
        $builderCssPath = base_path('plugins/JvBuilder/assets/css/jamvini-builder.css');
        $builderJsPath = base_path('plugins/JvBuilder/assets/js/jamvini-builder.js');
        $builderCssUrl = asset('plugins/jv-builder/css/jamvini-builder.css') . '?v=' . filemtime($builderCssPath);
        $builderJsUrl = asset('plugins/jv-builder/js/jamvini-builder.js') . '?v=' . filemtime($builderJsPath);
        $frontendCssUrl = theme_asset('css/frontend.css', 'public') . '?v=' . filemtime(theme_asset_path('css/frontend.css', 'public'));
    @endphp
    <title>JV Builder - {{ $page->title }}</title>
    <link rel="stylesheet" href="{{ $builderCssUrl }}">
    <link rel="stylesheet" href="{{ theme_asset('css/admin.css', 'admin') }}">
</head>
<body>
<div class="builder-wrapper">
    <aside class="builder-sidebar">
        <div class="builder-sidebar-header">Blocks</div>

        <div class="block-list">
            <div class="block-item" data-type="row" draggable="true">
                <div class="block-icon">{{ jv_icon('columns-3', '', 20) }}</div>
                <div><div class="block-label">Columns</div><div class="block-desc">Multi-column layout</div></div>
            </div>
            <div class="block-item" data-type="heading" draggable="true">
                <div class="block-icon">{{ jv_icon('heading-2', '', 20) }}</div>
                <div><div class="block-label">Heading</div><div class="block-desc">Section title</div></div>
            </div>
            <div class="block-item" data-type="text" draggable="true">
                <div class="block-icon">{{ jv_icon('type', '', 20) }}</div>
                <div><div class="block-label">Text</div><div class="block-desc">Paragraph content</div></div>
            </div>
            <div class="block-item" data-type="hero" draggable="true">
                <div class="block-icon">{{ jv_icon('panel-top', '', 20) }}</div>
                <div><div class="block-label">Hero Section</div><div class="block-desc">Full-width banner with CTA</div></div>
            </div>
            <div class="block-item" data-type="feature-card" draggable="true">
                <div class="block-icon">{{ jv_icon('badge-check', '', 20) }}</div>
                <div><div class="block-label">Feature Card</div><div class="block-desc">Icon, title, and description</div></div>
            </div>
            <div class="block-item" data-type="pricing-table" draggable="true">
                <div class="block-icon">{{ jv_icon('badge-dollar-sign', '', 20) }}</div>
                <div><div class="block-label">Pricing Table</div><div class="block-desc">Auto-pulls from Services</div></div>
            </div>
            <div class="block-item" data-type="cta" draggable="true">
                <div class="block-icon">{{ jv_icon('megaphone', '', 20) }}</div>
                <div><div class="block-label">Call to Action</div><div class="block-desc">Banner with CTA button</div></div>
            </div>
            <div class="block-item" data-type="image" draggable="true">
                <div class="block-icon">{{ jv_icon('image', '', 20) }}</div>
                <div><div class="block-label">Image</div><div class="block-desc">Add a picture</div></div>
            </div>
            <div class="block-item" data-type="quote" draggable="true">
                <div class="block-icon">{{ jv_icon('quote', '', 20) }}</div>
                <div><div class="block-label">Quote</div><div class="block-desc">Testimonial or pull quote</div></div>
            </div>
            <div class="block-item" data-type="tabs" draggable="true">
                <div class="block-icon">{{ jv_icon('panel-top-open', '', 20) }}</div>
                <div><div class="block-label">Tabs</div><div class="block-desc">Tabbed content area</div></div>
            </div>
            <div class="block-item" data-type="video" draggable="true">
                <div class="block-icon">{{ jv_icon('video', '', 20) }}</div>
                <div><div class="block-label">Video</div><div class="block-desc">YouTube or Vimeo</div></div>
            </div>
            <div class="block-item" data-type="button" draggable="true">
                <div class="block-icon">{{ jv_icon('square-mouse-pointer', '', 20) }}</div>
                <div><div class="block-label">Button</div><div class="block-desc">Call to action</div></div>
            </div>
            <div class="block-item" data-type="shortcode" draggable="true">
                <div class="block-icon">{{ jv_icon('brackets', '', 20) }}</div>
                <div><div class="block-label">Shortcode</div><div class="block-desc">[slider], [pricing], [form]</div></div>
            </div>
            <div class="block-item" data-type="domain-search" draggable="true">
                <div class="block-icon">{{ jv_icon('search', '', 20) }}</div>
                <div><div class="block-label">Domain Search</div><div class="block-desc">Live domain checker</div></div>
            </div>
            <div class="block-item" data-type="page-hero" draggable="true">
                <div class="block-icon">{{ jv_icon('gallery-horizontal-end', '', 20) }}</div>
                <div><div class="block-label">Page Hero</div><div class="block-desc">Title banner with background</div></div>
            </div>
            <div class="block-item" data-type="spacer" draggable="true">
                <div class="block-icon">{{ jv_icon('between-horizontal-start', '', 20) }}</div>
                <div><div class="block-label">Spacer</div><div class="block-desc">Empty space</div></div>
            </div>
        </div>
    </aside>

    <div class="builder-main">
        <div class="builder-toolbar">
            <span class="page-title">{{ $page->title }}</span>
            <div style="display: flex; gap: 8px; align-items: center;">
                <div class="preview-toggles" style="display: flex; gap: 2px; background: var(--builder-bg); border-radius: 6px; padding: 3px; margin-right: 12px;">
                    <button class="preview-toggle active" data-mode="desktop" title="Desktop" style="padding: 4px 10px; border: none; border-radius: 4px; cursor: pointer; font-size: 14px; background: transparent; color: var(--builder-text);">{{ jv_icon('monitor', '', 16) }}</button>
                    <button class="preview-toggle" data-mode="tablet" title="Tablet" style="padding: 4px 10px; border: none; border-radius: 4px; cursor: pointer; font-size: 14px; background: transparent; color: var(--builder-text);">{{ jv_icon('tablet', '', 16) }}</button>
                </div>
                <button id="previewBtn" class="builder-btn builder-btn-outline">{{ jv_icon('eye', '', 16) }} Preview</button>
                <button id="saveBtn" class="builder-btn builder-btn-primary">{{ jv_icon('save', '', 16) }} Save</button>
                <a href="{{ route('admin.jv-builder.index') }}" class="builder-btn builder-btn-outline">{{ jv_icon('arrow-left', '', 16) }} Back</a>
            </div>
        </div>

        <div class="builder-canvas">
            <div class="canvas-container" id="canvas"></div>
        </div>
    </div>

    <aside class="builder-settings" id="settingsPanel">
        <div class="settings-header">
            <span id="settingsTitle">Block Settings</span>
            <button class="builder-btn builder-btn-outline" onclick="builder.closeSettings()" style="padding: 4px 8px;">{{ jv_icon('x', '', 16) }}</button>
        </div>
        <div class="settings-body" id="settingsBody"></div>
    </aside>
</div>

<script src="{{ $builderJsUrl }}"></script>
<script>
    window.builder = new JamViniBuilder({
        saveUrl: '{{ route('admin.jv-builder.pages.save', $page) }}',
        previewUrl: '{{ route('admin.cms.pages.preview', $page) }}',
        builderCssUrl: @json($builderCssUrl),
        frontendCssUrl: @json($frontendCssUrl),
        csrfToken: '{{ csrf_token() }}',
        initialBlocks: {!! json_encode($page->blocks ?? []) !!}
    });
</script>
</body>
</html>
