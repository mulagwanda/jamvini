<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', \App\Models\Setting::get('site_title', 'JamVini Hosting'))</title>
    <meta name="description" content="@yield('description', \App\Models\Setting::get('site_description', ''))">
    @if(jv_theme_setting('favicon_url', null, 'public'))
        <link rel="icon" href="{{ asset('storage/' . jv_theme_setting('favicon_url', '', 'public')) }}">
    @endif
    <link rel="stylesheet" href="{{ theme_asset('css/frontend.css', 'public') }}?v={{ filemtime(theme_asset_path('css/frontend.css', 'public')) }}">
    <style>
        :root {
            --kmb-primary: {{ jv_theme_setting('primary_color', '#2563EB', 'public') }};
            --kmb-primary-hover: {{ jv_theme_setting('primary_color_hover', '#1D4ED8', 'public') }};
        }
    </style>
    @stack('styles')
</head>
<body>
@include('themes.kumbukumbu::partials.header')
<main>
    @yield('content')
</main>
@include('themes.kumbukumbu::partials.footer')
<script src="{{ theme_asset('js/frontend.js', 'public') }}?v={{ filemtime(theme_asset_path('js/frontend.js', 'public')) }}"></script>
@if(\App\Models\Setting::get('ai_assistant_show_on_public', '1') === '1' && \Illuminate\Support\Facades\Route::has('ai-assistant.message'))
    @include('plugins.AiAssistant::widget.loader')
@endif
@stack('scripts')
</body>
</html>
