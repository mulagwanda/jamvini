@extends('themes.pulse::layouts.frontend')

@section('title', 'Pulse Hosting')

@section('content')
<section class="pulse-hero pulse-hero-pro">
    <div class="pulse-container pulse-hero-pro-grid">
        <div class="pulse-hero-copy">
            <span class="eyebrow">JamVini Pulse Pro</span>
            <h1>Hosting, domains, billing, and support in one confident storefront.</h1>
            <p>Pulse is shaped for serious hosting companies: direct buying paths, premium service cards, client access, and banner-ready sections that work with JV Builder.</p>
            <div class="actions">
                <a class="btn btn-light btn-lg" href="{{ url('/services') }}">Explore Services</a>
                <a class="btn btn-ghost btn-lg" href="{{ url('/contact') }}">Talk to Sales</a>
            </div>
            <form action="{{ Route::has('order.domains') ? route('order.domains') : url('/domains') }}" method="GET" class="pulse-hero-search">
                <input type="text" name="domain" placeholder="Search your perfect domain">
                <button type="submit">Search</button>
            </form>
        </div>
        <div class="pulse-command-panel" aria-hidden="true">
            <div class="pulse-command-top">
                <span></span><span></span><span></span>
            </div>
            <div class="pulse-command-row is-live">
                <strong>Provisioning</strong>
                <em>Live</em>
            </div>
            <div class="pulse-command-meter"><span style="width: 82%"></span></div>
            <div class="pulse-command-grid">
                <div><b>99.9%</b><small>Uptime</small></div>
                <div><b>2.4s</b><small>Avg. setup</small></div>
                <div><b>.tz</b><small>Ready</small></div>
                <div><b>24/7</b><small>Support</small></div>
            </div>
        </div>
    </div>
</section>

<section class="pulse-section pulse-services-band">
    <div class="pulse-container">
        <div class="pulse-section-heading">
            <span class="eyebrow">Built for hosting teams</span>
            <h2>Sell the products clients already understand.</h2>
            <p>Use this theme as the polished storefront while JamVini handles ordering, invoices, services, domains, and support behind the scenes.</p>
        </div>
        <div class="pulse-service-grid">
            <article class="pulse-card pulse-service-card">
                <span>{{ jv_icon('server', '', 22) }}</span>
                <h3>Web Hosting</h3>
                <p>Present shared hosting, reseller packages, and panel automation with clear calls to action.</p>
            </article>
            <article class="pulse-card pulse-service-card">
                <span>{{ jv_icon('globe', '', 22) }}</span>
                <h3>Domains</h3>
                <p>Support domain search, .tz positioning, transfers, renewals, and registrar-driven workflows.</p>
            </article>
            <article class="pulse-card pulse-service-card">
                <span>{{ jv_icon('headphones', '', 22) }}</span>
                <h3>Client Care</h3>
                <p>Give visitors confidence that billing, tickets, and account history live in one calm portal.</p>
            </article>
        </div>
    </div>
</section>
@endsection
