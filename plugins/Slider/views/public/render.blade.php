@if($slider->activeSlides->count() > 0)
@php
    $settings = array_merge([
        'autoplay' => true,
        'pause_on_hover' => true,
        'navigation' => true,
        'pagination' => true,
        'keyboard' => true,
        'loop' => true,
        'speed' => 700,
        'delay' => 5500,
        'effect' => 'fade',
        'height' => 620,
        'radius' => 0,
        'theme' => 'dark',
    ], $slider->settings ?? []);
    $domId = 'jv-slider-' . $slider->id . '-' . preg_replace('/[^a-z0-9_-]/i', '', $slider->slug);
@endphp

<section
    class="jv-slider jv-slider-{{ $settings['effect'] }}"
    id="{{ $domId }}"
    data-autoplay="{{ !empty($settings['autoplay']) ? '1' : '0' }}"
    data-pause-on-hover="{{ !empty($settings['pause_on_hover']) ? '1' : '0' }}"
    data-keyboard="{{ !empty($settings['keyboard']) ? '1' : '0' }}"
    data-loop="{{ !empty($settings['loop']) ? '1' : '0' }}"
    data-speed="{{ (int) $settings['speed'] }}"
    data-delay="{{ (int) $settings['delay'] }}"
    style="--jvs-height: {{ (int) $settings['height'] }}px; --jvs-radius: {{ (int) $settings['radius'] }}px;"
>
    <div class="jv-slider-track">
        @foreach($slider->activeSlides as $slide)
            @php
                $align = $slide->alignment ?: 'center';
                $justify = $align === 'right' ? 'flex-end' : ($align === 'center' ? 'center' : 'flex-start');
                $textAlign = $align;
                $overlay = $slide->overlay_color ?: 'rgba(15,23,42,.58)';
                $textColor = $slide->text_color ?: '#ffffff';
                $contentWidth = $slide->content_width ?: '720px';
                $position = $slide->background_position ?: 'center center';
                $animation = $slide->animation ?: 'fade-up';
                $layers = is_array($slide->layers) ? $slide->layers : [];
            @endphp
            <article
                class="jv-slide {{ $loop->first ? 'is-active' : '' }}"
                data-animation="{{ $animation }}"
                style="@if($slide->image) background-image:url('{{ $slide->image }}'); @endif background-position:{{ $position }};"
            >
                <div class="jv-slide-overlay" style="background:{{ $overlay }};"></div>
                @if(count($layers))
                    <div class="jv-layer-stage">
                        @foreach($layers as $layer)
                            @php
                                $style = $layer['style'] ?? [];
                                $type = $layer['type'] ?? 'text';
                                $layerCss = collect([
                                    'left:' . (float) ($layer['x'] ?? 0) . '%',
                                    'top:' . (float) ($layer['y'] ?? 0) . '%',
                                    'width:' . (float) ($layer['width'] ?? 30) . '%',
                                    'height:' . (float) ($layer['height'] ?? 10) . '%',
                                    'font-size:' . (int) ($style['fontSize'] ?? 18) . 'px',
                                    'color:' . ($style['color'] ?? '#ffffff'),
                                    'font-weight:' . ($style['fontWeight'] ?? 400),
                                    'text-align:' . ($style['align'] ?? 'left'),
                                    'border-radius:' . (int) ($style['radius'] ?? 0) . 'px',
                                    in_array($type, ['button', 'shape'], true) ? 'background:' . ($style['background'] ?? ($type === 'button' ? '#ffffff' : 'rgba(255,255,255,.18)')) : null,
                                    'justify-content:' . (($style['align'] ?? 'left') === 'right' ? 'flex-end' : (($style['align'] ?? 'left') === 'center' ? 'center' : 'flex-start')),
                                ])->filter()->implode(';');
                            @endphp
                            @if($type === 'button')
                                <a class="jv-slide-layer jv-slide-layer-button" href="{{ $layer['link'] ?? '#' }}" target="{{ $layer['target'] ?? '_self' }}" style="{{ $layerCss }}">{{ $layer['content'] ?? 'Button' }}</a>
                            @elseif($type === 'image')
                                <div class="jv-slide-layer jv-slide-layer-image" style="{{ $layerCss }}">
                                    @if(!empty($layer['src']) || !empty($layer['content']))
                                        <img src="{{ $layer['src'] ?? $layer['content'] }}" alt="{{ $layer['alt'] ?? '' }}">
                                    @endif
                                </div>
                            @elseif($type === 'shape')
                                <div class="jv-slide-layer jv-slide-layer-shape" style="{{ $layerCss }}"></div>
                            @else
                                <div class="jv-slide-layer" style="{{ $layerCss }}">{{ $layer['content'] ?? '' }}</div>
                            @endif
                        @endforeach
                    </div>
                @else
                    <div class="jv-slide-inner" style="justify-content:{{ $justify }}; text-align:{{ $textAlign }};">
                        <div class="jv-slide-content" style="max-width:{{ $contentWidth }}; color:{{ $textColor }};">
                            @if($slide->subtitle)<p class="jv-slide-kicker">{{ $slide->subtitle }}</p>@endif
                            @if($slide->title)<h2>{{ $slide->title }}</h2>@endif
                            @if($slide->description)<p class="jv-slide-text">{{ $slide->description }}</p>@endif
                            @if($slide->button_text || $slide->button2_text)
                                <div class="jv-slide-actions" style="justify-content:{{ $justify }};">
                                    @if($slide->button_text)
                                        <a href="{{ $slide->button_link ?: '#' }}" class="jv-slide-btn jv-slide-btn-primary">{{ $slide->button_text }}</a>
                                    @endif
                                    @if($slide->button2_text)
                                        <a href="{{ $slide->button2_link ?: '#' }}" class="jv-slide-btn jv-slide-btn-secondary">{{ $slide->button2_text }}</a>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>
                @endif
            </article>
        @endforeach
    </div>

    @if(!empty($settings['navigation']) && $slider->activeSlides->count() > 1)
        <button class="jv-slider-nav jv-slider-prev" type="button" aria-label="Previous slide">
            <span>&lsaquo;</span>
        </button>
        <button class="jv-slider-nav jv-slider-next" type="button" aria-label="Next slide">
            <span>&rsaquo;</span>
        </button>
    @endif

    @if(!empty($settings['pagination']) && $slider->activeSlides->count() > 1)
        <div class="jv-slider-dots" role="tablist" aria-label="{{ $slider->title }} slides">
            @foreach($slider->activeSlides as $slide)
                <button class="{{ $loop->first ? 'is-active' : '' }}" type="button" data-slide="{{ $loop->index }}" aria-label="Go to slide {{ $loop->iteration }}"></button>
            @endforeach
        </div>
    @endif
</section>

@once
<style>
.jv-slider { position:relative; min-height:var(--jvs-height); overflow:hidden; border-radius:var(--jvs-radius); background:#0f172a; isolation:isolate; }
.jv-slider-track { position:relative; min-height:var(--jvs-height); }
.jv-slide { position:absolute; inset:0; min-height:var(--jvs-height); background:linear-gradient(135deg,#0f172a,#2563eb); background-size:cover; opacity:0; visibility:hidden; transform:scale(1.015); transition:opacity .7s ease, visibility .7s ease, transform .9s ease; }
.jv-slider-slide .jv-slide { transform:translateX(4%); }
.jv-slide.is-active { opacity:1; visibility:visible; transform:scale(1) translateX(0); z-index:2; }
.jv-slide-overlay { position:absolute; inset:0; z-index:1; }
.jv-slide-inner { position:relative; z-index:2; width:min(1180px,calc(100% - 40px)); min-height:var(--jvs-height); margin:0 auto; display:flex; align-items:center; padding:72px 0; }
.jv-slide-content { opacity:0; transform:translateY(24px); transition:opacity .65s ease .18s, transform .65s ease .18s; }
.jv-slide.is-active .jv-slide-content { opacity:1; transform:translateY(0); }
.jv-slide[data-animation="fade"] .jv-slide-content { transform:none; }
.jv-slide[data-animation="slide-left"] .jv-slide-content { transform:translateX(-28px); }
.jv-slide[data-animation="zoom"] .jv-slide-content { transform:scale(.96); }
.jv-slide.is-active[data-animation="slide-left"] .jv-slide-content,
.jv-slide.is-active[data-animation="zoom"] .jv-slide-content { transform:none; }
.jv-slide-kicker { margin:0 0 12px; text-transform:uppercase; font-weight:800; font-size:.78rem; letter-spacing:0; color:inherit; opacity:.86; }
.jv-slide h2 { margin:0; color:inherit; font-size:clamp(2.2rem,5vw,5.25rem); line-height:.98; letter-spacing:0; }
.jv-slide-text { margin:20px 0 0; color:inherit; opacity:.88; font-size:clamp(1rem,2vw,1.24rem); line-height:1.75; }
.jv-slide-actions { display:flex; align-items:center; gap:12px; flex-wrap:wrap; margin-top:28px; }
.jv-slide-btn { min-height:46px; display:inline-flex; align-items:center; justify-content:center; padding:0 20px; border-radius:12px; font-weight:800; text-decoration:none; }
.jv-slide-btn-primary { background:#fff; color:#0f172a; box-shadow:0 16px 40px rgba(0,0,0,.22); }
.jv-slide-btn-secondary { border:1px solid currentColor; color:inherit; background:rgba(255,255,255,.08); backdrop-filter:blur(10px); }
.jv-layer-stage { position:relative; z-index:2; width:min(1180px,calc(100% - 40px)); min-height:var(--jvs-height); margin:0 auto; }
.jv-slide-layer { position:absolute; z-index:2; display:flex; align-items:center; padding:4px; text-decoration:none; line-height:1.1; opacity:0; transform:translateY(18px); transition:opacity .65s ease .18s, transform .65s ease .18s; }
.jv-slide.is-active .jv-slide-layer { opacity:1; transform:none; }
.jv-slide-layer-button { box-shadow:0 16px 40px rgba(0,0,0,.22); }
.jv-slide-layer-image { overflow:hidden; padding:0; }
.jv-slide-layer-image img { width:100%; height:100%; object-fit:cover; display:block; border-radius:inherit; }
.jv-slider-nav { position:absolute; top:50%; transform:translateY(-50%); z-index:5; width:46px; height:46px; border:1px solid rgba(255,255,255,.24); border-radius:999px; background:rgba(15,23,42,.38); color:#fff; display:grid; place-items:center; cursor:pointer; backdrop-filter:blur(12px); }
.jv-slider-nav span { font-size:2rem; line-height:1; margin-top:-2px; }
.jv-slider-prev { left:20px; }
.jv-slider-next { right:20px; }
.jv-slider-dots { position:absolute; left:50%; bottom:22px; transform:translateX(-50%); z-index:5; display:flex; gap:9px; padding:8px 10px; border-radius:999px; background:rgba(15,23,42,.28); backdrop-filter:blur(12px); }
.jv-slider-dots button { width:9px; height:9px; padding:0; border:0; border-radius:999px; background:rgba(255,255,255,.48); cursor:pointer; transition:width .2s ease, background .2s ease; }
.jv-slider-dots button.is-active { width:26px; background:#fff; }
@media (max-width:760px) {
    .jv-slider, .jv-slider-track, .jv-slide, .jv-slide-inner { min-height:min(var(--jvs-height), 560px); }
    .jv-slide-inner { padding:58px 0 78px; }
    .jv-slider-nav { display:none; }
}
</style>
<script>
document.addEventListener("DOMContentLoaded", () => {
    document.querySelectorAll(".jv-slider").forEach((slider) => {
        const slides = Array.from(slider.querySelectorAll(".jv-slide"));
        const dots = Array.from(slider.querySelectorAll(".jv-slider-dots button"));
        const prev = slider.querySelector(".jv-slider-prev");
        const next = slider.querySelector(".jv-slider-next");
        const loop = slider.dataset.loop === "1";
        const autoplay = slider.dataset.autoplay === "1";
        const pauseOnHover = slider.dataset.pauseOnHover === "1";
        const keyboard = slider.dataset.keyboard === "1";
        const delay = Number(slider.dataset.delay || 5500);
        let index = Math.max(0, slides.findIndex((slide) => slide.classList.contains("is-active")));
        let timer = null;

        const show = (target) => {
            if (!slides.length) return;
            if (target < 0) target = loop ? slides.length - 1 : 0;
            if (target >= slides.length) target = loop ? 0 : slides.length - 1;
            index = target;
            slides.forEach((slide, slideIndex) => slide.classList.toggle("is-active", slideIndex === index));
            dots.forEach((dot, dotIndex) => dot.classList.toggle("is-active", dotIndex === index));
        };

        const stop = () => { if (timer) window.clearInterval(timer); timer = null; };
        const start = () => {
            stop();
            if (autoplay && slides.length > 1) timer = window.setInterval(() => show(index + 1), delay);
        };

        prev?.addEventListener("click", () => { show(index - 1); start(); });
        next?.addEventListener("click", () => { show(index + 1); start(); });
        dots.forEach((dot) => dot.addEventListener("click", () => { show(Number(dot.dataset.slide)); start(); }));
        if (pauseOnHover) {
            slider.addEventListener("mouseenter", stop);
            slider.addEventListener("mouseleave", start);
        }
        if (keyboard) {
            slider.tabIndex = 0;
            slider.addEventListener("keydown", (event) => {
                if (event.key === "ArrowLeft") { show(index - 1); start(); }
                if (event.key === "ArrowRight") { show(index + 1); start(); }
            });
        }
        show(index);
        start();
    });
});
</script>
@endonce
@endif
