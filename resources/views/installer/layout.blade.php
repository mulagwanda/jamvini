<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>JamVini Installation</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; background: #eef2f7; color: #1e293b; }
        .jv-icon { display: inline-block; width: 1em; height: 1em; flex-shrink: 0; vertical-align: -0.15em; stroke: currentColor; }
        .installer-wrapper { min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 24px; }
        .installer-card { background: white; border: 1px solid #e2e8f0; border-radius: 12px; box-shadow: 0 18px 50px rgba(15,23,42,0.10); width: 100%; max-width: 640px; overflow: hidden; }
        .installer-header { background: #111827; color: white; padding: 34px 32px; }
        .brand-row { display: flex; align-items: center; gap: 14px; justify-content: center; text-align: left; }
        .brand-mark { width: 48px; height: 48px; border-radius: 12px; background: #6C5CE7; display: grid; place-items: center; font-weight: 800; font-size: 1.25rem; }
        .installer-header h1 { font-size: 1.75rem; margin-bottom: 4px; letter-spacing: 0; }
        .installer-header p { color: #cbd5e1; font-size: 0.95rem; line-height: 1.5; }
        .steps { display: grid; grid-template-columns: repeat(4, 1fr); padding: 0; background: #f8fafc; border-bottom: 1px solid #e2e8f0; }
        .step { text-align: center; padding: 13px 8px; font-size: 0.76rem; color: #64748b; border-bottom: 3px solid transparent; }
        .step.active { color: #6C5CE7; font-weight: 700; border-bottom-color: #6C5CE7; background: #fff; }
        .installer-body { padding: 34px; }
        .page-title { font-size: 1.35rem; margin-bottom: 8px; }
        .page-copy { color: #64748b; margin-bottom: 24px; line-height: 1.6; }
        .form-group { margin-bottom: 20px; }
        .form-label { display: block; font-weight: 600; margin-bottom: 6px; font-size: 0.85rem; }
        .form-input { width: 100%; padding: 12px 16px; border: 1.5px solid #cbd5e1; border-radius: 8px; font-size: 1rem; }
        .form-input:focus { border-color: #6C5CE7; outline: none; box-shadow: 0 0 0 3px rgba(108,92,231,0.1); }
        .form-hint { margin-top: 6px; color: #64748b; font-size: 0.78rem; line-height: 1.45; }
        .btn { display: inline-block; padding: 12px 28px; border-radius: 8px; font-weight: 700; cursor: pointer; border: none; font-size: 0.95rem; text-decoration: none; text-align: center; }
        .btn-primary { background: #6C5CE7; color: white; }
        .btn-secondary { background: #f1f5f9; color: #1e293b; }
        .btn-block { width: 100%; }
        .alert { padding: 16px; border-radius: 8px; margin-bottom: 20px; }
        .alert-error { background: #fef2f2; color: #991b1b; border-left: 4px solid #ef4444; }
        .alert-success { background: #f0fdf4; color: #065f46; border-left: 4px solid #00B894; }
        .error-list { margin: 8px 0 0 18px; }
        .requirement { display: flex; align-items: center; justify-content: space-between; gap: 16px; padding: 12px 0; border-bottom: 1px solid #f1f5f9; }
        .status { border-radius: 999px; padding: 4px 10px; font-size: 0.76rem; font-weight: 700; white-space: nowrap; display: inline-flex; align-items: center; gap: 5px; }
        .pass { background: #ecfdf5; color: #047857; }
        .fail { background: #fef2f2; color: #b91c1c; }
        .action-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
        .install-overlay { position: fixed; inset: 0; background: rgba(15,23,42,0.58); display: none; align-items: center; justify-content: center; padding: 24px; z-index: 50; }
        .install-overlay.show { display: flex; }
        .install-progress { width: min(100%, 380px); background: #fff; border-radius: 12px; padding: 26px; text-align: center; box-shadow: 0 24px 70px rgba(15,23,42,0.22); }
        .spinner { width: 38px; height: 38px; border-radius: 50%; border: 4px solid #e2e8f0; border-top-color: #6C5CE7; margin: 0 auto 16px; animation: spin 0.8s linear infinite; }
        .install-progress strong { display: block; margin-bottom: 6px; font-size: 1.05rem; }
        .install-progress span { color: #64748b; font-size: 0.9rem; line-height: 1.5; }
        @keyframes spin { to { transform: rotate(360deg); } }
        @media (max-width: 560px) {
            .installer-wrapper { padding: 12px; align-items: flex-start; }
            .installer-card { border-radius: 10px; }
            .installer-header { padding: 26px 22px; }
            .brand-row { justify-content: flex-start; }
            .installer-body { padding: 24px 20px; }
            .step { font-size: 0.68rem; padding: 11px 4px; }
            .action-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
<div class="installer-wrapper">
    <div class="installer-card">
        <div class="installer-header">
            <div class="brand-row">
                <div class="brand-mark">JV</div>
                <div>
                    <h1>JamVini</h1>
                    <p>Open-source hosting billing and automation</p>
                </div>
            </div>
        </div>
        
        {{-- Step indicators --}}
        <div class="steps">
            @php
                $activeStep = $step ?? request()->segment(2) ?? 'requirements';
            @endphp
            @foreach(['requirements' => 'Requirements', 'database' => 'Database', 'admin' => 'Admin', 'complete' => 'Complete'] as $key => $label)
                <div class="step {{ $activeStep === $key ? 'active' : '' }}">
                
                    {{ $loop->iteration }}. {{ $label }}
                </div>
            @endforeach
        </div>
        
        <div class="installer-body">
            @yield('content')
        </div>
    </div>
</div>
<div class="install-overlay" id="installOverlay" aria-live="polite" aria-hidden="true">
    <div class="install-progress">
        <div class="spinner"></div>
        <strong id="installOverlayTitle">Working on it</strong>
        <span id="installOverlayText">Please keep this page open.</span>
    </div>
</div>
<script>
document.querySelectorAll('form[data-install-progress]').forEach((form) => {
    form.addEventListener('submit', () => {
        const overlay = document.getElementById('installOverlay');
        const title = document.getElementById('installOverlayTitle');
        const text = document.getElementById('installOverlayText');
        const button = form.querySelector('button[type="submit"]');

        if (title) title.textContent = form.dataset.progressTitle || 'Working on it';
        if (text) text.textContent = form.dataset.progressText || 'Please keep this page open.';
        if (button) {
            button.disabled = true;
            button.style.opacity = '0.75';
        }
        if (overlay) {
            overlay.classList.add('show');
            overlay.setAttribute('aria-hidden', 'false');
        }
    });
});
</script>
</body>
</html>
