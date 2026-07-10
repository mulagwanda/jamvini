@extends('themes.default::layouts.admin')

@section('title', 'SEO Toolkit')
@section('breadcrumbs')<span class="current">SEO Toolkit</span>@endsection

@section('content')
<style>
.seo-kpi-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(180px,1fr)); gap:14px; margin-bottom:18px; }
.seo-kpi { border:1px solid var(--jv-gray-200); background:#fff; border-radius:8px; padding:16px; }
.seo-kpi strong { display:block; font-size:1.8rem; color:var(--jv-gray-900); }
.seo-grid { display:grid; grid-template-columns:minmax(0,1.25fr) minmax(320px,.75fr); gap:18px; align-items:start; }
.seo-list { display:grid; gap:10px; }
.seo-row { display:flex; justify-content:space-between; gap:14px; padding:10px 0; border-bottom:1px solid var(--jv-gray-100); }
.seo-score { width:42px; height:42px; display:grid; place-items:center; border-radius:999px; font-weight:900; background:#ecfdf5; color:#047857; }
.seo-score.warn { background:#fff7ed; color:#c2410c; }
.seo-score.bad { background:#fef2f2; color:#b91c1c; }
@media (max-width: 980px) { .seo-grid { grid-template-columns:1fr; } }
</style>

<div class="page-header">
    <div>
        <h1 class="page-title">SEO Toolkit</h1>
        <p class="page-subtitle">Search visibility, technical SEO, analytics, and visitor behavior in one place.</p>
    </div>
    <div style="display:flex;gap:8px;flex-wrap:wrap;">
        <a href="{{ route('sitemap') }}" target="_blank" class="btn btn-outline-primary">{{ jv_icon('map', '', 16) }} Sitemap</a>
        <a href="{{ route('robots') }}" target="_blank" class="btn btn-outline-primary">{{ jv_icon('bot', '', 16) }} Robots</a>
    </div>
</div>

<div class="seo-kpi-grid">
    <div class="seo-kpi"><span>Pageviews</span><strong>{{ number_format($report['pageviews']) }}</strong></div>
    <div class="seo-kpi"><span>Visitors</span><strong>{{ number_format($report['visitors']) }}</strong></div>
    <div class="seo-kpi"><span>Online Now</span><strong>{{ number_format($report['online']) }}</strong></div>
    <div class="seo-kpi"><span>Content Issues</span><strong>{{ count($contentAudit) }}</strong></div>
</div>

<div class="seo-grid">
    <main style="display:grid;gap:18px;">
        <div class="card">
            <div class="card-header"><h3 class="card-title">{{ jv_icon('bar-chart-3', '', 18) }} Analytics Report</h3></div>
            <div class="card-body seo-list">
                <h4>Top Pages</h4>
                @forelse($report['top_pages'] as $page)
                    <div class="seo-row"><span>{{ $page->path ?: '/' }}</span><strong>{{ number_format($page->views) }}</strong></div>
                @empty
                    <div class="empty-state" style="padding:22px;">No pageviews tracked yet.</div>
                @endforelse
                <h4 style="margin-top:12px;">Traffic Sources</h4>
                @foreach($report['sources'] as $source)
                    <div class="seo-row"><span>{{ $source->source ?: 'direct' }}</span><strong>{{ number_format($source->visits) }}</strong></div>
                @endforeach
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h3 class="card-title">{{ jv_icon('activity', '', 18) }} Recent Visitor Activity</h3></div>
            <div class="card-body seo-list">
                @forelse($report['recent'] as $event)
                    <div class="seo-row">
                        <div>
                            <strong>{{ $event->path ?: '/' }}</strong>
                            <div style="color:var(--jv-gray-500);font-size:.86rem;">{{ $event->device_type }} · {{ $event->browser }} · {{ optional($event->occurred_at)->diffForHumans() }}</div>
                        </div>
                        <span class="badge badge-info">{{ $event->event_type }}</span>
                    </div>
                @empty
                    <div class="empty-state" style="padding:22px;">No visitor activity yet.</div>
                @endforelse
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h3 class="card-title">{{ jv_icon('check-circle', '', 18) }} Content SEO Checks</h3></div>
            <div class="card-body seo-list">
                @forelse($contentAudit as $item)
                    @php $scoreClass = $item['score'] < 50 ? 'bad' : ($item['score'] < 80 ? 'warn' : ''); @endphp
                    <div class="seo-row">
                        <div>
                            <strong>{{ $item['title'] }}</strong>
                            <div style="color:var(--jv-gray-500);font-size:.86rem;">/{{ $item['slug'] }} · {{ implode(', ', $item['checks']) }}</div>
                        </div>
                        <span class="seo-score {{ $scoreClass }}">{{ $item['score'] }}</span>
                    </div>
                @empty
                    <div class="empty-state" style="padding:22px;">No obvious content SEO issues found.</div>
                @endforelse
            </div>
        </div>
    </main>

    <aside>
        <form action="{{ route('admin.seo.update') }}" method="POST" class="card">
            @csrf
            <div class="card-header"><h3 class="card-title">{{ jv_icon('settings', '', 18) }} SEO Settings</h3></div>
            <div class="card-body" style="display:grid;gap:14px;">
                <div class="form-group">
                    <label class="form-label" for="site_title">Site Title</label>
                    <input type="text" id="site_title" name="site_title" class="form-input" value="{{ old('site_title', $settings['site_title']) }}">
                </div>
                <div class="form-group">
                    <label class="form-label" for="site_description">Meta Description</label>
                    <textarea id="site_description" name="site_description" class="form-textarea" rows="3" maxlength="320">{{ old('site_description', $settings['site_description']) }}</textarea>
                </div>
                <div class="form-group">
                    <label class="form-label" for="organization_name">Organization Name</label>
                    <input type="text" id="organization_name" name="organization_name" class="form-input" value="{{ old('organization_name', $settings['organization_name']) }}">
                </div>
                <div class="form-group">
                    <label class="form-label" for="organization_logo">Organization Logo URL</label>
                    <input type="text" id="organization_logo" name="organization_logo" class="form-input" value="{{ old('organization_logo', $settings['organization_logo']) }}">
                </div>
                <div class="form-group">
                    <label class="form-label" for="google_analytics">Google Analytics ID</label>
                    <input type="text" id="google_analytics" name="google_analytics" class="form-input" value="{{ old('google_analytics', $settings['google_analytics']) }}" placeholder="G-XXXXXXXXXX">
                </div>
                <div class="form-group">
                    <label class="form-label" for="facebook_pixel">Facebook Pixel ID</label>
                    <input type="text" id="facebook_pixel" name="facebook_pixel" class="form-input" value="{{ old('facebook_pixel', $settings['facebook_pixel']) }}">
                </div>
                <div class="form-group">
                    <label class="form-label" for="google_verification">Google Site Verification</label>
                    <input type="text" id="google_verification" name="google_verification" class="form-input" value="{{ old('google_verification', $settings['google_verification']) }}">
                </div>
                <div class="form-group">
                    <label class="form-label" for="robots_policy">Robots Policy</label>
                    <select id="robots_policy" name="robots_policy" class="form-select">
                        <option value="allow" {{ $settings['robots_policy'] === 'allow' ? 'selected' : '' }}>Allow indexing</option>
                        <option value="disallow" {{ $settings['robots_policy'] === 'disallow' ? 'selected' : '' }}>Discourage indexing</option>
                    </select>
                </div>
                <label class="toggle-switch"><input type="checkbox" name="sitemap_enabled" value="1" {{ $settings['sitemap_enabled'] === '1' ? 'checked' : '' }}><span class="toggle-slider"></span><span>Enable XML Sitemap</span></label>
                <label class="toggle-switch"><input type="checkbox" name="analytics_enabled" value="1" {{ $settings['analytics_enabled'] === '1' ? 'checked' : '' }}><span class="toggle-slider"></span><span>Enable JamVini Analytics</span></label>
                <label class="toggle-switch"><input type="checkbox" name="schema_org_enabled" value="1" {{ $settings['schema_org_enabled'] === '1' ? 'checked' : '' }}><span class="toggle-slider"></span><span>Enable Schema.org JSON-LD</span></label>
            </div>
            <div class="card-footer" style="display:flex;justify-content:flex-end;">
                <button class="btn btn-primary">{{ jv_icon('save', '', 16) }} Save SEO</button>
            </div>
        </form>
    </aside>
</div>
@endsection
