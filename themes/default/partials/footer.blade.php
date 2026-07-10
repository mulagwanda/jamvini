<footer class="footer">
  <div class="container">
    <div class="footer-grid">
      <div class="footer-brand">
        <a href="/" class="logo">
          <span class="logo-mark">{{ substr(\App\Models\Setting::get('company_name', 'J'), 0, 1) }}</span> 
          {{ \App\Models\Setting::get('company_name', 'JamVini Hosting') }}
        </a>
        <p>{{ \App\Models\Setting::get('site_tagline', 'Premium hosting that helps you ship faster.') }}</p>
        <div class="socials">
          <a href="#" aria-label="Twitter">𝕏</a>
          <a href="#" aria-label="Facebook">f</a>
          <a href="#" aria-label="Instagram">◉</a>
          <a href="#" aria-label="LinkedIn">in</a>
        </div>
      </div>
      <div>
        <h4>Product</h4>
        @php
          $productMenu = jv_menu_items('footer_product', [
              ['label' => 'Web Hosting', 'url' => '/hosting'],
              ['label' => 'Domains', 'url' => '/domains'],
              ['label' => 'SSL', 'url' => '/hosting'],
          ]);
        @endphp
        <ul>
          @foreach($productMenu as $item)
            <li><a href="{{ $item['url'] }}" target="{{ $item['target'] }}">{{ $item['label'] }}</a></li>
          @endforeach
        </ul>
      </div>
      <div>
        <h4>Company</h4>
        @php
          $companyMenu = jv_menu_items('footer_company', [
              ['label' => 'About', 'url' => '/about'],
              ['label' => 'Blog', 'url' => '/blog'],
              ['label' => 'Contact', 'url' => '/contact'],
          ]);
        @endphp
        <ul>
          @foreach($companyMenu as $item)
            <li><a href="{{ $item['url'] }}" target="{{ $item['target'] }}">{{ $item['label'] }}</a></li>
          @endforeach
        </ul>
      </div>
      <div>
        <h4>Support</h4>
        @php
          $supportMenu = jv_menu_items('footer_support', [
              ['label' => 'Client Area', 'url' => '/client/dashboard'],
              ['label' => 'Help Center', 'url' => '/contact'],
              ['label' => 'Status', 'url' => '#'],
          ]);
        @endphp
        <ul>
          @foreach($supportMenu as $item)
            <li><a href="{{ $item['url'] }}" target="{{ $item['target'] }}">{{ $item['label'] }}</a></li>
          @endforeach
        </ul>
      </div>
    </div>
    <div class="copyright">
      {{ jv_theme_setting('footer_text', '© ' . date('Y') . ' ' . \App\Models\Setting::get('company_name', 'JamVini Hosting') . '. All rights reserved.') }}
      @if(jv_theme_setting('show_powered_by', '1'))
        <span style="display:inline-block;margin-left:8px;">Powered by JamVini.</span>
      @endif
    </div>
  </div>
</footer>
