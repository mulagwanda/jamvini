@extends('themes.default::layouts.admin')

@section('title', 'AI Assistant Settings')
@section('breadcrumbs')<a href="{{ route('admin.ai-assistant.index') }}">AI Assistant</a> <span class="separator">/</span> <span class="current">Settings</span>@endsection

@section('content')
<div class="page-header"><h1 class="page-title">AI Assistant Settings</h1></div>

@unless($hasGroqKey)
    <div class="alert alert-warning">Add <strong>GROQ_API_KEY</strong> to your .env file before enabling live AI answers.</div>
@endunless

<form action="{{ route('admin.ai-assistant.settings.save') }}" method="POST">
    @csrf
    <div class="dash-card" style="margin-bottom:1.5rem;">
        <div class="dash-card-head"><h3>Provider</h3></div>
        <div class="form-grid">
            <div class="form-group"><label class="form-label">Model</label><input name="model" class="form-input" value="{{ old('model', $settings['model']) }}" required></div>
            <div class="form-group"><label class="form-label">Temperature</label><input type="number" name="temperature" class="form-input" min="0" max="1" step="0.1" value="{{ old('temperature', $settings['temperature']) }}" required></div>
        </div>
        <label class="toggle-switch"><input type="checkbox" name="enabled" value="1" {{ old('enabled', $settings['enabled']) === '1' ? 'checked' : '' }}><span class="toggle-slider"></span><span>Enable AI Assistant</span></label>
    </div>

    <div class="dash-card" style="margin-bottom:1.5rem;">
        <div class="dash-card-head"><h3>Widget</h3></div>
        <div class="form-grid">
            <div class="form-group"><label class="form-label">Title</label><input name="widget_title" class="form-input" value="{{ old('widget_title', $settings['widget_title']) }}" required></div>
            <div class="form-group"><label class="form-label">Brand Color</label><input type="color" name="brand_color" class="form-input" value="{{ old('brand_color', $settings['brand_color']) }}" required style="height:44px;"></div>
            <div class="form-group"><label class="form-label">Position</label><select name="position" class="form-select"><option value="right" {{ $settings['position'] === 'right' ? 'selected' : '' }}>Right</option><option value="left" {{ $settings['position'] === 'left' ? 'selected' : '' }}>Left</option></select></div>
            <div class="form-group"><label class="form-label">Escalation Department</label><input name="escalation_department" class="form-input" value="{{ old('escalation_department', $settings['escalation_department']) }}" required></div>
        </div>
        <div class="form-group"><label class="form-label">Welcome Message</label><textarea name="welcome_message" class="form-textarea" rows="3" required>{{ old('welcome_message', $settings['welcome_message']) }}</textarea></div>
        <div style="display:grid;gap:10px;">
            <label class="toggle-switch"><input type="checkbox" name="show_on_public" value="1" {{ $settings['show_on_public'] === '1' ? 'checked' : '' }}><span class="toggle-slider"></span><span>Show on public website</span></label>
            <label class="toggle-switch"><input type="checkbox" name="show_on_client" value="1" {{ $settings['show_on_client'] === '1' ? 'checked' : '' }}><span class="toggle-slider"></span><span>Show in client portal</span></label>
            <label class="toggle-switch"><input type="checkbox" name="kb_enabled" value="1" {{ $settings['kb_enabled'] === '1' ? 'checked' : '' }}><span class="toggle-slider"></span><span>Show Knowledge Base suggestions inside chat when Knowledge Base is active</span></label>
            <label class="toggle-switch"><input type="checkbox" name="require_contact_for_escalation" value="1" {{ $settings['require_contact_for_escalation'] === '1' ? 'checked' : '' }}><span class="toggle-slider"></span><span>Require visitor email before human escalation</span></label>
        </div>
    </div>

    <div style="display:flex;justify-content:flex-end;"><button class="btn btn-primary btn-lg">Save Settings</button></div>
</form>
@endsection
