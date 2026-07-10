@php
    $settings = array_merge([
        'height' => 620,
        'radius' => 0,
        'backgroundType' => 'gradient',
        'backgroundColor' => '#0f172a',
        'backgroundGradient' => 'linear-gradient(135deg, #0f172a 0%, #214f54 48%, #7a5cff 100%)',
        'backgroundImage' => '',
        'backgroundPosition' => 'center center',
        'overlay' => 'rgba(15,23,42,.35)',
    ], $banner->settings ?? []);
    $layers = is_array($banner->layers) ? $banner->layers : [];
    $domId = 'jv-banner-' . $banner->id . '-' . preg_replace('/[^a-z0-9_-]/i', '', $banner->slug);
    $background = ($settings['backgroundType'] ?? 'gradient') === 'image' && !empty($settings['backgroundImage'])
        ? "url('" . e($settings['backgroundImage']) . "')"
        : (($settings['backgroundType'] ?? 'gradient') === 'color' ? 'none' : ($settings['backgroundGradient'] ?? 'none'));
@endphp
<section id="{{ $domId }}" class="jv-banner-studio" style="--jvb-height:{{ (int) $settings['height'] }}px;--jvb-radius:{{ (int) $settings['radius'] }}px;--jvb-bg-color:{{ $settings['backgroundColor'] }};--jvb-bg-image:{!! $background !!};--jvb-bg-position:{{ $settings['backgroundPosition'] }};--jvb-overlay:{{ $settings['overlay'] }};">
    <div class="jv-banner-studio__overlay"></div>
    @foreach($layers as $layer)
        @php
            $style = $layer['style'] ?? [];
            $type = $layer['type'] ?? 'text';
            $css = sprintf(
                'left:%s%%;top:%s%%;width:%s%%;height:%s%%;font-size:%spx;color:%s;font-weight:%s;text-align:%s;justify-content:%s;background:%s;border-radius:%spx;letter-spacing:%spx;',
                (float) ($layer['x'] ?? 0),
                (float) ($layer['y'] ?? 0),
                (float) ($layer['width'] ?? 20),
                (float) ($layer['height'] ?? 8),
                (float) ($style['fontSize'] ?? 18),
                $style['color'] ?? '#fff',
                $style['fontWeight'] ?? 500,
                $style['align'] ?? 'left',
                ($style['align'] ?? 'left') === 'center' ? 'center' : (($style['align'] ?? 'left') === 'right' ? 'flex-end' : 'flex-start'),
                in_array($type, ['button', 'shape'], true) ? ($style['background'] ?? ($type === 'button' ? '#fff' : 'rgba(255,255,255,.16)')) : 'transparent',
                (float) ($style['radius'] ?? 0),
                (float) ($style['letterSpacing'] ?? 0)
            );
        @endphp
        @if($type === 'button')
            <a class="jv-banner-studio__layer" data-type="button" href="{{ $layer['link'] ?? '#' }}" target="{{ $layer['target'] ?? '_self' }}" style="{{ $css }}">{{ $layer['content'] ?? 'Button' }}</a>
        @else
            <div class="jv-banner-studio__layer" data-type="{{ $type }}" style="{{ $css }}">
                @if($type === 'image')
                    <img src="{{ $layer['src'] ?? $layer['content'] ?? '' }}" alt="{{ $layer['alt'] ?? '' }}">
                @elseif($type === 'domain-search')
                    <form action="{{ Route::has('order.domains') ? route('order.domains') : url('/domains') }}" method="GET">
                        <input name="domain" placeholder="{{ $layer['content'] ?? 'Search domain...' }}">
                        <button>Search</button>
                    </form>
                @else
                    {!! nl2br(e($layer['content'] ?? '')) !!}
                @endif
            </div>
        @endif
    @endforeach
</section>
<style>
#{{ $domId }}.jv-banner-studio{position:relative;min-height:var(--jvb-height);border-radius:var(--jvb-radius);overflow:hidden;background-color:var(--jvb-bg-color);background-image:var(--jvb-bg-image);background-position:var(--jvb-bg-position);background-size:cover;isolation:isolate}
#{{ $domId }} .jv-banner-studio__overlay{position:absolute;inset:0;background:var(--jvb-overlay);z-index:0}
#{{ $domId }} .jv-banner-studio__layer{position:absolute;z-index:1;display:flex;align-items:center;padding:4px;text-decoration:none;overflow:hidden}
#{{ $domId }} .jv-banner-studio__layer img{width:100%;height:100%;object-fit:contain;display:block}
#{{ $domId }} .jv-banner-studio__layer[data-type="domain-search"] form{width:100%;height:100%;display:flex}
#{{ $domId }} .jv-banner-studio__layer[data-type="domain-search"] input{flex:1;border:0;border-radius:8px 0 0 8px;padding:0 14px}
#{{ $domId }} .jv-banner-studio__layer[data-type="domain-search"] button{border:0;border-radius:0 8px 8px 0;background:#7a5cff;color:#fff;font-weight:800;padding:0 18px}
@media(max-width:760px){#{{ $domId }}.jv-banner-studio{min-height:min(var(--jvb-height),560px)}#{{ $domId }} .jv-banner-studio__layer{transform:scale(.82);transform-origin:left top}}
</style>
