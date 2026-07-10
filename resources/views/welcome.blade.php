<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ \App\Models\Setting::get('company_name', 'JamVini Hosting') }} — Web Hosting Management Platform</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('themes/default/assets/css/frontend.css') }}">
    <style>
        .landing-hero {
            background: linear-gradient(135deg, #0F172A 0%, #1a1f3a 50%, #2d1b4e 100%);
            color: #fff; padding: 100px 0 80px; position: relative; overflow: hidden;
        }
        .landing-hero::before {
            content: ''; position: absolute; top: -100px; right: -100px;
            width: 400px; height: 400px; border-radius: 50%;
            background: radial-gradient(circle, rgba(108,92,231,.4), transparent 70%);
        }
        .hero-inner { position: relative; text-align: center; max-width: 820px; margin: 0 auto; padding: 0 24px; }
        .hero-inner h1 { color: #fff; font-size: clamp(2.2rem, 5vw, 3.6rem); margin-bottom: 1.25rem; }
        .hero-inner h1 span { background: linear-gradient(90deg, #A29BFE, #c4b5fd); -webkit-background-clip: text; background-clip: text; color: transparent; }
        .hero-inner p { color: #cbd5e1; font-size: 1.15rem; max-width: 600px; margin: 0 auto 2.5rem; line-height: 1.7; }
        .hero-actions { display: flex; gap: 16px; justify-content: center; flex-wrap: wrap; }
        .btn-hero { padding: 14px 32px; border-radius: 12px; font-weight: 600; font-size: 1rem; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; transition: all .25s; }
        .btn-hero-primary { background: #6C5CE7; color: #fff; box-shadow: 0 8px 30px rgba(108,92,231,.4); }
        .btn-hero-primary:hover { background: #5a4bd1; transform: translateY(-2px); }
        .btn-hero-outline { border: 2px solid rgba(255,255,255,.3); color: #fff; background: transparent; }
        .btn-hero-outline:hover { border-color: #fff; background: rgba(255,255,255,.05); }

        .header { background: rgba(255,255,255,.95); border-bottom: 1px solid #e5e7eb; position: sticky; top: 0; z-index: 50; }
        .header-inner { display: flex; justify-content: space-between; align-items: center; height: 72px; max-width: 1200px; margin: 0 auto; padding: 0 24px; }
        .logo { font-family: 'Poppins', sans-serif; font-weight: 700; font-size: 1.4rem; color: #0F172A; display: flex; align-items: center; gap: .5rem; text-decoration: none; }
        .logo-icon { width: 38px; height: 38px; border-radius: 10px; background: linear-gradient(135deg, #6C5CE7, #A29BFE); display: grid; place-items: center; color: #fff; font-weight: 700; }
        .header-nav { display: flex; gap: 16px; align-items: center; }

        .features-section { padding: 80px 24px; max-width: 1100px; margin: 0 auto; }
        .section-label { display: inline-block; font-size: .8rem; font-weight: 600; letter-spacing: .12em; text-transform: uppercase; color: #6C5CE7; background: rgba(108,92,231,.1); padding: .35rem .8rem; border-radius: 999px; margin-bottom: 1rem; }
        .section-title { font-family: 'Poppins', sans-serif; font-size: 2.2rem; font-weight: 700; text-align: center; margin-bottom: 1rem; color: #0F172A; }
        .section-subtitle { text-align: center; color: #64748b; font-size: 1.05rem; margin-bottom: 3rem; max-width: 600px; margin-left: auto; margin-right: auto; }
        .features-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 24px; }
        .feature-card { background: #fff; border: 1px solid #e5e7eb; border-radius: 18px; padding: 32px; transition: all .25s; }
        .feature-card:hover { transform: translateY(-6px); box-shadow: 0 8px 30px rgba(0,0,0,.08); }
        .feature-icon { width: 56px; height: 56px; border-radius: 14px; display: flex; align-items: center; justify-content: center; font-size: 24px; margin-bottom: 20px; }
        .feature-icon.purple { background: #ede9fe; color: #6C5CE7; }
        .feature-icon.green { background: #dcfce7; color: #16a34a; }
        .feature-icon.amber { background: #fef3c7; color: #b45309; }
        .feature-icon.blue { background: #dbeafe; color: #2563eb; }
        .feature-title { font-weight: 600; font-size: 1.1rem; margin-bottom: 8px; color: #0F172A; }
        .feature-desc { color: #64748b; font-size: .9rem; line-height: 1.6; }

        .cta-section { background: linear-gradient(135deg, #6C5CE7, #4c3bb8); border-radius: 18px; padding: 60px 40px; text-align: center; max-width: 1000px; margin: 40px auto; color: #fff; }
        .cta-section h2 { color: #fff; font-size: 2rem; margin-bottom: 1rem; }
        .cta-section p { color: rgba(255,255,255,.85); margin-bottom: 2rem; font-size: 1.05rem; }
        .footer { background: #0F172A; color: #94a3b8; text-align: center; padding: 32px 24px; font-size: .88rem; }

        @media (max-width: 768px) {
            .features-section { padding: 60px 20px; }
            .section-title { font-size: 1.6rem; }
        }
    </style>
</head>
<body>

<header class="header">
    <div class="header-inner">
        <a href="/" class="logo">
            <span class="logo-icon">{{ substr(\App\Models\Setting::get('company_name', 'J'), 0, 1) }}</span>
            {{ \App\Models\Setting::get('company_name', 'JamVini Hosting') }}
        </a>
        <nav class="header-nav">
            @auth
                @if(auth('admin')->check())
                    <a href="/admin/dashboard" class="btn btn-primary btn-sm">Admin Panel</a>
                @else
                    <a href="/client/dashboard" class="btn btn-primary btn-sm">Client Portal</a>
                @endif
            @else
                <a href="/login" class="btn btn-sm" style="background: transparent; color: #475569; border: 1px solid #e5e7eb;">Sign In</a>
                <a href="/register" class="btn btn-primary btn-sm">Get Started Free</a>
            @endif
        </nav>
    </div>
</header>

<section class="landing-hero">
    <div class="hero-inner">
        <span class="section-label" style="background:rgba(255,255,255,.1);color:#c4b5fd;margin-bottom:2rem;">⚡ Open Source Hosting Management</span>
        <h1>Manage Your <span>Hosting Business</span> With Ease</h1>
        <p>The complete management platform for Tanzanian web hosting companies. Handle clients, invoices, domains, payments, and your website — all from one place. No expensive tiers, just what you need.</p>
        <div class="hero-actions">
            <a href="/register" class="btn-hero btn-hero-primary">🚀 Start Free — No Credit Card</a>
            <a href="#features" class="btn-hero btn-hero-outline">📖 Learn More</a>
        </div>
    </div>
</section>

<section id="features" class="features-section">
    <div style="text-align:center;"><span class="section-label">Why JamVini</span></div>
    <h2 class="section-title">Everything You Need</h2>
    <p class="section-subtitle">Purpose-built for Tanzanian web hosting companies. Replace WordPress + WHMCS with one platform.</p>
    
    <div class="features-grid">
        <div class="feature-card">
            <div class="feature-icon purple">👥</div>
            <h3 class="feature-title">Client Management</h3>
            <p class="feature-desc">Organize hosting clients with profiles, contacts, service history, and VAT/TIN tracking.</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon green">📄</div>
            <h3 class="feature-title">Smart Invoicing</h3>
            <p class="feature-desc">Generate professional invoices with 18% VAT, partial payments, and PDF downloads.</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon amber">🌐</div>
            <h3 class="feature-title">Domain Management</h3>
            <p class="feature-desc">Register .co.tz, .or.tz, .com and more. Auto-renewal reminders and WHOIS lookup.</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon blue">💰</div>
            <h3 class="feature-title">Mobile Money Ready</h3>
            <p class="feature-desc">Accept M-Pesa, Tigo Pesa, Airtel Money and bank transfers. Built for Tanzania.</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon purple">🧩</div>
            <h3 class="feature-title">Plugin Ecosystem</h3>
            <p class="feature-desc">Extend with plugins: SMS, payment gateways, registrars. Only install what you need.</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon green">🎨</div>
            <h3 class="feature-title">Visual Page Builder</h3>
            <p class="feature-desc">Build your website with drag-and-drop. No coding needed. Comes with a beautiful theme.</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon amber">🛒</div>
            <h3 class="feature-title">Order System</h3>
            <p class="feature-desc">Clients browse services, add to cart, checkout. Admin accepts and auto-generates invoices.</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon blue">📊</div>
            <h3 class="feature-title">Client Portal</h3>
            <p class="feature-desc">Clients view their services, domains, invoices, and submit support requests.</p>
        </div>
    </div>
</section>

<div class="cta-section">
    <h2>Ready to streamline your hosting business?</h2>
    <p>Join hosting companies across Tanzania using JamVini. Free to install, free to use core features.</p>
    <a href="/register" class="btn-hero btn-hero-outline" style="background:#fff;color:#6C5CE7;border:none;">Start Building Today →</a>
</div>

<footer class="footer">
    <div>&copy; {{ date('Y') }} {{ \App\Models\Setting::get('company_name', 'JamVini Hosting') }}. Built for Tanzania. 🇹🇿</div>
    <div style="margin-top: 8px;">
        <a href="/admin/login" style="color:#64748b;">Admin</a> &middot;
        <a href="/login" style="color:#64748b;">Client Login</a>
    </div>
</footer>

<script src="{{ asset('themes/default/assets/js/frontend.js') }}"></script>
</body>
</html>