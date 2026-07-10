<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>@yield('title', \App\Models\Setting::get('site_title', 'JamVini Hosting'))</title>
    <meta name="description" content="@yield('description', \App\Models\Setting::get('site_description', ''))" />
    @if(jv_theme_setting('favicon_url'))
        <link rel="icon" href="{{ asset('storage/' . jv_theme_setting('favicon_url')) }}">
    @endif
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ theme_asset('css/frontend.css', 'public') }}?v={{ filemtime(theme_asset_path('css/frontend.css', 'public')) }}">
    <style>
        :root {
            --primary: {{ jv_theme_setting('primary_color', '#6C5CE7') }};
            --primary-light: {{ jv_theme_setting('primary_color_hover', '#5A4BD1') }};
        }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    @stack('styles')
</head>
<body>

@include('themes.default::partials.header')

@if(session('support_access_admin_id') && auth()->check())
    <div style="background:#0f172a;color:#fff;padding:10px 18px;border-bottom:1px solid rgba(255,255,255,.12);">
        <div class="container" style="display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;">
            <div style="font-size:.9rem;">
                <strong>Support Access Active</strong>
                <span style="color:#cbd5e1;">Viewing client portal as {{ auth()->user()->full_name ?? auth()->user()->email }}.</span>
            </div>
            @if(\Illuminate\Support\Facades\Route::has('client.support-access.end'))
                <form action="{{ route('client.support-access.end') }}" method="POST" style="margin:0;">
                    @csrf
                    <button class="btn btn-sm" style="background:#fff;color:#0f172a;border:none;">Return to Admin</button>
                </form>
            @endif
        </div>
    </div>
@endif

<main>
    @yield('content')
</main>

@include('themes.default::partials.footer')

<script>
window.JamViniConfig = {
    currency: @json(\App\Models\Setting::get('currency', 'TZS')),
    currencyDecimals: {{ (int) \App\Models\Setting::get('currency_decimal_places', \App\Models\Setting::get('currency', 'TZS') === 'TZS' ? '0' : '2') }}
};
</script>
<script src="{{ theme_asset('js/frontend.js', 'public') }}?v={{ filemtime(theme_asset_path('js/frontend.js', 'public')) }}"></script>
@if(\App\Models\Setting::get('ai_assistant_show_on_public', '1') === '1' && \Illuminate\Support\Facades\Route::has('ai-assistant.message'))
    @include('plugins.AiAssistant::widget.loader')
@endif
@stack('scripts')
</body>
</html>
