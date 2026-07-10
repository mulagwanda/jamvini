<header class="pulse-admin-header">
    <div>
        <strong>@yield('title', 'Dashboard')</strong>
        <span>Hosting business heartbeat</span>
    </div>
    <div class="pulse-admin-actions">
        <a href="{{ url('/') }}" target="_blank" rel="noopener">{{ jv_icon('external-link', '', 15) }} View Website</a>
        <a href="{{ route('admin.theme.pulse.settings') }}">{{ jv_icon('settings', '', 15) }} Pulse Settings</a>
    </div>
</header>
