@extends('themes.default::layouts.admin')

@section('title', 'Social Centre Settings')
@section('breadcrumbs')<a href="{{ route('admin.social.index') }}">Social Centre</a> <span class="separator">/</span> <span class="current">Settings</span>@endsection

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Social Centre Settings</h1>
        <p class="page-subtitle">Set defaults for planning, AI copywriting, approvals, and campaign tracking.</p>
    </div>
</div>

<div class="alert alert-info">
    Social Centre currently supports a hybrid local workflow: plan, schedule, track platform records, and mark publishing manually. API automation should be enabled later on a live server with approved platform accounts.
</div>

<form action="{{ route('admin.social.settings.update') }}" method="POST">
    @csrf

    <div class="dash-card" style="margin-bottom:1rem;">
        <div class="dash-card-head"><h3>Composer Defaults</h3></div>
        <div class="form-group">
            <label class="form-label">Default Platforms</label>
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:8px;">
                @foreach($platforms as $value => $label)
                    <label style="border:1px solid var(--jv-gray-200);border-radius:10px;padding:10px;display:flex;gap:8px;align-items:center;">
                        <input type="checkbox" name="default_platforms[]" value="{{ $value }}" {{ in_array($value, old('default_platforms', $settings['default_platforms']), true) ? 'checked' : '' }}>
                        <span>{{ $label }}</span>
                    </label>
                @endforeach
            </div>
        </div>
        <div class="form-grid">
            <div class="form-group">
                <label class="form-label">Default Hashtags</label>
                <input name="default_hashtags" class="form-input" value="{{ old('default_hashtags', $settings['default_hashtags']) }}" placeholder="#webhosting #domains">
            </div>
            <div class="form-group">
                <label class="form-label">Default Status</label>
                <select name="default_status" class="form-select">
                    @foreach(['draft','ready','scheduled'] as $status)
                        <option value="{{ $status }}" {{ old('default_status', $settings['default_status']) === $status ? 'selected' : '' }}>{{ ucfirst($status) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Planning Timezone</label>
                <select name="timezone" class="form-select">
                    @foreach(timezone_identifiers_list() as $timezone)
                        <option value="{{ $timezone }}" {{ old('timezone', $settings['timezone']) === $timezone ? 'selected' : '' }}>{{ $timezone }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <label class="toggle-switch">
            <input type="checkbox" name="approval_required" value="1" {{ old('approval_required', $settings['approval_required']) === '1' ? 'checked' : '' }}>
            <span class="toggle-slider"></span><span>Require approval before publishing workflow</span>
        </label>
    </div>

    <div class="dash-card" style="margin-bottom:1rem;">
        <div class="dash-card-head"><h3>AI Copywriting Defaults</h3></div>
        <div class="form-group">
            <label class="form-label">Default AI Tone</label>
            <input name="ai_tone" class="form-input" value="{{ old('ai_tone', $settings['ai_tone']) }}" required>
        </div>
        <div class="form-group">
            <label class="form-label">Default AI Brief Context</label>
            <textarea name="ai_default_brief" class="form-textarea" rows="4" placeholder="Example: We sell reliable web hosting, domains, SSL, and business email for Tanzanian SMEs.">{{ old('ai_default_brief', $settings['ai_default_brief']) }}</textarea>
        </div>
        <div class="form-group">
            <label class="form-label">Brand Voice</label>
            <textarea name="brand_voice" class="form-textarea" rows="4" placeholder="Example: Clear, trustworthy, practical, locally aware, and never overpromising.">{{ old('brand_voice', $settings['brand_voice']) }}</textarea>
        </div>
    </div>

    <div class="dash-card" style="margin-bottom:1rem;">
        <div class="dash-card-head"><h3>UTM Tracking</h3></div>
        <label class="toggle-switch" style="margin-bottom:12px;">
            <input type="checkbox" name="utm_enabled" value="1" {{ old('utm_enabled', $settings['utm_enabled']) === '1' ? 'checked' : '' }}>
            <span class="toggle-slider"></span><span>Enable UTM defaults for social links</span>
        </label>
        <div class="form-grid">
            <div class="form-group"><label class="form-label">UTM Source</label><input name="utm_source" class="form-input" value="{{ old('utm_source', $settings['utm_source']) }}"></div>
            <div class="form-group"><label class="form-label">UTM Medium</label><input name="utm_medium" class="form-input" value="{{ old('utm_medium', $settings['utm_medium']) }}"></div>
            <div class="form-group"><label class="form-label">Campaign Prefix</label><input name="utm_campaign_prefix" class="form-input" value="{{ old('utm_campaign_prefix', $settings['utm_campaign_prefix']) }}"></div>
        </div>
    </div>

    <div style="display:flex;justify-content:flex-end;">
        <button class="btn btn-primary btn-lg">Save Settings</button>
    </div>
</form>
@endsection
