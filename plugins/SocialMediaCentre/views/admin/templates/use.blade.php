@extends('themes.default::layouts.admin')

@section('title', 'Use Template')
@section('breadcrumbs')<a href="{{ route('admin.social.templates.index') }}">Templates</a> <span class="separator">/</span> <span class="current">Use Template</span>@endsection

@push('styles')
<style>
.template-use-layout { display:grid; grid-template-columns:minmax(0,1fr) minmax(340px,430px); gap:18px; align-items:start; }
.variable-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(220px,1fr)); gap:12px; }
.variable-help { color:var(--jv-gray-500); font-size:.82rem; line-height:1.5; margin-bottom:16px; }
.template-preview-card { position:sticky; top:84px; border:1px solid var(--jv-gray-200); background:#fff; border-radius:12px; overflow:hidden; box-shadow:0 16px 40px rgba(15,23,42,.08); }
.template-preview-head { padding:14px 16px; border-bottom:1px solid var(--jv-gray-100); }
.template-preview-head h3 { margin:0 0 4px; font-size:1rem; }
.template-preview-body { padding:16px; }
.preview-title { font-weight:900; color:var(--jv-gray-900); margin-bottom:12px; }
.preview-caption { white-space:pre-wrap; line-height:1.55; color:var(--jv-gray-700); }
.preview-tags { margin-top:12px; color:var(--jv-primary); font-weight:800; line-height:1.5; }
.template-chip-row { display:flex; gap:6px; flex-wrap:wrap; margin-top:12px; }
.template-chip { border:1px solid var(--jv-gray-200); border-radius:999px; padding:4px 8px; color:var(--jv-gray-600); background:#fff; font-size:.7rem; font-weight:800; }
.empty-vars { border:1px dashed var(--jv-gray-300); border-radius:10px; padding:18px; color:var(--jv-gray-500); background:#f8fafc; }
@media (max-width:980px) { .template-use-layout { grid-template-columns:1fr; } .template-preview-card { position:static; } }
</style>
@endpush

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Use Template</h1>
        <p class="page-subtitle">{{ $template->name }} - fill the placeholders, then continue to the composer.</p>
    </div>
    <a href="{{ route('admin.social.templates.index') }}" class="btn btn-outline-primary">Back to Templates</a>
</div>

<form action="{{ route('admin.social.templates.compose', $template) }}" method="POST">
    @csrf
    <div class="template-use-layout">
        <div class="dash-card">
            <div class="dash-card-head"><h3>Template Variables</h3></div>
            <p class="variable-help">
                Fill only what you need. Empty fields remain visible as bracket placeholders in the composer, so the writer can finish them later.
            </p>

            @if(count($variables))
                <div class="variable-grid">
                    @foreach($variables as $variable)
                        <div class="form-group">
                            <label class="form-label">{{ str($variable)->replace('_', ' ')->title() }}</label>
                            <input
                                name="variables[{{ $variable }}]"
                                class="form-input"
                                value="{{ old('variables.' . $variable) }}"
                                placeholder="{{ str($variable)->replace('_', ' ')->title() }}"
                                data-template-variable="{{ $variable }}">
                        </div>
                    @endforeach
                </div>
            @else
                <div class="empty-vars">This template has no variables. Continue to the composer to customize it.</div>
            @endif

            <div style="display:flex;justify-content:flex-end;gap:10px;margin-top:1rem;">
                <a href="{{ route('admin.social.templates.index') }}" class="btn btn-outline-danger">Cancel</a>
                <button class="btn btn-primary btn-lg">Continue to Composer</button>
            </div>
        </div>

        <aside class="template-preview-card">
            <div class="template-preview-head">
                <h3>Live Copy Preview</h3>
                <div style="color:var(--jv-gray-500);font-size:.82rem;">{{ $template->description ?: 'Reusable social post template' }}</div>
                <div class="template-chip-row">
                    <span class="template-chip">{{ ucfirst(str_replace('-', ' ', $template->category)) }}</span>
                    @foreach($template->platforms ?? [] as $platform)
                        <span class="template-chip">{{ $platforms[$platform] ?? $platform }}</span>
                    @endforeach
                </div>
            </div>
            <div class="template-preview-body">
                <div id="previewTitle" class="preview-title"></div>
                <div id="previewCaption" class="preview-caption"></div>
                @if($template->hashtags)
                    <div class="preview-tags">{{ implode(' ', $template->hashtags) }}</div>
                @endif
            </div>
        </aside>
    </div>
</form>
@endsection

@push('scripts')
<script>
const rawTitle = @json($template->title_template);
const rawCaption = @json($template->caption_template);
const previewTitle = document.getElementById('previewTitle');
const previewCaption = document.getElementById('previewCaption');

document.querySelectorAll('[data-template-variable]').forEach(input => {
    input.addEventListener('input', renderTemplatePreview);
});

function renderTemplatePreview() {
    const values = {};
    document.querySelectorAll('[data-template-variable]').forEach(input => {
        values[input.dataset.templateVariable] = input.value.trim();
    });
    previewTitle.textContent = replaceTemplate(rawTitle, values);
    previewCaption.textContent = replaceTemplate(rawCaption, values);
}

function replaceTemplate(value, values) {
    const pattern = new RegExp('\\{\\{\\s*([a-zA-Z0-9_]+)\\s*\\}\\}', 'g');
    return String(value).replace(pattern, (_, key) => values[key] || '[' + key.replaceAll('_', ' ') + ']');
}

renderTemplatePreview();
</script>
@endpush
