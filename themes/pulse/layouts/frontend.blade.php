<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'sw' ? 'rtl' : 'ltr' }}">
<head>
    @php
        $favicon = jv_theme_setting('favicon_url', '');
        $siteWidth = jv_theme_setting('site_width', 'wide');
        $headerStyle = jv_theme_setting('header_style', 'sticky');
        $footerStyle = jv_theme_setting('footer_style', 'modern');
        $darkMode = (string) jv_theme_setting('dark_mode', '0') === '1';
        $customCss = jv_theme_setting('custom_css', '');
        $assetUrl = fn ($path) => $path && !str_starts_with($path, 'http') && !str_starts_with($path, '/') ? asset('storage/' . $path) : $path;
    @endphp
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'JamVini Pulse')</title>
    @if($favicon)<link rel="icon" href="{{ $assetUrl($favicon) }}">@endif
    <link href="{{ asset('themes/pulse/assets/css/frontend.css') }}" rel="stylesheet">
    <style>
        :root {
            --pulse-primary: {{ jv_theme_setting('primary_color', '#1a5276') }};
            --pulse-secondary: {{ jv_theme_setting('secondary_color', '#2e86c1') }};
            --pulse-accent: {{ jv_theme_setting('accent_color', '#f39c12') }};
            --pulse-text: {{ jv_theme_setting('text_color', '#1e293b') }};
            --pulse-bg: {{ jv_theme_setting('background_color', '#f8fafc') }};
            --pulse-heading-font: {{ jv_theme_setting('heading_font', 'Inter, sans-serif') }};
            --pulse-body-font: {{ jv_theme_setting('body_font', 'Inter, sans-serif') }};
            --pulse-container-width: {{ jv_theme_setting('container_width', '1180px') }};
        }
        {!! $customCss !!}
    </style>
    @stack('styles')
</head>
<body class="pulse-layout-{{ $siteWidth }} pulse-header-{{ $headerStyle }} pulse-footer-{{ $footerStyle }} {{ $darkMode ? 'pulse-dark' : '' }}">
    @include('themes.pulse::partials.header')
    
    <main class="pulse-main">
        @yield('content')
    </main>
    
    @include('themes.pulse::partials.footer')
    
    <script src="{{ asset('themes/pulse/assets/js/frontend.js') }}"></script>
    @stack('scripts')
</body>
</html>
