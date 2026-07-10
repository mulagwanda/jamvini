<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Login — {{ \App\Models\Setting::get('company_name', 'JamVini Hosting') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('themes/default/assets/css/admin.css') }}">
    <style>
        .login-wrapper {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #0F172A 0%, #1a1f3a 50%, #2d1b4e 100%);
            padding: 2rem;
        }
        .login-card {
            background: #fff;
            border-radius: 18px;
            box-shadow: 0 20px 60px rgba(108,92,231,.15);
            width: 100%;
            max-width: 420px;
            padding: 2.5rem;
        }
        .login-logo {
            text-align: center;
            margin-bottom: 2rem;
        }
        .login-logo-icon {
            width: 56px; height: 56px;
            border-radius: 14px;
            background: linear-gradient(135deg, #6C5CE7, #A29BFE);
            display: inline-flex; align-items: center; justify-content: center;
            font-size: 26px; color: #fff; font-weight: 700;
            margin-bottom: 1rem;
        }
        .login-title { font-family: 'Poppins', sans-serif; font-size: 1.5rem; font-weight: 700; text-align: center; margin-bottom: .25rem; }
        .login-subtitle { text-align: center; color: #64748b; margin-bottom: 2rem; font-size: .9rem; }
        .form-group { margin-bottom: 1.25rem; }
        .form-group label { display: block; font-weight: 500; margin-bottom: .5rem; color: #0F172A; font-size: .9rem; }
        .form-group input {
            width: 100%; padding: .85rem 1rem; border: 1px solid #e2e8f0;
            border-radius: 12px; font-size: .95rem; transition: all .2s;
        }
        .form-group input:focus { outline: none; border-color: #6C5CE7; box-shadow: 0 0 0 3px rgba(108,92,231,.1); }
        .btn-login {
            width: 100%; padding: .9rem; border: none; border-radius: 12px;
            background: linear-gradient(135deg, #6C5CE7, #A29BFE); color: #fff;
            font-size: 1rem; font-weight: 600; cursor: pointer; transition: all .2s;
        }
        .btn-login:hover { transform: translateY(-1px); box-shadow: 0 8px 24px rgba(108,92,231,.3); }
        .alert { padding: 1rem; border-radius: 12px; margin-bottom: 1.5rem; font-size: .9rem; }
        .alert-danger { background: #fee2e2; color: #991b1b; }
        .back-link { text-align: center; margin-top: 1rem; }
        .back-link a { color: #64748b; font-size: .85rem; text-decoration: none; }
    </style>
</head>
<body>
<div class="login-wrapper">
    <div class="login-card">
        <div class="login-logo">
            <div class="login-logo-icon">{{ substr(\App\Models\Setting::get('company_name', 'J'), 0, 1) }}</div>
            <h1 class="login-title">Admin Panel</h1>
            <p class="login-subtitle">Sign in to manage your hosting business</p>
        </div>

        @if($errors->any())
            <div class="alert alert-danger">
                @foreach($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('admin.login.submit') }}">
            @csrf
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" placeholder="admin@example.com" required autofocus>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="••••••••" required>
            </div>
            <button type="submit" class="btn-login">🔐 Sign In</button>
        </form>

        <div class="back-link">
            <a href="/">← Back to Website</a>
        </div>
    </div>
</div>
</body>
</html>