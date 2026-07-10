<section class="kmb-hero">
    <div class="kmb-container kmb-hero-grid">
        <div>
            <p class="kmb-eyebrow">Reliable hosting for growing teams</p>
            <h1>{{ jv_theme_setting('homepage_hero_title', 'Hosting built with care, speed, and local trust.', 'public') }}</h1>
            <p>{{ jv_theme_setting('homepage_hero_subtitle', 'A warm JamVini storefront for companies that sell domains, hosting, support, and service continuity without noise.', 'public') }}</p>
            <div class="kmb-hero-actions">
                <a href="/hosting" class="kmb-btn kmb-btn-primary">Explore Hosting</a>
                <a href="/domains" class="kmb-btn kmb-btn-soft">Search Domains</a>
            </div>
            <div class="kmb-trust-strip">
                <span>{{ jv_icon('badge-check', '', 18) }} Local domain ready</span>
                <span>{{ jv_icon('server', '', 18) }} Panel automation</span>
                <span>{{ jv_icon('headphones', '', 18) }} Client support</span>
            </div>
        </div>
        <div class="kmb-memory-card">
            <span>JamVini stack</span>
            <h2>Domains, hosting, invoices, and tickets.</h2>
            <p>A clear public face for a business that needs every operational detail close at hand.</p>
            <div class="kmb-mini-status">
                <b>Active orders</b><strong>128</strong>
            </div>
        </div>
    </div>
</section>

<section class="kmb-section">
    <div class="kmb-container">
        <div class="kmb-section-head">
            <p class="kmb-eyebrow">Why clients stay</p>
            <h2>Calm design, practical paths, and fewer dead ends.</h2>
        </div>
        <div class="kmb-feature-grid">
            <article>
                <span>01</span>
                <h3>Clear buying paths</h3>
                <p>Hosting, domains, support, and client access stay visible without overwhelming visitors.</p>
            </article>
            <article>
                <span>02</span>
                <h3>Builder ready</h3>
                <p>Pages created with JV Builder still render using the active public theme.</p>
            </article>
            <article>
                <span>03</span>
                <h3>Marketplace friendly</h3>
                <p>Public-only by design, so admin and client portal remain safe on Tanzanite.</p>
            </article>
        </div>
    </div>
</section>

@if(class_exists(\Plugins\Services\src\Models\Service::class))
    @php $services = \Plugins\Services\src\Models\Service::where('is_active', true)->latest()->limit(3)->get(); @endphp
    @if($services->count())
        <section class="kmb-section kmb-section-alt">
            <div class="kmb-container">
                <div class="kmb-section-head">
                    <p class="kmb-eyebrow">Plans</p>
                    <h2>Start with a package that fits today.</h2>
                </div>
                <div class="kmb-plan-grid">
                    @foreach($services as $service)
                        <article class="kmb-plan">
                            <h3>{{ $service->name }}</h3>
                            <p>{{ $service->description ?: 'Reliable hosting powered by JamVini.' }}</p>
                            <strong>{{ jv_format_money($service->base_price ?? $service->price ?? 0) }}</strong>
                            <a href="/cart" class="kmb-btn kmb-btn-soft">Choose Plan</a>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>
    @endif
@endif
