@extends('themes.default::layouts.admin')

@section('title', 'Maintenance Mode')
@section('breadcrumbs')<span class="current">Maintenance Mode</span>@endsection

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Maintenance Mode</h1>
        <p class="page-subtitle">Show a clean scheduled-maintenance page while admins keep working normally.</p>
    </div>
    <a href="{{ route('maintenance.preview') }}" target="_blank" class="btn btn-outline-primary">{{ jv_icon('eye', '', 16) }} Preview</a>
</div>

<div style="display:grid;grid-template-columns:minmax(0,1fr) 360px;gap:18px;align-items:start;">
    <form action="{{ route('admin.maintenance.update') }}" method="POST" class="card">
        @csrf
        <div class="card-header"><h3 class="card-title">{{ jv_icon('construction', '', 18) }} Settings</h3></div>
        <div class="card-body" style="display:grid;gap:14px;">
            <label class="toggle-switch"><input type="checkbox" name="enabled" value="1" {{ $settings['enabled'] === '1' ? 'checked' : '' }}><span class="toggle-slider"></span><span>Enable maintenance mode</span></label>
            <div class="form-group"><label class="form-label">Title</label><input class="form-input" name="title" value="{{ $settings['title'] }}"></div>
            <div class="form-group"><label class="form-label">Message</label><textarea class="form-textarea" name="message" rows="5">{{ $settings['message'] }}</textarea></div>
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:14px;">
                <div class="form-group"><label class="form-label">Start</label><input type="datetime-local" class="form-input" name="scheduled_start_at" value="{{ $settings['scheduled_start_at'] ? str_replace(' ', 'T', substr($settings['scheduled_start_at'], 0, 16)) : '' }}"></div>
                <div class="form-group"><label class="form-label">End</label><input type="datetime-local" class="form-input" name="scheduled_end_at" value="{{ $settings['scheduled_end_at'] ? str_replace(' ', 'T', substr($settings['scheduled_end_at'], 0, 16)) : '' }}"></div>
            </div>
            <div class="form-group"><label class="form-label">Bypass IPs</label><textarea class="form-textarea" name="bypass_ips" rows="3" placeholder="One IP per line">{{ $settings['bypass_ips'] }}</textarea></div>
            <div class="form-group"><label class="form-label">Contact Email</label><input type="email" class="form-input" name="contact_email" value="{{ $settings['contact_email'] }}"></div>
            <div class="form-group">
                <label class="form-label">Template Style</label>
                <select class="form-select" name="template_style">
                    @foreach(['calm' => 'Calm', 'bold' => 'Bold', 'minimal' => 'Minimal'] as $value => $label)
                        <option value="{{ $value }}" {{ $settings['template_style'] === $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="card-footer" style="display:flex;justify-content:flex-end;"><button class="btn btn-primary">{{ jv_icon('save', '', 16) }} Save Maintenance</button></div>
    </form>

    <aside class="card">
        <div class="card-header"><h3 class="card-title">{{ jv_icon('history', '', 18) }} Recent Changes</h3></div>
        <div class="card-body" style="display:grid;gap:10px;">
            @forelse($events as $event)
                <div style="border-bottom:1px solid var(--jv-gray-100);padding-bottom:10px;">
                    <strong>{{ str($event->type)->headline() }}</strong>
                    <div style="color:var(--jv-gray-500);font-size:.86rem;">{{ \Carbon\Carbon::parse($event->created_at)->diffForHumans() }}</div>
                </div>
            @empty
                <div class="empty-state" style="padding:22px;">No maintenance changes yet.</div>
            @endforelse
        </div>
    </aside>
</div>
@endsection
