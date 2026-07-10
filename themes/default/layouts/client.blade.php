<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Client Portal') — JamVini Hosting</title>
    <link rel="stylesheet" href="{{ theme_asset('css/admin.css', 'client') }}">
    <style>
        .client-wrapper { min-height: 100vh; background: var(--jv-gray-50); }
        .client-header { background: white; border-bottom: 1px solid var(--jv-gray-200); padding: 16px 24px; display: flex; align-items: center; justify-content: space-between; }
        .client-logo { font-family: var(--jv-font-heading); font-size: 1.3rem; font-weight: 700; color: var(--jv-primary); }
        .client-content { max-width: 1200px; margin: 0 auto; padding: 24px; }
        .client-nav { display: flex; gap: 12px; align-items: center; }
        .client-nav a { color: var(--jv-gray-600); text-decoration: none; font-size: 0.9rem; padding: 8px 12px; border-radius: var(--jv-radius-sm); }
        .client-nav a:hover, .client-nav a.active { background: var(--jv-gray-100); color: var(--jv-primary); }
    </style>
</head>
<body>
<div class="client-wrapper">
    <header class="client-header">
        <div class="client-logo">JamVini Hosting</div>
        <nav class="client-nav">
            <a href="{{ route('client.dashboard') }}" class="{{ request()->routeIs('client.dashboard') ? 'active' : '' }}">{{ jv_icon('home', '', 16) }} Dashboard</a>
            <a href="{{ route('client.services') }}" class="{{ request()->routeIs('client.services') ? 'active' : '' }}">{{ jv_icon('package', '', 16) }} Services</a>
            <a href="{{ route('client.invoices') }}" class="{{ request()->routeIs('client.invoices') ? 'active' : '' }}">{{ jv_icon('file-text', '', 16) }} Invoices</a>
            <a href="{{ route('client.domains') }}" class="{{ request()->routeIs('client.domains') ? 'active' : '' }}">{{ jv_icon('globe', '', 16) }} Domains</a>
            <form method="POST" action="{{ route('logout') }}" style="display: inline;">
                @csrf
                <button type="submit" class="btn btn-sm btn-outline-danger">{{ jv_icon('log-out', '', 16) }} Logout</button>
            </form>
        </nav>
    </header>
    <div class="client-content">
        @yield('content')
    </div>
</div>
<script>
window.JamViniConfig = {
    currency: @json(\App\Models\Setting::get('currency', 'TZS')),
    currencyDecimals: {{ (int) \App\Models\Setting::get('currency_decimal_places', \App\Models\Setting::get('currency', 'TZS') === 'TZS' ? '0' : '2') }}
};
</script>
<script src="{{ theme_asset('js/admin.js', 'client') }}"></script>
@if(\App\Models\Setting::get('ai_assistant_show_on_client', '1') === '1' && \Illuminate\Support\Facades\Route::has('ai-assistant.message'))
    @include('plugins.AiAssistant::widget.loader')
@endif
</body>
</html>
