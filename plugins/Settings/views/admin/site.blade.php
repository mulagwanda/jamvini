@extends('themes.default::layouts.admin')

@section('title', 'Site Settings')
@section('breadcrumbs')<span class="current">Site Settings</span>@endsection

@section('content')
<div class="page-header">
    <h1 class="page-title">Site Settings</h1>
    <p class="page-subtitle">Configure how your website appears to visitors</p>
</div>

<form action="{{ route('admin.settings.site.update') }}" method="POST">
    @csrf
    
    <div class="card">
        <div class="card-header"><h3 class="card-title">🏠 Front Page Display</h3></div>
        <div class="card-body">
            <div class="form-group">
                <label class="form-label">Homepage displays</label>
                <div style="display: flex; flex-direction: column; gap: 12px;">
                    <label class="checkbox-group">
                        <input type="radio" name="homepage_type" value="page" {{ $homepageType === 'page' ? 'checked' : '' }} onchange="toggleHomepageOptions()">
                        <span><strong>A static page</strong></span>
                    </label>
                    <label class="checkbox-group">
                        <input type="radio" name="homepage_type" value="posts" {{ $homepageType === 'posts' ? 'checked' : '' }} onchange="toggleHomepageOptions()">
                        <span><strong>Latest posts</strong> (blog style)</span>
                    </label>
                    <label class="checkbox-group">
                        <input type="radio" name="homepage_type" value="landing" {{ $homepageType === 'landing' ? 'checked' : '' }} onchange="toggleHomepageOptions()">
                        <span><strong>Built-in landing page</strong> (default)</span>
                    </label>
                </div>
            </div>
            
            <div id="pageOptions" style="{{ $homepageType === 'page' || $homepageType === 'posts' ? '' : 'display: none;' }} margin-top: 20px; padding: 20px; background: var(--jv-gray-50); border-radius: var(--jv-radius-md);">
                <div class="form-group" id="homepagePageGroup" style="{{ $homepageType === 'page' ? '' : 'display: none;' }}">
                    <label class="form-label" for="homepage_page_id">Homepage</label>
                    <select id="homepage_page_id" name="homepage_page_id" class="form-select">
                        <option value="">— Select a page —</option>
                        @foreach($pages as $page)
                            <option value="{{ $page->id }}" {{ $homepagePageId == $page->id ? 'selected' : '' }}>
                                {{ $page->title }}
                            </option>
                        @endforeach
                    </select>
                    <div class="form-hint">This page will be shown as your website's homepage</div>
                </div>
                
                <div class="form-group" id="postsPageGroup" style="{{ $homepageType === 'posts' ? '' : 'display: none;' }}">
                    <label class="form-label" for="posts_page_id">Posts Page</label>
                    <select id="posts_page_id" name="posts_page_id" class="form-select">
                        <option value="">— Select a page —</option>
                        @foreach($pages as $page)
                            <option value="{{ $page->id }}" {{ $postsPageId == $page->id ? 'selected' : '' }}>
                                {{ $page->title }}
                            </option>
                        @endforeach
                    </select>
                    <div class="form-hint">This page will display your latest blog posts</div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header"><h3 class="card-title">🔤 Site Identity</h3></div>
        <div class="card-body">
            <div class="form-group">
                <label class="form-label" for="site_title">Site Title</label>
                <input type="text" id="site_title" name="site_title" class="form-input" 
                       value="{{ old('site_title', \App\Models\Setting::get('site_title', config('app.name'))) }}">
            </div>
            <div class="form-group">
                <label class="form-label" for="site_tagline">Tagline</label>
                <input type="text" id="site_tagline" name="site_tagline" class="form-input" 
                       value="{{ old('site_tagline', \App\Models\Setting::get('site_tagline', '')) }}" 
                       placeholder="Just another JamVini site">
                <div class="form-hint">A short description of your website</div>
            </div>
            <div class="form-group">
                <label class="form-label" for="site_description">Default Meta Description</label>
                <textarea id="site_description" name="site_description" class="form-textarea" rows="2" placeholder="A short search engine description for pages without custom SEO text.">{{ old('site_description', \App\Models\Setting::get('site_description', '')) }}</textarea>
            </div>
        </div>
    </div>
    
    <div style="display: flex; gap: 12px; justify-content: flex-end; margin-top: 16px;">
        <button type="submit" class="btn btn-primary btn-lg">💾 Save Site Settings</button>
    </div>
</form>
@endsection

@push('scripts')
<script>
function toggleHomepageOptions() {
    const type = document.querySelector('input[name="homepage_type"]:checked').value;
    document.getElementById('pageOptions').style.display = (type === 'page' || type === 'posts') ? '' : 'none';
    document.getElementById('homepagePageGroup').style.display = type === 'page' ? '' : 'none';
    document.getElementById('postsPageGroup').style.display = type === 'posts' ? '' : 'none';
}
</script>
@endpush
