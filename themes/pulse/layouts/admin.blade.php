<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'JamVini Pulse Admin')</title>
    <link href="{{ asset('themes/pulse/assets/css/admin.css') }}" rel="stylesheet">
    @stack('styles')
</head>
<body>
    <div class="pulse-admin-container">
        @include('themes.pulse::partials.admin-sidebar')
        
        <div class="pulse-admin-main">
            @include('themes.pulse::partials.admin-header')
            @yield('content')
        </div>
    </div>
    
    <script src="{{ asset('themes/pulse/assets/js/admin.js') }}"></script>
    @stack('scripts')
</body>
</html>
