@php
    $item = $item ?? null;
    $type = $type ?? 'page';
    $isPost = $type === 'post';
    $titleValue = old('title', $item->title ?? '');
    $slugValue = old('slug', $item->slug ?? '');
    $contentValue = old('content', $item->content ?? '');
    $excerptValue = old('excerpt', $item->excerpt ?? '');
    $statusValue = old('status', $item->status ?? 'draft');
    $featuredImageValue = old('featured_image', $item->featured_image ?? '');
    $metaTitleValue = old('meta_title', $item->meta_title ?? '');
    $metaDescriptionValue = old('meta_description', $item->meta_description ?? '');
    $previewUrl = $previewUrl ?? null;
@endphp

<style>
.cms-editor-grid { display: grid; grid-template-columns: minmax(0, 1fr) 320px; gap: 20px; align-items: start; }
.cms-editor-toolbar { display: flex; gap: 6px; flex-wrap: wrap; padding: 8px; background: var(--jv-gray-50); border: 1px solid var(--jv-gray-200); border-bottom: 0; border-radius: 8px 8px 0 0; align-items:center; }
.cms-tool,
.cms-format-select,
.cms-color { min-width: 34px; height: 32px; border: 1px solid var(--jv-gray-200); background: #fff; border-radius: 6px; cursor: pointer; font-weight: 700; color: var(--jv-gray-700); }
.cms-tool { padding: 0 8px; display:inline-flex; align-items:center; justify-content:center; gap:6px; }
.cms-tool.is-active { color:var(--jv-primary); border-color:var(--jv-primary); background:#f8f7ff; }
.cms-format-select { min-width: 150px; padding: 0 10px; font-weight: 600; }
.cms-color { width:34px; padding:3px; }
.cms-toolbar-separator { width: 1px; height: 28px; background: var(--jv-gray-200); margin: 2px 2px; }
.cms-editor-surface { min-height: 420px; padding: 18px; border: 1px solid var(--jv-gray-200); border-radius: 0 0 8px 8px; background: #fff; outline: none; line-height: 1.7; }
.cms-editor-source { display:none; width:100%; min-height:420px; padding:18px; border:1px solid var(--jv-gray-200); border-radius:0 0 8px 8px; font-family:ui-monospace,SFMono-Regular,Menlo,Consolas,monospace; line-height:1.55; }
.cms-editor-wrap.is-source .cms-editor-surface { display:none; }
.cms-editor-wrap.is-source .cms-editor-source { display:block; }
.cms-editor-surface:focus { border-color: var(--jv-primary); box-shadow: 0 0 0 3px rgba(108,92,231,.08); }
.cms-side-card { background: #fff; border: 1px solid var(--jv-gray-200); border-radius: 8px; padding: 16px; margin-bottom: 16px; }
.cms-featured-preview { width: 100%; min-height: 150px; border: 1px dashed var(--jv-gray-300); border-radius: 8px; display: grid; place-items: center; background: var(--jv-gray-50); overflow: hidden; color: var(--jv-gray-500); }
.cms-featured-preview img { width: 100%; height: 170px; object-fit: cover; display: block; }
.cms-media-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(110px, 1fr)); gap: 10px; max-height: 360px; overflow: auto; }
.cms-media-choice { border: 1px solid var(--jv-gray-200); border-radius: 8px; overflow: hidden; background: #fff; cursor: pointer; text-align: left; }
.cms-media-choice img { width: 100%; height: 86px; object-fit: cover; display: block; }
.cms-media-choice span { display: block; padding: 6px; font-size: .7rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.cms-permalink { display: grid; grid-template-columns: minmax(260px, max-content) minmax(260px, 1fr); align-items: stretch; }
.cms-permalink span { display: flex; align-items: center; min-width: 260px; padding: 10px 12px; background: var(--jv-gray-50); border: 1px solid var(--jv-gray-200); border-right: 0; border-radius: 8px 0 0 8px; color: var(--jv-gray-500); font-size: .85rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.cms-permalink input { border-radius: 0 8px 8px 0; min-width: 0; }
@media (max-width: 980px) { .cms-editor-grid { grid-template-columns: 1fr; } }
@media (max-width: 640px) {
    .cms-permalink { grid-template-columns: 1fr; }
    .cms-permalink span { min-width: 0; border-right: 1px solid var(--jv-gray-200); border-radius: 8px 8px 0 0; }
    .cms-permalink input { border-radius: 0 0 8px 8px; }
}
</style>

<div class="cms-editor-grid">
    <div>
        <div class="card" style="margin-bottom: 16px;">
            <div class="card-body">
                <div class="form-group">
                    <label class="form-label" for="title">Title</label>
                    <input type="text" id="title" name="title" class="form-input" value="{{ $titleValue }}" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="slug">Permalink</label>
                    <div class="cms-permalink">
                        <span>{{ url($isPost ? '/blog' : '/') }}/</span>
                        <input type="text" id="slug" name="slug" class="form-input" value="{{ $slugValue }}" placeholder="auto-generated-from-title">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="excerpt">{{ $isPost ? 'Excerpt' : 'Summary' }}</label>
                    <textarea id="excerpt" name="excerpt" class="form-textarea" rows="3">{{ $excerptValue }}</textarea>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <label class="form-label" for="content">Content</label>
                <div class="cms-editor-toolbar" aria-label="Editor toolbar">
                    <select class="cms-format-select" data-action="format" title="Text style">
                        <option value="p">Paragraph</option>
                        <option value="h2">Heading 2</option>
                        <option value="h3">Heading 3</option>
                        <option value="h4">Heading 4</option>
                        <option value="blockquote">Quote</option>
                        <option value="pre">Code Block</option>
                    </select>
                    <span class="cms-toolbar-separator" aria-hidden="true"></span>
                    <button type="button" class="cms-tool" data-command="bold" title="Bold">{{ jv_icon('bold', '', 16) }}</button>
                    <button type="button" class="cms-tool" data-command="italic" title="Italic">{{ jv_icon('italic', '', 16) }}</button>
                    <button type="button" class="cms-tool" data-command="underline" title="Underline">{{ jv_icon('underline', '', 16) }}</button>
                    <button type="button" class="cms-tool" data-command="strikeThrough" title="Strikethrough">{{ jv_icon('strikethrough', '', 16) }}</button>
                    <span class="cms-toolbar-separator" aria-hidden="true"></span>
                    <button type="button" class="cms-tool" data-command="insertUnorderedList" title="Bulleted List">{{ jv_icon('list', '', 16) }}</button>
                    <button type="button" class="cms-tool" data-command="insertOrderedList" title="Numbered List">{{ jv_icon('list-ordered', '', 16) }}</button>
                    <button type="button" class="cms-tool" data-command="outdent" title="Outdent">{{ jv_icon('outdent', '', 16) }}</button>
                    <button type="button" class="cms-tool" data-command="indent" title="Indent">{{ jv_icon('indent', '', 16) }}</button>
                    <span class="cms-toolbar-separator" aria-hidden="true"></span>
                    <button type="button" class="cms-tool" data-command="justifyLeft" title="Align Left">{{ jv_icon('align-left', '', 16) }}</button>
                    <button type="button" class="cms-tool" data-command="justifyCenter" title="Align Center">{{ jv_icon('align-center', '', 16) }}</button>
                    <button type="button" class="cms-tool" data-command="justifyRight" title="Align Right">{{ jv_icon('align-right', '', 16) }}</button>
                    <button type="button" class="cms-tool" data-command="justifyFull" title="Justify">{{ jv_icon('align-justify', '', 16) }}</button>
                    <span class="cms-toolbar-separator" aria-hidden="true"></span>
                    <input type="color" class="cms-color" data-action="foreColor" value="#111827" title="Text color">
                    <input type="color" class="cms-color" data-action="hiliteColor" value="#fff3bf" title="Highlight color">
                    <span class="cms-toolbar-separator" aria-hidden="true"></span>
                    <button type="button" class="cms-tool" data-command="undo" title="Undo">{{ jv_icon('undo-2', '', 16) }}</button>
                    <button type="button" class="cms-tool" data-command="redo" title="Redo">{{ jv_icon('redo-2', '', 16) }}</button>
                    <button type="button" class="cms-tool" data-action="link" title="Link">{{ jv_icon('link', '', 16) }}</button>
                    <button type="button" class="cms-tool" data-command="unlink" title="Remove Link">{{ jv_icon('unlink', '', 16) }}</button>
                    <button type="button" class="cms-tool" data-action="image" title="Insert Image">{{ jv_icon('image', '', 16) }}</button>
                    <button type="button" class="cms-tool" data-action="hr" title="Horizontal Rule">{{ jv_icon('minus', '', 16) }}</button>
                    <button type="button" class="cms-tool" data-command="removeFormat" title="Clear Formatting">{{ jv_icon('eraser', '', 16) }}</button>
                    <button type="button" class="cms-tool" data-action="source" title="Source">{{ jv_icon('code-2', '', 16) }}</button>
                </div>
                <div class="cms-editor-wrap" id="cmsEditorWrap">
                    <div id="cmsEditor" class="cms-editor-surface" contenteditable="true">{!! $contentValue !!}</div>
                    <textarea id="cmsSourceEditor" class="cms-editor-source" spellcheck="false">{{ $contentValue }}</textarea>
                </div>
                <textarea id="content" name="content" style="display:none;">{{ $contentValue }}</textarea>
            </div>
        </div>
    </div>

    <aside>
        <div class="cms-side-card">
            <h3 style="margin: 0 0 12px; font-size: 1rem;">Publish</h3>
            <div class="form-group">
                <label class="form-label" for="status">Status</label>
                <select id="status" name="status" class="form-select">
                    <option value="draft" {{ $statusValue === 'draft' ? 'selected' : '' }}>Draft</option>
                    <option value="published" {{ $statusValue === 'published' ? 'selected' : '' }}>Published</option>
                </select>
            </div>

            @if($isPost)
                <div class="form-group">
                    <label class="form-label" for="published_at">Publish Date</label>
                    <input type="datetime-local" id="published_at" name="published_at" class="form-input" value="{{ old('published_at', optional($item?->published_at ?? null)->format('Y-m-d\TH:i')) }}">
                </div>
            @else
                <div class="form-group">
                    <label class="form-label" for="template">Template</label>
                    <select id="template" name="template" class="form-select">
                        <option value="default" {{ old('template', $item->template ?? 'default') === 'default' ? 'selected' : '' }}>Default</option>
                        <option value="full-width" {{ old('template', $item->template ?? '') === 'full-width' ? 'selected' : '' }}>Full Width</option>
                    </select>
                </div>
            @endif

            <div style="display: grid; gap: 8px; margin-top: 12px;">
                <button type="submit" class="btn btn-primary">{{ $item ? 'Save Changes' : ($isPost ? 'Create Post' : 'Create Page') }}</button>
                @if($previewUrl)
                    <a href="{{ $previewUrl }}" target="_blank" class="btn btn-outline-primary">Preview</a>
                @endif
            </div>
        </div>

        @if($isPost)
            <div class="cms-side-card">
                <h3 style="margin: 0 0 12px; font-size: 1rem;">Categories</h3>
                @forelse($categories as $cat)
                    <label class="checkbox-group">
                        <input type="checkbox" name="categories[]" value="{{ $cat->id }}" {{ in_array($cat->id, old('categories', $item ? $item->categories->pluck('id')->all() : [])) ? 'checked' : '' }}>
                        {{ $cat->name }}
                    </label>
                @empty
                    <p style="color: var(--jv-gray-500); margin: 0;">No categories yet.</p>
                @endforelse
            </div>
        @endif

        <div class="cms-side-card">
            <h3 style="margin: 0 0 12px; font-size: 1rem;">Featured Image</h3>
            <input type="hidden" id="featured_image" name="featured_image" value="{{ $featuredImageValue }}">
            <div id="featuredPreview" class="cms-featured-preview">
                @if($featuredImageValue)
                    <img src="{{ Str::startsWith($featuredImageValue, ['http://', 'https://', '/']) ? $featuredImageValue : asset('storage/' . $featuredImageValue) }}" alt="">
                @else
                    <span>No image selected</span>
                @endif
            </div>
            <button type="button" class="btn btn-outline-primary btn-sm" style="margin-top: 10px;" onclick="openCmsMediaPicker('featured')">Choose Image</button>
            <button type="button" class="btn btn-outline-danger btn-sm" style="margin-top: 10px;" onclick="clearFeaturedImage()">Remove</button>
        </div>

        <div class="cms-side-card">
            <h3 style="margin: 0 0 12px; font-size: 1rem;">Search Preview</h3>
            <div class="form-group">
                <label class="form-label" for="meta_title">Meta Title</label>
                <input type="text" id="meta_title" name="meta_title" class="form-input" value="{{ $metaTitleValue }}" maxlength="255">
            </div>
            <div class="form-group">
                <label class="form-label" for="meta_description">Meta Description</label>
                <textarea id="meta_description" name="meta_description" class="form-textarea" rows="4" maxlength="500">{{ $metaDescriptionValue }}</textarea>
            </div>
        </div>
    </aside>
</div>

<div id="cmsMediaModal" style="display:none; position: fixed; inset: 0; z-index: 1200; background: rgba(15,23,42,.72); padding: 32px;">
    <div style="background: #fff; border-radius: 10px; max-width: 920px; max-height: 86vh; margin: 0 auto; overflow: hidden; display: flex; flex-direction: column;">
        <div style="padding: 16px 20px; border-bottom: 1px solid var(--jv-gray-200); display: flex; align-items: center; justify-content: space-between;">
            <h3 style="margin: 0;">Media Library</h3>
            <button type="button" class="btn btn-sm btn-outline-danger" onclick="closeCmsMediaPicker()">Close</button>
        </div>
        <div style="padding: 18px;">
            <div class="cms-media-grid">
                @foreach(($media ?? collect()) as $file)
                    <button type="button" class="cms-media-choice" data-url="{{ $file->url }}" data-path="{{ $file->path }}" data-name="{{ $file->original_name }}">
                        <img src="{{ $file->thumbnail_url }}" alt="{{ $file->alt_text ?? $file->original_name }}">
                        <span>{{ $file->original_name }}</span>
                    </button>
                @endforeach
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
let cmsMediaTarget = 'featured';

function slugifyCms(value) {
    return value.toString().toLowerCase().trim()
        .replace(/['"]/g, '')
        .replace(/[^a-z0-9]+/g, '-')
        .replace(/^-+|-+$/g, '');
}

function syncCmsEditor() {
    const editor = document.getElementById('cmsEditor');
    const content = document.getElementById('content');
    const source = document.getElementById('cmsSourceEditor');
    const wrap = document.getElementById('cmsEditorWrap');
    if (!content) return;
    content.value = wrap?.classList.contains('is-source') ? source.value.trim() : editor.innerHTML.trim();
}

function openCmsMediaPicker(target) {
    cmsMediaTarget = target;
    document.getElementById('cmsMediaModal').style.display = 'block';
}

function closeCmsMediaPicker() {
    document.getElementById('cmsMediaModal').style.display = 'none';
}

function setFeaturedImage(path, url) {
    document.getElementById('featured_image').value = path;
    document.getElementById('featuredPreview').innerHTML = `<img src="${url}" alt="">`;
}

function clearFeaturedImage() {
    document.getElementById('featured_image').value = '';
    document.getElementById('featuredPreview').innerHTML = '<span>No image selected</span>';
}

document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('cmsEditorForm');
    const title = document.getElementById('title');
    const slug = document.getElementById('slug');
    let slugTouched = Boolean(slug?.value);

    slug?.addEventListener('input', () => {
        slugTouched = true;
        slug.value = slugifyCms(slug.value);
    });

    title?.addEventListener('input', () => {
        if (!slugTouched && slug) slug.value = slugifyCms(title.value);
    });

    form?.addEventListener('submit', syncCmsEditor);

    document.querySelectorAll('.cms-format-select').forEach(select => {
        select.addEventListener('change', () => {
            document.getElementById('cmsEditor')?.focus();
            document.execCommand('formatBlock', false, select.value);
            syncCmsEditor();
        });
    });

    document.querySelectorAll('.cms-tool').forEach(button => {
        button.addEventListener('click', () => {
            const action = button.dataset.action;
            const command = button.dataset.command;
            const value = button.dataset.value || null;
            document.getElementById('cmsEditor')?.focus();

            if (action === 'link') {
                const url = prompt('Enter URL');
                if (url) document.execCommand('createLink', false, url);
                syncCmsEditor();
                return;
            }

            if (action === 'image') {
                openCmsMediaPicker('content');
                return;
            }

            if (action === 'hr') {
                document.execCommand('insertHorizontalRule', false, null);
                syncCmsEditor();
                return;
            }

            if (action === 'source') {
                const wrap = document.getElementById('cmsEditorWrap');
                const editor = document.getElementById('cmsEditor');
                const source = document.getElementById('cmsSourceEditor');
                if (wrap.classList.contains('is-source')) {
                    editor.innerHTML = source.value;
                    wrap.classList.remove('is-source');
                    editor.focus();
                } else {
                    source.value = editor.innerHTML.trim();
                    wrap.classList.add('is-source');
                    source.focus();
                }
                syncCmsEditor();
                return;
            }

            if (command) document.execCommand(command, false, value);
            syncCmsEditor();
        });
    });

    document.querySelectorAll('.cms-color').forEach(input => {
        input.addEventListener('input', () => {
            document.getElementById('cmsEditor')?.focus();
            document.execCommand(input.dataset.action, false, input.value);
            syncCmsEditor();
        });
    });

    const cmsEditor = document.getElementById('cmsEditor');
    const cmsSource = document.getElementById('cmsSourceEditor');
    cmsEditor?.addEventListener('input', syncCmsEditor);
    cmsSource?.addEventListener('input', syncCmsEditor);
    cmsEditor?.addEventListener('keyup', refreshCmsToolbar);
    cmsEditor?.addEventListener('mouseup', refreshCmsToolbar);
    cmsEditor?.addEventListener('paste', event => {
        event.preventDefault();
        const text = (event.clipboardData || window.clipboardData).getData('text/plain');
        document.execCommand('insertText', false, text);
        syncCmsEditor();
    });

    document.querySelectorAll('.cms-media-choice').forEach(choice => {
        choice.addEventListener('click', () => {
            const url = choice.dataset.url;
            const path = choice.dataset.path;

            if (cmsMediaTarget === 'content') {
                document.getElementById('cmsEditor')?.focus();
                document.execCommand('insertImage', false, url);
                syncCmsEditor();
            } else {
                setFeaturedImage(path, url);
            }

            closeCmsMediaPicker();
        });
    });
});

function refreshCmsToolbar() {
    document.querySelectorAll('.cms-tool[data-command]').forEach(button => {
        try {
            button.classList.toggle('is-active', document.queryCommandState(button.dataset.command));
        } catch (e) {}
    });
}
</script>
@endpush
