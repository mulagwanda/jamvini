<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sign In — {{ \App\Models\Setting::get('company_name', 'JamVini Hosting') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('themes/default/assets/css/frontend.css') }}">
    <style>
        .auth-wrapper {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #f1f5f9, #ede9fe);
            padding: 2rem;
        }
        .auth-card {
            background: #fff;
            border-radius: 18px;
            box-shadow: 0 20px 60px rgba(108,92,231,.15);
            width: 100%;
            max-width: 440px;
            padding: 3rem 2.5rem;
        }
        .auth-logo {
            text-align: center;
            margin-bottom: 2rem;
        }
        .auth-logo-icon {
            width: 52px; height: 52px;
            border-radius: 14px;
            background: linear-gradient(135deg, #6C5CE7, #A29BFE);
            display: inline-flex; align-items: center; justify-content: center;
            font-size: 24px; color: #fff; font-weight: 700;
            margin-bottom: 1rem;
        }
        .auth-title { font-family: 'Poppins', sans-serif; font-size: 1.5rem; font-weight: 700; text-align: center; margin-bottom: .5rem; color: #0F172A; }
        .auth-subtitle { text-align: center; color: #64748b; margin-bottom: 2rem; font-size: .92rem; }
        .form-group { margin-bottom: 1.25rem; }
        .form-group label { display: block; font-weight: 500; margin-bottom: .5rem; color: #0F172A; font-size: .9rem; }
        .form-group input {
            width: 100%; padding: .85rem 1rem; border: 1px solid #e2e8f0;
            border-radius: 12px; font-size: .95rem; font-family: inherit; transition: all .2s;
        }
        .form-group input:focus { outline: none; border-color: #6C5CE7; box-shadow: 0 0 0 3px rgba(108,92,231,.1); }
        .form-footer { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; }
        .form-footer a { color: #6C5CE7; font-size: .88rem; }
        .btn-primary {
            width: 100%; padding: .9rem; border: none; border-radius: 12px;
            background: linear-gradient(135deg, #6C5CE7, #A29BFE); color: #fff;
            font-size: 1rem; font-weight: 600; cursor: pointer; transition: all .2s;
        }
        .btn-primary:hover { transform: translateY(-1px); box-shadow: 0 8px 24px rgba(108,92,231,.3); }
        .auth-footer { text-align: center; margin-top: 1.5rem; font-size: .9rem; color: #64748b; }
        .auth-footer a { color: #6C5CE7; font-weight: 600; }
        .alert { padding: 1rem; border-radius: 12px; margin-bottom: 1.5rem; font-size: .9rem; }
        .alert-danger { background: #fee2e2; color: #991b1b; }
    </style>
</head>
<body>
<div class="auth-wrapper">
    <div class="auth-card">
        <div class="auth-logo">
            <div class="auth-logo-icon">{{ substr(\App\Models\Setting::get('company_name', 'J'), 0, 1) }}</div>
            <h1 class="auth-title">Welcome Back</h1>
            <p class="auth-subtitle">Sign in to manage your services and domains</p>
        </div>

        @if($errors->any())
            <div class="alert alert-danger">
                @foreach($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        @if(session('status'))
            <div class="alert" style="background:#dcfce7;color:#166534;">{{ session('status') }}</div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" placeholder="you@example.com" required autofocus>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="••••••••" required>
            </div>
            <div class="form-footer">
                <label style="display:flex;align-items:center;gap:.5rem;cursor:pointer;">
                    <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}>
                    <span style="font-size:.88rem;">Remember me</span>
                </label>
                <a href="{{ route('password.request') }}">Forgot password?</a>
            </div>
            <button type="submit" class="btn-primary">🔐 Sign In</button>
        </form>

        <div class="auth-footer">
            Don't have an account? <a href="{{ route('register') }}">Create one here</a>
        </div>

        <div style="text-align:center;margin-top:1rem;">
            <a href="/" style="color:#64748b;font-size:.85rem;">← Back to Home</a>
        </div>
    </div>
</div>
</body>
</html>