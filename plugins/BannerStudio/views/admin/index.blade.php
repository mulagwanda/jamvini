@extends('themes.default::layouts.admin')

@section('title', 'Banner Studio')
@section('breadcrumbs')<span class="current">Banner Studio</span>@endsection

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Banner Studio</h1>
        <p class="page-subtitle">Create hero banners with draggable text, images, buttons, shapes, and search layers.</p>
    </div>
    <a href="{{ route('admin.banner-studio.create') }}" class="btn btn-primary">{{ jv_icon('plus', '', 16) }} New Banner</a>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">{{ jv_icon('panel-top', '', 18) }} Banners</h3>
        <span class="badge badge-gray">{{ $banners->total() }} total</span>
    </div>
    <div class="card-body" style="padding:0;">
        @if($banners->count())
            <table class="table" style="margin:0;">
                <thead><tr><th>Banner</th><th>Shortcode</th><th>Status</th><th>Updated</th><th style="text-align:right;">Actions</th></tr></thead>
                <tbody>
                    @foreach($banners as $banner)
                        <tr>
                            <td><strong>{{ $banner->title }}</strong><br><small style="color:var(--jv-gray-500);">{{ $banner->slug }}</small></td>
                            <td><code>[banner slug="{{ $banner->slug }}"]</code></td>
                            <td><span class="pill pill-{{ $banner->is_active ? 'ok' : 'mute' }}">{{ $banner->is_active ? 'Active' : 'Inactive' }}</span></td>
                            <td>{{ $banner->updated_at?->diffForHumans() }}</td>
                            <td style="text-align:right;">
                                <a href="{{ route('admin.banner-studio.studio', $banner) }}" class="btn btn-sm btn-primary">{{ jv_icon('wand-sparkles', '', 15) }} Studio</a>
                                <a href="{{ route('admin.banner-studio.edit', $banner) }}" class="btn btn-sm btn-outline-primary">{{ jv_icon('settings', '', 15) }}</a>
                                <form action="{{ route('admin.banner-studio.destroy', $banner) }}" method="POST" style="display:inline;" data-confirm="Delete this banner?" data-danger="true">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger">{{ jv_icon('trash-2', '', 15) }}</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="empty-state">
                <div class="empty-state-icon">{{ jv_icon('panel-top', '', 42) }}</div>
                <div class="empty-state-title">No banners yet</div>
                <div class="empty-state-desc">Create a hero banner for your homepage, promos, or landing pages.</div>
                <a href="{{ route('admin.banner-studio.create') }}" class="btn btn-primary">{{ jv_icon('plus', '', 16) }} Create Banner</a>
            </div>
        @endif
    </div>
</div>
{{ $banners->links() }}
@endsection
