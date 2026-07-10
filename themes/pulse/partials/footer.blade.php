@php
    $brandName = jv_theme_setting('brand_name', 'JamVini Pulse');
    $footerLogo = jv_theme_setting('footer_logo_url', '') ?: jv_theme_setting('logo_url', '');
    $tagline = jv_theme_setting('footer_tagline', 'The heartbeat of your hosting business.');
    $copyright = jv_theme_setting('copyright_text', '© ' . date('Y') . ' JamVini Hosting. All rights reserved.');
    $assetUrl = fn ($path) => $path && !str_starts_with($path, 'http') && !str_starts_with($path, '/') ? asset('storage/' . $path) : $path;
    $footerLinks = json_decode(jv_theme_setting('footer_links', '[]'), true);
    $footerLinks = is_array($footerLinks) ? $footerLinks : [];
    
    // Social links from settings
    $socialLinks = json_decode(jv_theme_setting('social_links', '[]'), true);
    $socialLinks = is_array($socialLinks) ? $socialLinks : [];
@endphp

<footer class="pulse-site-footer">
    <div>
        <strong class="pulse-footer-brand">
            @if($footerLogo)
                <img src="{{ $assetUrl($footerLogo) }}" alt="{{ $brandName }}">
            @else
                💜 {{ $brandName }}
            @endif
        </strong>
        <span>{{ $tagline }}</span>
        
        @if($socialLinks)
            <div class="pulse-footer-social">
                @foreach($socialLinks as $social)
                    @if(!empty($social['url']))
                        <a href="{{ $social['url'] }}" aria-label="{{ $social['label'] ?? 'Social' }}" target="_blank">
                            {{ $social['icon'] ?? '🔗' }}
                        </a>
                    @endif
                @endforeach
            </div>
        @endif
    </div>
    
    @if($footerLinks)
        <nav>
            @foreach($footerLinks as $link)
                @if(!empty($link['label']) && !empty($link['url']))
                    <a href="{{ $link['url'] }}">{{ $link['label'] }}</a>
                @endif
            @endforeach
        </nav>
    @endif
    
    <small>{{ $copyright }}</small>
</footer>