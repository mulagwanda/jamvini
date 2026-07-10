<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'JamVini Hosting')</title>
    <link rel="stylesheet" href="{{ theme_asset('css/admin.css', 'client') }}">
    <style>
        .auth-wrapper {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, var(--jv-gray-100) 0%, var(--jv-gray-200) 100%);
            padding: 20px;
        }
        .auth-card {
            background: white;
            border-radius: var(--jv-radius-xl);
            box-shadow: var(--jv-shadow-lg);
            width: 100%;
            max-width: 440px;
            padding: 40px;
        }
        .auth-logo {
            text-align: center;
            margin-bottom: 32px;
        }
        .auth-logo-icon {
            width: 64px; height: 64px;
            border-radius: var(--jv-radius-lg);
            background: var(--jv-gradient-primary);
            display: inline-flex;
            align-items: center; justify-content: center;
            font-size: 28px; color: white; margin-bottom: 16px;
        }
        .auth-title { font-size: 1.5rem; font-weight: 700; margin-bottom: 4px; }
        .auth-subtitle { font-size: 0.9rem; color: var(--jv-gray-500); }
    </style>
</head>
<body>
    <div class="auth-wrapper">
        <div class="auth-card">
            <div class="auth-logo">
                <div class="auth-logo-icon">JH</div>
                <h1 class="auth-title">@yield('heading', 'JamVini Hosting')</h1>
                <p class="auth-subtitle">@yield('subtitle', '')</p>
            </div>
            @yield('content')
        </div>
    </div>
</body>
</html>
