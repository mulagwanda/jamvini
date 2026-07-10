@php
    $brandName = jv_theme_setting('brand_name', 'JamVini Pulse');
    $logo = jv_theme_setting('logo_url', '');
    $assetUrl = fn ($path) => $path && !str_starts_with($path, 'http') && !str_starts_with($path, '/') ? asset('storage/' . $path) : $path;
    $menuItems = function_exists('jv_menu_items')
        ? jv_menu_items('primary', [
            ['label' => 'Home', 'url' => '/'],
            ['label' => 'Services', 'url' => '/services'],
            ['label' => 'Pricing', 'url' => '/pricing'],
            ['label' => 'About', 'url' => '/about'],
            ['label' => 'Contact', 'url' => '/contact'],
        ])
        : [];
    $extraLinks = json_decode(jv_theme_setting('custom_nav_links', '[]'), true);
    $extraLinks = is_array($extraLinks) ? $extraLinks : [];
@endphp

<header class="pulse-site-header">
    <a href="{{ url('/') }}" class="pulse-brand">
        @if($logo)
            <img src="{{ $assetUrl($logo) }}" alt="{{ $brandName }}">
        @else
            <span class="pulse-brand-mark">{{ jv_icon('activity', '', 18) }}</span>
            <span>{{ $brandName }}</span>
        @endif
    </a>
    
    <nav>
        @foreach($menuItems as $item)
            <a href="{{ $item['url'] ?? '#' }}" class="{{ !empty($item['active']) ? 'active' : '' }}" target="{{ $item['target'] ?? '_self' }}">
                {{ $item['label'] }}
            </a>
        @endforeach
        @foreach($extraLinks as $link)
            @if(!empty($link['label']) && !empty($link['url']))
                <a href="{{ $link['url'] }}" target="{{ $link['target'] ?? '_self' }}">
                    {{ $link['label'] }}
                </a>
            @endif
        @endforeach
        <a href="/client/dashboard" class="btn btn-sm btn-primary">Client Area</a>
    </nav>
    
    <!-- Mobile Toggle -->
    <button class="pulse-mobile-toggle" aria-label="Toggle navigation" type="button">
        <span></span>
        <span></span>
        <span></span>
    </button>
</header>

<!-- Mobile Navigation -->
<nav class="pulse-nav-mobile" role="navigation" aria-label="Mobile Navigation">
    @foreach($menuItems as $item)
        <a href="{{ $item['url'] ?? '#' }}" class="{{ !empty($item['active']) ? 'active' : '' }}">
            {{ $item['label'] }}
        </a>
    @endforeach
    @foreach($extraLinks as $link)
        @if(!empty($link['label']) && !empty($link['url']))
            <a href="{{ $link['url'] }}">{{ $link['label'] }}</a>
        @endif
    @endforeach
    <a href="/client/dashboard" class="btn btn-primary btn-block">Client Area</a>
</nav>
