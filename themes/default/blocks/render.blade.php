{{-- TEXT --}}
@if($block['type'] === 'text')
    @php $tag = $data['tag'] ?? ($block['type'] === 'heading' ? 'h2' : 'p'); @endphp
    <{!! $tag !!} style="font-size: {{ $data['fontSize'] ?? '16' }}px; color: {{ $data['color'] ?? '#1e293b' }}; text-align: {{ $data['align'] ?? 'left' }}; line-height: 1.7; font-weight: {{ $block['type'] === 'heading' ? '700' : '400' }};">
        {!! nl2br(e($data['content'] ?? '')) !!}
    </{!! $tag !!}>

{{-- HEADING --}}
@elseif($block['type'] === 'heading')
    <div style="text-align: {{ $data['align'] ?? 'center' }};">
        @if(!empty($data['eyebrow']))
            <span class="eyebrow">{{ $data['eyebrow'] }}</span>
        @endif
        <{!! $data['tag'] ?? 'h2' !!} style="font-size: {{ $data['fontSize'] ?? '32' }}px; color: {{ $data['color'] ?? '#0f172a' }}; font-weight: 700; line-height: 1.3; margin-bottom: {{ !empty($data['subheading']) ? '0.75rem' : '0' }};">
            {{ $data['content'] ?? '' }}
        </{!! $data['tag'] ?? 'h2' !!}>
        @if(!empty($data['subheading']))
            <p style="font-size: 1.05rem; color: #64748b; max-width: 600px; margin: 0 auto;">
                {{ $data['subheading'] }}
            </p>
        @endif
    </div>

{{-- FEATURE CARD --}}
@elseif($block['type'] === 'feature-card')
    @php
        $iconColor = $data['iconColor'] ?? '#6C5CE7';
        $iconStyle = $data['iconStyle'] ?? 'filled';
        $iconPosition = $data['iconPosition'] ?? 'top';
        $align = $data['align'] ?? 'center';
        
        if ($iconStyle === 'filled') {
            $iconBg = "background: {$iconColor}; color: white;";
        } elseif ($iconStyle === 'outline') {
            $iconBg = "background: transparent; color: {$iconColor}; border: 2px solid {$iconColor};";
        } else {
            $iconBg = "background: transparent; color: {$iconColor};";
        }
        
        $isHorizontal = $iconPosition === 'left';
    @endphp
    
    <div class="feature-card" style="text-align: {{ $isHorizontal ? 'left' : $align }}; {{ $isHorizontal ? 'display: flex; align-items: flex-start; gap: 20px;' : '' }}">
        <div class="feature-icon" style="{{ $iconBg }} {{ $isHorizontal ? '' : 'margin: 0 auto 16px;' }} flex-shrink: 0; transition: all 0.25s;">
            {{ $data['icon'] ?? '⚡' }}
        </div>
        <div style="{{ $isHorizontal ? 'flex: 1;' : '' }}">
            <h3>{{ $data['title'] ?? '' }}</h3>
            <p>{{ $data['description'] ?? '' }}</p>
        </div>
    </div>

{{-- FEATURES GRID --}}
@elseif($block['type'] === 'features')
    @php
        $items = $data['items'] ?? [];
        $columns = max(1, min(4, (int) ($data['columns'] ?? 3)));
    @endphp
    <section class="features-block">
        @if(!empty($data['title']) || !empty($data['heading']) || !empty($data['subtitle']))
            <div class="section-head" style="text-align:center;margin-bottom:24px;">
                @if(!empty($data['title']) || !empty($data['heading']))
                    <h2>{{ $data['title'] ?? $data['heading'] }}</h2>
                @endif
                @if(!empty($data['subtitle']))
                    <p>{{ $data['subtitle'] }}</p>
                @endif
            </div>
        @endif
        <div style="display:grid;grid-template-columns:repeat({{ $columns }},minmax(0,1fr));gap:18px;">
            @forelse($items as $item)
                <div class="feature-card" style="background:#fff;border:1px solid #e2e8f0;border-radius:8px;padding:20px;">
                    @if(!empty($item['icon']))<div class="feature-icon" style="margin-bottom:12px;">{{ $item['icon'] }}</div>@endif
                    <h3>{{ $item['title'] ?? '' }}</h3>
                    <p>{{ $item['description'] ?? $item['text'] ?? '' }}</p>
                </div>
            @empty
                <div class="feature-card" style="background:#fff;border:1px solid #e2e8f0;border-radius:8px;padding:20px;">
                    <h3>Feature</h3>
                    <p>Add feature items in the page builder or theme demo JSON.</p>
                </div>
            @endforelse
        </div>
    </section>

{{-- PRICING TABLE --}}
@elseif($block['type'] === 'pricing-table')
    @php
        $plans = \Plugins\Services\src\Models\Service::where('is_active', true)
            ->whereHas('group', fn($q) => $q->where('module', 'hosting'))
            ->orderBy('amount')
            ->get();
    @endphp
    
    <section style="background: {{ $bg }}; padding: {{ $padding }};">
        <div class="container">
            @if(!empty($data['eyebrow']) || !empty($data['heading']))
                <div class="section-head reveal">
                    @if(!empty($data['eyebrow']))
                        <span class="eyebrow">{{ $data['eyebrow'] }}</span>
                    @endif
                    @if(!empty($data['heading']))
                        <h2>{{ $data['heading'] }}</h2>
                    @endif
                    @if(!empty($data['subheading']))
                        <p>{{ $data['subheading'] }}</p>
                    @endif
                </div>
            @endif
            
            @if(($data['showToggle'] ?? true) && $plans->count() > 0)
                <div class="toggle-wrap reveal">
                    <div class="pricing-toggle">
                        <button class="active" data-mode="monthly">Monthly</button>
                        <button data-mode="annual">Annual (-20%)</button>
                    </div>
                </div>
            @endif
            
            <div class="pricing-grid">
                @foreach($plans as $index => $plan)
                    @php $featured = $index === 1; @endphp
                    <div class="price-card reveal {{ $featured ? 'featured' : '' }}">
                        @if($featured)<span class="badge">Popular</span>@endif
                        <h3>{{ $plan->name }}</h3>
                        <p class="plan-desc">{{ $plan->notes ?? 'Perfect for growing businesses.' }}</p>
                        <div class="price">
                            <strong data-currency="{{ \App\Models\Setting::get('currency', 'TZS') }}" data-monthly="{{ $plan->amount }}" data-annual="{{ $plan->amount * 12 * 0.8 }}">
                                {{ jv_format_money($plan->amount) }}
                            </strong>
                            <span class="price-period">/{{ $plan->billing_cycle ?? 'mo' }}</span>
                        </div>
                        <a href="/order/hosting" class="btn {{ $featured ? 'btn-light' : 'btn-outline' }} btn-block">
                            {{ $featured ? 'Choose ' . $plan->name : 'Get Started' }}
                        </a>
                    </div>
                @endforeach
                
                @if($plans->isEmpty())
                    <div class="price-card reveal">
                        <h3>Starter</h3>
                        <p class="plan-desc">Add services in the admin panel.</p>
                        <div class="price"><strong data-currency="{{ \App\Models\Setting::get('currency', 'TZS') }}" data-monthly="25000" data-annual="240000">{{ jv_format_money(25000) }}</strong><span>/mo</span></div>
                        <a href="/hosting" class="btn btn-outline btn-block">Get Started</a>
                    </div>
                    <div class="price-card featured reveal">
                        <span class="badge">Popular</span>
                        <h3>Business</h3>
                        <p class="plan-desc">For growing companies.</p>
                        <div class="price"><strong data-currency="{{ \App\Models\Setting::get('currency', 'TZS') }}" data-monthly="50000" data-annual="480000">{{ jv_format_money(50000) }}</strong><span>/mo</span></div>
                        <a href="/hosting" class="btn btn-light btn-block">Choose Business</a>
                    </div>
                    <div class="price-card reveal">
                        <h3>Premium</h3>
                        <p class="plan-desc">Maximum performance.</p>
                        <div class="price"><strong data-currency="{{ \App\Models\Setting::get('currency', 'TZS') }}" data-monthly="100000" data-annual="960000">{{ jv_format_money(100000) }}</strong><span>/mo</span></div>
                        <a href="/hosting" class="btn btn-outline btn-block">Go Premium</a>
                    </div>
                @endif
            </div>
        </div>
    </section>

{{-- PAGE HERO --}}
@elseif($block['type'] === 'page-hero')
    @php
        $phHeight = ($data['height'] ?? 'normal') === 'small' ? '3rem 0' : (($data['height'] ?? '') === 'large' ? '7rem 0' : '5rem 0');
    @endphp
    <section class="page-hero" style="background: {{ $data['bgColor'] ?? '#0F172A' }}; padding: {{ $phHeight }}; text-align: {{ $data['align'] ?? 'center' }};">
        <div class="container">
            @if(($data['showBreadcrumbs'] ?? true) && !request()->is('/'))
                <div class="breadcrumb">
                    <a href="/">Home</a> / <span>{{ $data['title'] ?? $page->title ?? '' }}</span>
                </div>
            @endif
            <h1 style="color: {{ $data['textColor'] ?? '#fff' }};">{{ $data['title'] ?? $page->title ?? '' }}</h1>
            @if(!empty($data['subtitle']))
                <p style="color: rgba(255,255,255,0.8);">{{ $data['subtitle'] }}</p>
            @endif
        </div>
    </section>


{{-- CALL TO ACTION --}}
@elseif($block['type'] === 'cta')
    @php
        $ctaBg = $data['bgColor'] ?? '#6C5CE7';
        $btnStyle = $data['buttonStyle'] ?? 'light';
        
        if ($btnStyle === 'light') {
            $btnClass = 'btn-light';
        } elseif ($btnStyle === 'outline') {
            $btnClass = '';
            $btnInline = 'background: transparent; border: 2px solid rgba(255,255,255,.8); color: #fff;';
        } else {
            $btnClass = 'btn-primary';
        }
    @endphp
    
    <div class="cta reveal" style="background: {{ $ctaBg }};">
        <h2>{{ $data['heading'] ?? 'Ready to launch?' }}</h2>
        <p>{{ $data['text'] ?? '' }}</p>
        @if(!empty($data['buttonText']))
            <a href="{{ $data['buttonLink'] ?? '#' }}" class="btn {{ $btnClass }}" style="{{ $btnInline ?? '' }}">
                {{ $data['buttonText'] }} →
            </a>
        @endif
    </div>

{{-- SPACER --}}
@elseif($block['type'] === 'spacer')
    <div style="height: {{ $data['height'] ?? '40' }}px;"></div>

{{-- IMAGE --}}
@elseif($block['type'] === 'image')
    @if(!empty($data['src']))
        <div style="text-align: {{ $data['align'] ?? 'center' }};">
            <img src="{{ $data['src'] }}" alt="{{ $data['alt'] ?? '' }}" style="width: {{ $data['width'] ?? 'auto' }}; max-width: 100%; border-radius: 8px;">
        </div>
    @endif

{{-- QUOTE --}}
@elseif($block['type'] === 'quote')
    <figure style="margin:0;padding:28px;border-left:4px solid {{ $data['accentColor'] ?? '#2f6f73' }};background:#fff;border-radius:8px;text-align:{{ $data['align'] ?? 'left' }};">
        <blockquote style="margin:0;color:{{ $data['textColor'] ?? '#0f172a' }};font-size:1.25rem;line-height:1.7;font-weight:600;">
            {!! nl2br(e($data['quote'] ?? '')) !!}
        </blockquote>
        @if(!empty($data['author']))
            <figcaption style="margin-top:18px;color:#64748b;">
                <strong style="color:#0f172a;">{{ $data['author'] }}</strong>
                @if(!empty($data['role']))<span> - {{ $data['role'] }}</span>@endif
            </figcaption>
        @endif
    </figure>

{{-- TABS --}}
@elseif($block['type'] === 'tabs')
    @php
        $tabId = 'jv-tabs-' . substr(md5(json_encode($data)), 0, 8);
        $items = is_array($data['items'] ?? null) ? $data['items'] : [];
        if (empty($items)) {
            $items = [
                ['title' => 'Overview', 'content' => 'Explain this section here.'],
                ['title' => 'Details', 'content' => 'Add useful details here.'],
            ];
        }
        $activeColor = $data['activeColor'] ?? '#6C5CE7';
    @endphp
    <div id="{{ $tabId }}" class="jv-builder-tabs" style="--tab-active:{{ $activeColor }};">
        @if(!empty($data['heading']))<h3 style="margin-top:0;">{{ $data['heading'] }}</h3>@endif
        <div style="display:flex;gap:8px;flex-wrap:wrap;border-bottom:1px solid #e2e8f0;margin-bottom:16px;">
            @foreach($items as $i => $item)
                <button type="button" data-tab-index="{{ $i }}" style="border:0;background:{{ $i === 0 ? $activeColor : 'transparent' }};color:{{ $i === 0 ? '#fff' : '#334155' }};padding:10px 14px;border-radius:8px 8px 0 0;font-weight:700;cursor:pointer;">{{ $item['title'] ?? 'Tab' }}</button>
            @endforeach
        </div>
        @foreach($items as $i => $item)
            <div data-tab-panel="{{ $i }}" style="{{ $i === 0 ? '' : 'display:none;' }}line-height:1.7;color:#334155;">{!! nl2br(e($item['content'] ?? '')) !!}</div>
        @endforeach
    </div>
    <script>
        (function(){
            const root = document.getElementById(@json($tabId));
            if (!root || root.dataset.ready) return;
            root.dataset.ready = '1';
            const active = @json($activeColor);
            root.querySelectorAll('[data-tab-index]').forEach(button => {
                button.addEventListener('click', () => {
                    root.querySelectorAll('[data-tab-index]').forEach(btn => { btn.style.background = 'transparent'; btn.style.color = '#334155'; });
                    root.querySelectorAll('[data-tab-panel]').forEach(panel => panel.style.display = 'none');
                    button.style.background = active;
                    button.style.color = '#fff';
                    root.querySelector(`[data-tab-panel="${button.dataset.tabIndex}"]`).style.display = '';
                });
            });
        })();
    </script>

{{-- BUTTON --}}
@elseif($block['type'] === 'button')
    @php
        $btnSizes = ['sm' => '8px 20px; font-size: 0.85rem;', 'md' => '12px 28px; font-size: 0.95rem;', 'lg' => '16px 36px; font-size: 1.1rem;'];
        $btnColors = [
            'primary' => 'background: #6C5CE7; color: white;',
            'outline' => 'background: transparent; border: 2px solid #6C5CE7; color: #6C5CE7;',
            'dark' => 'background: #0f172a; color: white;',
            'white' => 'background: white; color: #0f172a; border: 1px solid #e2e8f0;',
        ];
    @endphp
    <div style="text-align: {{ $data['align'] ?? 'center' }};">
        <a href="{{ $data['link'] ?? '#' }}" style="display: inline-block; border-radius: 8px; font-weight: 600; text-decoration: none; {{ $btnSizes[$data['size'] ?? 'md'] }} {{ $btnColors[$data['style'] ?? 'primary'] }}">
            {{ $data['text'] ?? 'Click' }}
        </a>
    </div>

{{-- SHORTCODE --}}
@elseif($block['type'] === 'shortcode')
    @php echo do_shortcode_rendered($data['shortcode'] ?? ''); @endphp

{{-- DOMAIN SEARCH --}}
@elseif($block['type'] === 'domain-search')
    <div style="max-width: 600px; margin: 0 auto; text-align: center;">
        <h2 style="margin-bottom: 16px;">🔍 Find Your Perfect Domain</h2>
        <form action="/domains" method="GET" style="display: flex; gap: 8px;">
            <input type="text" name="domain" class="form-input" placeholder="Enter domain name..." style="flex: 1; height: 48px;">
            <button class="btn btn-primary" style="height: 48px; padding: 0 24px;">Search</button>
        </form>
    </div>

{{-- ROW / COLUMNS --}}
@elseif($block['type'] === 'row')
    <div style="display: flex; gap: 16px; flex-wrap: wrap;">
        @foreach(($data['columns'] ?? []) as $col)
            <div style="flex: 0 0 {{ $col['width'] ?? '50%' }}; {{ !empty($col['bg']) ? 'background: '.$col['bg'].';' : '' }} padding: 12px; border-radius: 8px; box-sizing: border-box;">
                @foreach(($col['blocks'] ?? []) as $innerBlock)
                    @include('themes.default::blocks.render', ['block' => $innerBlock, 'data' => $innerBlock['data'] ?? []])
                @endforeach
            </div>
        @endforeach
    </div>

{{-- VIDEO --}}
@elseif($block['type'] === 'video')
    @php
        $url = $data['url'] ?? '';
        $embedUrl = $url;
        if (($data['provider'] ?? 'youtube') === 'youtube' && $url) {
            $ytId = null;
            parse_str(parse_url($url, PHP_URL_QUERY) ?? '', $params);
            $ytId = $params['v'] ?? null;
            if (!$ytId) {
                $parts = explode('/', trim($url, '/'));
                $ytId = end($parts);
            }
            $autoplay = !empty($data['autoplay']) ? '?autoplay=1' : '';
            $embedUrl = "https://www.youtube.com/embed/{$ytId}{$autoplay}";
        }
    @endphp
    @if($url)
        <div style="position: relative; padding-bottom: 56.25%; height: 0; overflow: hidden; max-width: 100%;">
            <iframe src="{{ $embedUrl }}" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; border: none; border-radius: 8px;" allowfullscreen></iframe>
        </div>
    @endif
{{-- HERO --}}
@elseif($block['type'] === 'hero')
    @php
        $bgType = $data['bgType'] ?? 'gradient';
        $bgImage = $data['bgImage'] ?? '';
        $overlayOpacity = $data['overlayOpacity'] ?? '0.4';
        $align = $data['align'] ?? 'center';
        $heading = $data['heading'] ?? $data['title'] ?? '';
        $subtitle = $data['subtitle'] ?? $data['text'] ?? '';
        $primaryBtnText = $data['primaryBtnText'] ?? $data['primary_button_text'] ?? '';
        $primaryBtnLink = $data['primaryBtnLink'] ?? $data['primary_button_url'] ?? '/hosting';
        $secondaryBtnText = $data['secondaryBtnText'] ?? $data['secondary_button_text'] ?? '';
        $secondaryBtnLink = $data['secondaryBtnLink'] ?? $data['secondary_button_url'] ?? '/contact';
        
        $heroBg = ($bgType === 'image' && !empty($bgImage))
            ? "url({$bgImage}) center/cover"
            : 'linear-gradient(135deg, #0F172A 0%, #1a1f3a 50%, #2d1b4e 100%)';
        $heroOverlay = ($bgType === 'image') ? "rgba(15,23,42,{$overlayOpacity})" : 'transparent';
        $heroAlign = ($align === 'left') ? 'text-align: left;' : 'text-align: center;';
        $heroHeight = !empty($data['fullHeight']) ? 'min-height: 100vh;' : '';
    @endphp
    
    <section class="hero" style="background: {{ $heroBg }}; {{ $heroHeight }} padding: 6rem 0; position: relative; color: #fff;">
        <div style="position: absolute; inset: 0; background: {{ $heroOverlay }};"></div>
        <div class="container" style="position: relative; text-align: {{ $data['align'] ?? 'center' }}; max-width: 820px;">
            @if(!empty($data['eyebrow']))
                <span class="eyebrow" style="background:rgba(255,255,255,.1);color:#c4b5fd;display:inline-block;margin-bottom:1rem;">
                    {{ $data['eyebrow'] }}
                </span>
            @endif
            
            @if(!empty($heading))
                <h1 style="color:#fff;margin-bottom:1rem;">{!! $heading !!}</h1>
            @endif
            
            @if(!empty($subtitle))
                <p style="color:#cbd5e1;font-size:1.15rem;max-width:600px;margin:0 auto 2rem;">{{ $subtitle }}</p>
            @endif
            
            @if(($data['showDomainSearch'] ?? true) && Route::has('order.domains'))
                <form action="{{ route('order.domains') }}" method="GET" class="domain-search" style="justify-content:{{ $align === 'left' ? 'flex-start' : 'center' }};">
                    <input type="text" name="domain" placeholder="e.g. mybusiness.co.tz" autocomplete="off" />
                    <button type="submit" class="btn btn-primary">Search</button>
                </form>
            @endif
            
            @if(!empty($primaryBtnText) || !empty($secondaryBtnText))
                <div style="display:flex;gap:16px;justify-content:{{ $align === 'left' ? 'flex-start' : 'center' }};flex-wrap:wrap;margin-top:1.5rem;">
                    @if(!empty($primaryBtnText))
                        <a href="{{ $primaryBtnLink }}" class="btn btn-primary">{{ $primaryBtnText }}</a>
                    @endif
                    @if(!empty($secondaryBtnText))
                        <a href="{{ $secondaryBtnLink }}" class="btn" style="background:transparent;border:2px solid rgba(255,255,255,.5);color:#fff;padding:.85rem 1.6rem;border-radius:12px;font-weight:600;text-decoration:none;">{{ $secondaryBtnText }}</a>
                    @endif
                </div>
            @endif
        </div>
    </section>
@endif
