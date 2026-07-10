<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'JamVini Pulse Client')</title>
    <link href="{{ asset('themes/pulse/assets/css/client.css') }}" rel="stylesheet">
    @stack('styles')
</head>
<body>
    <header class="pulse-client-header">
        <div style="max-width:1180px;margin:0 auto;padding:0 20px;display:flex;align-items:center;justify-content:space-between;color:#fff;">
            <strong>JamVini Pulse</strong>
            <nav style="display:flex;gap:16px;font-size:.9rem;">
                <a href="{{ url('/client/dashboard') }}" style="color:#fff;text-decoration:none;">Dashboard</a>
                <a href="{{ url('/client/services') }}" style="color:#fff;text-decoration:none;">Services</a>
                <a href="{{ url('/client/invoices') }}" style="color:#fff;text-decoration:none;">Invoices</a>
            </nav>
        </div>
    </header>
    <main class="pulse-client-content">
        @yield('content')
    </main>
    @stack('scripts')
</body>
</html>
