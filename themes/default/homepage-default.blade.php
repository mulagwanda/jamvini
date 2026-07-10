{{-- Hero --}}
<section class="hero">
  <div class="container hero-inner">
    <span class="eyebrow" style="background:rgba(255,255,255,.1);color:#c4b5fd">⚡ Trusted by businesses across Tanzania</span>
    <h1>{!! jv_theme_setting('homepage_hero_title', 'Hosting that <span>actually flies.</span>') !!}</h1>
    <p>{{ jv_theme_setting('homepage_hero_subtitle', 'SSD-powered servers, free SSL & domain, and a 99.9% uptime guarantee.') }}</p>
    {{-- Mode Toggle --}}
<div style="display: flex; justify-content: center; gap: 8px; margin-bottom: 20px;">
    <label style="cursor: pointer; padding: 10px 24px; border-radius: 999px; font-size: 0.88rem; font-weight: 600; background: #6C5CE7; color: #fff; border: 2px solid #6C5CE7; transition: all .2s;" class="search-mode">
        <input type="radio" name="type" value="register" checked style="display: none;" onchange="updateSearchMode(this)"> 🟢 Register Domain
    </label>
    <label style="cursor: pointer; padding: 10px 24px; border-radius: 999px; font-size: 0.88rem; font-weight: 600; background: transparent; color: #c4b5fd; border: 2px solid rgba(255,255,255,.2); transition: all .2s;" class="search-mode">
        <input type="radio" name="type" value="transfer" style="display: none;" onchange="updateSearchMode(this)"> 🔄 Transfer Domain
    </label>
</div>

{{-- Search Bar --}}
<form action="/domains" method="GET" class="domain-search">
    <input type="hidden" name="type" id="heroSearchType" value="register">
    <input type="text" name="domain" placeholder="e.g. mybusiness.co.tz" autocomplete="off" />
    <button type="submit" class="btn btn-primary">Search</button>
</form>

<script>
function updateSearchMode(radio) {
    document.querySelectorAll('.search-mode').forEach(label => {
        label.style.background = 'transparent';
        label.style.color = '#c4b5fd';
        label.style.border = '2px solid rgba(255,255,255,.2)';
    });
    const active = radio.closest('.search-mode');
    active.style.background = '#6C5CE7';
    active.style.color = '#fff';
    active.style.border = '2px solid #6C5CE7';
    const typeInput = document.getElementById('heroSearchType');
    if (typeInput) typeInput.value = radio.value;
}
</script>
  </div>
</section>

{{-- Features --}}
@if(jv_theme_setting('homepage_show_features', '1') === '1')
<section class="features">
  <div class="container">
    <div class="section-head reveal">
      <span class="eyebrow">Why Choose Us</span>
      <h2>Built for speed and scale</h2>
      <p>Everything you need to launch your next big idea, backed by a team that genuinely cares.</p>
    </div>
    <div class="features-grid">
      <div class="feature-card reveal"><div class="feature-icon">⚡</div><h3>Lightning Fast</h3><p>NVMe SSD storage and global CDN deliver sub-second page loads everywhere.</p></div>
      <div class="feature-card reveal"><div class="feature-icon">🔒</div><h3>Bulletproof Security</h3><p>Free SSL, automated backups, malware scanning and DDoS protection included.</p></div>
      <div class="feature-card reveal"><div class="feature-icon">🚀</div><h3>One-Click Deploy</h3><p>Install WordPress, Laravel, or Node.js apps in seconds — no terminal required.</p></div>
      <div class="feature-card reveal"><div class="feature-icon">💬</div><h3>24/7 Support</h3><p>Real humans, average response under 90 seconds. We've got your back.</p></div>
    </div>
  </div>
</section>
@endif

{{-- Pricing --}}
@if(jv_theme_setting('homepage_show_pricing', '1') === '1' && class_exists(\Plugins\Services\src\Models\Service::class))
<section>
  <div class="container">
    <div class="section-head reveal">
      <span class="eyebrow">Pricing</span>
      <h2>Plans for every stage</h2>
      <p>Start small, scale as you grow. No hidden fees, cancel anytime.</p>
    </div>
    @php 
        $services = \Plugins\Services\src\Models\Service::where('is_active', true)
            ->whereHas('group', fn($q) => $q->where('module', 'hosting'))
            ->get(); 
    @endphp
    @if($services->count() > 0)
    <div class="pricing-grid">
      @foreach($services as $index => $service)
      @php 
        $featured = $index === 1 && $services->count() >= 3;
        $price = $service->pricing['monthly'] ?? $service->pricing['annually'] ?? $service->amount;
        $cycle = $service->billing_cycle ?? 'monthly';
      @endphp
      <div class="price-card reveal {{ $featured ? 'featured' : '' }}">
        @if($featured)<span class="badge">Popular</span>@endif
        <h3>{{ $service->name }}</h3>
        <p class="plan-desc">{{ $service->description ?? 'Perfect for growing businesses.' }}</p>
        <div class="price"><strong>{{ jv_format_money($price) }}</strong><span>/{{ $cycle }}</span></div>
        @if(is_array($service->features) && count($service->features) > 0)
        <ul class="plan-features">
            @foreach($service->features as $feature)
                <li>{{ $feature }}</li>
            @endforeach
        </ul>
        @endif
        <a href="/hosting" class="btn {{ $featured ? 'btn-light' : 'btn-outline' }} btn-block">Get Started</a>
      </div>
      @endforeach
    </div>
    @else
    <div style="text-align: center; padding: 40px; color: var(--jv-gray-500);">
        <p>No hosting plans configured yet. <a href="/admin/services/create">Add services in the admin panel</a>.</p>
    </div>
    @endif
  </div>
</section>
@endif

{{-- Testimonials --}}
@if(jv_theme_setting('homepage_show_testimonials', '0') === '1')
<section class="testimonials bg-gray">
  <div class="container">
    <div class="section-head reveal">
      <span class="eyebrow">Testimonials</span>
      <h2>What our customers say</h2>
    </div>
    <div class="carousel reveal">
      <div class="carousel-track">
        <div class="testimonial">
          <p>"Switched to {{ \App\Models\Setting::get('company_name', 'JamVini Hosting') }} and saw immediate improvements. Support is incredible."</p>
          <div class="testimonial-author"><div class="avatar">JK</div><strong>Jane Kimaro</strong><span>Founder, TechStart</span></div>
        </div>
        <div class="testimonial">
          <p>"Managing 20+ client sites has never been easier. The client portal saves me hours every week."</p>
          <div class="testimonial-author"><div class="avatar">PM</div><strong>Paul Mushi</strong><span>Web Agency Owner</span></div>
        </div>
      </div>
      <div class="carousel-dots"></div>
    </div>
  </div>
</section>
@endif

{{-- CTA --}}
<div class="cta reveal">
  <h2>Ready to launch?</h2>
  <p>Join businesses already running on {{ \App\Models\Setting::get('company_name', 'JamVini Hosting') }}.</p>
  <a href="/hosting" class="btn btn-light">Start Building Today →</a>
</div>
