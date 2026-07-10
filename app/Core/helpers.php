<?php

if (!function_exists('jv_icon')) {
    function jv_icon(string $icon, string $class = '', int $size = 20, array $attrs = []): \Illuminate\Support\HtmlString
    {
        $name = jv_icon_name($icon);
        $paths = jv_lucide_paths($name) ?? jv_lucide_paths('circle');
        $attributes = array_merge([
            'class' => trim('jv-icon ' . $class),
            'width' => (string) $size,
            'height' => (string) $size,
            'viewBox' => '0 0 24 24',
            'fill' => 'none',
            'stroke' => 'currentColor',
            'stroke-width' => '2',
            'stroke-linecap' => 'round',
            'stroke-linejoin' => 'round',
            'aria-hidden' => 'true',
            'focusable' => 'false',
        ], $attrs);

        $htmlAttributes = collect($attributes)
            ->filter(fn ($value) => $value !== null && $value !== false)
            ->map(fn ($value, $key) => $key . '="' . e((string) $value) . '"')
            ->implode(' ');

        return new \Illuminate\Support\HtmlString("<svg {$htmlAttributes}>{$paths}</svg>");
    }
}

if (!function_exists('jv_icon_name')) {
    function jv_icon_name(?string $icon): string
    {
        $icon = trim((string) $icon);
        $map = [
            '📊' => 'layout-dashboard', '👥' => 'users', '🌐' => 'globe', '📄' => 'file-text',
            '🛒' => 'shopping-cart', '⚙️' => 'settings', '⚙' => 'settings', '🖥️' => 'server',
            '🖥' => 'server', '📝' => 'notebook-pen', '🎧' => 'headphones', '🔌' => 'plug',
            '💳' => 'credit-card', '💬' => 'message-circle', '📦' => 'package', '🏠' => 'home',
            '🖼️' => 'image', '🖼' => 'image', '🧭' => 'compass', '🔍' => 'search',
            '📋' => 'clipboard-list', '🎞️' => 'gallery-horizontal', '🎞' => 'gallery-horizontal',
            '📚' => 'book-open', '🎨' => 'palette', '👤' => 'user', '🏢' => 'building-2',
            '🧩' => 'puzzle', '⏰' => 'clock', '🚪' => 'log-out', '✅' => 'check-circle',
            '✓' => 'check', '❌' => 'x-circle', '✕' => 'x', '⚠️' => 'triangle-alert',
            '⚠' => 'triangle-alert', '▶️' => 'play', '▶' => 'play', '🔄' => 'refresh-cw',
            '🔒' => 'lock', '📧' => 'mail', '💰' => 'dollar-sign', '📤' => 'send',
            '⏳' => 'hourglass', '🎉' => 'sparkles', '📁' => 'folder', '🏷️' => 'tag',
            '🏷' => 'tag', '🔗' => 'link', '↑' => 'upload', 'ℹ️' => 'info',
            'ℹ' => 'info', 'AI' => 'bot', 'JV' => 'wand-sparkles', 'TH' => 'palette',
            'IMG' => 'image', 'SL' => 'gallery-horizontal', 'R' => 'trending-up',
            'M' => 'chart-no-axes-column-increasing', 'I' => 'file-text', 'O' => 'shopping-cart',
            'C' => 'user-plus', 'S' => 'package-plus', 'D' => 'globe', 'G' => 'settings',
            'A' => 'activity', 'P' => 'pause-circle', 'B' => 'building-2', 'J' => 'check-circle',
        ];

        $normalized = strtolower(str_replace(['_', ' '], '-', $icon));

        return $map[$icon] ?? $map[strtoupper($icon)] ?? $normalized ?: 'circle';
    }
}

if (!function_exists('jv_lucide_paths')) {
    function jv_lucide_paths(string $name): ?string
    {
        return [
            'activity' => '<path d="M22 12h-4l-3 9L9 3l-3 9H2"/>',
            'align-center' => '<path d="M17 12H7"/><path d="M19 18H5"/><path d="M21 6H3"/>',
            'align-justify' => '<path d="M3 12h18"/><path d="M3 18h18"/><path d="M3 6h18"/>',
            'align-left' => '<path d="M15 12H3"/><path d="M17 18H3"/><path d="M21 6H3"/>',
            'align-right' => '<path d="M21 12H9"/><path d="M21 18H7"/><path d="M21 6H3"/>',
            'arrow-left' => '<path d="m12 19-7-7 7-7"/><path d="M19 12H5"/>',
            'bar-chart-3' => '<path d="M3 3v18h18"/><path d="M18 17V9"/><path d="M13 17V5"/><path d="M8 17v-3"/>',
            'banknote' => '<rect width="20" height="12" x="2" y="6" rx="2"/><circle cx="12" cy="12" r="2"/><path d="M6 12h.01"/><path d="M18 12h.01"/>',
            'badge-check' => '<path d="M3.85 8.62a4 4 0 0 1 4.78-4.77 4 4 0 0 1 6.74 0 4 4 0 0 1 4.78 4.78 4 4 0 0 1 0 6.74 4 4 0 0 1-4.78 4.78 4 4 0 0 1-6.74 0 4 4 0 0 1-4.78-4.78 4 4 0 0 1 0-6.75Z"/><path d="m9 12 2 2 4-4"/>',
            'badge-dollar-sign' => '<path d="M3.85 8.62a4 4 0 0 1 4.78-4.77 4 4 0 0 1 6.74 0 4 4 0 0 1 4.78 4.78 4 4 0 0 1 0 6.74 4 4 0 0 1-4.78 4.78 4 4 0 0 1-6.74 0 4 4 0 0 1-4.78-4.78 4 4 0 0 1 0-6.75Z"/><path d="M12 7v10"/><path d="M15 9.5A3 3 0 0 0 12 8a3 3 0 0 0 0 6 3 3 0 0 1 0 6 3 3 0 0 1-3-1.5"/>',
            'book-open' => '<path d="M12 7v14"/><path d="M3 18a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1h5a4 4 0 0 1 4 4 4 4 0 0 1 4-4h5a1 1 0 0 1 1 1v13a1 1 0 0 1-1 1h-6a3 3 0 0 0-3 3 3 3 0 0 0-3-3z"/>',
            'bold' => '<path d="M6 4h8a4 4 0 0 1 0 8H6z"/><path d="M6 12h9a4 4 0 0 1 0 8H6z"/>',
            'bot' => '<path d="M12 8V4H8"/><rect width="16" height="12" x="4" y="8" rx="2"/><path d="M2 14h2"/><path d="M20 14h2"/><path d="M15 13v2"/><path d="M9 13v2"/>',
            'brackets' => '<path d="M16 3h3v18h-3"/><path d="M8 21H5V3h3"/>',
            'building-2' => '<path d="M6 22V4a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v18Z"/><path d="M6 12H4a2 2 0 0 0-2 2v8h20v-8a2 2 0 0 0-2-2h-2"/><path d="M10 6h4"/><path d="M10 10h4"/><path d="M10 14h4"/><path d="M10 18h4"/>',
            'between-horizontal-start' => '<rect width="13" height="7" x="8" y="3" rx="1"/><path d="m2 9 3-3-3-3"/><rect width="13" height="7" x="8" y="14" rx="1"/><path d="m2 20 3-3-3-3"/>',
            'chart-no-axes-column-increasing' => '<path d="M5 21v-6"/><path d="M12 21V9"/><path d="M19 21V3"/>',
            'check' => '<path d="M20 6 9 17l-5-5"/>',
            'check-circle' => '<path d="M9 12l2 2 4-4"/><circle cx="12" cy="12" r="10"/>',
            'chevron-down' => '<path d="m6 9 6 6 6-6"/>',
            'circle' => '<circle cx="12" cy="12" r="10"/>',
            'calendar-days' => '<path d="M8 2v4"/><path d="M16 2v4"/><rect width="18" height="18" x="3" y="4" rx="2"/><path d="M3 10h18"/><path d="M8 14h.01"/><path d="M12 14h.01"/><path d="M16 14h.01"/><path d="M8 18h.01"/><path d="M12 18h.01"/><path d="M16 18h.01"/>',
            'clipboard-list' => '<rect width="8" height="4" x="8" y="2" rx="1"/><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><path d="M12 11h4"/><path d="M12 16h4"/><path d="M8 11h.01"/><path d="M8 16h.01"/>',
            'clock' => '<circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/>',
            'code-2' => '<path d="m18 16 4-4-4-4"/><path d="m6 8-4 4 4 4"/><path d="m14.5 4-5 16"/>',
            'columns-3' => '<rect width="18" height="18" x="3" y="3" rx="2"/><path d="M9 3v18"/><path d="M15 3v18"/>',
            'compass' => '<path d="m16.24 7.76-2.12 6.36-6.36 2.12 2.12-6.36z"/><circle cx="12" cy="12" r="10"/>',
            'credit-card' => '<rect width="20" height="14" x="2" y="5" rx="2"/><path d="M2 10h20"/>',
            'database' => '<ellipse cx="12" cy="5" rx="9" ry="3"/><path d="M3 5v14c0 1.7 4 3 9 3s9-1.3 9-3V5"/><path d="M3 12c0 1.7 4 3 9 3s9-1.3 9-3"/>',
            'download' => '<path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><path d="M7 10l5 5 5-5"/><path d="M12 15V3"/>',
            'dollar-sign' => '<line x1="12" x2="12" y1="2" y2="22"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7H14a3.5 3.5 0 0 1 0 7H6"/>',
            'eraser' => '<path d="m7 21-4.3-4.3a2.4 2.4 0 0 1 0-3.4L13.3 2.7a2.4 2.4 0 0 1 3.4 0l4.6 4.6a2.4 2.4 0 0 1 0 3.4L11 21"/><path d="M22 21H7"/><path d="m5 11 9 9"/>',
            'external-link' => '<path d="M15 3h6v6"/><path d="M10 14 21 3"/><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/>',
            'eye' => '<path d="M2.1 12s3.7-7 9.9-7 9.9 7 9.9 7-3.7 7-9.9 7-9.9-7-9.9-7Z"/><circle cx="12" cy="12" r="3"/>',
            'file' => '<path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7z"/><path d="M14 2v4a2 2 0 0 0 2 2h4"/>',
            'file-spreadsheet' => '<path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7z"/><path d="M14 2v4a2 2 0 0 0 2 2h4"/><path d="M8 13h8"/><path d="M8 17h8"/><path d="M10 9v8"/><path d="M14 9v8"/>',
            'file-text' => '<path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7z"/><path d="M14 2v4a2 2 0 0 0 2 2h4"/><path d="M10 9H8"/><path d="M16 13H8"/><path d="M16 17H8"/>',
            'filter' => '<path d="M22 3H2l8 9.5V19l4 2v-8.5z"/>',
            'folder' => '<path d="M20 20a2 2 0 0 0 2-2V8a2 2 0 0 0-2-2h-7.9a2 2 0 0 1-1.7-.9L9.6 3.9A2 2 0 0 0 7.9 3H4a2 2 0 0 0-2 2v13a2 2 0 0 0 2 2Z"/>',
            'gallery-horizontal' => '<path d="M2 3v18"/><path d="M22 3v18"/><rect width="12" height="14" x="6" y="5" rx="2"/>',
            'gallery-horizontal-end' => '<path d="M2 7v10"/><path d="M6 5v14"/><rect width="12" height="14" x="10" y="5" rx="2"/>',
            'globe' => '<circle cx="12" cy="12" r="10"/><path d="M12 2a14.5 14.5 0 0 0 0 20 14.5 14.5 0 0 0 0-20"/><path d="M2 12h20"/>',
            'hard-drive' => '<line x1="22" x2="2" y1="12" y2="12"/><path d="M5.45 5.11 2 12v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6l-3.45-6.89A2 2 0 0 0 16.76 4H7.24a2 2 0 0 0-1.79 1.11z"/><line x1="6" x2="6.01" y1="16" y2="16"/><line x1="10" x2="10.01" y1="16" y2="16"/>',
            'heading-2' => '<path d="M4 12h8"/><path d="M4 18V6"/><path d="M12 18V6"/><path d="M21 18h-4c0-4 4-3 4-6 0-1.5-1-2-2-2s-2 .5-2 2"/>',
            'headphones' => '<path d="M3 14h3a2 2 0 0 1 2 2v3a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-7a9 9 0 0 1 18 0v7a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3"/>',
            'home' => '<path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2h-4v-7H9v7H5a2 2 0 0 1-2-2z"/>',
            'hourglass' => '<path d="M5 22h14"/><path d="M5 2h14"/><path d="M17 22v-4.2a4 4 0 0 0-1.2-2.8L12 12l-3.8 3A4 4 0 0 0 7 17.8V22"/><path d="M7 2v4.2A4 4 0 0 0 8.2 9L12 12l3.8-3A4 4 0 0 0 17 6.2V2"/>',
            'image' => '<rect width="18" height="18" x="3" y="3" rx="2"/><circle cx="9" cy="9" r="2"/><path d="m21 15-3.1-3.1a2 2 0 0 0-2.8 0L6 21"/>',
            'indent' => '<path d="M21 6H11"/><path d="M21 12H11"/><path d="M21 18H11"/><path d="m3 8 4 4-4 4"/>',
            'info' => '<circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/>',
            'italic' => '<line x1="19" x2="10" y1="4" y2="4"/><line x1="14" x2="5" y1="20" y2="20"/><line x1="15" x2="9" y1="4" y2="20"/>',
            'layout-dashboard' => '<rect width="7" height="9" x="3" y="3" rx="1"/><rect width="7" height="5" x="14" y="3" rx="1"/><rect width="7" height="9" x="14" y="12" rx="1"/><rect width="7" height="5" x="3" y="16" rx="1"/>',
            'landmark' => '<line x1="3" x2="21" y1="22" y2="22"/><line x1="6" x2="6" y1="18" y2="11"/><line x1="10" x2="10" y1="18" y2="11"/><line x1="14" x2="14" y1="18" y2="11"/><line x1="18" x2="18" y1="18" y2="11"/><polygon points="12 2 20 7 4 7"/>',
            'link' => '<path d="M10 13a5 5 0 0 0 7.1 0l2-2a5 5 0 0 0-7.1-7.1l-1.1 1.1"/><path d="M14 11a5 5 0 0 0-7.1 0l-2 2a5 5 0 0 0 7.1 7.1l1.1-1.1"/>',
            'list' => '<path d="M8 6h13"/><path d="M8 12h13"/><path d="M8 18h13"/><path d="M3 6h.01"/><path d="M3 12h.01"/><path d="M3 18h.01"/>',
            'list-check' => '<path d="M11 18H3"/><path d="m15 18 2 2 4-4"/><path d="M16 12H3"/><path d="M16 6H3"/>',
            'list-ordered' => '<path d="M10 6h11"/><path d="M10 12h11"/><path d="M10 18h11"/><path d="M4 6h1v4"/><path d="M4 10h2"/><path d="M6 18H4c0-1 2-2 2-3s-1-1.5-2-1"/>',
            'lock' => '<rect width="18" height="11" x="3" y="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>',
            'log-out' => '<path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><path d="m16 17 5-5-5-5"/><path d="M21 12H9"/>',
            'mail' => '<rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-10 6L2 7"/>',
            'menu' => '<path d="M4 12h16"/><path d="M4 6h16"/><path d="M4 18h16"/>',
            'megaphone' => '<path d="m3 11 18-5v12L3 14v-3z"/><path d="M11.6 16.8a3 3 0 1 1-5.8-1.6"/>',
            'message-circle' => '<path d="M7.9 20A9 9 0 1 0 4 16.1L2 22Z"/>',
            'minus' => '<path d="M5 12h14"/>',
            'monitor' => '<rect width="20" height="14" x="2" y="3" rx="2"/><path d="M8 21h8"/><path d="M12 17v4"/>',
            'mouse-pointer-2' => '<path d="m4 4 7.1 17 2.5-7.4L21 11.1z"/>',
            'notebook-pen' => '<path d="M13.4 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-7.4"/><path d="M2 6h4"/><path d="M2 10h4"/><path d="M2 14h4"/><path d="M2 18h4"/><path d="M21.4 5.6a2.1 2.1 0 1 0-3-3L12 9v3h3z"/>',
            'outdent' => '<path d="M21 6H11"/><path d="M21 12H11"/><path d="M21 18H11"/><path d="m7 8-4 4 4 4"/>',
            'package' => '<path d="m7.5 4.3 9 5.2"/><path d="M21 8a2 2 0 0 0-1-1.7l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.7l7 4a2 2 0 0 0 2 0l7-4a2 2 0 0 0 1-1.7Z"/><path d="m3.3 7 8.7 5 8.7-5"/><path d="M12 22V12"/>',
            'package-plus' => '<path d="M16 16h6"/><path d="M19 13v6"/><path d="m7.5 4.3 9 5.2"/><path d="M21 10V8a2 2 0 0 0-1-1.7l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.7l7 4a2 2 0 0 0 2 0l2-1.1"/><path d="m3.3 7 8.7 5 8.7-5"/><path d="M12 22V12"/>',
            'palette' => '<circle cx="13.5" cy="6.5" r=".5"/><circle cx="17.5" cy="10.5" r=".5"/><circle cx="8.5" cy="7.5" r=".5"/><circle cx="6.5" cy="12.5" r=".5"/><path d="M12 2C6.5 2 2 6 2 11c0 4.4 3.6 8 8 8h1.5a2.5 2.5 0 0 0 0-5H11a2 2 0 0 1 0-4h1a10 10 0 0 0 10-10 2 2 0 0 0-2-2z"/>',
            'panel-top' => '<rect width="18" height="18" x="3" y="3" rx="2"/><path d="M3 9h18"/>',
            'pause-circle' => '<circle cx="12" cy="12" r="10"/><path d="M10 15V9"/><path d="M14 15V9"/>',
            'pencil' => '<path d="M21.2 8.4 8.6 21H3v-5.6L15.6 2.8a2 2 0 0 1 2.8 0l2.8 2.8a2 2 0 0 1 0 2.8Z"/><path d="m14 5 5 5"/>',
            'play' => '<polygon points="6 3 20 12 6 21 6 3"/>',
            'plug' => '<path d="M12 22v-5"/><path d="M9 8V2"/><path d="M15 8V2"/><path d="M18 8v5a6 6 0 0 1-12 0V8Z"/>',
            'plus' => '<path d="M5 12h14"/><path d="M12 5v14"/>',
            'plus-circle' => '<circle cx="12" cy="12" r="10"/><path d="M8 12h8"/><path d="M12 8v8"/>',
            'puzzle' => '<path d="M15.4 15.4A2.1 2.1 0 1 0 18 18v1.5a2.5 2.5 0 0 1-2.5 2.5H13v-2.1a2.1 2.1 0 1 0-2 0V22H8.5A2.5 2.5 0 0 1 6 19.5V17H3.9a2.1 2.1 0 1 1 0-4H6v-2.5A2.5 2.5 0 0 1 8.5 8H11V5.9a2.1 2.1 0 1 1 4 0V8h2.5a2.5 2.5 0 0 1 2.5 2.5V13h-2.1a2.1 2.1 0 0 0-2.5 2.4Z"/>',
            'refresh-cw' => '<path d="M3 12a9 9 0 0 1 15-6.7L21 8"/><path d="M21 3v5h-5"/><path d="M21 12a9 9 0 0 1-15 6.7L3 16"/><path d="M3 21v-5h5"/>',
            'redo-2' => '<path d="m15 14 5-5-5-5"/><path d="M20 9H10a5 5 0 0 0 0 10h1"/>',
            'search' => '<circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/>',
            'save' => '<path d="M15.2 3a2 2 0 0 1 1.4.6l3.8 3.8a2 2 0 0 1 .6 1.4V19a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2z"/><path d="M17 21v-8H7v8"/><path d="M7 3v5h8"/>',
            'send' => '<path d="m22 2-7 20-4-9-9-4Z"/><path d="M22 2 11 13"/>',
            'server' => '<rect width="20" height="8" x="2" y="2" rx="2"/><rect width="20" height="8" x="2" y="14" rx="2"/><path d="M6 6h.01"/><path d="M6 18h.01"/>',
            'settings' => '<path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.52a2 2 0 0 1-1 1.72l-.15.1a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.38a2 2 0 0 0-.73-2.73l-.15-.1a2 2 0 0 1-1-1.72v-.52a2 2 0 0 1 1-1.72l.15-.1a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2Z"/><circle cx="12" cy="12" r="3"/>',
            'shopping-cart' => '<circle cx="8" cy="21" r="1"/><circle cx="19" cy="21" r="1"/><path d="M2.1 2.1h2l2.7 12.4a2 2 0 0 0 2 1.6h8.9a2 2 0 0 0 2-1.6L21 8H5.1"/>',
            'sliders-horizontal' => '<line x1="21" x2="14" y1="4" y2="4"/><line x1="10" x2="3" y1="4" y2="4"/><line x1="21" x2="12" y1="12" y2="12"/><line x1="8" x2="3" y1="12" y2="12"/><line x1="21" x2="16" y1="20" y2="20"/><line x1="12" x2="3" y1="20" y2="20"/><line x1="14" x2="14" y1="2" y2="6"/><line x1="8" x2="8" y1="10" y2="14"/><line x1="16" x2="16" y1="18" y2="22"/>',
            'shapes' => '<path d="M8.3 10a2.3 2.3 0 1 0 0-4.6 2.3 2.3 0 0 0 0 4.6Z"/><path d="M20 14h-5v5h5z"/><path d="m4 22 4-7 4 7z"/>',
            'sparkles' => '<path d="m12 3-1.9 5.8a2 2 0 0 1-1.3 1.3L3 12l5.8 1.9a2 2 0 0 1 1.3 1.3L12 21l1.9-5.8a2 2 0 0 1 1.3-1.3L21 12l-5.8-1.9a2 2 0 0 1-1.3-1.3Z"/><path d="M5 3v4"/><path d="M19 17v4"/><path d="M3 5h4"/><path d="M17 19h4"/>',
            'square-mouse-pointer' => '<path d="M12.034 12.681a.5.5 0 0 1 .647-.647l7.12 2.84a.5.5 0 0 1-.033.94l-2.62.873a2 2 0 0 0-1.264 1.264l-.873 2.62a.5.5 0 0 1-.94.033z"/><path d="M21 11V5a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h6"/>',
            'store' => '<path d="m2 7 2-4h16l2 4"/><path d="M4 7v13a1 1 0 0 0 1 1h14a1 1 0 0 0 1-1V7"/><path d="M8 21v-7h8v7"/><path d="M2 7h20"/><path d="M6 7v3a2 2 0 0 0 4 0V7"/><path d="M14 7v3a2 2 0 0 0 4 0V7"/>',
            'strikethrough' => '<path d="M16 4H9a3 3 0 0 0-2.83 4"/><path d="M14 12a4 4 0 0 1 0 8H6"/><path d="M4 12h16"/>',
            'tag' => '<path d="M12.6 2.6a2 2 0 0 0-1.4-.6H4a2 2 0 0 0-2 2v7.2a2 2 0 0 0 .6 1.4l8.8 8.8a2 2 0 0 0 2.8 0l7.2-7.2a2 2 0 0 0 0-2.8z"/><circle cx="7.5" cy="7.5" r=".5"/>',
            'tablet' => '<rect width="16" height="20" x="4" y="2" rx="2"/><path d="M12 18h.01"/>',
            'terminal' => '<polyline points="4 17 10 11 4 5"/><line x1="12" x2="20" y1="19" y2="19"/>',
            'trending-up' => '<polyline points="22 7 13.5 15.5 8.5 10.5 2 17"/><polyline points="16 7 22 7 22 13"/>',
            'triangle-alert' => '<path d="m21.7 18-8-14a2 2 0 0 0-3.4 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.7-3"/><path d="M12 9v4"/><path d="M12 17h.01"/>',
            'trash-2' => '<path d="M3 6h18"/><path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/>',
            'type' => '<polyline points="4 7 4 4 20 4 20 7"/><line x1="9" x2="15" y1="20" y2="20"/><line x1="12" x2="12" y1="4" y2="20"/>',
            'underline' => '<path d="M6 4v6a6 6 0 0 0 12 0V4"/><line x1="4" x2="20" y1="20" y2="20"/>',
            'undo-2' => '<path d="M9 14 4 9l5-5"/><path d="M4 9h10a5 5 0 0 1 0 10h-1"/>',
            'unlink' => '<path d="m18.84 12.25 1.72-1.71a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="m5.17 11.75-1.71 1.71a5 5 0 0 0 7.07 7.07l1.71-1.71"/><line x1="8" x2="16" y1="2" y2="22"/>',
            'upload' => '<path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><path d="m17 8-5-5-5 5"/><path d="M12 3v12"/>',
            'user' => '<path d="M19 21a7 7 0 0 0-14 0"/><circle cx="12" cy="7" r="4"/>',
            'user-plus' => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M19 8v6"/><path d="M22 11h-6"/>',
            'users' => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.9"/><path d="M16 3.1a4 4 0 0 1 0 7.8"/>',
            'video' => '<path d="m16 13 5.2 3.1a.5.5 0 0 0 .8-.4V8.3a.5.5 0 0 0-.8-.4L16 11"/><rect width="14" height="12" x="2" y="6" rx="2"/>',
            'wand-sparkles' => '<path d="m21.6 12.4-9.2 9.2a2 2 0 0 1-2.8 0l-7.2-7.2a2 2 0 0 1 0-2.8l9.2-9.2a2 2 0 0 1 2.8 0l7.2 7.2a2 2 0 0 1 0 2.8Z"/><path d="m14.5 7.5 2 2"/><path d="M5 6v4"/><path d="M19 14v4"/><path d="M3 8h4"/><path d="M17 16h4"/>',
            'x' => '<path d="M18 6 6 18"/><path d="m6 6 12 12"/>',
            'x-circle' => '<circle cx="12" cy="12" r="10"/><path d="m15 9-6 6"/><path d="m9 9 6 6"/>',
        ][$name] ?? null;
    }
}

if (!function_exists('plugin_path')) {
    function plugin_path(string $path = ''): string
    {
        $path = ltrim($path, '/');

        if ($path === '') {
            return base_path('plugins');
        }

        if (function_exists('app') && app()->bound(\App\Core\PluginManager::class)) {
            $segments = explode('/', $path, 2);
            $base = app(\App\Core\PluginManager::class)->pluginPath($segments[0]);

            return isset($segments[1]) ? $base . '/' . $segments[1] : $base;
        }

        return base_path('plugins/' . $path);
    }
}

if (!function_exists('jv_menu_items')) {
    function jv_menu_items(string $location, array $fallback = []): array
    {
        try {
            if (
                !class_exists(\Plugins\Menus\src\Models\Menu::class) ||
                !\Illuminate\Support\Facades\Schema::hasTable('menus') ||
                !\Illuminate\Support\Facades\Schema::hasTable('menu_items')
            ) {
                return jv_normalize_menu_fallback($fallback);
            }

            $menu = \Plugins\Menus\src\Models\Menu::where('location', $location)
                ->where('is_active', true)
                ->with(['rootItems' => fn ($query) => $query->where('is_active', true)->with(['children' => fn ($children) => $children->where('is_active', true)])])
                ->first();

            if (!$menu || $menu->rootItems->isEmpty()) {
                return jv_normalize_menu_fallback($fallback);
            }

            return $menu->rootItems
                ->filter(fn ($item) => jv_menu_item_is_visible($item))
                ->map(fn ($item) => jv_transform_menu_item($item))
                ->values()
                ->all();
        } catch (\Throwable $e) {
            return jv_normalize_menu_fallback($fallback);
        }
    }
}

if (!function_exists('jv_transform_menu_item')) {
    function jv_transform_menu_item($item): array
    {
        $url = $item->resolvedUrl();
        $children = $item->children
            ->filter(fn ($child) => jv_menu_item_is_visible($child))
            ->map(fn ($child) => jv_transform_menu_item($child))
            ->values()
            ->all();

        return [
            'label' => $item->label,
            'url' => $url,
            'target' => $item->target ?: '_self',
            'active' => jv_menu_url_is_active($url) || collect($children)->contains(fn ($child) => $child['active'] ?? false),
            'children' => $children,
        ];
    }
}

if (!function_exists('jv_menu_item_is_visible')) {
    function jv_menu_item_is_visible($item): bool
    {
        if ($item->visibility === 'guest') {
            return !auth()->check();
        }

        if ($item->visibility === 'auth') {
            return auth()->check();
        }

        return true;
    }
}

if (!function_exists('jv_menu_url_is_active')) {
    function jv_menu_url_is_active(string $url): bool
    {
        $path = trim(parse_url($url, PHP_URL_PATH) ?: '/', '/');

        if ($path === '') {
            return request()->is('/');
        }

        return request()->is($path) || request()->is($path . '/*');
    }
}

if (!function_exists('jv_normalize_menu_fallback')) {
    function jv_normalize_menu_fallback(array $fallback): array
    {
        return collect($fallback)->map(function ($item) {
            $url = $item['url'] ?? '#';

            return [
                'label' => $item['label'] ?? $url,
                'url' => $url,
                'target' => $item['target'] ?? '_self',
                'active' => jv_menu_url_is_active($url),
                'children' => $item['children'] ?? [],
            ];
        })->all();
    }
}

if (!function_exists('theme_path')) {
    function theme_path(string $path = ''): string
    {
        return base_path('themes/' . ltrim($path, '/'));
    }
}

if (!function_exists('theme_asset')) {
    function theme_asset(string $path, string $area = 'public'): string
    {
        if (!function_exists('app')) {
            return asset("themes/default/assets/{$path}");
        }

        $theme = active_theme($area);

        return asset("themes/{$theme}/assets/{$path}");
    }
}

if (!function_exists('theme_asset_path')) {
    function theme_asset_path(string $path, string $area = 'public'): string
    {
        return public_path('themes/' . active_theme($area) . '/assets/' . ltrim($path, '/'));
    }
}

if (!function_exists('jv_theme_view')) {
    function jv_theme_view(string $view, string $area = 'public', string $fallbackTheme = 'default'): string
    {
        $theme = active_theme($area);
        $candidate = "themes.{$theme}::{$view}";

        if (\Illuminate\Support\Facades\View::exists($candidate)) {
            return $candidate;
        }

        return "themes.{$fallbackTheme}::{$view}";
    }
}

if (!function_exists('jv_theme_setting_key')) {
    function jv_theme_setting_key(string $theme, string $key): string
    {
        return 'theme_' . preg_replace('/[^a-z0-9_]+/', '_', strtolower($theme)) . '_' . $key;
    }
}

if (!function_exists('active_theme')) {
    function active_theme(string $area = 'public'): string
    {
        try {
            $area = in_array($area, ['public', 'client', 'admin'], true) ? $area : 'public';
            $theme = \App\Models\Setting::get("active_{$area}_theme");

            if (!$theme && $area === 'public') {
                $theme = \App\Models\Setting::get('active_theme', 'default');
            }

            $theme = $theme ?: 'default';

            return is_dir(base_path("themes/{$theme}")) ? $theme : 'default';
        } catch (\Throwable $e) {
            return 'default';
        }
    }
}

if (!function_exists('plugin_view')) {
    function plugin_view(string $plugin, string $view, array $data = [])
    {
        if (!function_exists('app')) {
            throw new \RuntimeException('Application not booted');
        }
        return app(\App\Core\ViewResolver::class)->resolve($plugin, $view);
    }
}

if (!function_exists('do_action')) {
    function do_action(string $hook, ...$args): void
    {
        if (class_exists(\App\Core\Hooks\Action::class)) {
            \App\Core\Hooks\Action::do($hook, ...$args);
        }
    }
}

if (!function_exists('apply_filters')) {
    function apply_filters(string $hook, mixed $value, ...$args): mixed
    {
        if (class_exists(\App\Core\Hooks\Filter::class)) {
            return \App\Core\Hooks\Filter::apply($hook, $value, ...$args);
        }
        return $value;
    }
}

if (!function_exists('do_shortcode')) {
    function do_shortcode(string $content): string
    {
        $pattern = '/\[(\w+)(?:\s+([^\]]+))?\]/';
        
        return preg_replace_callback($pattern, fn ($matches) => do_shortcode_rendered($matches[0]), $content);
    }
}

if (!function_exists('do_shortcode_rendered')) {
    function do_shortcode_rendered(string $sc): string
    {
        $pattern = '/\[(\w+)(?:\s+([^\]]+))?\]/';
        
        if (preg_match($pattern, $sc, $matches)) {
            $name = $matches[1];

            return (string) apply_filters("shortcode.{$name}", '', jv_shortcode_attrs($matches[2] ?? ''), $name, $sc);
        }
        
        return '';
    }
}

if (!function_exists('jv_shortcode_attrs')) {
    function jv_shortcode_attrs(string $raw): array
    {
        $attrs = [];

        preg_match_all('/([\w-]+)=["\']([^"\']*)["\']/', $raw, $attrMatches);
        foreach ($attrMatches[1] as $i => $key) {
            $attrs[$key] = $attrMatches[2][$i];
        }

        return $attrs;
    }
}

if (!function_exists('get_registrar_for_tld')) {
    /**
     * Get the registrar slug for a TLD, with fallback.
     * 1. Check TLD-specific registrar in domain_tlds
     * 2. Fall back to global default registrar setting
     * 3. Return null if nothing configured
     */
    function get_registrar_for_tld(string $tld): ?string
    {
        // 1. Check TLD-specific configuration
        $tldConfig = \Plugins\Domains\src\Models\DomainTld::where('tld', $tld)
            ->where('is_active', true)
            ->first();
        
        if ($tldConfig && !empty($tldConfig->registrar_slug)) {
            return $tldConfig->registrar_slug;
        }
        
        // 2. Fall back to global default
        $defaultRegistrar = \App\Models\Setting::get('domain_default_registrar');
        if (!empty($defaultRegistrar) && $defaultRegistrar !== 'manual') {
            return $defaultRegistrar;
        }
        
        // 3. Try to find any registrar that supports this TLD
        $registrar = \App\Core\Registries\RegistrarRegistry::findForTld($tld);
        if ($registrar) {
            return $registrar['slug'];
        }
        
        return null;
    }
}

if (!function_exists('get_server_for_service')) {
    /**
     * Get the server for a service, with fallback.
     * 1. Check service-specific server assignment
     * 2. Fall back to global default server setting
     * 3. Return null if nothing configured
     */
    function get_server_for_service($serviceId): ?\Plugins\Services\src\Models\Server
    {
        // 1. Check service-specific server
        $pivot = \DB::table('server_service')
            ->where('service_id', $serviceId)
            ->where('is_default', true)
            ->first();
        
        if ($pivot) {
            return \Plugins\Services\src\Models\Server::find($pivot->server_id);
        }
        
        // 2. Fall back to global default
        $defaultServerId = \App\Models\Setting::get('hosting_default_server');
        if ($defaultServerId) {
            return \Plugins\Services\src\Models\Server::find($defaultServerId);
        }
        
        return null;
    }
}
if (!function_exists('jv_theme_setting')) {
    function jv_theme_setting(string $key, $default = null, string $area = 'public')
    {
        return \App\Core\Registries\SettingRegistry::getThemeValue($key, $default, $area);
    }
}

if (!function_exists('jv_format_money')) {
    function jv_format_money($amount, ?string $currency = null): string
    {
        $currency = $currency ?: \App\Models\Setting::get('currency', 'TZS');
        $precision = \App\Models\Setting::get('currency_decimal_places', $currency === 'TZS' ? '0' : '2');

        return trim($currency . ' ' . number_format((float) $amount, (int) $precision));
    }
}

if (!function_exists('jv_format_date')) {
    function jv_format_date($date, ?string $format = null): string
    {
        if (!$date) {
            return '—';
        }

        $format = $format ?: \App\Models\Setting::get('date_format', 'd/m/Y');

        return $date instanceof \Carbon\CarbonInterface
            ? $date->format($format)
            : \Carbon\Carbon::parse($date)->format($format);
    }
}

if (!function_exists('jv_tax_rate')) {
    function jv_tax_rate(): float
    {
        if (\App\Models\Setting::get('vat_enabled', '1') !== '1') {
            return 0.0;
        }

        return (float) \App\Models\Setting::get('vat_rate', '18');
    }
}

if (!function_exists('jv_tax_label')) {
    function jv_tax_label(): string
    {
        return \App\Models\Setting::get('tax_label', 'VAT');
    }
}
