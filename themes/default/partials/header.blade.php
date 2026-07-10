<header class="header">
  <div class="container nav">
    <a href="/" class="logo">
      @if(jv_theme_setting('show_logo', '1') && jv_theme_setting('logo_url'))
        <img src="{{ asset('storage/' . jv_theme_setting('logo_url')) }}" alt="{{ \App\Models\Setting::get('company_name', 'JamVini Hosting') }}" style="max-height: 38px; width: auto;">
      @else
        <span class="logo-mark">{{ substr(\App\Models\Setting::get('company_name', 'J'), 0, 1) }}</span>
        {{ \App\Models\Setting::get('company_name', 'JamVini Hosting') }}
      @endif
    </a>
    
    @php
      $primaryMenu = jv_menu_items('primary', [
          ['label' => 'Home', 'url' => '/'],
          ['label' => 'About', 'url' => '/about'],
          ['label' => 'Hosting', 'url' => '/hosting'],
          ['label' => 'Domains', 'url' => '/domains'],
          ['label' => 'Blog', 'url' => '/blog'],
          ['label' => 'Contact', 'url' => '/contact'],
      ]);
    @endphp
    <nav class="nav-links">
      @include('themes.default::partials.menu-items', ['items' => $primaryMenu])
    </nav>

    <div class="nav-cta">
      <a href="/cart" style="position: relative; margin-right: 4px;">
          🛒
          @php $cartCount = app(\Plugins\Ordering\src\Services\CartService::class)->count(); @endphp
          @if($cartCount > 0)
              <span style="position: absolute; top: -8px; right: -10px; background: var(--primary); color: #fff; font-size: 0.65rem; width: 18px; height: 18px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 600;">{{ $cartCount }}</span>
          @endif
      </a>
      @auth
          <a href="/client/dashboard" class="btn btn-outline">Dashboard</a>
      @else
          <a href="/login" class="btn btn-outline">Login</a>
      @endif
      {{-- <a href="/hosting" class="btn btn-primary">Get Started</a>  --}}
      <button class="hamburger" aria-label="Menu"><span></span><span></span><span></span></button>
  </div>
    
  </div>
</header>
