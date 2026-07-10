<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex,nofollow">
    <title>{{ $settings['title'] ?? 'Maintenance' }}</title>
    <style>
        :root { color-scheme: light; font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif; }
        body { margin:0; min-height:100vh; display:grid; place-items:center; background:#f6f8fb; color:#172033; padding:24px; }
        .maintenance { max-width:760px; text-align:center; background:#fff; border:1px solid #e5e7eb; border-radius:14px; padding:42px; box-shadow:0 24px 80px rgba(15,23,42,.08); }
        .mark { width:64px; height:64px; border-radius:999px; display:grid; place-items:center; margin:0 auto 20px; background:#eef2ff; color:#4338ca; font-size:30px; }
        h1 { margin:0 0 12px; font-size:clamp(2rem,5vw,3.2rem); line-height:1.05; }
        p { color:#526176; font-size:1.08rem; line-height:1.7; margin:0 auto; max-width:620px; }
        .time { margin-top:24px; color:#111827; font-weight:800; }
        a { color:#4338ca; font-weight:800; }
    </style>
</head>
<body>
    <main class="maintenance">
        <div class="mark">!</div>
        <h1>{{ $settings['title'] ?? 'We are improving your experience' }}</h1>
        <p>{{ $settings['message'] ?? 'JamVini is currently undergoing scheduled maintenance. Please check back soon.' }}</p>
        @if(!empty($settings['scheduled_end_at']))
            <div class="time">Expected back: {{ \Carbon\Carbon::parse($settings['scheduled_end_at'])->format('M j, Y H:i') }}</div>
        @endif
        @if(!empty($settings['contact_email']))
            <p style="margin-top:18px;">Need help? <a href="mailto:{{ $settings['contact_email'] }}">{{ $settings['contact_email'] }}</a></p>
        @endif
    </main>
</body>
</html>
