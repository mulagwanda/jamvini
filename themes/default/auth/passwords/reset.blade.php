<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Set New Password — {{ \App\Models\Setting::get('company_name', 'JamVini Hosting') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('themes/default/assets/css/frontend.css') }}">
    <style>
        .auth-wrapper { min-height: 100vh; display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, #f1f5f9, #ede9fe); padding: 2rem; }
        .auth-card { background: #fff; border-radius: 18px; box-shadow: 0 20px 60px rgba(108,92,231,.15); width: 100%; max-width: 440px; padding: 3rem 2.5rem; }
        .auth-logo { text-align: center; margin-bottom: 2rem; }
        .auth-logo-icon { width: 52px; height: 52px; border-radius: 14px; background: linear-gradient(135deg, #6C5CE7, #A29BFE); display: inline-flex; align-items: center; justify-content: center; font-size: 24px; color: #fff; font-weight: 700; margin-bottom: 1rem; }
        .auth-title { font-family: 'Poppins', sans-serif; font-size: 1.5rem; font-weight: 700; text-align: center; margin-bottom: .5rem; color: #0F172A; }
        .auth-subtitle { text-align: center; color: #64748b; margin-bottom: 2rem; font-size: .92rem; }
        .form-group { margin-bottom: 1.25rem; }
        .form-group label { display: block; font-weight: 500; margin-bottom: .5rem; color: #0F172A; font-size: .9rem; }
        .form-group input { width: 100%; padding: .85rem 1rem; border: 1px solid #e2e8f0; border-radius: 12px; font-size: .95rem; }
        .form-group input:focus { outline: none; border-color: #6C5CE7; box-shadow: 0 0 0 3px rgba(108,92,231,.1); }
        .btn-primary { width: 100%; padding: .9rem; border: none; border-radius: 12px; background: linear-gradient(135deg, #6C5CE7, #A29BFE); color: #fff; font-size: 1rem; font-weight: 600; cursor: pointer; }
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
            <h1 class="auth-title">Set New Password</h1>
            <p class="auth-subtitle">Choose a strong password for your account</p>
        </div>

        @if($errors->any())
            <div class="alert alert-danger">@foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>
        @endif

        <form method="POST" action="{{ route('password.update') }}">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" value="{{ $email ?? old('email') }}" placeholder="you@example.com" required readonly>
            </div>
            <div class="form-group">
                <label for="password">New Password</label>
                <input type="password" id="password" name="password" placeholder="Min 8 characters" required>
            </div>
            <div class="form-group">
                <label for="password_confirmation">Confirm Password</label>
                <input type="password" id="password_confirmation" name="password_confirmation" placeholder="Repeat password" required>
            </div>
            <button type="submit" class="btn-primary">🔒 Reset Password</button>
        </form>

        <div class="auth-footer"><a href="{{ route('login') }}">← Back to Sign In</a></div>
    </div>
</div>
</body>
</html>