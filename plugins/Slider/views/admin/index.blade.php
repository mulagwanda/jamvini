@extends('themes.default::layouts.admin')

@section('title', 'Sliders')
@section('breadcrumbs')<span class="current">Sliders</span>@endsection

@section('content')
<div class="page-header">
    <div style="display:flex;justify-content:space-between;align-items:center;gap:16px;flex-wrap:wrap;">
        <div>
            <h1 class="page-title">Sliders</h1>
            <p class="page-subtitle">Build professional hero sliders and reusable content carousels.</p>
        </div>
        <a href="{{ route('admin.slider.create') }}" class="btn btn-primary">New Slider</a>
    </div>
</div>

<style>
.slider-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(310px,1fr)); gap:18px; }
.slider-card { background:#fff; border:1px solid var(--jv-gray-200); border-radius:8px; overflow:hidden; display:flex; flex-direction:column; }
.slider-cover { min-height:150px; background:linear-gradient(135deg,#0f172a,#2563eb); display:flex; align-items:flex-end; padding:18px; color:#fff; }
.slider-cover h3 { margin:0; color:#fff; }
.slider-body { padding:16px; display:grid; gap:10px; }
.slider-meta { display:flex; gap:8px; flex-wrap:wrap; align-items:center; }
.slider-actions { padding:14px 16px; border-top:1px solid var(--jv-gray-200); display:flex; gap:8px; justify-content:flex-end; flex-wrap:wrap; }
</style>

@if($sliders->count() > 0)
    <div class="slider-grid">
        @foreach($sliders as $slider)
            <article class="slider-card">
                <div class="slider-cover">
                    <div>
                        <div style="font-size:.8rem;opacity:.8;margin-bottom:6px;">{{ ucfirst($slider->type) }} Slider</div>
                        <h3>{{ $slider->title }}</h3>
                    </div>
                </div>
                <div class="slider-body">
                    <div class="slider-meta">
                        <span class="badge badge-gray">v1</span>
                        <span class="badge badge-info">{{ $slider->slides_count }} slides</span>
                        <span class="badge badge-{{ $slider->is_active ? 'success' : 'gray' }}">{{ $slider->is_active ? 'Active' : 'Inactive' }}</span>
                    </div>
                    <div>
                        <label class="form-label" style="margin-bottom:4px;">Shortcode</label>
                        <code style="display:block;white-space:normal;">[slider slug="{{ $slider->slug }}"]</code>
                    </div>
                </div>
                <div class="slider-actions">
                    <a href="{{ route('admin.slider.studio', $slider) }}" class="btn btn-sm btn-primary">Studio</a>
                    <a href="{{ route('admin.slider.edit', $slider) }}" class="btn btn-sm btn-primary">Edit</a>
                    <form action="{{ route('admin.slider.destroy', $slider) }}" method="POST" data-confirm="Delete this slider?" data-danger="true">
                        @csrf @method('DELETE')
                        <button class="btn btn-sm btn-outline-danger">Delete</button>
                    </form>
                </div>
            </article>
        @endforeach
    </div>
    <div style="margin-top:18px;">{{ $sliders->links() }}</div>
@else
    <div class="empty-state" style="padding:60px;">
        <div class="empty-state-icon">SL</div>
        <div class="empty-state-title">No sliders yet</div>
        <div class="empty-state-desc">Create a slider, add slides, then place it with a shortcode.</div>
        <a href="{{ route('admin.slider.create') }}" class="btn btn-primary">Create First Slider</a>
    </div>
@endif
@endsection
