<header class="kmb-header">
    <div class="kmb-container kmb-nav">
        <a href="/" class="kmb-logo">
            @if(jv_theme_setting('show_logo', '1', 'public') && jv_theme_setting('logo_url', null, 'public'))
                <img src="{{ asset('storage/' . jv_theme_setting('logo_url', '', 'public')) }}" alt="{{ \App\Models\Setting::get('company_name', 'JamVini Hosting') }}">
            @else
                <span>{{ substr(\App\Models\Setting::get('company_name', 'J'), 0, 1) }}</span>
                {{ \App\Models\Setting::get('company_name', 'JamVini Hosting') }}
            @endif
        </a>

        @php
            $primaryMenu = jv_menu_items('primary', [
                ['label' => 'Home', 'url' => '/'],
                ['label' => 'Hosting', 'url' => '/hosting'],
                ['label' => 'Domains', 'url' => '/domains'],
                ['label' => 'Blog', 'url' => '/blog'],
                ['label' => 'Contact', 'url' => '/contact'],
            ]);
        @endphp
        <nav class="kmb-menu" id="kmbMenu">
            @foreach($primaryMenu as $item)
                <a href="{{ $item['url'] }}" target="{{ $item['target'] ?? '_self' }}">{{ $item['label'] }}</a>
            @endforeach
        </nav>

        <div class="kmb-actions">
            <a href="/cart" class="kmb-cart" aria-label="Cart">
                Cart
                @if(class_exists(\Plugins\Ordering\src\Services\CartService::class))
                    @php $cartCount = app(\Plugins\Ordering\src\Services\CartService::class)->count(); @endphp
                    @if($cartCount > 0)<strong>{{ $cartCount }}</strong>@endif
                @endif
            </a>
            @auth
                <a href="/client/dashboard" class="kmb-btn kmb-btn-ghost">Dashboard</a>
            @else
                <a href="/login" class="kmb-btn kmb-btn-ghost">Login</a>
            @endif
            <button class="kmb-menu-toggle" type="button" data-kmb-menu-toggle aria-label="Open menu">Menu</button>
        </div>
    </div>
</header>
